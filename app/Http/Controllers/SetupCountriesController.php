<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefCountry;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupCountriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefCountry::query();

          return DataTables::eloquent($query)
                           ->addIndexColumn()                           
                           ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'RN_IsActive' => 'Active',
          'RN_Code' => 'Code',          
          'RN_Desc' => 'Description',
          'RN_EconomicGrouping' => 'Economic Grouping',
          'RN_CountryDialingCode' => 'Dialing Code',
          'RN_RX_NKLocalCurrency' => 'Local Currency',

        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
      $model = '\App\Models\RefCountry';
      return Excel::download(new SetupExport($model), 'countries.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefCountry';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/countries')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {        
        $data = [];

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $query = RefCountry::select('id', 'RN_Code', 'RN_Desc');

            if($request->has('precise') && $request->precise = 1){
              $data = $query->where('RN_Code', $search)->first();
            } else {
              $data = $query->where('RN_Code','LIKE',"%$search%")
                            ->orWhere('RN_Desc','LIKE',"%$search%")
                            ->groupBy('RN_Code')
                            ->limit(5)
                            ->get();;
            }            
        }

        return response()->json($data);
    }
}
