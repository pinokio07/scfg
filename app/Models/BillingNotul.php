<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingNotul extends Model
{
    use HasFactory;
    protected $table = 'tps_billing';
    protected $guarded = ['id'];

    public function house()
    {
      return $this->belongsTo(House::class, 'NO_BARANG', 'NO_BARANG');  
    }

    public function details()
    {
      return $this->hasMany(BillingNotulDetail::class, 'KODE_BILLING', 'KODE_BILLING');  
    }
}
