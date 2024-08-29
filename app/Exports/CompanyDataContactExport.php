<?php

namespace App\Exports;

use App\Models\OrgContact;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompanyDataContactExport implements FromView,ShouldAutoSize,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): view
    {
      $headers = collect(OrgContact::first())->keys();
      $exc = [
        'OC_SystemLastEditTimeUtc',
        'OC_SystemLastEditUser',
        'OC_SystemCreateTimeUtc',
        'OC_SystemCreateUser',
        'created_at',
        'updated_at',
        'deleted_at'
      ];
      $ref = [        
        'OC_OH',
      ]; 
      $items = OrgContact::with(['organization'])
                              ->get();

      return view('exports.orgcontact', compact(['headers', 'exc', 'ref', 'items']));
    }

    public function title(): string
    {
        return "Contact List";
    }
}
