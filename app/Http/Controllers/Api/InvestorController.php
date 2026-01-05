<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Investor, Investment};
use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class InvestorController extends Controller {
    private InvestmentService $investmentService;

    public function __construct(InvestmentService $investmentService) {
        $this->investmentService = $investmentService;
    }

    public function import(Request $request) {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $file = fopen($request->file('file')->path(), 'r');
        $header = fgetcsv($file);

        $count = 0;
        $errors = [];

        while ($row = fgetcsv($file)) {
            try {
                $data = array_combine($header, $row);

                $investor = Investor::firstOrCreate(
                    ['investor_id' => $data['investor_id']],
                    ['name' => $data['name'], 'age' => (int)$data['age']]
                );

                // Parse DD-MM-YYYY format
                $investmentDate = Carbon::createFromFormat(
                    'd-m-Y',
                    $data['investment_date']
                );

                Investment::create([
                    'investor_id' => $investor->id,
                    'amount' => (float)$data['investment_amount'],
                    'investment_date' => $investmentDate,
                ]);

                $count++;
            } catch (Exception $e) {
                $errors[] = "Row " . ($count + 1) . ": " .
                    $e->getMessage();
            }
        }
        fclose($file);

        return response()->json([
            'imported' => $count,
            'errors' => $errors
        ], 200);
    }

    public function averageAge() {
        return response()->json([
            'average_age' => $this->investmentService
                ->getAverageAge()
        ]);
    }

    public function averageInvestmentAmount() {
        return response()->json([
            'average_investment_amount' => $this->investmentService
                ->getAverageInvestmentAmount()
        ]);
    }

    public function totalInvestments() {
        return response()->json([
            'total_investments' => $this->investmentService
                ->getTotalInvestments()
        ]);
    }

    public function getAllInvestors(Request $request) {
        $perPage = (int)$request->query('per_page', 50);
        $investors = $this->investmentService
            ->getAllInvestors($perPage);

        return response()->json($investors);
    }
}
