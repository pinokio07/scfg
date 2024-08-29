<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlbBranch extends Model
{
    use HasFactory;
    protected $table = 'tps_glb_branches';
    protected $guarded = ['id'];

    public function company()
    {
      return $this->belongsTo(GlbCompany::class, 'company_id');
    }
}
