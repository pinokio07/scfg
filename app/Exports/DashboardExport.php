<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DashboardExport implements FromView, ShouldAutoSize
{
    private $query;

    public function __construct(Builder $query)
    {
      $this->query = $query;
    }
    public function view(): view
    {
        $query = $this->query;
        $data = $query->get();
        $tipe = '';

        return view('exports.dashboard', compact(['data', 'tipe']));
    }
}
