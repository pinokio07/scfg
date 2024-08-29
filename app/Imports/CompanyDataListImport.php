<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\OrgHeader;
use App\Models\OrgCompanyData;
use App\Models\AccGlAccount;

class CompanyDataListImport implements ToCollection
{
    use Importable;

    public function collection(Collection $rows)
    {
        $headers = collect($rows[0]);
        $headers->shift();

        foreach ($rows as $key => $col) {
          if($key > 0 && $col[86] != ''){
            $orgHeader = OrgHeader::where('OH_Code', $col[86])->first();

            if($orgHeader){
              unset($col[0]);
              $data = $headers->combine($col);             

              $companyData = OrgCompanyData::updateOrCreate([
                'OB_OH' => $orgHeader->id,
              ], $data->except([
                            'OB_OH',
                            'OB_GC',
                            'OB_OH_APSettlementOrg',
                            'OB_OH_ARSettlementOrg',
                            'OB_OJ_ARDebtorGroup',
                            'OB_OG_APCreditorGroup',
                          ])->toArray());

              if($data['OB_OH_APSettlementOrg'] != ''){
                $orgAPSettlement = OrgHeader::where('OH_Code', $data['OB_OH_APSettlementOrg'])
                                            ->first();
                if($orgAPSettlement){
                  $companyData->OB_OH_APSettlementOrg = $orgAPSettlement->id;
                }
              }

              if($data['OB_OH_ARSettlementOrg'] != ''){
                $orgARSettlement = OrgHeader::where('OH_Code', $data['OB_OH_ARSettlementOrg'])
                                            ->first();
                if($orgARSettlement){
                  $companyData->OB_OH_ARSettlementOrg = $orgARSettlement->id;
                }
              }

              if($data['OB_OJ_ARDebtorGroup'] != ''){
                $orgARDebtor = AccGlAccount::where('AG_AccountNum', $data['OB_OJ_ARDebtorGroup'])
                                            ->first();
                if($orgARDebtor){
                  $companyData->OB_OJ_ARDebtorGroup = $orgARDebtor->id;
                }
              }

              if($data['OB_OG_APCreditorGroup'] != ''){
                $orgAPDebtor = AccGlAccount::where('AG_AccountNum', $data['OB_OG_APCreditorGroup'])
                                            ->first();
                if($orgAPDebtor){
                  $companyData->OB_OG_APCreditorGroup = $orgAPDebtor->id;
                }
              }

              $companyData->save();

            }
          }
        }
    }
}
