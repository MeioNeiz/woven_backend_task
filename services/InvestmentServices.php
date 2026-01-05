<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Investor;

class InvestmentService {
    public function getAverageAge() {
        return Investor::query()
            ->selectRaw('AVG(age) as average_age')
            ->first()
            ->average_age;
    }

    public function getAverageInvestmentAmount() {
        return Investment::query()
            ->selectRaw('AVG(amount) as average_amount')
            ->first()
            ->average_amount;
    }

    public function getTotalInvestments() {
        return Investment::query()->count();
    }

    public function getAllInvestors($perPage = 50) {
        return Investor::query()
            ->select('id', 'investor_id', 'name', 'age')
            ->with('investments:investor_id,amount,investment_date')
            ->paginate($perPage);
    }
}
