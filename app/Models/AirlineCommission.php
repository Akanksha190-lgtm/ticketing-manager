<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirlineCommission extends Model
{
    use HasFactory;
    protected $table = 'sys_commission_list';

    protected $fillable = [
        'airline',
        'code',
        'numeric',
        'au_commission',
        'ex_au_commission',
    ];

    protected $casts = [
        'numeric' => 'integer',
        'au_commission' => 'float',
        'ex_au_commission' => 'float',
    ];

    public function airline()
    {
        return $this->belongsTo(AirlineCommission::class,'airline_id');
    }
}
