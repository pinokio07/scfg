<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunningCodeHeader extends Model
{
    use HasFactory;
    protected $table = 'running_code_headers';
    protected $guarded = ['id'];

    public function details()
    {
      return $this->hasMany(RunningCodeDetail::class, 'header_id');
    }

    public function terakhir()
    {
      return $this->details()->orderBy('created_at', 'desc')->first();
    }

    public function bulanIni($month)
    {
      return $this->details()->where('month', $month);
    }

    public function tahunIni($year)
    {
      return $this->details()->where('year', $year);
    }
}
