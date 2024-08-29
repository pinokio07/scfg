<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefAirline;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupAirlinesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefAirline::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()
                          ->addColumn('action', function($row){
                            $btn = '';                           

                            if(!$row->organization){
                            $btn .= '<a href="/setup/airlines-create/'.$row->id.'" class="btn btn-primary btn-xs elevation-2">Create Organization</a>';
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
          'RM_AccountingCode' => 'Numeric Code',
          'RM_ThreeLetterCode' => 'Three Char',
          'RM_TwoCharacterCode' => 'Two Char',
          'RM_AirlineName1' => 'Airline Name 1',
          'RM_AirlineName2' => 'Airline Name 2',
          'RM_AddressLine1' => 'Address Line 1',
          'RM_AddressLine2' => 'Address Line 2',
          'RM_AirlineCountry' => 'Airline Country',
          'RM_AirlineCity' => 'Airline City',
          'RM_AirlineState' => 'Airline State',
          'RM_AirlinePostalCode' => 'Airline Post Code',
          'action' => 'Action'
        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
        $model = '\App\Models\RefAirline';
        return Excel::download(new SetupExport($model), 'airlines.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefAirline';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/airlines')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {
      $data = [];
      if($request->has('q') && $request->q != ''){
          $search = $request->q;
          $data = RefAirline::select("id", "RM_TwoCharacterCode", "RM_AirlineName1")
                                ->where('RM_IsActive', true)
                                ->where(function($query) use ($search){
                                  $query->where('RM_TwoCharacterCode', 'LIKE', "%$search%")
                                        ->orWhere('RM_AirlineName1', 'LIKE', "%$search%");
                                })
                                ->limit(5)
                                ->get();
      }

      return response()->json($data);
    }
}
