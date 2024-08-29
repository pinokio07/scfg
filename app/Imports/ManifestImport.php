<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\Importable;

class ManifestImport implements WithMultipleSheets, SkipsUnknownSheets
{
    use Importable;

    public function sheets(): array
    {
        return [
          0 => new HeaderSheetImport(),
          1 => new HeaderSheetImport(),
          2 => new HeaderSheetImport(),
          3 => new HeaderSheetImport()
        ];
        
    }

    public function onUnknownSheet($skip)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$skip} was skipped");
    }
}
