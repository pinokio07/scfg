<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tariff extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tps_tariffs';
    protected $guarded = ['id'];

    public function schema()
    {
      return $this->hasMany(TariffSchema::class, 'tariff_id');
    }
}
