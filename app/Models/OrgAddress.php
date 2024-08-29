<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgAddress extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tps_org_address';
    protected $guarded = ['id'];

    public function header()
    {
      return $this->belongsTo(OrgHeader::class, 'OA_OH');
    }

    public function country()
    {
      return $this->belongsTo(RefCountry::class, 'OA_RN_NKCountryCode','RN_Code');
    }

    public function contact()
    {
      return $this->hasMany(OrgContact::class, 'id', 'OC_OA_OrgAddress');
    }
    
    public function fullAddress()
    {
      $address = $this->OA_Address1.' '.$this->OA_Address2.' '.$this->OA_City.' '.$this->OA_State.' '.$this->OA_PostCode.' '.$this->country->RN_Desc;

      return $address; //tadi lupa return
    }
}
