<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Investor, Investment};
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvestorController extends Controller {
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
            } catch (\Exception $e) {
                $errors[] = "Row " . ($count + 1) . ": " . $e->getMessage();
            }
        }
        fclose($file);

        return response()->json([
            'imported' => $count,
            'errors' => $errors
        ], 200);
    }
}
