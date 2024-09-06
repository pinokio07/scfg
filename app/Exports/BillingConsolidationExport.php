<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\BillingConsolidation;

class BillingConsolidationExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $id;

    function __construct(int $id)
    {
      $this->id = $id;
    }

    public function view(): View
    {
        $billing = BillingConsolidation::with(['sppbmcp.house'])
                                       ->findOrFail($this->id);

        return view('exports.billing-consolidation', compact(['billing']));
    }

    public function title(): string
    {
        return 'RPT_BILLINGKONSOLIDASI';
    }
}
