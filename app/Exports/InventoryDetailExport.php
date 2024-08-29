<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Master;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class InventoryDetailExport implements FromView, ShouldAutoSize
{
    protected $items, $mawb, $start, $end;

    public function __construct(Collection $items, string $mawb, Carbon $start, Carbon $end)
    {
      $this->items = $items;
      $this->mawb = $mawb;
      $this->start = $start;
      $this->end = $end;
    }

    public function view(): view
    {
      
      $items = $this->items;
      $mawb = $this->mawb;
      $start = $this->start;
      $end = $this->end;
      $company = activeCompany();
      $jenis = 'xls';

      return view('exports.inventorydetailexcel', compact(['items', 'company', 'jenis', 'mawb', 'start', 'end']));

    }
}
