<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefCommodity;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupCommoditiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefCommodity::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()                           
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'RH_Code' => 'Commodity Code',
          'RH_Description' => 'Commodity Description',
          'RH_UniversalCommodityGroup' => 'Universal Commodity Group',
          'RH_IATACommodityItem' => 'IATA Commodity Item'
        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
        $model = '\App\Models\RefCommodity';
        return Excel::download(new SetupExport($model), 'commodities.xlsx');
    }

    public function upload(Request $request)
    {
          $model = '\App\Models\RefCommodity';
          Excel::import(new SetupImport($model), $request->upload);
            
          return redirect('/setup/commodities')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {
        $data = [];

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = RefCommodity::select("id","RH_Code","RH_Description")
                                ->where(function($query) use($search){
                                  $query->where('RH_Code','LIKE',"%$search%")
                                        ->orWhere('RH_Description','LIKE',"%$search%");
                                })
                                ->get();
        }

        return response()->json($data);
    }
}
