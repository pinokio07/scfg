<?php

namespace App\Imports;

use App\Models\Master;
use App\Models\House;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use DB, Str;

class UploadImport implements ToCollection
{
    use Importable;

    private $jenis;

    public function __construct(string $jenis)
    {
        $this->jenis = $jenis;
    }

    public function collection(Collection $rows)
    {
        $headers = collect($rows[0]);
        $headers->shift();
        // $show = [];

        foreach ($rows as $key => $col) {
          if ($key > 0 && $col[2] != '') {
            unset($col[0]);
            $data = $headers->combine($col);

            if($data->has(['NO_MASTER_BL_AWB'])){
              DB::beginTransaction();
              try {

                $nomaster = Str::replace(['-', ' '], '', $data['NO_MASTER_BL_AWB']);

                if ($this->jenis == 'master') {

                  Master::firstOrCreate([
                    'MAWBNumber' => $nomaster,
                    'MAWBDate' => $data['TGL_MASTER_BL_AWB']
                  ],$data->except(['NO_MASTER_BL_AWB', 'TGL_MASTER_BL_AWB'])->toArray());

                } elseif ($this->jenis == 'house') {
                  
                  $master = Master::where('MAWBNumber', $nomaster)->first();

                  $master->houses()->firstOrCreate([
                    'NO_BARANG' => $data['NO_BARANG']
                  ], $data->except(['NO_BARANG'])->toArray());

                } elseif ($this->jenis == 'hscode') {

                  $house = House::where('NO_BARANG', $data['NO_BARANG'])->first();

                  $house->details()->firstOrCreate([
                    'NO_HOUSE_BLAWB' => $data['NO_BARANG']
                  ], $data->toArray());
                } elseif ($this->jenis == 'nopen') {
                  $house = House::where('NO_MASTER_BLAWB', $nomaster)
                                ->where('NO_BARANG', $data['NO_BARANG'])
                                ->first();

                  if($house) {
                    $house->update([
                      'NO_DAFTAR_PABEAN' => $data['NOPEN']
                    ]);
                  }
                }
                
                DB::commit();

                // if($data['NO_BARANG'] == 'L6300968'){
                //   $show[] = $house;
                // }

              } catch (\Throwable $th) {

                DB::rollback();
                throw $th;

              }
            }                        
          }
        }

        // dd($show);
    }
}
