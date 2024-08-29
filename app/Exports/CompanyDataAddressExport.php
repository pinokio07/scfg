<?php

namespace App\Exports;

use App\Models\OrgAddress;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompanyDataAddressExport implements FromView,ShouldAutoSize,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): view
    {
      $headers = collect(OrgAddress::first())->keys();
      $exc = [
        'OA_SystemLastEditTimeUtc',
        'OA_SystemLastEditUser',
        'OA_SystemCreateTimeUtc',
        'OA_SystemCreateUser',
        'created_at',
        'updated_at',
        'deleted_at'
      ];
      $ref = [        
        'OA_OH',
      ]; 
      $items = OrgAddress::with(['header'])
                              ->get();

      return view('exports.orgaddress', compact(['headers', 'exc', 'ref', 'items']));
    }

    public function title(): string
    {
        return "Address List";
    }
}
