<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tariff;
use App\Models\TariffSchema;
use DataTables, DB, Auth, Arr;

class SetupTariffSchemaController extends Controller
{    
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = Tariff::query();

          return DataTables::eloquent($query)
                           ->addIndexColumn()
                           ->editColumn('name', function($row){
                            $btn = '<a href="'.route('setup.tariff-schema.edit', ['tariff_schema' => $row->id]).'">'.$row->name.'</a>';

                            return $btn;
                           })
                           ->addColumn('actions', function($row){
                            $btn = '<button type="button"
                                       data-href="'.route('setup.tariff-schema.duplicate', ['schema' => $row->id]).'"
                                       class="btn btn-xs btn-info elevation-2 duplicate"
                                       data-toggle="tooltip"
                                       title="Duplicate"
                                       ><i class="fas fa-copy"></i></button>';

                            return $btn;
                           })
                           ->rawColumns(['name', 'actions'])
                           ->toJson();                          
        }

        $items = collect([
          'id' => 'id',
          'name' => 'Name',
          'minimum' => 'Min Tariff',
          'actions' => 'Actions'
        ]);

        return view('pages.setup.tariff.index', compact(['items']));
    }
    
    public function create()
    {
        $item = new Tariff;

        return view('pages.setup.tariff.create-edit', compact(['item']));
    }
    
    public function store(Request $request)
    {
        $data = $request->validate([
          'name' => 'required',
          'minimum' => 'required|numeric',
          'vat' => 'nullable|numeric'
        ]);

        if($data){
          DB::beginTransaction();

          try {
            $tariff = Tariff::create($data);
            DB::commit();

            return redirect('/setup/tariff-schema/'.$tariff->id.'/edit')->with('sukses', 'Create Schema Success.');
          } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
          }
        }
    }
    
    public function show($id)
    {
        //
    }
    
    public function edit(Tariff $tariff_schema)
    {
        $item = $tariff_schema->load(['schema']);

        return view('pages.setup.tariff.create-edit', compact(['item']));
    }
    
    public function update(Request $request, Tariff $tariff_schema)
    {
        $data = $request->validate([
          'name' => 'required',
          'minimum' => 'required|numeric',
          'vat' => 'nullable|numeric'
        ]);

        if($data){
          DB::beginTransaction();

          try {
            $tariff_schema->update($data);
            DB::commit();

            return redirect('/setup/tariff-schema/'.$tariff_schema->id.'/edit')->with('sukses', 'Create Schema Success.');
          } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
          }
        }
    }
    
    public function destroy($id)
    {
        //
    }

    public function storechema(Request $request)
    {
        $data = $request->validate([
          'tariff_id' => 'required|numeric',
          'urut' => 'required|numeric',
          'name' => 'required',
          'rate' => 'required|numeric',
          'column' => 'required',
          'days' => 'nullable|numeric'
        ]);

        if($data){
          $tariff = Tariff::findOrFail($request->tariff_id);

          DB::beginTransaction();

          try {
            foreach ($tariff->schema->where('urut', '>=', $data['urut']) as $item) {
              $item->update(['urut' => ($item->urut + 1)]);
            }

            $schema = TariffSchema::create($data);

            DB::commit();

            if($request->as_one > 0){
              $schema->update(['as_one' => true, 'is_fixed' => false]);
            } else if ($request->is_fixed > 0){
              $schema->update(['as_one' => false, 'is_fixed' => true]);
            } else {
              $schema->update(['as_one' => false, 'is_fixed' => false]);
            }

            DB::commit();

            return redirect('/setup/tariff-schema/'.$tariff->id.'/edit')->with('sukses', 'Add Tariff Success.');
          } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
          }
        }
    }

    public function updateschema(Request $request, TariffSchema $schema)
    {
        $data = $request->validate([
          'urut' => 'required|numeric',
          'name' => 'required',
          'rate' => 'required|numeric',
          'column' => 'required',
          'days' => 'nullable|numeric'
        ]);

        if($data){
          $tariff = $schema->tariff;
          $count = $tariff->schema->count();

          DB::beginTransaction();

          try {
            $aslinya = $schema->urut;

            $schema->update($data);

            if($request->as_one > 0){
              $schema->update(['as_one' => true, 'is_fixed' => false]);
            } else if ($request->is_fixed > 0){
              $schema->update(['as_one' => false, 'is_fixed' => true]);
            } else {
              $schema->update(['as_one' => false, 'is_fixed' => false]);
            }

            DB::commit();
            
            $schema->refresh();

            if($aslinya != $data['urut']){
              $others = $tariff->schema->where('id', '<>', $schema->id)
                                       ->where('urut', '>=', $schema->urut);

              foreach ($others->sortBy('urut') as $item) {
                if($item->urut < $count){
                  $item->update(['urut' => ($item->urut + 1)]);
                }                
              }
            }

            DB::commit();

            return redirect('/setup/tariff-schema/'.$tariff->id.'/edit')->with('sukses', 'Update Tariff Success.');
          } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
          }
        }
    }

    public function duplicate(Tariff $schema)
    {
        DB::beginTransaction();

        try {
          $newSchema = $schema->replicate();
          $newSchema->save();
          DB::commit();

          foreach ($schema->schema as $item) {
            $newItem = $item->replicate();
            $newItem->tariff_id = $newSchema->id;
            $newItem->save();

            DB::commit();
          }

          return redirect('/setup/tariff-schema')->with('sukse', 'Duplicate Schema Success.');
        } catch (\Throwable $th) {

          DB::rollback();
          throw $th;
        }
    }

    public function destroyschema(TariffSchema $schema)
    {
      DB::beginTransaction();

      try {
        $tariff = $schema->tariff;

        $schema->delete();
        DB::commit();

        $others = $tariff->schema;

        $no = 1;

        foreach ($others->sortBy('urut') as $item) {
          // if($item->urut < $count){
            $item->update(['urut' => ($no++)]);
            DB::commit();
          // }                
        }        

        return redirect('/setup/tariff-schema/'.$tariff->id.'/edit')->with('sukses', 'Delete Tariff Success.');

      } catch (\Throwable $th) {
        DB::rollback();
        throw $th;
      }
    }
}
