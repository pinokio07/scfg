<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefPackType extends Model
{
    use HasFactory;
    protected $table = 'tps_ref_packtypes';
    protected $guarded = ['id'];
}
