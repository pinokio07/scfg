<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Master extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tps_master';
    protected $guarded = ['id'];
    protected $casts = [
      'ArrivalDate' => 'date',
    ];

    public function resolveRouteBinding($encryptedId, $field = null)
    {
        return $this->where('id', Crypt::decrypt ($encryptedId))->firstOrFail();
    }

    public function getArrivalsAttribute()
    {
      return ($this->ArrivalDate) ? Carbon::parse($this->ArrivalDate)->format('d-m-Y') .' '. $this->ArrivalTime : '';
    }

    public function getDepartureAttribute()
    {
      return ($this->DepartureDate) ? Carbon::parse($this->DepartureDate)->format('d-m-Y') .' '. $this->DepartureTime : '';
    }

    public function getDateMawbAttribute()
    {
      return ($this->MAWBDate) ? Carbon::parse($this->MAWBDate)->format('d-m-Y') : '';
    }

    public function getDatePuAttribute()
    {
      return ($this->PUDate) ? Carbon::parse($this->PUDate)->format('d-m-Y') : '';
    }

    public function getDateMgAttribute()
    {
      return ($this->MasukGudang) ? Carbon::parse($this->MasukGudang)->format('d-m-Y H:i') : '';
    }

    public function getMawbParseAttribute()
    {
      $num = str_replace(' ', '', $this->MAWBNumber);
      $first = substr($num, 0, 3);
      $second = substr($num, 3, 11);

      return $first .'-'. $second;
    }

    public function getMawbLogAttribute()
    {
      $num = str_replace(' ', '', $this->MAWBNumber);
      $first = substr($num, 0, 3);
      $second = substr($num, 3, 4);
      $third = substr($num, 7, 11);

      return $first.' '.$second.' '.$third;
    }

    public function agency()
    {
        // if($this->houses()->where('NO_BARANG', 'LIKE', 'SF%')->exists()){
        //   $agency = 'KERRY';
        // } elseif($this->houses()->where('NO_BARANG', 'LIKE', 'JK%')->exists()) {
        //   $agency = 'SKYWIN';
        // } else {
        //   $agency = 'JGE';
        // }

        // return $agency;

        return '';
    }

    public function branch()
    {
      return $this->belongsTo(GlbBranch::class, 'mBRANCH');
    }

    public function customs()
    {
      return $this->belongsTo(RefCustomsOffice::class, 'KPBC', 'Kdkpbc');
    }

    public function unlocoOrigin()
    {
      return $this->belongsTo(RefUnloco::class, 'Origin', 'RL_Code');
    }

    public function unlocoTransit()
    {
      return $this->belongsTo(RefUnloco::class, 'Transit', 'RL_Code');
    }

    public function unlocoDestination()
    {
      return $this->belongsTo(RefUnloco::class, 'Destination', 'RL_Code');
    }

    public function warehouseLine1()
    {
      return $this->belongsTo(RefBondedWarehouse::class, 'OriginWarehouse', 'warehouse_code');
    }

    public function houses()
    {
      return $this->hasMany(House::class, 'MasterID');
    }

    public function pjt()
    {
      return $this->hasOne(IdModul::class, 'NPWP', 'NPWP');
    }

    public function partials()
    {
      return $this->hasMany(MasterPartial::class, 'MasterID');
    }

    public function plponline()
    {
      return $this->hasMany(PlpOnline::class, 'master_id');
    }

    public function latestPlp()
    {
      return $this->plponline()->latest();
    }

    public function pendingPlp()
    {
      return $this->plponline()->pending();
    }

    public function pendingAjuPlp()
    {
      return $this->plponline()->pendingAju();
    }

    public function pendingBatalPlp()
    {
      return $this->plponline()->pendingBatal();
    }
    
    public function approvedPlp()
    {
      return $this->plponline()->approved();
    }

    public function approvedBatalPlp()
    {
      return $this->plponline()->ApprovedBatal();
    }

    public function schemaTariff()
    {
      return $this->belongsTo(Tariff::class, 'tariff_id');
    }

    public function tariff()
    {
      return $this->hasMany(HouseTariff::class, 'master_id')->whereNull('house_id');
    }

    public function estimatedTariff()
    {
      return $this->tariff()->estimate()->orderBy('urut');
    }

    public function actualTariff()
    {
      return $this->tariff()->actual()->orderBy('urut');
    }

    public function logs()
    {
      return $this->morphMany(TpsLog::class, 'logable');
    }

    public function Schedulelogs()
    {
      return $this->morphMany(SchedulerLog::class, 'logable');
    }
    
}
