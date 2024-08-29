<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseTegah extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tps_bc_tegah';
    protected $guarded = ['id'];

    public function house()
    {
      return $this->belongsTo(House::class, 'house_id');
    }

    public function getMawbParseAttribute()
    {
      $num = str_replace([' ','-'], '', $this->MAWBNumber);
      $first = substr($num, 0, 3);
      $second = substr($num, 3, 11);
      // $third = substr($num, 7, 4);

      return $first .'-'. $second;
    }

    public function scopeActive($query)
    {
      return $query->where('is_tegah', true);
    }
}
