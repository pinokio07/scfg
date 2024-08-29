<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseTariff extends Model
{
    use HasFactory;
    protected $table = 'tps_house_tariffs';
    protected $guarded = ['id'];

    public function master()
    {
      return $this->belongsTo(Master::class, 'master_id');
    }

    public function house()
    {
      return $this->belongsTo(House::class, 'house_id');
    }

    public function chargecode()
    {
      return $this->belongsTo(AccChargeCode::class, 'charge_code');  
    }

    public function scopeEstimate($query)
    {
      return $query->where('is_estimate', true);
    }

    public function scopeActual($query)
    {
      return $query->where('is_estimate', false);
    }
}
