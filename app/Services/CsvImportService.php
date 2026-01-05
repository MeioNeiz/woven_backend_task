<?php

namespace App\Services;

use App\Models\{Investor, Investment};
use Carbon\Carbon;
use SplFileObject;
use Exception;

class CsvImportService {
    public function import(string $filePath) {
        $rows = $this->readCsv($filePath);
        $parsedData = $this->parseData($rows);
        return $this->createInvestorsAndInvestments($parsedData);
    }

    private function readCsv(string $filePath): array {
        $file = new SplFileObject($filePath, 'r');
        $file->setFlags(
            SplFileObject::READ_CSV |
                SplFileObject::SKIP_EMPTY
        );

        $rows = [];
        $header = null;

        foreach ($file as $index => $row) {
            if ($index === 0) {
                $header = $row;
                continue;
            }
            if (empty($row[0])) continue;

            $rows[] = array_combine($header, $row);
        }

        return $rows;
    }

    private function parseData(array $rows): array {
        $parsed = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                if (!is_numeric($row['age'])) {
                    throw new Exception("Age must be numeric");
                }

                if (!is_numeric($row['investment_amount'])) {
                    throw new Exception(
                        "Investment amount must be numeric"
                    );
                }

                $parsed[] = [
                    'investor_id' => trim($row['investor_id']),
                    'name' => trim($row['name']),
                    'age' => (int)$row['age'],
                    'investment_amount' =>
                    (float)$row['investment_amount'],
                    'investment_date' =>
                    $this->parseDate($row['investment_date']),
                ];
            } catch (Exception $e) {
                $errors[] = [
                    'row' => $index + 2,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'data' => $parsed,
            'errors' => $errors
        ];
    }

    private function parseDate(string $dateString): Carbon {
        $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateString);
            } catch (Exception $e) {
                continue;
            }
        }

        throw new Exception(
            "Invalid date format: $dateString"
        );
    }

    private function createInvestorsAndInvestments(
        array $parsedData
    ): array {
        $investorData = [];
        $investmentData = [];

        // Prepare data for batch insert
        foreach ($parsedData['data'] as $item) {
            // Get unique investors
            if (!isset($investorData[$item['investor_id']])) {
                $investorData[$item['investor_id']] = [
                    'investor_id' => $item['investor_id'],
                    'name' => $item['name'],
                    'age' => $item['age'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Collect all investments
            $investmentData[] = [
                'investor_id' => $item['investor_id'],
                'amount' => $item['investment_amount'],
                'investment_date' => $item['investment_date'],
            ];
        }

        // Batch upsert investors
        Investor::upsert(
            array_values($investorData),
            ['investor_id'],
            ['name', 'age']
        );

        // Get investor IDs
        $investors = Investor::whereIn(
            'investor_id',
            array_keys($investorData)
        )->pluck('id', 'investor_id');

        // Map investments to investor IDs
        $investmentsToInsert = [];
        foreach ($investmentData as $inv) {
            $investmentsToInsert[] = [
                'investor_id' => $investors[$inv['investor_id']],
                'amount' => $inv['amount'],
                'investment_date' => $inv['investment_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert investments
        Investment::insert($investmentsToInsert);

        return [
            'imported' => count($investmentData),
            'errors' => $parsedData['errors'],
            'total_errors' => count($parsedData['errors'])
        ];
    }
}
