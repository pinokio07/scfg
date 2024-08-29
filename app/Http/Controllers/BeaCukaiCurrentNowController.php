<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\House;
use App\Models\HouseTegah;
use DataTables, Crypt, Auth, DB;

class BeaCukaiCurrentNowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $tanggal = today();
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
                           ->addColumn('NO_SEGEL', function($row){
                            return $row->SEAL_NO ?? $row->master->NO_SEGEL;
                           })
                           ->editColumn('NO_SPPB', function($row){
                            return $row->SPPBNumber ?? $row->NO_SPPB ?? "-";
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
                            }

                            return $btn;
                           })
                           ->rawColumns(['Penegahan'])
                           ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'NM_PEMBERITAHU' => 'Nama Pemberitahu',
          'NO_BC11' => 'Nomor BC 11',
          'TGL_BC11' => 'Tanggal BC 11',
          'NO_POS_BC11' => 'Pos',
          'NO_FLIGHT' => 'Sarana Pengangkut',
          'NO_PLP' => 'Nomor PLP',
          'TGL_PLP' => 'Tanggal PLP',
          'NO_SEGEL' => 'Segel',
          'JML_BRG' => 'Jumlah Koli',
          'BRUTO' => 'Bruto',
          'NO_MASTER_BLAWB' => 'MAWB',
          'NO_HOUSE_BLAWB' => 'HAWB',
          'UR_BRG' => 'Uraian Barang',
          'NM_PENERIMA' => 'Consignee',
          'AL_PENERIMA' => 'Alamat',
          'NO_SPPB' => 'Nomor SPPB',
          'TGL_SPPB' => 'Tanggal SPPB',
          'Status' => 'Status',
          'SCAN_IN_DATE' => 'Tanggal dan Waktu Masuk TPS',
          'Keterangan' => 'Keterangan',
          'Penegahan' => 'Penegahan'
        ]);

        return view('pages.beacukai.currentnow', compact(['items']));
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

            return redirect('/bea-cukai/current-now')->with('sukses', 'Tegah House Success.');
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
