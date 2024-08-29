<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefContainer;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupContainersController extends Controller
{    
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefContainer::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()                           
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'RC_Code' => 'Type',
          'RC_Description' => 'Description',
          'RC_ShippingMode' => 'Shipping Mode',
          'RC_ContainerType' => 'Container Type',
          'RC_ISOType' => 'ISO Type',
          'RC_Length' => 'Length',
          'RC_Height' => 'Height',
          'RC_Width' => 'Width'
        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
        $model = '\App\Models\RefContainer';
        return Excel::download(new SetupExport($model), 'containers.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefContainer';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/containers')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {
      $data = [];
      if($request->has('q') && $request->q != ''){
          $search = $request->q;
          $data = ReffContainer::select("id","RC_Code")
                                ->where('RC_IsActive', true)
                                ->where('RC_Code','LIKE',"%$search%")
                                ->limit(5)
                                ->get();
      }

      return response()->json($data);
    }
}
