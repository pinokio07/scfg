<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class House extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tps_houses';
    protected $guarded = ['id'];
    
    protected $casts = [
      'TGL_TIBA' => 'datetime',
      'ExitDate' => 'date',
      'TGL_BC11' => 'date',
      'TGL_MASTER_BLAWB' => 'date',
      'TGL_HOUSE_BLAWB' => 'date',
      'BC_DATE' => 'datetime',
    ];

    protected $appends = [
      'mawb_parse',
      'fob_cal',
    ];

    public function resolveRouteBinding($encryptedId, $field = null)
    {
        return $this->where('id', Crypt::decrypt ($encryptedId))->firstOrFail();
    }

    public function getMawbParseAttribute()
    {
      $num = str_replace(' ', '', $this->NO_MASTER_BLAWB);
      $first = substr($num, 0, 3);
      $second = substr($num, 3, 11);
      // $third = substr($num, 7, 4);

      return $first .'-'. $second;
    }

    public function getFobCalAttribute()
    {
      return ($this->KD_VAL == 'USD') ? $this->FOB : $this->details->sum('CIF_USD');
    }

    public function getSumCifDtlAttribute()
    {
      return $this->details->sum('CIF_USD');
    }

    public function master()
    {
      return $this->belongsTo(Master::class, 'MasterID');
    }
    
    public function details()
    {
      return $this->hasMany(HouseDetail::class, 'HouseID');
    }

    public function unlocoOrigin()
    {
      return $this->belongsTo(RefUnloco::class, 'KD_PEL_MUAT', 'RL_Code');
    }

    public function unlocoTransit()
    {
      return $this->belongsTo(RefUnloco::class, 'KD_PEL_TRANSIT', 'RL_Code');
    }

    public function unlocoDestination()
    {
      return $this->belongsTo(RefUnloco::class, 'KD_PEL_AKHIR', 'RL_Code');
    }

    public function unlocoBongkar()
    {
      return $this->belongsTo(RefUnloco::class, 'KD_PEL_BONGKAR', 'RL_Code');
    }

    public function customs()
    {
      return $this->belongsTo(RefCustomsOffice::class, 'KD_KANTOR', 'Kdkpbc');
    }

    public function branch()
    {
      return $this->belongsTo(GlbBranch::class, 'BRANCH');
    }

    public function logs()
    {
      return $this->morphMany(TpsLog::class, 'logable');
    }

    public function schemaTariff()
    {
      return $this->belongsTo(Tariff::class, 'tariff_id');
    }

    public function tariff()
    {
      return $this->hasMany(HouseTariff::class, 'house_id');
    }

    public function estimatedTariff()
    {
      return $this->tariff()->estimate()->orderBy('urut');
    }

    public function actualTariff()
    {
      return $this->tariff()->actual()->orderBy('urut');
    }

    public function tegah()
    {
      return $this->hasMany(HouseTegah::class, 'house_id');
    }

    public function activeTegah()
    {
      return $this->tegah()->active();
    }

    public function sppb()
    {
      return $this->hasOne(Sppb::class, 'NO_BL_AWB', 'NO_HOUSE_BLAWB');
    }
    
    public function Schedulelogs()
    {
      return $this->morphMany(SchedulerLog::class, 'logable');
    }

    public function bclog()
    {
      return $this->hasMany(BcLog::class, 'HouseID');  
    }

    public function bclog102()
    {
      return $this->hasOne(BcLog::class, 'HouseID')->where('BC_CODE', 102);
    }

    public function print401()
    {
      return $this->hasOne(BcLog::class, 'HouseID')->where('BC_CODE', 401);
    }

    public function sppbmcp()
    {
      return $this->hasOne(BillingConsolidationSppbmcp::class, 'NO_BARANG', 'NO_BARANG');
    }

    public function notul()
    {
      return $this->hasOne(BillingNotul::class, 'NO_BARANG', 'NO_BARANG');
    }

    public function batch()
    {
      return $this->hasOne(PjtBatch::class, 'HouseID');  
    }

    public function pjt()
    {
      return $this->belongsTo(IdModul::class, 'NO_ID_PEMBERITAHU', 'NPWP');
    }
}
