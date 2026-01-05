<?php

use App\Http\Controllers\Api\InvestorController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::post('/investors/import', [InvestorController::class, 'import']);
    Route::get('/investors/stats/average-age', [InvestorController::class, 'averageAge']);
    Route::get('/investors/stats/average-investment', [InvestorController::class, 'averageInvestmentAmount']);
    Route::get('/investors/stats/total-investments', [InvestorController::class, 'totalInvestments']);
    Route::get('/investors', [InvestorController::class, 'getAllInvestors']);
});
