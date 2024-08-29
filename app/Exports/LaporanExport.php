<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanExport implements FromView, ShouldAutoSize
{
    protected $items, $jenis, $start, $end;

    public function __construct(Collection $items, string $jenis, string $start, string $end)
    {
      $this->items = $items;
      $this->jenis = $jenis;
      $this->start = $start;
      $this->end = $end;
    }

    public function view(): view
    {
      
      $items = $this->items;
      $jenis = $this->jenis;
      $start = $this->start;
      $end = $this->end;

      return view('exports.laporan.'.$jenis, compact(['items', 'jenis', 'start', 'end']));

    }
}
