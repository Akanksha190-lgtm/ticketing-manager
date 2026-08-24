<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;
    protected $table = 'routes';

    protected $fillable = [
        'origin',
        'destination',
        'origin_code',
        'destination_code'
    ];
    public function fares()
    {
        return $this->hasMany(Fare::class, 'route_id');
    }

    public function fareCommissionEntries()
    {
        return $this->hasMany(FareCommissionEntries::class, 'route_id');
    }
}
