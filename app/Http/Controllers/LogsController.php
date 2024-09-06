<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SoapHelper;
use App\Helpers\Barkir;
use App\Models\BcLog;
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

    public function bc(Request $request)
    {
      if($request->has('master')){
        return $this->bcGroup($request);
      }
      if($request->has('code'))
      {
        return $this->bcHouse($request);
      }
      if($request->has('group'))
      {
        return $this->groupBC($request);
      }
      $query = BcLog::orderBy('BC_DATE', 'desc')
                      ->where('HouseID', $request->house);
      return DataTables::eloquent($query)
                       ->addIndexColumn()
                       ->editColumn('created_at', function($row){
                        return $row->created_at->translatedFormat('l, d F Y H:i:s');
                       })
                       ->editColumn('BC_DATE', function($row){
                        return $row->BC_DATE?->format('d-m-Y H:i:s');
                       })
                       ->addColumn('PDF', function($row){
                        $url = '';
                        if(in_array($row->BC_CODE,[303, 304, 305, 306, 401, 402, 403, 404]))
                        {
                          $url = '<a href="'.route('logs.cetak').'?id='.\Crypt::encrypt($row->LogID).'"
                                     target="_blank">Download</a>';
                        }

                        return $url;
                       })
                       ->rawColumns(['PDF'])
                       ->toJson();
    }

    public function bcGroup(Request $request)
    {
      $query = House::where('MasterID', $request->master)
                    ->whereNotNull('BC_CODE');

      $houses = $query->get();
      $count = $houses->whereNotIn('BC_CODE', ['ERR', 902, 903, 906, 908, 909])->count();
      $data = $houses->groupBy('BC_CODE');

      $table = DataTables::of($data)
                       ->addColumn('BC_CODE', function($row){
                        return $row->first()->BC_CODE ?? "";
                       })
                       ->addColumn('CN_COUNT', function($row){
                        $count = $row->count() ?? 0;

                        $a = '<a href="#"
                                  data-toggle="modal"
                                  data-target="#modal-respon"
                                  class="cncount"
                                  data-code="'.$row->first()->BC_CODE.'"
                                  data-mawb="'.$row->first()->MasterID.'">'.$count.'</a>';
                        return $a;
                       })
                       ->addColumn('BC_STATUS', function($row){
                        return $row->first()->BC_STATUS ?? "";
                       })
                       ->rawColumns(['CN_COUNT'])
                       ->toJson();
      return response()->json([
        'count' => $count,
        'table' => $table
      ]);
    }

    public function bcHouse(Request $request)
    {
      $code = $request->code;
      $mawb = $request->mawb;
      $query = House::where('BC_CODE', $code)
                    ->where('MasterID', $mawb)
                    ->with(['bclog' => function($bc) use ($code){
                      $bc->where('BC_CODE', $code);
                    }]);

      return DataTables::eloquent($query)
                       ->addIndexColumn()
                       ->editColumn('BC_DATE', function($row){
                        return $row->BC_DATE?->format('d-m-Y H:i:s');
                       })
                       ->editColumn('NO_BARANG', function($row){
                        $nobarang = $row->NO_BARANG;
                        $url = '<a href="'.route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($row->id)]).'" target="_blank">'.$nobarang.'</a>';
                        // return $row->BC_DATE?->translatedFormat('d-m-Y H:i');
                        return $url;
                       })
                       ->addColumn('PDF', function($row){
                        $url = '';
                        if(in_array($row->BC_CODE,[303, 304, 305, 306, 401, 402, 403, 404])
                            && $row->bclog->isNotEmpty())
                        {
                          $url = '<a href="'.route('logs.cetak').'?id='.\Crypt::encrypt($row->bclog?->first()?->LogID).'"
                                     target="_blank">Download</a>';
                        }

                        return $url;
                       })
                       ->addColumn('actions', function($row){
                        $btn = '<button class="btn btn-xs btn-warning btn-block elevation-2 fcdownload"
                                        data-id="'.$row->id.'"
                                        data-code="'.$row->BC_CODE.'"
                                        data-mawb="'.$row->MasterID.'">
                                  <i class="fas fa-sync"></i> Force Download
                                </button>';
                        return $btn;
                       })
                       ->rawColumns(['PDF', 'NO_BARANG', 'actions'])
                       ->toJson();
    }

    public function groupBC(Request $request)
    {
      if($request->has('detail'))
      {
        return $this->groupBCDetail($request);
      }
      $hids = House::where('MasterID', $request->mawb)->pluck('id')->toArray();

      $logs = BcLog::whereIn('HouseID', $hids)
                    ->get()
                    ->sortByDesc('BC_DATE')
                    ->groupBy('BC_CODE');

      return DataTables::of($logs)
                        ->addIndexColumn()
                        ->addColumn('BC_CODE', function($row){
                          return $row->first()->BC_CODE;
                        })
                        ->addColumn('CN_COUNT', function($row){
                          $count = $row->count() ?? 0;

                          $a = '<a href="#"
                                    data-toggle="modal"
                                    data-target="#modal-respon"
                                    class="cndetail"
                                    data-code="'.$row->first()->BC_CODE.'"
                                    data-mawb="'.$row->first()->MAWB.'">'.$count.'</a>';
                          return $a;
                        })
                        ->addColumn('BC_TEXT', function($row){
                          return $row->first()->BC_TEXT;
                        })
                        ->rawColumns(['CN_COUNT'])
                        ->toJson();
                    
    }

    public function groupBCDetail(Request $request)
    {
      $query = BcLog::where('MAWB', $request->mawb)
                    ->where('BC_CODE', $request->detail);

      return DataTables::eloquent($query)
                        ->addIndexColumn()
                        ->editColumn('BC_DATE', function($row){
                          return $row->BC_DATE?->format('d-m-Y H:i:s');
                        })
                        ->addColumn('NM_PENERIMA', function($row){
                          return $row->house?->NM_PENERIMA ?? "-";
                        })
                        ->addColumn('ChargeableWeight', function($row){
                          return $row->house?->ChargeableWeight ?? "-";
                        })
                        ->addColumn('BRUTO', function($row){
                          return $row->house?->BRUTO ?? "-";
                        })
                        ->editColumn('NO_BARANG', function($row){
                          $nobarang = $row->NO_BARANG;
                          $url = '<a href="'.route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($row->HouseID)]).'" target="_blank">'.$nobarang.'</a>';
                          // return $row->BC_DATE?->translatedFormat('d-m-Y H:i');
                          return $url;
                         })
                        ->addColumn('PDF', function($row){
                          $url = '';
                          if(in_array($row->BC_CODE,[303, 304, 305, 306, 401, 402, 403, 404]))
                          {
                            $url = '<a href="'.route('logs.cetak').'?id='.\Crypt::encrypt($row->LogID).'"
                                        target="_blank">Download</a>';
                          }

                          return $url;
                        })
                        ->addColumn('actions', function($row){
                          return '';
                        })
                        ->rawColumns(['PDF', 'NO_BARANG'])
                        ->toJson();
    }

    public function cetak(Request $request)
    {
      $id = \Crypt::decrypt($request->id);

      $log = BcLog::findOrFail($id);

      if($log->BC_CODE == 401)
      {
        $barkir = new Barkir;

        $barkir->fetch401($log->id, [$log->NO_BARANG]);
      }
      if($log->BC_CODE == 303)
      {
        $barkir = new Barkir;

        $barkir->fetch303($log->id, [$log->NO_BARANG]);
      }

      $XML = simplexml_load_string(base64_decode($log->XML));

      if (isset($XML->HEADER->PDF)) {
        $PDF = base64_decode($XML->HEADER->PDF);
        $name = $log->NO_BARANG.'-'.$log->BC_CODE;
        // dd($PDF);
        // session_write_close();
        // header('Cache-Control: must-revalidate');
        // header('Pragma: public');
        // header('Content-Description: File Transfer');
        // header('Content-Disposition: inline; filename=' . $log->NO_BARANG.'-'.$log->BC_CODE . '.pdf');
        // header('Content-Transfer-Encoding: binary');
        // header('Content-Length: ' . strlen($PDF));
        // header('Content-Type: application/pdf');
        // echo $PDF;

        \Storage::disk('public')->put('tmp/'.$name.'.pdf', $PDF);

        $file = public_path('storage/tmp/'.$name.'.pdf');
        return response()->file($file);
      }
    }
}
