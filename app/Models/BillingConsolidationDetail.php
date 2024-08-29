<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingConsolidationDetail extends Model
{
    use HasFactory;
    protected $table = 'tps_billing_konsolidasi_detail';
    protected $guarded = ['id'];
}
