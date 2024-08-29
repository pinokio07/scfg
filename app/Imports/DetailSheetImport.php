<?php

namespace App\Imports;

use App\Models\House;
use App\Models\HouseDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use DB;

class DetailSheetImport implements ToCollection, SkipsEmptyRows
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        
    }
}
