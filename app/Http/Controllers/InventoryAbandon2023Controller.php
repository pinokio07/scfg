<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use Carbon\Carbon;
use DataTables, Crypt;


class InventoryAbandon2023Controller extends Controller
{    
    public function index(Request $request)
    {
        if($request->ajax()){
          $tanggal = today()->subDays(30)->format('Y-m-d');
          $kdtps = config('app.tps.kode_tps') ?? "-";
          $kdgdng = config('app.tps.kode_gudang') ?? "-";

          $query = House::with(['master', 'details'])                      
                        ->whereNull('ExitDate')
                        ->whereNotNull('SCAN_IN_DATE')
                        ->where('SCAN_IN_DATE', '<', $tanggal);

          return DataTables::eloquent($query)
                          ->addIndexColumn()
                          ->addColumn('KD_TPS', function() use ($kdtps){
                            return $kdtps;
                          })
                          ->editColumn('KD_GUDANG', function() use ($kdgdng){
                            return $kdgdng;
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
                          ->addColumn('NO_SEGEL', function($row){
                            return $row->SEAL_NO ?? $row->master->NO_SEGEL;
                          })
                          ->editColumn('TGL_SPPB', function($row){
                            $tglplp = $row->SPPBDate ?? $row->TGL_SPPB;
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
                          ->addColumn('Status', function($row) use ($tanggal){
                            $dateIn = Carbon::parse($row->SCAN_IN_DATE);

                            return ($dateIn->diffInDays($tanggal, false) > 30) 
                                      ? 'Abandon' : 'Current Now';
                          })
                          ->addColumn('Keterangan', function($row){
                            return ($row->activeTegah->isNotEmpty()) ? "RESTRICTED" : "";
                          })
                          ->toJson();
        }
        $items = getNewFormat();
        $form = '';

        return view('pages.beacukai.inventory-2023', compact(['items', 'form']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
