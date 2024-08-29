<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Models\House;
use App\Exports\InventoryDetailExport;
use Carbon\Carbon;
use DataTables, Crypt, Excel, PDF;

class BeaCukaiInventory2023Controller extends Controller
{
    public function index(Request $request)
    {
      if($request->ajax()){
        $query1 = House::query();
        $query2 = House::query();

        if($request->from
            && $request->to){
          $start = Carbon::createFromFormat('d-m-Y', $request->from);
          $end = Carbon::createFromFormat('d-m-Y', $request->to);

          $h1 = $query1->whereBetween('SCAN_IN_DATE', [
                        $start->startOfDay(),
                        $end->endOfDay()
                      ])
                      ->whereNotNull('TPS_GateInStatus')
                      ->with('master')
                      ->get();

          $h1->map(function($h){
            $h->SCAN_OUT_DATE = null;
            $h->KD_DOK_INOUT = 3;
            $plpdate = ($h->master->PLPDate) ? \Carbon\Carbon::parse($h->master->PLPDate)->format('Y') : '';
            $h->NO_DOK_INOUT = $h->master->PLPNumber.'/PLP/'.($plpdate);
            $h->TGL_DOK_INOUT = str_replace('-','', $h->master->PLPDate);

            return $h;
          });

          $h2 = $query2->whereBetween('SCAN_OUT_DATE', [
                        $start->startOfDay(),
                        $end->endOfDay()
                      ])
                      ->whereNotNull('TPS_GateOutStatus')
                      ->with('master')
                      ->get();
          $query = $h1->toBase()->merge($h2);
        }

        return DataTables::of($query->sortBy('created_at'))
                          ->addIndexColumn()
                          ->addColumn('KD_TPS', function(){
                            return config('app.tps.kode_tps') ?? "-";
                          })
                          ->editColumn('KD_GUDANG', function(){
                            return config('app.tps.kode_gudang') ?? "-";
                          })
                          ->editColumn('NO_PLP', function($row){
                            return $row->master->PLPNumber ?? "-";
                          })
                          ->editColumn('NO_POS_BC11', function($row){
                            return $row->NO_POS_BC11.$row->NO_SUBPOS_BC11.str_pad($row->NO_SUBSUBPOS_BC11, 4, 0, STR_PAD_LEFT);
                          })
                          ->editColumn('TGL_TIBA', function($row){
                            $tglplp = $row->TGL_TIBA;
                            if($tglplp){
                              $time = Carbon::parse($tglplp);
                              $display = $time->format('Ymd');
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
                          ->editColumn('TGL_PLP', function($row){
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
                              $display = $time->format('Ymd');
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
                          ->editColumn('TGL_SEGEL_BC', function($row){
                            $tglplp = $row->SEAL_DATE ?? $row->SPPBDate;
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
                           ->editColumn('TGL_DAFTAR_PABEAN', function($row){
                            $tglplp = $row->TGL_DAFTAR_PABEAN;
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
                          ->editColumn('NO_MASTER_BLAWB', function($row){    
                            return $row->mawb_parse;
                           })
                          ->editColumn('TGL_HOUSE_BLAWB', function($row){
                            $tglplp = $row->TGL_HOUSE_BLAWB;
                            if($tglplp){
                              $time = Carbon::parse($tglplp);
                              $display = $time->format('Ymd');
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
                          ->editColumn('TGL_MASTER_BLAWB', function($row){
                            $tglplp = $row->TGL_HOUSE_BLAWB;
                            if($tglplp){
                              $time = Carbon::parse($tglplp);
                              $display = $time->format('Ymd');
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
                          ->addColumn('KD_DOK_INOUT', function($row){
                            if($row->KD_DOK_INOUT){
                              $kd = $row->KD_DOK_INOUT;
                            } else {
                              $kd = $row->JNS_AJU;
                            }

                            return $kd;
                           })
                          ->addColumn('NO_DOK_INOUT', function($row){
                            if($row->NO_DOK_INOUT){
                              $kd = $row->NO_DOK_INOUT;
                            } else {
                              $kd = $row->SPPBNumber;
                            }

                            return $kd;
                           })
                          ->addColumn('TGL_DOK_INOUT', function($row){
                              if($row->TGL_DOK_INOUT){
                                $tglplp = $row->TGL_DOK_INOUT;
                              } else {
                                $tglplp = $row->SPPBDate;
                              }
                              
                              if($tglplp){
                                $time = Carbon::parse($tglplp);
                                $display = $time->format('Ymd');
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
                           ->addColumn('WK_DOK_INOUT', function($row){
                            if($row->SCAN_OUT_DATE){
                              $tglplp = $row->SCAN_OUT_DATE;
                            } else {
                              $tglplp = $row->SCAN_IN_DATE;
                            }
                            
                            if($tglplp){
                              $time = Carbon::parse($tglplp);
                              $display = $time->format('YmdHis');
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
                          ->addColumn('REF_NUM', function($row){
                            if($row->SCAN_OUT_DATE){
                              $ref = $row->TPS_GateOutREF;
                            } else {
                              $ref = $row->TPS_GateInREF;
                            }

                            return $ref;
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
                          ->addColumn('NO_POLISI', function(){
                            return "-";
                           })
                          ->toJson();
      }
      $items = collect([
        'id' => 'id',
        'KD_TPS' => 'Kode TPS',
        'NM_PENGANGKUT' => 'Nama Pengangkut',
        'NO_FLIGHT' => 'No. Voy/Flight',
        'TGL_TIBA' => 'Tgl. Tiba',
        'KD_GUDANG' => 'Kd. Gudang',
        'REF_NUM' => 'Ref Number',
        'NO_HOUSE_BLAWB' => 'No BL/AWB',
        'TGL_HOUSE_BLAWB' => 'Tgl BL/AWB',
        'NO_MASTER_BLAWB' => 'No Master BL/AWB',
        'TGL_MASTER_BLAWB' => 'Tgl Master BL/AWB',
        'NO_ID_PENERIMA' => 'Id Consignee',
        'NM_PENERIMA' => 'Consignee',
        'BRUTO' => 'Bruto',
        'JNS_KMS' => 'Kode Kemasan',
        'JML_BRG' => 'Jumlah Kemasan',
        'KD_DOK_INOUT' => 'Kd Dok In/Out',
        'NO_DOK_INOUT' => 'No Dok In/Out',
        'TGL_DOK_INOUT' => 'Tgl Dok In/Out',
        'WK_DOK_INOUT' => 'Waktu In/Out',
        'NO_POLISI' => 'Nomor Polisi',
        'NO_BC11' => 'No BC 11',
        'TGL_BC11' => 'Tgl BC 11',
        'NO_POS_BC11' => 'No Pos BC',
        'KD_PEL_MUAT' => 'Pel Muat',
        'KD_PEL_TRANSIT' => 'Pel Transit',
        'KD_PEL_BONGKAR' => 'Pel Bongkar',
        'NO_DAFTAR_PABEAN' => 'No Daftar Pabean',
        'TGL_DAFTAR_PABEAN' => 'Tgl Daftar Pabean',
        'SEAL_NO' => 'No Segel BC',
        'TGL_SEGEL_BC' => 'Tgl Segel BC',
      ]);
      $form = 'inventory';

      return view('pages.beacukai.inventory-2023', compact(['items', 'form']));
    }
}
