<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeDok extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_kode_docs';
    protected $guarded = ['id'];
}
