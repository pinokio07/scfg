<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingConsolidation extends Model
{
    use HasFactory;
    protected $table = 'tps_billing_konsolidasi';
    protected $primaryKey = 'BillingID';
    protected $guarded = ['BillingID'];
    protected $casts = [
      // 'WK_REKAM' => 'datetime',
      // 'TGL_BILLING' => 'datetime',
      // 'TGL_JT_TEMPO' => 'datetime',
    ];

    public function details()
    {
      return $this->hasMany(BillingConsolidationDetail::class, 'BillingID', 'BillingID');
    }

    public function sppbmcp()
    {
      return $this->hasMany(BillingConsolidationSppbmcp::class, 'BillingID', 'BillingID');
    }

    public function pjt()
    {
      return $this->belongsTo(IdModul::class, 'ID_PEMBERITAHU', 'NPWP');
    }

    public function batch()
    {
      return $this->hasOne(BillingConsolBatch::class, 'fms_bk_id');
    }
}
