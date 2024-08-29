<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefExchangeRate extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_exchange_rate';
    protected $guarded = ['id'];
    protected $casts = [
      'RE_ReferenceDate' => 'datetime',
    ];

    public function currency()
    {
        return $this->belongsTo(RefCurrency::class, 'RE_RX_NKExCurrency', 'RX_Code');
    }
    
}
