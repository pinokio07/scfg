<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefBondedWarehouse extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_bonded_warehouses';
    protected $guarded = ['id'];
}
