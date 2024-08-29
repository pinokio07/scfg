<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefVessel;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupVesselsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefVessel::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()                           
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'RV_Code' => 'Vessel Name',
          'RV_LloydsNumber' => 'Lloyd Number',
          'RV_VesselType' => 'Vessel Type',
          'RV_RN_NKCountryOfReg' => 'Country of Registration',
          'RV_RadioCallSign' => 'Radio Call Sign'

        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
      $model = '\App\Models\RefVessel';
      return Excel::download(new SetupExport($model), 'vessels.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefVessel';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/vessels')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {
      $data = [];
      if($request->has('q') && $request->q != ''){
          $search = $request->q;
          $data = ReffVessel::select("RV_Code")
                                ->where('RV_IsActive', true)
                                ->where('RV_Code','LIKE',"%$search%")
                                ->limit(5)
                                ->get();
      }

      return response()->json($data);
    }
    
}
