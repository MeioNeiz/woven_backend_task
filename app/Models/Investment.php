<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model {
    protected $fillable = ['investor_id', 'amount', 'investment_date'];

    public function investor() {
        return $this->belongsTo(Investor::class);
    }
}
