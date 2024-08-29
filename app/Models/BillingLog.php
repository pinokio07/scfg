<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingLog extends Model
{
    use HasFactory;
    protected $table = 'tps_billing_log';
    protected $primaryKey = 'LogID';
    protected $guarded = ['LogID'];
}
