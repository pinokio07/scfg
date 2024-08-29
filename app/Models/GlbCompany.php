<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlbCompany extends Model
{
    use HasFactory;
    protected $table = 'tps_glb_companies';
    protected $guarded = ['id'];

    public function getLogo()
    {
      return (!$this->GC_Logo) ? asset('/img/defaultLogo.png') : asset('/img/companies/'.$this->GC_Logo);
    }

    public function branches()
    {
      return $this->hasMany(GlbBranch::class, 'company_id');
    }
}
