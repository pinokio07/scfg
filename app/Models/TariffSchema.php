<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffSchema extends Model
{
    use HasFactory;    
    protected $table = 'tps_tariff_schemas';
    protected $guarded = ['id'];

    public function tariff()
    {
      return $this->belongsTo(Tariff::class, 'tariff_id');
    }
}
