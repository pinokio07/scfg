<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sppb extends Model
{
    use HasFactory;
    protected $table = 'tps_sppbdata';
    protected $guarded = ['id'];
    protected $timestamp = false;

    public function house()
    {
      return $this->belongsTo(House::class, 'NO_BL_AWB', 'NO_HOUSE_BLAWB');
    }
}
