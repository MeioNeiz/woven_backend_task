<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investor extends Model {
    protected $fillable = ['investor_id', 'name', 'age'];

    public function investments() {
        return $this->hasMany(Investment::class);
    }
}
