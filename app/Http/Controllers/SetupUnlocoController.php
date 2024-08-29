<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefUnloco;
use App\Exports\SetupExport;
use App\Imports\SetupImport;
use DataTables;
use Excel;

class SetupUnlocoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = RefUnloco::query();

          return DataTables::eloquent($query)
                          ->addIndexColumn()
                          ->addColumn('options', function($row){
                            $options = '';
                            if($row->RL_HasAirport == true){
                              $options .= 'Airport;';
                            }
                            if($row->RL_HasSeaport == true){
                              $options .= 'Seaport;';
                            }
                            if($row->RL_HasRail == true){
                              $options .= 'Rail;';
                            }
                            if($row->RL_HasRoad == true){
                              $options .= 'Road;';
                            }
                            if($row->RL_HasPost == true){
                              $options .= 'Post;';
                            }
                            if($row->RL_HasCustomsLodge == true){
                              $options .= 'Customs Lodge;';
                            }
                            if($row->RL_HasUnload == true){
                              $options .= 'Unload;';
                            }
                            if($row->RL_HasStore == true){
                              $options .= 'Store;';
                            }
                            if($row->RL_HasTerminal == true){
                              $options .= 'Terminal;';
                            }
                            if($row->RL_HasDischarge == true){
                              $options .= 'Discharge;';
                            }
                            if($row->RL_HasOutport == true){
                              $options .= 'Outport;';
                            }
                            if($row->RL_HasBorderCrossing == true){
                              $options .= 'Border Crossing;';
                            }
                            return $options;
                          })
                          ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'RL_IsActive' => 'Active',
          'RL_Code' => 'Code',          
          'RL_PortName' => 'Port Name',
          'RL_NameWithDiacriticals' => 'Diacriticals Name',
          'RL_IATA' => 'IATA',
          'RL_RN_NKCountryCode' => 'Country Code',
          'RL_IATARegionCode' => 'IATA Region',
          'options' => 'Options'
        ]);

        return view('pages.setup.indexall', compact(['items']));
    }

    public function download()
    {
      $model = '\App\Models\RefUnloco';
      return Excel::download(new SetupExport($model), 'unloco.xlsx');
    }

    public function upload(Request $request)
    {
        $model = '\App\Models\RefUnloco';
        Excel::import(new SetupImport($model), $request->upload);
          
        return redirect('/setup/unloco')->with('sukses', 'Upload Success.');
    }

    public function select2(Request $request)
    {
        $data = [];

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            $data = RefUnloco::select("id","RL_Code","RL_PortName", "RL_RN_NKCountryCode")
                                ->where('RL_Code','LIKE',"%$search%")
                                ->orWhere('RL_PortName','LIKE',"%$search%")
                                ->orWhere('RL_NameWithDiacriticals', 'LIKE',"%$search%")
                                ->groupBy('RL_Code')
                                ->limit(10)
                                ->get();
        }

        return response()->json($data);
    }
}
