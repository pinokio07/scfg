<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefContainer extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_containers';
    protected $guarded = ['id'];
}
