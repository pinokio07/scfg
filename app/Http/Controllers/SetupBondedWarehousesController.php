<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefBondedWarehouse;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;
use DB;

class SetupBondedWarehousesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefBondedWarehouse::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()
                          ->editColumn('company_name', function($row){
                            $name = $row->company_name;

                            $url = '<a href="'.route('setup.bonded-warehouses.edit', ['bonded_warehouse' => $row->id]).'">'.$name.'</a>';

                            return $url;
                          })
                          ->addColumn('tariff', function($row){
                            $btn = '<a href="#" id="tariff_'.$row->id.'" class="tariff" data-type="text" data-id="'.$row->id.'" data-pk="'.$row->id.'" data-url="'.url()->current().'/'.$row->id.'" data-title="Edit Tariff" data-nama="tariff">'.$row->tariff.'</a>';

                            return $btn;
                          })
                          ->rawColumns(['company_name', 'tariff'])
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'company_name' => 'Company Name',
          'tps_code' => 'TPS Code',
          'warehouse_code' => 'Warehouse Code',
          'address' => 'Address',
          'tariff' => 'Tariff'
        ]);

        return view('pages.setup.warehouse.index', compact(['items']));
    }

    public function create()
    {
      $item = new RefBondedWarehouse;
      $disabled = false;

      return view('pages.setup.warehouse.create-edit', compact(['item', 'disabled']));
    }

    public function store(Request $request)
    {
      $data = $this->validatedRequest();

      if($data){
        DB::beginTransaction();
        try {
          $item = RefBondedWarehouse::create($data);
          DB::commit();

          return redirect()->route('setup.bonded-warehouses.edit', ['bonded_warehouse' => $item->id])
                           ->with('sukses', 'Create Warehouse Success');
        } catch (\Throwable $th) {
          DB::rollback();
          throw $th;          
        }
      }
    }

    public function show(RefBondedWarehouse $bonded_warehouse)
    {
      $item = $bonded_warehouse;
      $disabled = 'disabled';

      return view('pages.setup.warehouse.create-edit', compact(['item', 'disabled']));
    }

    public function edit(RefBondedWarehouse $bonded_warehouse)
    {
      $item = $bonded_warehouse;
      $disabled = false;

      return view('pages.setup.warehouse.create-edit', compact(['item', 'disabled']));  
    }

    public function update(Request $request, RefBondedWarehouse $bonded_warehouse)
    {
      if($request->ajax()){
        $data = $request->validate([
          'pk' => 'required|numeric',
          'nama' => 'required',
          'val' => 'required'
        ]);
        if($data){
          $column = $request->nama;
          $warehouse = RefBondedWarehouse::findOrFail($request->pk);
  
          DB::beginTransaction();
  
          try {
            $warehouse->update([$column => $request->val]);
  
            DB::commit();
  
            return response()->json(['status' => "OK"]);
          } catch (\Throwable $th) {
            DB::rollback();
  
            return response()->json(['status' => 'ERROR', 'message' => $th->getMessag()]);
          }
        }
      } else {
        $data = $this->validatedRequest();

        if($data){
          DB::beginTransaction();

          try {
            $bonded_warehouse->update($data);
            DB::commit();

            return redirect()->route('setup.bonded-warehouses.edit', ['bonded_warehouse' => $bonded_warehouse->id])
                           ->with('sukses', 'Update Warehouse Success');
          } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
          }
        }
      }      
    }

    public function download()
    {
        $model = '\App\Models\RefBondedWarehouse';
        return Excel::download(new SetupExport($model), 'warehouse.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefBondedWarehouse';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/bonded-warehouses')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {
        $data = [];

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = RefBondedWarehouse::where(function($query) use($search){
                                        $query->where('tps_code','LIKE',"%$search%")
                                              ->orWhere('company_name','LIKE',"%$search%")
                                              ->orWhere('warehouse_code','LIKE',"%$search%");
                                      })
                                      ->get();
        }

        return response()->json($data);
    }

    public function validatedRequest()
    {
      return request()->validate([
        'company_name' => 'required',
        'tps_code' => 'required',
        'warehouse_code' => 'required',
        'address' => 'nullable',
        'tariff' => 'nullable|numeric'
      ]);
    }

}
