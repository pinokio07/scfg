<?php

namespace App\Imports;

use App\Models\Master;
use App\Models\House;
use App\Models\MasterPartial;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use DB;

class HouseSheetImport implements ToCollection, SkipsEmptyRows
{    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        
    }
}
