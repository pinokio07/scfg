<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CeisaToken extends Model
{
    use HasFactory;
    protected $table = 'tps_ceisa_tokens';
    protected $guarded = ['id'];

    public function branch()
    {
      return $this->belongsTo(GlbBranch::class, 'vendor', 'CB_Code');
    }
}
