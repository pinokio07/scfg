<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingNotulDetail extends Model
{
    use HasFactory;
    protected $table = 'tps_billing_detail';
    protected $guarded = ['id'];

    public function billing()
    {
      return $this->belongsTo(BillingNotul::class, 'KODE_BILLING', 'KODE_BILLING');  
    }
}
