<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefCommodity extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_commodities';
    protected $guarded = ['id'];
}
