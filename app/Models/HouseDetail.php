<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tps_house_items';
    protected $guarded = ['id'];
    protected $casts = [
      'TGL_SKEP' => 'date',
    ];

    public function house()
    {
      return $this->belongsTo(House::class, 'HouseID');
    }

    public function tarifBm()
    {
      return $this->belongsTo(RefTarifBM::class, 'HS_CODE', 'HSCode');
    }

    public function tarifPph()
    {
      return $this->belongsTo(RefTarifPPH::class, 'HS_CODE', 'HSCode');
    }

    public function tarifBmtp()
    {
      return $this->belongsTo(RefTarifBMTP::class, 'HS_CODE', 'HSCode');
    }

    public function logs()
    {
      return $this->morphMany(TpsLog::class, 'logable');
    }
}
