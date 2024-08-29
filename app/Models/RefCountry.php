<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefCountry extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_countries';
    protected $guarded = ['id'];
}
