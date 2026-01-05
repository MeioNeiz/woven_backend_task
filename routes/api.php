<?php

use App\Http\Controllers\Api\InvestorController;
use Illuminate\Support\Facades\Route;

Route::post('/investors/import', [InvestorController::class, 'import']);
