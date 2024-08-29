<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\House;
use App\Models\HouseTegah;
use DataTables, Crypt, Auth, DB;

class BeaCukaiCurrentNow2023Controller extends Controller
{    
    public function index(Request $request)
    {
      if($request->ajax()){
        $tanggal = today();
        $kdtps = config('app.tps.kode_tps') ?? "-";
        $kdgdng = config('app.tps.kode_gudang') ?? "-";
        if($request->tanggal){
          $tanggal = Carbon::createFromFormat('d-m-Y', $request->tanggal);
        }
        
        $query = House::with(['master', 'details', 'activeTegah'])
                        ->where(function($ex) use ($tanggal){
                          $ex->where('ExitDate', '>', $tanggal)
                            ->orWhereNull('ExitDate');
                        })
                        ->whereNotNull('SCAN_IN_DATE')
                        ->where('SCAN_IN_DATE', '<=', $tanggal);
                        
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
                        ->addColumn('Penegahan', function($row){
                          $btn = '';
                          if($row->activeTegah->isEmpty()){
                            $btn = '<button id="btnTegah_'.$row->id.'"
                                          data-toggle="modal"
                                          data-target="#modal-tegah"
                                          class="btn btn-xs btn-danger elevation-2 tegah"
                                          data-id="'.Crypt::encrypt($row->id).'">
                                          <i class="fas fa-stop"></i> Stop</button>';
                          } else {
                            $btn = '<span class="text-danger">RESTRICTED</span>';
                          }

                          return $btn;
                         })
                         ->rawColumns(['Penegahan'])
                        ->toJson();
      }

      $items = collect([
        'id' => 'id',
        'KD_TPS' => 'Kode TPS',
        'NM_PENGANGKUT' => 'Nama Pengangkut',
        'NO_FLIGHT' => 'No. Voy/Flight',
        'TGL_TIBA' => 'Tgl. Tiba',
        'KD_GUDANG' => 'Kd. Gudang',
        'TPS_GateInREF' => 'Ref Number',
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
        'Penegahan' => 'Penegahan'
      ]);
      $form = 'current-now-bc';

      return view('pages.beacukai.inventory-2023', compact(['items', 'form']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
          'house_id' => 'required',
          'AlasanTegah' => 'required'
        ]);

        if($data){
          $house = House::findOrFail(Crypt::decrypt($request->house_id));
          $user = Auth::user();
          DB::beginTransaction();

          try {
            $tegah = HouseTegah::create([
              'house_id' => $house->id,
              'HAWBNumber' => $house->NO_HOUSE_BLAWB,
              'HAWBDate' => $house->TGL_HOUSE_BLAWB,
              'MAWBNumber' => $house->NO_MASTER_BLAWB,
              'MAWBDate' => $house->TGL_MASTER_BLAWB,
              'TanggalTegah' => now(),
              'AlasanTegah' => $request->AlasanTegah,
              'NamaPetugas' => $user->name,
              'Consignee' => $house->NM_PENERIMA,
              'Bruto' => $house->BRUTO,
              'Koli' => $house->JML_BRG
            ]);

            createLog('App\Models\House', $house->id, 'Tegah by '.$user->name.', reason: "'.strip_tags($request->AlasanTegah).'"');

            DB::commit();

            if($request->ajax()){
              return response()->json([
                'status' => 'OK',
                'message' => 'Tegah house Success.'
              ]);
            }

            return redirect('/bea-cukai/current-now-2023')->with('sukses', 'Tegah House Success.');
          } catch (\Throwable $th) {
            DB::rollback();

            if($request->ajax()){
              return response()->json([
                'status' => 'ERROR',
                'message' => $th->getMessage()
              ]);
            }

            throw $th;
          }
        }
    }
}
