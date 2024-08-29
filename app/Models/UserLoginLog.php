<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    use HasFactory;
    protected $table = 'tps_login_logs';
    protected $guarded = ['id'];

    function user()
    {
      return $this->belongsTo(User::class, 'user_id');  
    }
}
