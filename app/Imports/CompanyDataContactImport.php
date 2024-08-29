<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\OrgHeader;
use App\Models\OrgContact;
use Auth;

class CompanyDataContactImport implements ToCollection
{
    use Importable;

    public function collection(Collection $rows)
    {
        $headers = collect($rows[0]);
        $headers->shift();
        $timeUTC = now()->setTimezone('UTC');

        foreach ($rows as $key => $col) {
          if($key > 0 && $col[1] != ''){
            $orgHeader = OrgHeader::where('OH_Code', $col[1])->first();

            if($orgHeader){
              unset($col[0]);
              $data = $headers->combine($col);

              $orgContact = OrgContact::updateOrCreate([
                'OC_OH' => $orgHeader->id,
                'OC_ContactName' => $col[5]
              ], $data->except(['OC_OH', 'OC_ContactName'])->toArray());

              if($orgContact->wasRecentlyCreated){
                $orgContact->OC_SystemCreateTimeUtc = $timeUTC;
                $orgContact->OC_SystemCreateUser = Auth::id();
              } else {
                $orgContact->OC_SystemLastEditTimeUtc = $timeUTC;
                $orgContact->OC_SystemLastEditUser = Auth::id();
              }

              $orgContact->save();
            }
          }
        }
    }
}
