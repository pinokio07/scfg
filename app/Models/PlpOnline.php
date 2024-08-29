<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlpOnline extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'tps_plp_online';
    protected $guarded = ['id'];

    public function master()
    {
      return $this->belongsTo(Master::class, 'master_id');
    }

    public function scopePending($query)
    {
      return $query->where('status', 'Pending');
    }

    public function scopePendingAju($query)
    {
      return $query->where('pengajuan', true)->where('status', 'Pending');
    }

    public function scopePendingBatal($query)
    {
      return $query->where('pembatalan', true)->where('status', 'Pending');
    }

    public function scopeRejected($query)
    {
      return $query->where('FL_SETUJU', 'T');
    }

    public function scopeApproved($query)
    {
      return $query->where('pengajuan', true)->where('status', 'Approved');
    }

    public function scopeApprovedBatal($query)
    {
      return $query->where('pembatalan', true)->where('status', 'Approved');
    }

    public function logs()
    {
      return $this->hasMany(PlpOnlineLog::class, 'plp_id');
    }
}
