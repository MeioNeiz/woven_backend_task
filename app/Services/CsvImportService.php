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
        $created = 0;
        $failed = [];

        foreach ($parsedData['data'] as $item) {
            try {
                $investor = Investor::firstOrCreate(
                    ['investor_id' => $item['investor_id']],
                    ['name' => $item['name'], 'age' => $item['age']]
                );

                Investment::create([
                    'investor_id' => $investor->id,
                    'amount' => $item['investment_amount'],
                    'investment_date' =>
                    $item['investment_date'],
                ]);

                $created++;
            } catch (Exception $e) {
                $failed[] = [
                    'investor_id' => $item['investor_id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'imported' => $created,
            'errors' => array_merge(
                $parsedData['errors'],
                $failed
            ),
            'total_errors' => count(
                $parsedData['errors']
            ) + count($failed)
        ];
    }
}
