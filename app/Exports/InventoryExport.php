<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InventoryExport implements FromView, ShouldAutoSize
{
  protected $items, $start, $end;

  public function __construct(Collection $items, Carbon $start, Carbon $end)
  {
    $this->items = $items;
    $this->start = $start;
    $this->end = $end;
  }

  public function view(): view
  {
    
    $items = $this->items;
    $start = $this->start;
    $end = $this->end;
    $company = activeCompany();
    $jenis = 'xls';

    return view('exports.inventoryexcel', compact(['items', 'company', 'jenis', 'start', 'end']));

  }
}
