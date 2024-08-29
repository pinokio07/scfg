<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PjtBatch extends Model
{
    use HasFactory;
    protected $table = 'tps_pjt_batches';
    protected $guarded = ['id'];

    public function getFile()
    {
      return (!$this->xml) ? '#' : asset('/storage/file/xml').'/'.$this->xml;
    }

    public function house()
    {
      return $this->belongsTo(House::class, 'HouseID')->withTrashed();
    }
}
