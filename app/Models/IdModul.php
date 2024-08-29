<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdModul extends Model
{
    use HasFactory;
    protected $table = 'tps_id_modul';
    
    public function houses()
    {
      return $this->hasMany(House::class, 'NO_ID_PEMBERITAHU', 'NPWP');
    }

    public function masters()
    {
      return $this->hasMany(Master::class, 'NPWP', 'NPWP');
    }
}
