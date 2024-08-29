<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use App\Models\HouseTegah;
use App\Exports\TegahExport;
use Carbon\Carbon;
use DataTables, Crypt, Auth, Excel, PDF, DB;

class BeaCukaiStopSystemController extends Controller
{    
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
                            ->editColumn('MAWBNumber', function($row){
                              return $row->mawb_parse;
                             })
                            ->addColumn('TanggalTegah', function($row){
                              $tglplp = optional($row->activeTegah)->first()->TanggalTegah ?? "";
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
                            ->addColumn('AlasanTegah', function($row){
                              return optional($row->activeTegah)->first()->AlasanTegah ?? "-";
                            })
                            ->addColumn('NamaPetugas', function($row){
                              return optional($row->activeTegah)->first()->NamaPetugas ?? "-";
                            })
                            ->addColumn('actions', function($row){
                              if($row->activeTegah->isNotEmpty()){
                                $tegah = $row->activeTegah->first();

                                $btn = '<button id="btnTegah_'.$row->id.'"
                                            data-toggle="modal"
                                            data-target="#modal-tegah"
                                            data-info="Lepas"
                                            class="btn btn-xs btn-success elevation-2 tegah"
                                            data-id="'.$tegah->id.'">
                                            <i class="fas fa-lock-open"></i> Lepas</button>';
                              } else {
                                $btn = '<button id="btnTegah_'.$row->id.'"
                                            data-toggle="modal"
                                            data-target="#modal-tegah"
                                            data-info="Tegah"
                                            class="btn btn-xs btn-danger elevation-2 tegah"
                                            data-id="'.Crypt::encrypt($row->id).'">
                                            <i class="fas fa-stop"></i> Stop</button>';
                              }

                              return $btn;
                            })
                            ->rawColumns(['actions'])
                            ->toJson();  
        }
        $items = collect([
          'id' => 'id',
          'MAWBNumber' => 'MAWB Number',
          'NO_HOUSE_BLAWB' => 'HAWB Number',
          'JML_BRG' => 'Koli',
          'BRUTO' => 'Bruto',
          'NM_PENERIMA' => 'Consignee',
          'AlasanTegah' => 'Alasan Tegah',
          'TanggalTegah' => 'Tanggal Tegah',
          'NamaPetugas' => 'Nama Petugas',
          'actions' => 'Action'
        ]);

        return view('pages.beacukai.stopsystem', compact(['items']));
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

            return redirect('/bea-cukai/stop-system')->with('sukses', 'Tegah House Success.');
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

    public function update(Request $request, HouseTegah $stop_system)
    {
        $data = $request->validate([
          'AlasanLepasTegah' => 'required'
        ]);

        if($data){
          $user = Auth::user();

          DB::beginTransaction();
          
          try {

            $stop_system->update([
              'TanggalLepasTegah' => now(),
              'AlasanLepasTegah' => $request->AlasanLepasTegah,
              'PetugasLepasTegah' => $user->name,
              'is_tegah' => false,
            ]);

            createLog('App\Models\House', $stop_system->house_id, 'Lepas Tegah by '.$user->name.', reason: "'.strip_tags($request->AlasanLepasTegah).'"');

            DB::commit();

            if($request->ajax()){
              return response()->json([
                'status' => 'OK',
                'message' => 'Lepas Tegah Success.'
              ]);
            }

            return redirect('/bea-cukai/stop-system')->with('sukses', 'Lepas Tegah Success.');
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

    public function download(Request $request)
    {
      if($request->jenis == 'xls'){

        return Excel::download(new TegahExport(), 'tegah-'.today()->format('d-m-Y').'.xlsx');

      } elseif($request->jenis == 'pdf'){

        $items = HouseTegah::with(['house.master.warehouseLine1'])
                            ->where('is_tegah', true)
                            ->get();
        $company = activeCompany();
        $jenis = 'pdf';

        $pdf = PDF::setOption([
          'enable_php' => true,
        ]);

        $pdf->loadView('exports.tegah', compact(['items', 'company', 'jenis']));

        return $pdf->setPaper('LEGAL', 'landscape')->stream();
      }
      
    }

}
