<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FareCommissionEntries extends Model
{
    use HasFactory;

    protected $table = 'fare_and_commission_entries';

    protected $fillable = [
        'airline',
        'airline_code',
        'origin',
        'destination',
        'route_id',
        'cabin_id',
        'source_id',
        'tour_code',
        'pcc_iata_ref',
        'published',
        'currency_id',
        'disc_comm',
        'net',
        'markup',
        'travel_from',
        'travel_to',
        'gross',
        'valid_until',
        'internal_notes',
        'booking_notes',
        'status',
    ];

    protected $casts = [
    'published' => 'float',
    'disc_comm' => 'float',
    'net' => 'float',
    'markup' => 'float',
    'gross' => 'float',
    'valid_until' => 'date',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class,'route_id');
    }

    public function fareSource()
    {
        return $this->belongsTo(FareSource::class, 'source_id');
    }

    public function cabin()
    {
        return $this->belongsTo(Cabin::class, 'cabin_id');
    }

     public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }
}
