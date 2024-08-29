<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class PlpOnlineLog extends Model
{
    use HasFactory;
    protected $table = 'tps_plp_log';
    protected $guarded = ['id'];

    public function user()
    {
      return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function plponline()
    {
      return $this->belongsTo(PlpOnline::class, 'plp_id');
    }

    public function getResParseAttribute()
    {
      preg_match('/<'.$this->Service.'Result>(.*)<\/'.$this->Service.'Result>/', $this->Response, $match);

      return $match[1] ?? "-";
    }
}
