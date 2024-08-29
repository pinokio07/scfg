<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Models\House;
use App\Exports\InventoryExport;
use Carbon\Carbon;
use DataTables, Crypt, Excel, PDF;

class BeaCukaiInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){

          $query = Master::with(['houses' => function($h){
                            $h->withCount('sppb')
                              ->withCount('activeTegah');
                          }]);

          if($request->from
              && $request->to){
            $start = Carbon::createFromFormat('d-m-Y', $request->from);
            $end = Carbon::createFromFormat('d-m-Y', $request->to);

            $query->whereHas('houses', function($h) use ($start, $end){
              return $h->whereBetween('SCAN_IN_DATE', [
                        $start->startOfDay(),
                        $end->endOfDay()
                      ]);
            });
          }   

          return DataTables::eloquent($query)
                           ->addIndexColumn()                           
                           ->addColumn('NO_PLP', function($row){
                            return $row->PLPNumber ?? "-";
                           })
                           ->addColumn('TGL_PLP', function($row){
                            $tglplp = $row->PLPDate;
                            if($tglplp){
                              $time = Carbon::parse($tglplp);
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
                           ->editColumn('PUDate', function($row){
                            $tglplp = $row->PUDate;
                            if($tglplp){
                              $time = Carbon::parse($tglplp);
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
                           ->editColumn('MasukGudang', function($row){
                            $tglplp = $row->MasukGudang;
                            if($tglplp){
                              $time = Carbon::parse($tglplp);
                              $display = $time->format('d/m/Y H:i:s');
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
                           ->addColumn('mawb_parse', function($row){
                            $btn = '<a href="'.route('bea-cukai.inventory.show', ['inventory_mawb' => Crypt::encrypt($row->id)]).'">'.$row->mawb_parse.'</a>';

                            return $btn;
                           })
                           ->addColumn('CN_TOTAL', function($row){
                            return $row->houses->count();
                           })
                           ->addColumn('GATE_IN', function($row){
                            return $row->houses->where('SCAN_IN', 'Y')->count();
                           })
                           ->addColumn('SPPB', function($row){
                            return $row->houses->sum('sppb_count');
                           })
                           ->addColumn('PENDING', function($row){
                            return $row->houses->whereNull('SCAN_OUT')
                                               ->where('sppb_count', 0)
                                               ->count();
                           })
                           ->addColumn('GATE_OUT', function($row){
                            return $row->houses->where('SCAN_OUT', 'Y')->count();
                           })
                           ->addColumn('CURRENT_NOW', function($row){
                            return $row->houses->where('SCAN_IN', 'Y')
                                               ->whereNull('SCAN_OUT')->count();
                           })
                           ->addColumn('Keterangan', function($row){
                            $info = '';
                            $class = '';

                            if($row->houses->sum('active_tegah_count') > 0){
                              $info = 'Restricted';
                              $class = 'text-danger';
                            } else if($row->IsCompleted == true){
                              $info = 'Completed';
                              $class = 'text-success';
                            }

                            return '<span class="'.$class.'">'.$info.'</span>';
                           })
                           ->rawColumns(['mawb_parse', 'Keterangan'])
                           ->toJson();
        }

        $items = collect([
          'id' => 'id',          
          'PUNumber' => 'Nomor BC 11',
          'PUDate' => 'Tanggal BC 11',
          'POSNumber' => 'Pos',
          'FlightNo' => 'Sarana Pengangkut',
          'NO_PLP' => 'Nomor PLP',
          'TGL_PLP' => 'Tanggal PLP',
          'NO_SEGEL' => 'Segel',
          'mNoOfPackages' => 'Jumlah Koli',
          'mGrossWeight' => 'Bruto',
          'mawb_parse' => 'MAWB',
          'NM_PEMBERITAHU' => 'Nama Pemberitahu',
          'CN_TOTAL' => 'CN Total',
          'GATE_IN' => 'Gate In',
          'SPPB' => 'SPPB',
          'GATE_OUT' => 'Gate Out',
          'PENDING' => 'Pending',
          'CURRENT_NOW' => 'Current Now',
          'MasukGudang' => 'Masuk TPS',
          'Keterangan' => 'Keterangan',
        ]);

        return view('pages.beacukai.inventory', compact('items'));
    }
    
    public function show(Request $request, Master $inventory_mawb)
    {
        if($request->ajax()){
          $query = $inventory_mawb->houses();

          return DataTables::eloquent($query)
                            ->addIndexColumn()
                            ->addColumn('NO_PLP', function($row){
                              return $row->master->PLPNumber ?? "-";
                             })
                             ->addColumn('TGL_PLP', function($row){
                              $tglplp = $row->master->PLPDate;
                              if($tglplp){
                                $time = Carbon::parse($tglplp);
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
                             ->editColumn('TGL_BC11', function($row){
                              $tglplp = $row->TGL_BC11;
                              if($tglplp){
                                $time = Carbon::parse($tglplp);
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
                              $tglplp = $row->SCAN_IN_DATE;
                              if($tglplp){
                                $time = Carbon::parse($tglplp);
                                $display = $time->format('d/m/Y H:i:s');
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
                            ->editColumn('SCAN_OUT_DATE', function($row){
                              $tglplp = $row->SCAN_OUT_DATE;
                              if($tglplp){
                                $time = Carbon::parse($tglplp);
                                $display = $time->format('d/m/Y H:i:s');
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
                            ->editColumn('NO_MASTER_BLAWB', function($row){
                              return $row->mawb_parse;
                            })
                             ->addColumn('UR_BRG', function($row){
                              $brg = '';
                              $count = $row->details->count();
  
                              if($count > 0){
                                foreach ($row->details as $key => $detail) {
                                  $brg .= $detail->UR_BRG;
                                  (($key + 1) < $count) ? $brg .= ', ' : '';
                                }
                              }
  
                              return $brg;
                             })
                            ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'NO_BC11' => 'No BC 11',
          'TGL_BC11' => 'Tgl BC 11',
          'NO_POS_BC11' => 'Pos BC',
          'NO_PLP' => 'Nomor PLP',
          'TGL_PLP' => 'Tanggal PLP',
          'JML_BRG' => 'Jumlah Koli',
          'BRUTO' => 'Bruto',
          'NO_MASTER_BLAWB' => 'MAWB',
          'NO_HOUSE_BLAWB' => 'HAWB',
          'LM_TRACKING' => 'LM Tracking',
          'UR_BRG' => 'Uraian Barang',
          'NM_PENERIMA' => 'Consignee',
          'AL_PENERIMA' => 'Alamat',
          'SCAN_IN_DATE' => 'Masuk',
          'SCAN_OUT_DATE' => 'Keluar',
        ]);

        return view('pages.beacukai.viewinventory', compact(['items', 'inventory_mawb']));
    }

    public function download(Request $request)
    {
      $query = Master::with(['houses' => function($h){
              $h->withCount('sppb')
                ->withCount('activeTegah');
            }]);

      if($request->from
          && $request->to){

        $start = Carbon::createFromFormat('d-m-Y', $request->from);
        $end = Carbon::createFromFormat('d-m-Y', $request->to);

        $query->whereHas('houses', function($h) use ($start, $end){
          return $h->whereBetween('SCAN_IN_DATE', [
              $start->startOfDay(),
              $end->endOfDay()
            ]);
          });
      }
      
      $items = $query->get();

      if($request->jenis == 'xls'){

        return Excel::download(new InventoryExport($items, $start, $end), 'inventory-'.today()->format('d-m-Y').'.xlsx');

      } elseif($request->jenis == 'pdf'){
        
        $company = activeCompany();
        $jenis = 'pdf';

        $pdf = PDF::setOption([
          'enable_php' => true,
        ]);

        $pdf->loadView('exports.inventory', compact(['items', 'company', 'jenis', 'start', 'end']));

        return $pdf->setPaper('LEGAL', 'landscape')->stream();
      }
    }
}
