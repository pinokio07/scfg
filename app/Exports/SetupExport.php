<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SetupExport implements FromView, ShouldAutoSize
{
    private $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }
    public function view(): View
    {      
      $data = $this->model::all();
      $exc = ['created_at', 'updated_at', 'deleted_at'];
      $headers = collect($this->model::first())->keys();

      return view('exports.setup', compact(['data', 'headers', 'exc']));
    }
}
