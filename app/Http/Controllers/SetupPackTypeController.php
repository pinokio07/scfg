<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefPackType;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupPackTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefPackType::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()                           
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'F3_Code' => 'Code',
          'F3_Description' => 'Description',
        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
      $model = '\App\Models\RefPackType';
      return Excel::download(new SetupExport($model), 'pack-type.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefPackType';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/pack-type')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {
        $data = [];

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = ReffPackType::select("id","F3_Code","F3_Description")
                                ->where(function($query) use($search){
                                  $query->where('F3_Code','LIKE',"%$search%")
                                        ->orWhere('F3_Description','LIKE',"%$search%");
                                })
                                ->get();
        }

        return response()->json($data);
    }
}
