<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunningCodeDetail extends Model
{
    use HasFactory;
    protected $table = 'running_code_details';
    protected $guarded = ['id'];

    public function runningHeader()
    {
      return $this->belongsTo(RunningCodeHeader::class, 'header_id');
    }

}
