<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefShippingLine;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupShippingLinesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefShippingLine::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()
                          ->addColumn('action', function($row){
                            $btn = '';                           

                            if(!$row->organization){
                            $btn .= '<a href="/setup/shipping-create/'.$row->id.'" class="btn btn-primary btn-xs elevation-2">Create Organization</a>';
                            } else{
                              $btn .= '<a href="/setup/organization/'.$row->RM_OH.'" class="btn btn-success btn-xs elevation-2">Edit Organization</a>';
                            }
          
                            return $btn;
                          })
                          ->rawColumns(['action'])
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'RSL_CarrierName' => 'Carrier Name',
          'RSL_StandardCarrierAlphaCode' => 'Carrier Alpha Code',
          'RSL_CargoWiseOneCode' => 'CW1 Code',
          'action' => 'Action'
        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
      $model = '\App\Models\RefShippingLine';
      return Excel::download(new SetupExport($model), 'shipping-lines.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefShippingLine';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/shipping-lines')->with('sukses', 'Upload Success.');
    }
}
