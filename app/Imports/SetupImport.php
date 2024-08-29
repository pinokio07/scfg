<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use DB;

class SetupImport implements ToCollection
{
    use Importable;

    private $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }
    public function collection(Collection $rows)
    {              
        $headers = collect($rows[0]);
        $headers->shift();
        $kolom = 0;
        foreach ($rows as $key => $col) {
          if($key > 0 && $col[2] != ''){
            
            unset($col[0]);            
            $data = $headers->combine($col);

            DB::beginTransaction();

            try {

              if($data['id'] != ''){
                $existing = $this->model::find($data['id']);

                if($existing){
                  $existing->update($data->except(['id'])->toArray());
                }

              } else {
                $this->model::firstOrCreate($data->toArray());
              }
              
              DB::commit();

            } catch (\Throwable $th) {

              DB::rollback();
              throw $th;

            }
            
          }
        }
    }
}
