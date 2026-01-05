<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\{CsvImportService, InvestmentService};
use Illuminate\Http\Request;

class InvestorController extends Controller {
    private CsvImportService $csvImportService;
    private InvestmentService $investmentService;

    public function __construct(
        CsvImportService $csvImportService,
        InvestmentService $investmentService
    ) {
        $this->csvImportService = $csvImportService;
        $this->investmentService = $investmentService;
    }

    public function import(Request $request) {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $result = $this->csvImportService->import($request->file('file')->path());

        $statusCode = $result['total_errors'] > 0 ? 207 : 200;

        return response()->json($result, $statusCode);
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
