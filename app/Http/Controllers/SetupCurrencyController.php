<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefCurrency;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupCurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefCurrency::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()                           
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'RX_IsActive' => 'Active',
          'RX_Code' => 'Code',          
          'RX_Desc' => 'Description',
          'RX_Symbol' => 'Symbol'
        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
      $model = '\App\Models\RefCurrency';
      return Excel::download(new SetupExport($model), 'currencies.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefCurrency';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/currencies')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {        
        $data = [];

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = RefCurrency::select("id","RX_Code", "RX_Symbol", "RX_Desc")
                                ->where('RX_Code','LIKE',"%$search%")
                                ->orWhere('RX_Symbol','LIKE',"%$search%")
                                ->orWhere('RX_Desc','LIKE',"%$search%")
                                ->get();
        }

        return response()->json($data);
    }
}
