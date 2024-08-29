<?php

namespace App\Exports;

use App\Models\HouseTegah;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TegahExport implements FromView, ShouldAutoSize
{
    public function view(): view
    {
      
      $items = HouseTegah::with(['house.master.warehouseLine1'])
                            ->where('is_tegah', true)
                            ->get();
      $company = activeCompany();
      $jenis = 'xls';

      return view('exports.tegahexcel', compact(['items', 'company', 'jenis']));

    }
}
