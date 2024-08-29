<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SoapHelper;
use App\Models\TpsLog;
use App\Models\Master;
use App\Models\House;
use App\Models\HouseDetail;
use App\Models\PlpOnlineLog;
use App\Models\SchedulerLog;
use DataTables, Str;

class LogsController extends Controller
{
    public function show(Request $request)
    {
      if($request->ajax()){
        $query = TpsLog::query();
        $type = $request->type;
        $id = $request->id;

        switch ($type) {
          case 'master':
            $master = Master::selectRaw('tps_master.id as mid, tps_houses.id as hid, tps_house_items.id as did')
                            ->join('tps_houses', 'tps_master.id', '=', 'tps_houses.MasterID', 'left outer')
                            ->join('tps_house_items', 'tps_houses.id', '=', 'tps_house_items.HouseID', 'left outer')
                            ->where('tps_master.id', $id)
                            ->get();
                           
            $house = $master->unique('hid')
                            ->pluck('hid')
                            ->toArray();
            $detail = $master->where('did', '<>', null)
                             ->unique('did')
                             ->pluck('did')
                             ->toArray();
            
            $query->where(function($m) use ($id){
                    $m->where('logable_type', 'App\Models\Master')
                      ->where('logable_id', $id);
                  })
                  ->orWhere(function($h) use ($house){
                    $h->where('logable_type', 'App\Models\House')
                          ->whereIn('logable_id', $house);
                  })
                  ->orWhere(function($d) use ($detail){
                    $d->where('logable_type', 'App\Models\HouseDetail')
                           ->whereIn('logable_id', $detail);
                  });
            
            break;
          
          case 'house':
            $house = House::findOrFail($request->id);
            $detail = $house->details()->pluck('id')->toArray();
            
            $query->where('logable_type', 'App\Models\House')
                  ->where('logable_id', $house->id)
                  ->orWhere(function($d) use ($detail){
                    $d->where('logable_type', 'App\Models\HouseDetail')
                          ->whereIn('logable_id', $detail);
                  });
            break;
          
          default:
            $query = '';
            break;
        }

        $query->orderBy('created_at', 'desc');

        return DataTables::eloquent($query)
                         ->addIndexColumn()
                         ->addColumn('user', function($row){
                          return $row->user->name ?? "-";
                         })
                         ->editColumn('created_at', function($row){
                          return $row->created_at->translatedFormat('l, d F Y H:i');
                         })
                         ->rawColumns(['keterangan'])
                         ->toJson();
      }
      
    }

    public function plp(Request $request)
    {
      $master = Master::findOrFail($request->id);
      $plp = $master->plponline->pluck('id')->toArray();
      $sh = new SoapHelper;
      $re = '/<a ?.*?>([^<]+)<\/a>/m';

      $query = PlpOnlineLog::whereIn('plp_id', $plp);

      $query->orderBy('created_at', 'desc');

      return DataTables::eloquent($query)
                       ->addIndexColumn()
                       ->editColumn('Response', function($row) use ($sh, $re){
                        $service = $row->Service;

                        $result = $sh->getResults($service.'Result', $row->Response);

                        if(strpos($result, 'DOCUMENT')){
                          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
                          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));

                          $show = $hasil;
                        } else {
                          $show = $result;
                        }

                        return $show;
                       })
                       ->editColumn('created_at', function($row){
                        return $row->created_at->translatedFormat('l, d F Y H:i');
                       })
                       ->rawColumns(['Reason'])
                       ->toJson();

    }

    public function sch(Request $request)
    {
        $rid = $request->id;
        $hids = House::where('MasterID', $rid)->pluck('id')->toArray();

        $query = SchedulerLog::where(function($m) use ($rid){
                                $m->where('logable_type', '\App\Models\Master')
                                  ->where('logable_id', $rid);
                              })
                              ->orWhere(function($h) use ($hids){
                                $h->where('logable_type', '\App\Models\House')
                                  ->whereIn('logable_id', $hids);
                              });

      $query->orderBy('created_at', 'desc');

      return DataTables::eloquent($query)
                       ->addIndexColumn()
                       ->editColumn('request', function($row){
                        $res = $row->request;
                        $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $res);
                        $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));

                        return $hasil;
                       })
                       ->editColumn('created_at', function($row){
                        return $row->created_at->translatedFormat('l, d F Y H:i');
                       })
                      //  ->rawColumns(['Reason'])
                       ->toJson();
    }
}
