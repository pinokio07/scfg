<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulerLog extends Model
{
    use HasFactory;
    protected $table = 'tps_scheduler_log';
    protected $guarded = ['id'];

    public function logable()
    {
      return $this->morphTo();
    }
}
