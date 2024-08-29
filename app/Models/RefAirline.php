<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefAirline extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_airlines';
    protected $guarded = ['id'];
}
