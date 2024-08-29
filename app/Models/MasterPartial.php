<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPartial extends Model
{
    use HasFactory;
    protected $table = 'tps_master_partial';
    protected $guarded = ['PartialID'];
    protected $primaryKey = 'PartialID';
    public $timestamps = false;
    protected $casts = [
      'TGL_TIBA' => 'date',
      'TGL_BC11' => 'date'
    ];

    public function master()
    {
      return $this->belongsTo(Master::class, 'MasterID', 'id');  
    }

    public function houses()
    {
      return $this->hasMany(House::class, 'PartialID');  
    }
}
