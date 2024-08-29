<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeRes extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_kode_res';
    protected $guarded = ['id'];
}
