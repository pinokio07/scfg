<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefVessel extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_vessels';
    protected $guarded = ['id'];
}
