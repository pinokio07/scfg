<?php

namespace App\Imports;

use App\Models\OrgHeader;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Str;

class OrgImport implements ToCollection, SkipsEmptyRows
{
    use Importable;

    public function collection(Collection $rows)
    {
      $headers = collect($rows[0]);
      $headers->shift();
      
      foreach ($rows as $key => $col) {
        if($key > 0 && isset($col[5]) && $col[5] != '' ){
          unset($col[0]);            
          $data = $headers->combine($col);

          if($data['OH_Code']){
            
            $orgHeader = OrgHeader::updateOrCreate(['OH_Code' => $data['OH_Code']], $data->toArray());
          } else {            
            $orgHeader = OrgHeader::firstOrCreate($data->toArray());

            $exc = ['PT', 'PT.', 'CV', 'CV.'];

            $name = explode(' ', $data['OH_FullName']);

            if(in_array(Str::upper($name[0]), $exc)){
              unset($name[0]);
            }
            
            $name = array_merge($name);         

            if(isset($data['OH_IsNationalAccount']) && $data['OH_IsNationalAccount'] > 0){
              $countcode = '_ID';
            } elseif(isset($data['OH_IsGlobalAccount']) && $data['OH_IsGlobalAccount'] > 0){
              $countcode = '_WW';
            } else {
              if(isset($data['OH_RL_NKClosestPort']) && $data['OH_RL_NKClosestPort'] != ''){
                $countcode = substr($data['OH_RL_NKClosestPort'], -3);
              }              
            }

            $jml = count($name);

            if($jml > 1){
              $namaSet = Str::upper(substr($name[0],0, 3).substr($name[1], 0, 3).$countcode);
            } else {
              $namaSet = Str::upper(substr($name[0],0, 6).$countcode);
            }

            $namaDepan = preg_replace('/[^A-Za-z0-9\-]/', '', $namaSet);

            $cek = OrgHeader::where('OH_Code', 'LIKE', $namaDepan.'%')->count();

            $urut = sprintf('%03d', $cek + 1);
                      
            $orgHeader->OH_Code = $namaDepan.$urut;
            $orgHeader->save();
          }        
        }
      }
    }

}
