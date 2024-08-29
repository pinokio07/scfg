<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class RolesExport implements WithMultipleSheets
{
  use Exportable;

  public function sheets(): array
    {
      $sheets = [
        new RolesListExport(),
        new RolesUsersExport(),
      ];

      return $sheets;
    } 

}
