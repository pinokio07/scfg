<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class BarkirImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {      
        return [
          0 => new HouseSheetImport(),
          1 => new DetailSheetImport(),
        ];
        
    }

    public function onUnknownSheet($skip)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$skip} was skipped");
    }
}
