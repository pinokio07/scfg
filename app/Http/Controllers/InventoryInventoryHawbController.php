<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Models\House;
use Carbon\Carbon;
use DataTables, Crypt;

class InventoryInventoryHawbController extends Controller
{    
    public function index(Request $request)
    {
        if($request->ajax()){
          $kdtps = config('app.tps.kode_tps') ?? "-";
          $kdgdng = config('app.tps.kode_gudang') ?? "-";
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

            // dd($query);
          }

          return DataTables::of($query->sortBy('created_at'))
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
                            ->editColumn('NO_MASTER_BLAWB', function($row){
                              return $row->mawb_parse;
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
                             ->editColumn('SCAN_OUT_DATE', function($row){
                              $tglplp = $row->SCAN_OUT_DATE;
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

        return view('pages.beacukai.viewinventory', compact(['items']));
    }
}
