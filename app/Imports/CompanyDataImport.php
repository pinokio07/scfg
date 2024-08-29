<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Importable;

class CompanyDataImport implements WithMultipleSheets
{
    use Importable;

    public function sheets(): array
    {
      $sheets = [
        // new CompanyDataListImport(),
        new CompanyDataAddressImport(),
        new CompanyDataContactImport(),
      ];
      
      return $sheets;
    }
}
