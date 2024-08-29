<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgHeader extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tps_org_header';
    protected $guarded = ['id'];

    public function sales()
    {
      return $this->belongsTo(User::class, 'OH_SalesRep')->withTrashed();
    }
    public function unloco()
    {
      return $this->belongsTo(RefUnloco::class, 'OH_RL_NKClosestPort');
    }

    public function address()
    {
      return $this->hasMany(OrgAddress::class, 'OA_OH', 'id');
    }

    public function mainAddress()
    {
      return $this->address()->where('OA_Type', 'OFC');
    }

    public function taxAddress()
    {
      return $this->address()->where('OA_Type', 'TAX');
    }

    public function hasNpwp()
    {
      if($this->taxAddress()->whereNotNull('OA_TaxID')->exists()){
        return true;
      }
      return false;
    }    

    public function contacts()
    {
      return $this->hasMany(OrgContact::class, 'OC_OH', 'id');
    }    

    public function region()
    {
      return $this->address()->first()->OA_RN_NKCountryCode;
    }
}
