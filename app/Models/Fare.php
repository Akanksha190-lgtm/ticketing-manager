<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fare extends Model
{
    use HasFactory;
    protected $table = 'fares';

    protected $fillable = [
        'airline',
        'airline_code',
        'origin',
        'destination',
        'cabin_id',
        'fare_source_id',
        'tour_code',
        'pcc_iata_ref',
        'published_fare',
        'currency_id',
        'discount_commission_percent',
        'agency_markup',
        'travel_from',
        'travel_to',
        'valid_until',
        'internal_notes',
        'booking_notes',
    ];

    public function cabin()
    {
        return $this->belongsTo(Cabin::class);
    }

    public function fareSource()
    {
        return $this->belongsTo(FareSource::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
