<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FareSource extends Model
{
    use HasFactory;
    protected $table = 'fare_sources';

    public function fares()
    {
        return $this->hasMany(Fare::class, 'fare_source_id');
    }
}
