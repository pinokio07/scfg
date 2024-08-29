<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompanyDataExport implements WithMultipleSheets
{    
    use Exportable;

    public function sheets(): array
    {
        $sheets = [
          // new CompanyDataListExport(),
          new CompanyDataAddressExport(),
          new CompanyDataContactExport(),
        ];

        return $sheets;
    }
}
