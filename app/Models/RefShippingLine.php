<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefShippingLine extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_shipping_lines';
    protected $guarded = ['id'];
}
