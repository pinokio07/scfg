<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefCurrency extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_currencies';
    protected $guarded = ['id'];
}
