<?php

namespace App\Exports;

use App\Models\OrgCompanyData;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompanyDataListExport implements FromView,ShouldAutoSize,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): view
    {
      $headers = collect(OrgCompanyData::first())->keys();
      $exc = [
        'created_at',
        'updated_at',
        'deleted_at'
      ];
      $ref = [        
        'OB_OH',
        'OB_GC',
        'OB_OH_APSettlementOrg',
        'OB_OH_ARSettlementOrg',
        'OB_OJ_ARDebtorGroup',
        'OB_OG_APCreditorGroup',
      ]; 
      $items = OrgCompanyData::with([
                                      'header',
                                      'company',
                                      'arGroup.glaccount',
                                      'apGroup.glaccount',
                                      'settlementAR',
                                      'settlementAP'])
                              ->get();

      return view('exports.companydata', compact(['headers', 'exc', 'ref', 'items']));
    }

    public function title(): string
    {
        return "Company Data";
    }
}
