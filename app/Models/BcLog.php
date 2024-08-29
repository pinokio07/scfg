<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcLog extends Model
{
    use HasFactory;
    protected $table = 'tps_bc_log';
    protected $primaryKey = 'LogID';
    protected $guarded = ['LogID'];

    protected $casts = [
      'BC_DATE' => 'datetime',
      'SENTON' => 'datetime'
    ];

    public function house()
    {
      return $this->belongsTo(House::class, 'HouseID', 'id');  
    }
}
