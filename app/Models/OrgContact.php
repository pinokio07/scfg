<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgContact extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tps_org_contacts';
    protected $guarded = ['id'];

    public function organization()
    {
      return $this->belongsTo(OrgHeader::class, 'OC_OH');
    }

    public function address()
    {
      return $this->belongsTo(OrgAddress::class, 'OC_OA_OrgAddress');
    }
}
