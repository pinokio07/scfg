<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use Carbon\Carbon;
use DataTables, Auth, Crypt, Str, DB, PDF;

class SewaGudangController extends Controller
{   
    public function index(Request $request)
    {
      if($request->ajax()){
        $query = House::whereHas('tariff')
                      ->with(['master']);

        if($request->tanggal){
          $tanggal = Carbon::createFromFormat('d-m-Y', $request->tanggal);
          $start = $tanggal->copy()->startOfDay()->format('Y-m-d H:i:s');
          $end = $tanggal->copy()->endOfDay()->format('Y-m-d H:i:s');

          $query->whereBetween('SCAN_OUT_DATE', [$start, $end]);
        }

        return DataTables::eloquent($query)
                         ->addIndexColumn()
                        //  ->editColumn('NO_BARANG', function($row){
                        //   $btn = '<a href="'.route('manifest.shipments.show', ['shipment' => Crypt::encrypt($row->id)]).'">'.$row->NO_BARANG.'</a>';

                        //   return $btn;
                        //  })
                         ->addColumn('ArrivalDate', function($row){
                            if($row->master->ArrivalDate){
                              $time = Carbon::parse($row->master->ArrivalDate);
                              $display = $time->format('d/m/Y');
                              $timestamp = $time->timestamp;
                            } else {
                              $display = "-";
                              $timestamp = 0;
                            }

                            $show = [
                              'display' => $display,
                              'timestamp' => $timestamp
                            ];

                            return $show; 
                         })
                         ->editColumn('SCAN_IN_DATE', function($row){
                            if($row->SCAN_IN_DATE){
                              $time = Carbon::parse($row->SCAN_IN_DATE);
                              $display = $time->format('d/m/Y H:i');
                              $timestamp = $time->timestamp;
                            } else {
                              $display = "-";
                              $timestamp = 0;
                            }

                            $show = [
                              'display' => $display,
                              'timestamp' => $timestamp
                            ];

                            return $show; 
                         })
                         ->addColumn('ArrivalTime', function($row){
                            return $row->master->ArrivalTime;
                         })
                         ->editColumn('NO_MASTER_BLAWB', function($row){
                            return $row->mawb_parse;
                         })
                         ->addColumn('actions', function($row){
                          $rid = \Crypt::encrypt($row->id);
                          $route = route('download.calculate.house', ['house' => $rid]);
                          $btn = '<a href="'.$route.'?header=1"
                                     class="btn btn-xs elevation-2 btn-primary"
                                     target="_blank">
                                    <i class="fas fa-print"></i> Header
                                  </a>';
                          $btn .= '<a href="'.$route.'?header=0"
                                     class="btn btn-xs elevation-2 btn-info ml-1"
                                     target="_blank">
                            <i class="fas fa-print"></i> No Header
                          </a>';

                          return $btn;
                         })
                         ->rawColumns(['actions'])
                         ->toJson();
      }

      $items = collect([
        'id' => 'id',
        'NM_PEMBERITAHU' => 'Nama Pemberitahu',
        'NO_MASTER_BLAWB' => 'No Master BLAWB',
        'NO_BARANG' => 'No Barang',
        'NM_PENERIMA' => 'Nama Penerima',
        'AL_PENERIMA' => 'Alamat Penerima',
        'ArrivalDate' => 'Tanggal Tiba',
        'ArrivalTime' => 'Jam Tiba',
        'SCAN_IN_DATE' => 'Scan Time',
        'actions' => 'Actions'
      ]);

      return view('pages.sewagudang.index', compact(['items']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request, House $house)
    {
        $shipment = $house->load(['estimatedTariff', 'schemaTariff']);
        if(!$shipment->schemaTariff){
          return redirect()->back()->with('gagal', 'Please Save estimated first.');
        }
        $company = activeCompany();
        $header = $request->header;

        if(!$shipment->tariff_no){
          DB::beginTransaction();

          try {
            $running = getRunning('INV', 'TARIF', today()->format('Y-m-d'));
            $shipment->update(['tariff_no' => $running]);
            DB::commit();
          } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
          }
        }

        $pdf = PDF::setOption([
          'enable_php' => true,
        ]);
  
        if($header > 0){
          $page = 'exports.inv';
        } else {
          $page = 'exports.invnoheader';
        }
  
        $pdf->loadView($page, compact(['shipment', 'header', 'company']));
  
        return $pdf->stream();
    }
}
