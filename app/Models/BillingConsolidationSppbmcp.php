<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingConsolidationSppbmcp extends Model
{
    use HasFactory;
    protected $table = 'tps_billing_konsolidasi_sppbmcp';
    protected $guarded = ['id'];

    public function billing()
    {
      return $this->belongsTo(BillingConsolidation::class, 'BillingID', 'BillingID');  
    }

    public function billingdetails()
    {
      return $this->hasMany(BillingConsolidationDetail::class, 'BillingID', 'BillingID');
    }

    public function house()
    {
      return $this->belongsTo(House::class, 'NO_BARANG', 'NO_BARANG')->withDefault();  
    }
}
