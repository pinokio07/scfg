<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use League\Flysystem\UnableToWriteFile;
use Illuminate\Http\Request;
use App\Helpers\SoapHelper;
use App\Helpers\Barkir;
use App\Models\SchedulerLog;
use App\Models\KodeRes;
use App\Models\Master;
use App\Models\IdModul;
use App\Models\MasterLegacy;
use App\Models\MasterBatch;
use App\Models\GlbBranch;
use App\Models\House;
use App\Models\Sppb;
use App\Models\PermitLegacy;
use App\Models\PlpOnline;
use App\Models\PlpOnlineLog;
use App\Models\BillingLog;
use App\Models\BillingBatch;
use App\Models\BillingConsolidation;
use App\Models\BillingConsolidationLegacy;
use App\Models\BillingConsolBatch;
use App\Models\BillingConsolidationDetail;
use App\Models\BillingConsolidationSppbmcp;
use App\Jobs\KerryScenarioJob;
use App\Jobs\TarikResponJob;
use App\Jobs\SyncLegacyJob;
use Carbon\Carbon;
use Str, Arr, Auth, DB, Config;

class SchedulerController extends Controller
{
    public function index(Request $request)
    {
       $jenis = $request->jenis;
       $id = $request->id ?? NULL;

       switch ($jenis) {
        case 'ftpin':
          return $this->ftpin($request, $id);
          break;
        case 'ftpout':
          return $this->ftpout($request, $id);
          break;
        case 'gatein':
          return $this->gatein($request, $id);
          break;
        case 'gateout':
          return $this->gateout($request, $id);
          break;
        case 'importpermit':
          return $this->importpermit();
          break;
        case 'bc23permit':
          return $this->bc23permit();
          break;
        case 'bc16permit':
          return $this->bc16permit();
          break;
        case 'manualpermit':
          return $this->manualpermit();
          break;
        case 'plp':
          return $this->plp();
          break;
        case 'sppbpib':          
          return $this->sppbOnDemand($request);
          break;
        case 'sppbbc23':
          return $this->sppbBc23OnDemand($request);
          break;
        case 'sppbbc16':
          return $this->sppbBc16OnDemand($request);
          break;
        case 'manual':
          return $this->manualOnDemand($request);
          break;
        case 'getbilling':
          return $this->getBilling();
          break;
        case 'billing':
          return $this->billing($request);
          break;
        case 'respon':
          return $this->respon();
          break;        
        default:
          # code...
          break;
       }
    }

    public function ftpin(Request $request, $id = NULL)
    {        
        if(!$id){
          $houses = House::where('SCAN_IN', 'Y')
                          ->whereNull('CW_Ref_GateIn')
                          ->limit(5)
                          ->get();
          if(!$houses){
            echo "No house found for sending FTP IN.";
          }
          foreach ($houses as $house) {
            $date = Carbon::parse($house->SCAN_IN_DATE);

            DB::beginTransaction();

            echo "Begin Transaction";

            try {
              $giwia = $this->createInXML($house, $date->setTimeZone('UTC'));

              if($giwia){
                $house->update(['CW_Ref_GateIn' => $giwia]);
              
                createLog('App\Models\House', $house->id, 'Create file '.$giwia.' at '.now()->translatedFormat('l d F Y H:i'));

                DB::commit();

                echo 'Berhasil kirim file FTP '.$giwia." menunggu 1 detik untuk proses selanjutnya ....<br/>";

              } else {

                echo 'Gagal kirim file FTP, menunggu 1 detik untuk proses selanjutnya ....<br/>';

              }              

            } catch (\Throwable $th) {
              DB::rollback();

              echo 'Gagal kirim file FTP, Status:'.$th->getMessage().' menunggu 1 detik untuk proses selanjutnya ....<br/>';
            }

            sleep(1);
          }
          if($request->auto > 0){
            $jenis = $request->jenis;
            return view('scheduler', compact(['jenis']));
          }
        } else {
          $house = House::findOrFail($id);
          
          $date = Carbon::parse($house->SCAN_IN_DATE);

          DB::beginTransaction();

          try {
            $giwia = $this->createInXML($house, $date->setTimeZone('UTC'));

            if($giwia){
              $house->update(['CW_Ref_GateIn' => $giwia]);
            
              createLog('App\Models\House', $house->id, 'Create file '.$giwia.' at '.now()->translatedFormat('l d F Y H:i'));

              DB::commit();

              if($request->ajax()){
                return response()->json([
                  'status' => 'OK',
                  'message' => 'Send SFTP Success, file : '.$giwia
                ]);
              }

              echo 'Berhasil kirim file FTP '.$giwia." menunggu 1 detik untuk proses selanjutnya ....<br/>";
            } else {

              if($request->ajax()){
                return response()->json([
                  'status' => 'ERROR',
                  'message' => 'Send SFTP Failed, reason : '.$giwia
                ]);
              }
  
              echo 'Gagal kirim file FTP, menunggu 1 detik untuk proses selanjutnya ....<br/>';

            }            

          } catch (\Throwable $th) {
            
            DB::rollback();

            if($request->ajax()){
              return response()->json([
                'status' => 'ERROR',
                'message' => $th->getMessage()
              ]);
            }

            echo 'Gagal kirim file FTP, ERROR : '.$th->getMessage().' menunggu 1 detik untuk proses selanjutnya ....<br/>';
          }
        }        
    }

    public function ftpout(Request $request, $id = NULL)
    {
        if(!$id){
          $houses = House::where('SCAN_OUT', 'Y')
                          ->whereNull('CW_Ref_GateOut')
                          ->limit(5)
                          ->get();
          if(!$houses){
            echo "No house found for sending FTP OUT.";
          }
          foreach ($houses as $house) {
            $date = Carbon::parse($house->SCAN_OUT_DATE);

            DB::beginTransaction();

            echo "Begin Transaction";

            try {
              $gowia = $this->createOutXML($house, $date->setTimeZone('UTC'));

              if($gowia){
                $house->update(['CW_Ref_GateOut' => $gowia]);

                createLog('App\Models\House', $house->id, 'Create file '.$gowia.' at '.now()->translatedFormat('l d F Y H:i'));

                DB::commit();

                echo 'Berhasil kirim file FTP '.$gowia." menunggu 1 detik untuk proses selanjutnya ....<br/>";
              } else {
                echo 'Gagal kirim file FTP, menunggu 1 detik untuk proses selanjutnya ....<br/>';
              }              
            
            } catch (\Throwable $th) {
              DB::rollback();

              echo 'Gagal kirim file FTP, Status:'.$th->getMessage().' menunggu 1 detik untuk proses selanjutnya ....<br/>';
            }
            sleep(1);
          }
          if($request->auto > 0){
            $jenis = $request->jenis;
            return view('scheduler', compact(['jenis']));
          }
        } else {
          $house = House::findOrFail($id);

          $date = Carbon::parse($house->SCAN_OUT_DATE);

          DB::beginTransaction();

          try {
            $gowia = $this->createOutXML($house, $date->setTimeZone('UTC'));

            if($gowia){
              $house->update(['CW_Ref_GateOut' => $gowia]);

              createLog('App\Models\House', $house->id, 'Create file '.$gowia.' at '.now()->translatedFormat('l d F Y H:i'));

              DB::commit();

              if($request->ajax()){
                return response()->json([
                  'status' => 'OK',
                  'message' => 'Send SFTP Success, file : '.$gowia
                ]);
              }

              echo 'Berhasil kirim file FTP....<br/>';
            } else {
              if($request->ajax()){
                return response()->json([
                  'status' => 'ERROR',
                  'message' => 'Send SFTP Failed, code : '.$gowia
                ]);
              }
  
              echo 'Gagal kirim file FTP, menunggu 1 detik untuk proses selanjutnya ....<br/>';
            }
          
          } catch (\Throwable $th) {
            DB::rollback();

            if($request->ajax()){
              return response()->json([
                'status' => 'ERROR',
                'message' => $th->getMessage(),
              ]);
            }

            echo 'Gagal kirim file FTP, Status:'.$th->getMessage().' menunggu 1 detik untuk proses selanjutnya ....<br/>';
          }
        }        
    }

    public function gatein(Request $request, $id = NULL)
    {
      if(!$id){
        $houses = House::whereNotNull('SCAN_IN_DATE')
                        // ->whereNotNull('PLP_SETUJU_DATE')
                        ->whereNull('TPS_GateInREF')
                        ->where('TGL_TIBA', '>', '2024-07-01')
                        ->whereNotNull('NO_BC11')
                        ->where('NO_BC11', '<>', '')
                        // ->whereIn('BRANCH', [1, 2]) // Remove when barkir live
                        ->whereHas('master', function($m){
                          return $m->whereNotNull('PLPNumber')
                                   ->whereNotNull('PLPDate');
                        })
                        ->with(['master'])
                        ->limit(50)
                        ->get();

        if(count($houses) == 0){
          \Log::warning("No house found for gate in.");
          echo "No house found for gate in.";
        }

        foreach ($houses as $house) {
          \Log::notice("Start Scheduler Gate In for ".$house->NO_BARANG);

          $hasil = $this->sendGateIn($request, $house);

          \Log::info('Kirim Gate In '.$house->NO_BARANG.' Status: '.$hasil['status'].'; Info : '.$hasil['info']);

          echo 'Kirim ulang Gate In '.$house->NO_BARANG.' Status: '.$hasil['status'].'; Info : '.$hasil['info']." menunggu proses AWB selanjutnya ....<br/>";
        }

        if($request->auto > 0){
          $jenis = $request->jenis;
          return view('scheduler', compact(['jenis']));
        }

      } else {
        $house = House::findOrFail($id);
        $hasil = $this->sendGateIn($request, $house, true);

        if($request->ajax()){
          return response()->json($hasil);
        }

        echo 'Kirim ulang Gate In '.$house->NO_BARANG.' Status: '.$hasil['status'].'; Info : '.$hasil['info']."<br/>";
      }      
    }

    public function gateout(Request $request, $id = NULL)
    {
      if(!$id){
        $houses = House::whereNotNull('SCAN_IN_DATE')
                        ->whereNotNull('SCAN_OUT_DATE')
                        ->whereNotNull('NO_BC11')
                        ->where('NO_BC11', '<>', '')
                        ->whereNull('TPS_GateOutREF')
                        ->where('TGL_TIBA', '>', '2024-07-01')
                        ->where(function($h){
                          $h->whereNotNull('NO_DAFTAR_PABEAN')
                            // ->orWhereIn('JNS_AJU', [1,2]);
                            ->orWhere(function($hb){
                              $hb->whereIn('JNS_AJU', [1,2])
                                 ->whereHas('bclog', function($bc){
                                  return $bc->whereIn('BC_CODE', [401,403,404]);
                                 });
                            });
                        })
                        // ->whereIn('BRANCH', [1,2]) // Remove when barkir live
                        // ->whereNotNull('NO_DAFTAR_PABEAN') // Remove when barkir live
                        ->limit(50)
                        ->get();
        if(count($houses) == 0){
          \Log::warning('No house found for gate out.');
          echo "No house found for gate out.";
        }
                        
        foreach ($houses as $house) {
          if(!$house->NO_DAFTAR_PABEAN && !in_array($house->JNS_AJU, [1,2])){
            if($request->ajax()){
              return response()->json(['status' => "ERROR", 'message' => 'Pabean No is Empty']);
            }
            \Log::warning('No Pabean kosong untuk hawb '.$house->NO_BARANG);
            echo 'NO PABEAN KOSONG';
          } else {
            \Log::notice("Start Scheduler Gate Out for ".$house->NO_BARANG);

            $hasil = $this->sendGateOut($request, $house);

            \Log::info('Kirim Gate Out '.$house->NO_BARANG.' Status: '.$hasil['status'].'; Info : '.$hasil['info']);

            echo 'Kirim ulang Gate Out '.$house->NO_BARANG.' Status: '.$hasil['status'].'; Info : '.$hasil['info']." menunggu proses AWB selanjutnya ....<br/>";
          }          
        }
        if($request->auto > 0){
          $jenis = $request->jenis;
          return view('scheduler', compact(['jenis']));
        }
      } else {
        $house = House::findOrFail($id);
        if(!$house->NO_DAFTAR_PABEAN){
          if($request->ajax()){
            return response()->json(['status' => "ERROR", 'message' => 'Pabean No is Empty']);
          }
          echo 'NO PABEAN KOSONG';
        } else {
          $hasil = $this->sendGateOut($request, $house, true);

          if($request->ajax()){
            return response()->json($hasil);
          }
  
          echo 'Kirim ulang Gate Out '.$house->NO_BARANG.' Status: '.$hasil['status'].'; Info : '.$hasil['info']."<br/>";
        }        
      }      
    }

    public function plp()
    {
        $sh = new SoapHelper;  
        $soap = $sh->soap();
        
        DB::beginTransaction();
        \Log::notice('Scheduler PLP Start');
        try {
          $sResponse = $soap->GetResponPLP_Tujuan(
            [
              'UserName' => config('app.tps.user'),
              'Password' => config('app.tps.password'), 
              'Kd_ASP' => config('app.tps.kode_tps')
            ]);

          $response =  $soap->__getLastResponse();
          $request =  $soap->__getLastRequest();
          
          $resParse = $this->getResults('GetResponPLPTujuanResult', $response);

          if(strpos($resParse, 'RESPONPLP')){
            $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $resParse);
            $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
            $sppb = $sh->getResults('DOCUMENT', $hasil);
            $res = simplexml_load_string($sppb);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);

            if (!array_key_exists('HEADER', $data)) {
              foreach ($data as $plp) {

                $this->updatePlp($plp, 'Scheduler');

              }
            } else {              

              $this->updatePlp($data, 'Scheduler');

            }
          } else {
            $res = $this->getResults('KD_RES', $resParse);

            $kodeRes = KodeRes::where('kode', $res)->first();

            $status = 'ERROR';
            $message = ($kodeRes) 
                        ? $kodeRes->kode . ' - ' .$kodeRes->uraian
                        : $resParse;           
          }

          $this->createLog(NULL, NULL, 'GetResponPLP_Tujuan', $request, $response, $message);
          \Log::info($message);
          DB::commit();
          
        } catch (\Throwable $th) {
          DB::rollback();
          
          \Log::error('Request : <br/><xmp>',
                  $soap->__getLastRequest(),
                  '</xmp><br/><br/> Error Message : <br/>',
                  $th->getMessage());

          echo 'Request : <br/><xmp>',
                  $soap->__getLastRequest(),
                  '</xmp><br/><br/> Error Message : <br/>',
                  $th->getMessage();
        }
    }

    public function sppbOnDemand(Request $request)
    {
      $validate = $request->validate([
        'no_sppb' => 'required',
        'tgl_sppb' => 'required|date'
      ]);

      if($validate){
        $hid = NULL;
        $mdl = NULL;
        if($request->house_id){
          $house = House::findOrFail($request->house_id);
          $hid = $request->house_id;
          $mdl = 'App\Models\House';
        }
        $sh = new SoapHelper;
        $tglparse = Carbon::parse($request->tgl_sppb)->format('dmY');
  
        $soap = $sh->soap();
  
        DB::beginTransaction();
  
        try {
          
          $sResponse = $soap->GetImpor_SPPB(
            array(
              'UserName' => config('app.tps.user'),
              'Password' => config('app.tps.password'),
              'No_Sppb' => $request->no_sppb,
              'Tgl_Sppb' => $tglparse,
              'NPWP_Imp' => $request->npwp_imp,
            ));
            
          $LastRequest =  $soap->__getLastRequest();
          $soapResponse = $soap->__getLastResponse();

          $log = $this->createLog($mdl, $hid, 'GetImpor_SPPB', $LastRequest, $soapResponse, 'PENDING');
  
          DB::commit();

          $response = $sh->getResults('GetImpor_SppbResult', $soapResponse);          
          
          if ($response != ''
              && !str_contains($response, 'Belum ada data baru')
              && !str_contains($response, 'Data tidak ditemukan')
              && !str_contains($response, 'Anda tidak berhak mengambil data ini')
              && !str_contains($response, 'Anda tidak mempunyai hak akses terhadap gudang')
              && !str_contains($response, 'Maaf coba cek kembali Nomor SPPB')) {

            $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
            $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
            $sppb = $sh->getResults('DOCUMENT', $hasil);
            $res = simplexml_load_string($sppb);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);

            $dataPib = [];
  
            if (!array_key_exists('HEADER', $data)) {
              foreach ($data as $sppb) {
                $c = $this->updatePib($sppb, 'On Demand');
                if($c > 0)
                {
                  $dataToSave = $this->getDataPIB($sppb);

                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $sppb["HEADER"]["CAR"]
                                  ], $dataToSave);
  
                  $dataPib[]  = [
                    'NO_SPPB' => $data['HEADER']['NO_SPPB'],
                    'TGL_SPPB' => date('Y-m-d', strtotime($data['HEADER']['TGL_SPPB'])),
                    'NO_PIB' => $data['HEADER']['NO_PIB'],
                    'TGL_PIB' => date('Y-m-d', strtotime($data['HEADER']['TGL_PIB'])),
                    'CAR' => $data['HEADER']['CAR']
                  ];
                }                
              }
            } else {
              $c = $this->updatePib($data, 'On Demand');
              if($c > 0)
              {
                $dataToSave = $this->getDataPIB($data);

                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $data["HEADER"]["CAR"]
                                ], $dataToSave);
  
                $dataPib[]  = [
                  'NO_SPPB' => $data['HEADER']['NO_SPPB'],
                  'TGL_SPPB' => date('Y-m-d', strtotime($data['HEADER']['TGL_SPPB'])),
                  'NO_PIB' => $data['HEADER']['NO_PIB'],
                  'TGL_PIB' => date('Y-m-d', strtotime($data['HEADER']['TGL_PIB'])),
                  'CAR' => $data['HEADER']['CAR']
                ];
              }              
            }
            DB::commit();
  
            $log->update(['info' => 'COMPLETE']);
  
            DB::commit();

            return response()->json([
              'status' => 'OK',
              'message' => 'Berhasil mendapatkan data SPPB',
              'sppb' => $dataPib
            ]);
  
            echo "Fetch Permit Complete.";
  
          } else {
            $log->update(['info' => 'GAGAL : '.$response]);
            return response()->json([
              'status' => 'ERROR',
              'message' => $response
            ]);
          }  
  
        } catch (\Throwable $th) {
          DB::rollback();
  
          $this->createLog($mdl, $hid, 'GetImpor_SPPB', NULL, NULL, 'FAILED : '.$th->getMessage());
  
          DB::commit();

          if($request->ajax()){
            return response()->json([
              'status' => 'ERROR',
              'message' => $th->getMessage()
            ]);
          }
  
          echo 'Request : <br/><xmp>',
                  $soap->__getLastRequest(),
                  '</xmp><br/><br/> Error Message : <br/>',
                  $th->getMessage();
        }
      }
      
    }

    public function sppbBc23OnDemand(Request $request)
    {
      $validate = $request->validate([
        'no_sppb' => 'required',
        'tgl_sppb' => 'required|date'
      ]);

      if($validate){
        $hid = NULL;
        $mdl = NULL;
        if($request->house_id){
          $house = House::findOrFail($request->house_id);
          $hid = $house->id;
          $mdl = 'App\Models\House';
        }
        $sh = new SoapHelper;
        $tglparse = Carbon::parse($request->tgl_sppb)->format('dmY');
          
        $soap = $sh->soap();
  
        DB::beginTransaction();
  
        try {
          
          $sResponse = $soap->GetSppb_Bc23(
            array(
              'UserName' => config('app.tps.user'),
              'Password' => config('app.tps.password'),
              'No_Sppb' => $request->no_sppb,
              'Tgl_Sppb' => $tglparse,
              'NPWP_Imp' => $request->npwp_imp,
            ));
            
          $LastRequest =  $soap->__getLastRequest();
          $soapResponse = $soap->__getLastResponse();
          
          $log = $this->createLog($mdl, $hid, 'GetSppb_Bc23', $LastRequest, $soapResponse, 'PENDING');

          DB::commit();

          $response = $sh->getResults('GetSppb_Bc23Result', $soapResponse);
          
          if ($response != ''
              && !str_contains($response, 'Belum ada data baru')
              && !str_contains($response, 'Data tidak ditemukan')
              && !str_contains($response, 'Anda tidak berhak mengambil data ini')
              && !str_contains($response, 'Anda tidak mempunyai hak akses terhadap gudang')
              && !str_contains($response, 'Maaf coba cek kembali Nomor SPPB')) {   
            
            $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
            $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
            $sppb = $sh->getResults('DOCUMENT', $hasil);
            $res = simplexml_load_string($sppb);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);
            
            $dataPib = [];
  
            if (!array_key_exists('HEADER', $data)) {
              foreach ($data as $sppb) {
                $c = $this->updateBc($sppb, 'On Demand');
                if($c > 0)
                {
                  $dataToSave = $this->getDataBC23($sppb);

                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $sppb["HEADER"]["CAR"]
                                  ], $dataToSave);
  
                  $dataPib[]  = [
                    'NO_SPPB' => $sppb['HEADER']['NO_SPPB'],
                    'TGL_SPPB' => date('Y-m-d', strtotime($sppb['HEADER']['TGL_SPPB'])),
                    'NO_PIB' => $sppb['HEADER']['NO_PIB'],
                    'TGL_PIB' => date('Y-m-d', strtotime($sppb['HEADER']['TGL_PIB'])),
                    'CAR' => $sppb['HEADER']['CAR']
                  ];
                }
              }
            } else {
              $c = $this->updateBc($data, 'On Demand');
              if($c > 0)
              {
                $dataToSave = $this->getDataBC23($data);

                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $data["HEADER"]["CAR"]
                                ], $dataToSave);
  
                $dataPib[]  = [
                  'NO_SPPB' => $data['HEADER']['NO_SPPB'],
                  'TGL_SPPB' => date('Y-m-d', strtotime($data['HEADER']['TGL_SPPB'])),
                  'NO_PIB' => $data['HEADER']['NO_PIB'],
                  'TGL_PIB' => date('Y-m-d', strtotime($data['HEADER']['TGL_PIB'])),
                  'CAR' => $data['HEADER']['CAR']
                ];
              }              
            }
            DB::commit();
  
            $log->update(['info' => 'COMPLETE']);
  
            DB::commit();

            return response()->json([
              'status' => 'OK',
              'message' => 'Berhasil mendapatkan data SPPB',
              'sppb' => $dataPib
            ]);
  
            echo "Fetch Permit Complete.";
  
          } else {
            $log->update(['info' => 'GAGAL : '.$response]);
            return response()->json([
              'status' => 'ERROR',
              'message' => $response
            ]);
          }  
  
        } catch (\Throwable $th) {
          DB::rollback();
  
          $this->createLog($mdl, $hid, 'GetSppb_Bc23', NULL, NULL, 'FAILED : '.$th->getMessage());
  
          DB::commit();

          if($request->ajax()){
            return response()->json([
              'status' => 'ERROR',
              'message' => $th->getMessage()
            ]);
          }
  
          echo 'Request : <br/><xmp>',
                  $soap->__getLastRequest(),
                  '</xmp><br/><br/> Error Message : <br/>',
                  $th->getMessage();
        }
      }
      
    }
    
    public function sppbBc16OnDemand(Request $request)
    {
      $validate = $request->validate([
        'no_sppb' => 'required',
        'tgl_sppb' => 'required|date',
      ]);

      if($validate){
        $hid = NULL;
        $mdl = NULL;
        if($request->house_id){
          $house = House::findOrFail($request->house_id);
          $hid = $house->id;
          $mdl = 'App\Models\House';
        }
        $sh = new SoapHelper;
        $tglparse = Carbon::parse($request->tgl_sppb)->format('dmY');
  
        $soap = $sh->soap();
  
        DB::beginTransaction();
  
        try {
          
          $sResponse = $soap->GetDokumenPabean_OnDemand(
            array(
              'UserName' => config('app.tps.user'),
              'Password' => config('app.tps.password'),
              'KdDok' => 41,
              'NoDok' => $request->no_sppb,
              'TglDok' => $tglparse,              
            ));
            
          $LastRequest =  $soap->__getLastRequest();
          $soapResponse = $soap->__getLastResponse();          
          
          $log = $this->createLog($mdl, $hid, 'GetDokumenPabean_OnDemand', $LastRequest, $soapResponse, 'PENDING');
          
          DB::commit();

          $response = $sh->getResults('GetDokumenPabean_OnDemandResult', $soapResponse);          
          
          if ($response != ''
              && !str_contains($response, 'Belum ada data baru')
              && !str_contains($response, 'Data Tidak Ditemukan')
              && !str_contains($response, 'Anda tidak berhak mengambil data ini')
              && !str_contains($response, 'Anda tidak mempunyai hak akses terhadap gudang')
              && !str_contains($response, 'Maaf coba cek kembali Nomor SPPB')) {

            $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
            $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
            $sppb = $sh->getResults('DOCUMENT', $hasil);
            $res = simplexml_load_string($sppb);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);
            
            $dataPib = [];
  
            if (!array_key_exists('HEADER', $data)) {
              foreach ($data as $sppb) {
                $c = $this->updateBc16($sppb, 'On Demand');
                if($c > 0)
                {
                  $dataToSave = $this->getDataBc16($sppb);

                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $sppb["HEADER"]["CAR"]
                                  ], $dataToSave);
  
                  $dataPib[]  = [
                    'NO_SPPB' => $sppb['HEADER']['NO_DOK_INOUT'],
                    'TGL_SPPB' => date('Y-m-d', strtotime($sppb['HEADER']['TGL_DOK_INOUT'])),
                    'NO_PIB' => $sppb['HEADER']['NO_DOK_INOUT'],
                    'TGL_PIB' => date('Y-m-d', strtotime($sppb['HEADER']['TGL_DOK_INOUT'])),
                    'CAR' => $sppb['HEADER']['CAR']
                  ];
                }                
              }
            } else {

              $c = $this->updateBc16($data, 'On Demand');
              if($c > 0)
              {
                $dataToSave = $this->getDataBc16($data);
              
                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $data["HEADER"]["CAR"]
                                ], $dataToSave);
                                
                $dataPib[]  = [
                  'NO_SPPB' => $data['HEADER']['NO_DOK_INOUT'],
                  'TGL_SPPB' => date('Y-m-d', strtotime($data['HEADER']['TGL_DOK_INOUT'])),
                  'NO_PIB' => $data['HEADER']['NO_DOK_INOUT'],
                  'TGL_PIB' => date('Y-m-d', strtotime($data['HEADER']['TGL_DOK_INOUT'])),
                  'CAR' => $data['HEADER']['CAR']
                ];
              }              
            }
            DB::commit();
  
            $log->update(['info' => 'COMPLETE']);
  
            DB::commit();

            return response()->json([
              'status' => 'OK',
              'message' => 'Berhasil mendapatkan data SPPB',
              'sppb' => $dataPib
            ]);
  
            echo "Fetch Permit Complete.";
  
          } else {
            $log->update(['info' => 'GAGAL : '.$response]);
            return response()->json([
              'status' => 'ERROR',
              'message' => $response
            ]);
          }  
  
        } catch (\Throwable $th) {
          DB::rollback();
  
          $this->createLog($mdl, $hid, 'GetDokumenPabean_OnDemand', NULL, NULL, 'FAILED : '.$th->getMessage());
  
          DB::commit();

          if($request->ajax()){
            return response()->json([
              'status' => 'ERROR',
              'message' => $th->getMessage()
            ]);
          }
  
          echo 'Request : <br/><xmp>',
                  $soap->__getLastRequest(),
                  '</xmp><br/><br/> Error Message : <br/>',
                  $th->getMessage();
        }
      }
      
    }

    public function manualOnDemand(Request $request)
    {
      $validate = $request->validate([
        'no_sppb' => 'required',
        'tgl_sppb' => 'required|date',
        'kd_doc' => 'required|numeric',
      ]);

      if($validate){
        $hid = NULL;
        $mdl = NULL;
        if($request->house_id){
          $house = House::findOrFail($request->house_id);
          $hid = $house->id;
          $mdl = 'App\Models\House';
        }
        $sh = new SoapHelper;
        $tglparse = Carbon::parse($request->tgl_sppb)->format('dmY');
  
        $soap = $sh->soap();
  
        DB::beginTransaction();
  
        try {
          
          $sResponse = $soap->GetDokumenManual_OnDemand(
            array(
              'UserName' => config('app.tps.user'),
              'Password' => config('app.tps.password'),
              'KdDok' => $request->kd_doc,
              'NoDok' => $request->no_sppb,
              'TglDok' => $tglparse,              
            ));
            
          $LastRequest =  $soap->__getLastRequest();
          $soapResponse = $soap->__getLastResponse();          

          $log = $this->createLog($mdl, $hid, 'GetDokumenManual_OnDemand', $LastRequest, $soapResponse, 'PENDING');          
          
          DB::commit();

          $response = $sh->getResults('GetDokumenManual_OnDemandResult', $soapResponse);          
          
          if ($response != ''
              && !str_contains($response, 'Belum ada data baru')
              && !str_contains($response, 'Data Tidak Ditemukan')
              && !str_contains($response, 'Anda tidak berhak mengambil data ini')
              && !str_contains($response, 'Anda tidak mempunyai hak akses terhadap gudang')
              && !str_contains($response, 'Maaf coba cek kembali Nomor SPPB')) {

            $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
            $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
            $sppb = $sh->getResults('DOCUMENT', $hasil);
            $res = simplexml_load_string($sppb);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);

            // dd($data);

            $dataPib = [];
  
            if (!array_key_exists('HEADER', $data)) {
              foreach ($data as $sppb) {
                $this->updateManual($sppb);

                $dataToSave = $this->getDataManual($sppb);

                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $sppb["HEADER"]["NO_CONT"]
                                ], $dataToSave);

                $dataPib[]  = [
                  'NO_SPPB' => $sppb['HEADER']['NO_DOK_INOUT'],
                  'TGL_SPPB' => date('Y-m-d', strtotime($sppb['HEADER']['TGL_DOK_INOUT'])),
                  'NO_PIB' => $sppb['HEADER']['NO_DOK_INOUT'],
                  'TGL_PIB' => date('Y-m-d', strtotime($sppb['HEADER']['TGL_DOK_INOUT'])),
                  'CAR' => $sppb['HEADER']['NO_CONT']
                ];
              }
            } else {
              $this->updateManual($data);

              $dataToSave = $this->getDataManual($data);

              $sppbSave = Sppb::updateOrCreate([
                                'CAR' => $data["HEADER"]["NO_CONT"]
                              ], $dataToSave);

              $dataPib[]  = [
                'NO_SPPB' => $data['HEADER']['NO_DOK_INOUT'],
                'TGL_SPPB' => date('Y-m-d', strtotime($data['HEADER']['TGL_DOK_INOUT'])),
                'NO_PIB' => $data['HEADER']['NO_DOK_INOUT'],
                'TGL_PIB' => date('Y-m-d', strtotime($data['HEADER']['TGL_DOK_INOUT'])),
                'CAR' => $data['HEADER']['NO_CONT']
              ];
            }
            DB::commit();
  
            $log->update(['info' => 'COMPLETE']);
  
            DB::commit();

            return response()->json([
              'status' => 'OK',
              'message' => 'Berhasil mendapatkan dokumen manual',
              'sppb' => $dataPib
            ]);
  
            echo "Fetch Permit Complete.";
  
          } else {            
            return response()->json([
              'status' => 'ERROR',
              'message' => $response
            ]);
          }  
  
        } catch (\Throwable $th) {
          DB::rollback();
  
          $this->createLog($mdl, $hid, 'GetDokumenManual_OnDemandResult', NULL, NULL, 'FAILED : '.$th->getMessage());
  
          DB::commit();

          if($request->ajax()){
            return response()->json([
              'status' => 'ERROR',
              'message' => $th->getMessage()
            ]);
          }
  
          echo 'Request : <br/><xmp>',
                  $soap->__getLastRequest(),
                  '</xmp><br/><br/> Error Message : <br/>',
                  $th->getMessage();
        }
      }
      
    }
    
    public function importpermit()
    {
      $sh = new SoapHelper;

      $soap = $sh->soap();

      DB::beginTransaction();

      try {
        
        $sResponse = $soap->GetImporPermit_FASP(
          array(
            'UserName' => config('app.tps.user'),
            'Password' => config('app.tps.password'),
            'Kd_ASP' => config('app.tps.kode_tps')
          ));
          
        $LastRequest =  $soap->__getLastRequest();
        $soapResponse = $soap->__getLastResponse();        

        $response = $sh->getResults('GetImporPermit_FASPResult', $soapResponse);

        if ($response != ''
            && !str_contains($response, 'Belum ada data baru')
            && !str_contains($response, 'Data tidak ditemukan')
            && !str_contains($response, 'Anda tidak berhak mengambil data ini')
            && !str_contains($response, 'Anda tidak mempunyai hak akses terhadap gudang')
            && !str_contains($response, 'Maaf coba cek kembali Nomor SPPB')) {
          $log = $this->createLog(NULL, NULL, 'PIB', NULL, $soapResponse, 'PENDING');

          DB::commit();
  
          
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          
          $res = simplexml_load_string($hasil);
          $json = json_encode($res);
          $data = json_decode($json, TRUE);
          $p = 0;
          foreach($data as $d){
            if(array_key_exists('HEADER', $d)){
              $c = $this->updatePib($d, 'Scheduler');
              if($c > 0)
              {
                $dataToSave = $this->getDataPIB($d);

                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $d["HEADER"]["CAR"]
                                ], $dataToSave);
              }
              $p += $c;
            } else {
              foreach($d as $sppb){
                $c = $this->updatePib($sppb, 'Scheduler');
                if($c > 0)
                {
                  $dataToSave = $this->getDataPIB($sppb);
  
                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $sppb["HEADER"]["CAR"]
                                  ], $dataToSave);
                }
                $p += $c;
              }
              
            }            
            
          }
          
          DB::commit();

          $log->update(['info' => ($p > 0) ? 'COMPLETE' : 'CANCELLED']);

          DB::commit();

          echo "Fetch Permit Complete.";

        } else {
          \Log::warning($response);
          return $this->updatePermit();
        }

      } catch (\SoapFault $fault) {
        DB::rollback();

        \Log::error($fault);

        echo 'Request : <br/><xmp>',
                $soap->__getLastRequest(),
                '</xmp><br/><br/> Error Message : <br/>',
                $fault->getMessage();
      }      
    }

    public function bc23permit()
    {
      $sh = new SoapHelper;

      $soap = $sh->soap();

      DB::beginTransaction();

      try {
        $sResponse = $soap->GetBC23Permit_FASP(
          array(
            'UserName' => config('app.tps.user'),
            'Password' => config('app.tps.password'),
            'Kd_ASP' => config('app.tps.kode_tps')
          ));
          
        $LastRequest =  $soap->__getLastRequest();
        $soapResponse = $soap->__getLastResponse();        

        $response = $sh->getResults('GetBC23Permit_FASPResult', $soapResponse);

        if ($response != ''
              && !str_contains($response, 'Belum ada data baru')
              && !str_contains($response, 'Data tidak ditemukan')
              && !str_contains($response, 'Anda tidak berhak mengambil data ini')
              && !str_contains($response, 'Anda tidak mempunyai hak akses terhadap gudang')
              && !str_contains($response, 'Maaf coba cek kembali Nomor SPPB')) {
          $log = $this->createLog(NULL, NULL, 'BC23', NULL, $soapResponse, 'PENDING');

          DB::commit();  
          
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          
          $res = simplexml_load_string($hasil);
          $json = json_encode($res);
          $data = json_decode($json, TRUE);
          $p = 0;
          foreach($data as $d){
            if(array_key_exists('HEADER', $d)){
              $c = $this->updateBc($d, 'Scheduler');
              if($c > 0)
              {
                $dataToSave = $this->getDataBC23($d);

                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $d["HEADER"]["CAR"]
                                ], $dataToSave);
              } else {
                $this->sendToTPS($soapResponse, 'BC23');
              }
              $p += $c;
            } else {
              foreach($d as $sppb){
                $c = $this->updateBc($sppb, 'Scheduler');
                if($c > 0)
                {
                  $dataToSave = $this->getDataBC23($sppb);
  
                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $sppb["HEADER"]["CAR"]
                                  ], $dataToSave);
                } else {
                  $this->sendToTPS($soapResponse, 'BC23');
                }
                $p += $c;
              }
            }

          }

          DB::commit();

          $log->update(['info' => ($p > 0) ? 'COMPLETE' : 'CANCELLED']);

          DB::commit();

          echo "Fetch Permit Complete.";

        } else {
          \Log::warning($response);
          echo $response;
          return $this->updatePermitBc();
        }

      } catch (\SoapFault $fault) {
        DB::rollback();

        \Log::error($fault);

        echo 'Request : <br/><xmp>',
                $soap->__getLastRequest(),
                '</xmp><br/><br/> Error Message : <br/>',
                $fault->getMessage();
      }
      
    }

    public function bc16permit()
    {
      $sh = new SoapHelper;

      $soap = $sh->soap();

      DB::beginTransaction();

      try {
        $sResponse = $soap->GetDokumenPabeanPermit_FASP(
          array(
            'UserName' => config('app.tps.user'),
            'Password' => config('app.tps.password'),
            'Kd_Tps' => config('app.tps.kode_tps')
          ));
          
        $LastRequest =  $soap->__getLastRequest();
        $soapResponse = $soap->__getLastResponse();
        
        $response = $sh->getResults('GetDokumenPabeanPermit_FASPResult', $soapResponse);

        if ($response != ''
              && !str_contains($response, 'Belum ada data baru')
              && !str_contains($response, 'Data tidak ditemukan')
              && !str_contains($response, 'Anda tidak berhak mengambil data ini')
              && !str_contains($response, 'Anda tidak mempunyai hak akses terhadap gudang')
              && !str_contains($response, '“Maaf coba cek kembali Nomor SPPB')) {
          $log = $this->createLog(NULL, NULL, 'BC16', NULL, $soapResponse, 'PENDING');

          DB::commit();  
          
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          
          $res = simplexml_load_string($hasil);
          $json = json_encode($res);
          $data = json_decode($json, TRUE);
          $p = 0;
          foreach($data as $d){
            if(array_key_exists('HEADER', $d)){
              $c = $this->updateBc16($d, 'Scheduler');
              if($c > 0)
              {
                $dataToSave = $this->getDataBC16($d);

                $sppbSave = Sppb::updateOrCreate([
                                'CAR' => $d["HEADER"]["CAR"]
                              ], $dataToSave);
              }
              $p += $c;
            } else {
              foreach($d as $sppb){
                $c = $this->updateBc16($sppb, 'Scheduler');
                if($c > 0)
                {
                  $dataToSave = $this->getDataBC16($sppb);
  
                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $sppb["HEADER"]["CAR"]
                                  ], $dataToSave);
                }
                $p += $c;
              }
            } 
          }
          
          DB::commit();

          $log->update(['info' => ($p > 0) ? 'COMPLETE' : 'CANCELLED']);

          DB::commit();

          echo "Fetch Permit Complete.";

        } else {
          \Log::warning($response);
          return $this->updatePermitBc16();
        }

      } catch (\SoapFault $fault) {
        DB::rollback();

        \Log::error($fault);

        echo 'Request : <br/><xmp>',
                $soap->__getLastRequest(),
                '</xmp><br/><br/> Error Message : <br/>',
                $fault->getMessage();
      }

      
    }

    public function manualpermit()
    {
      $sh = new SoapHelper;

      $soap = $sh->soap();
    }

    public function getBilling()
    {
        $npwp = [
          // '930976485402000',
          // '862102258402000',
          // '018020776017000',
        ];
        $moduls = IdModul::whereIn('NPWP', $npwp)->get();

        foreach($moduls as $pjt)
        {
          $SENDING_USER = 'BillingKolektif'; //isset($CU->username) ? $CU->username : '';
          $SENDING_DATE = date('Y-m-d H:i:s');
          $SOAP_USER = $pjt->User_BarangKiriman;
          $SOAP_PASS = $pjt->Password_BarangKiriman;
          $SOAP_SIGN = $pjt->Token_BarangKiriman;
          $PJT_NPWP = $pjt->NPWP;

          $SOAP_SETTING = [
            'stream_context'=> stream_context_create([
              'ssl'=> [
                'verify_peer'=>false,
                'verify_peer_name'=>false, 
                'allow_self_signed' => true 
              ]
            ]),
          ];

          $SOAP_URL       = asset('WSBillingKolektif.wsdl');
          $SOAP_LOCATION  = 'https://esbbcext01.beacukai.go.id:9082/BarangKirimanOnline/WSBillingKolektif.wsdl';

          $webServiceClient = new \SoapClient($SOAP_URL, $SOAP_SETTING);
          $requestData = [
            "id" => $SOAP_USER.'^$'.$SOAP_PASS,
            "npwp" => $PJT_NPWP,            
            "sign" => $SOAP_SIGN
          ];

          try {
            $response = $webServiceClient->getBillingKolektif($requestData);
            $RESP = isset($response->return) ? $response->return : '';
            $LastResponse =  $webServiceClient->__getLastResponse();
            $LastRequest =  $webServiceClient->__getLastRequest();

            $cont = '';
            $log_folder = date('Y-m-d');
            $log_fn = 'BILLING_KOLEKTIF_'.date("d-M-Y_His").".txt";

            if(!empty($RESP))
            {                
                $cont .= "\n " . date('H:i')."  ".str_replace(array("\r\n", "\n", "\r"),"",$RESP);
                \Storage::disk('public')->put('/logs/responseBilling/'.$PJT_NPWP.'/'.$log_folder.'/'.$log_fn, $cont);

                \Log::info('Create Response xml /logs/responseBilling/'.$PJT_NPWP.'/'.$log_folder.'/'.$log_fn);            
            }
            $A_RESP = base64_encode($RESP);
            $KolektifDB = array(
                'XML'   => $A_RESP,
            );

            if (strlen($A_RESP) > 28) {
              $billing = BillingLog::create([
                          'XML' => $A_RESP
                        ]);

              DB::commit();

              \Log::info('Fetch Billing Success, start compile XML for '.$billing->LogID);

              $newRequest = new \Illuminate\Http\Request();
              $newRequest->setMethod('GET');
              $newRequest->merge(['id' => $billing->LogID]);

              $this->billing($newRequest);
            } else {
              \Log::warning('Get Billing respon '.$A_RESP);
            }
          } catch(\SoapFault $th) {
            DB::rollback();

            \Log::error($th);
          }

        } 
    }

    public function billing(Request $request)
    {
      $billing = BillingLog::findOrFail($request->id);
      $xml = base64_decode($billing->XML);
      $res = simplexml_load_string($xml);
      $bConsol = [];
      DB::beginTransaction();
      \Log::info('Start Compile Billing id :'.$billing->LogID);
      try {
        if(property_exists($res, 'HEADER'))
        {
          $k = 0;
          foreach($res->HEADER as $key => $hdr)
          {            
            $BillConsol = [
              'ID_PEMBERITAHU' => (string)$hdr->ID_PEMBERITAHU,
              'NO_SPPBMCP_KONSOLIDASI' => (string)$hdr->NO_SPPBMCP_KONSOLIDASI,
              'TGL_SPPBMCP_KONSOLIDASI' => date('Y-m-d H:i:s',strtotime((string)$hdr->TGL_SPPBMCP_KONSOLIDASI)),
              'KD_RESPON' => (string)$hdr->KD_RESPON,
              'KET_RESPON' => (string)$hdr->KET_RESPON,
              'WK_REKAM' => (string)$hdr->WK_REKAM,
              'KODE_BILLING' => (string)$hdr->KODE_BILLING,
              'TGL_BILLING' => date('Y-m-d H:i:s',strtotime((string)$hdr->TGL_BILLING)),
              'TGL_JT_TEMPO' => date('Y-m-d H:i:s',strtotime((string)$hdr->TGL_JT_TEMPO)),
              'TOTAL_BILLING' => (string)$hdr->TOTAL_BILLING,
            ];

            $tgBilling = Carbon::parse($BillConsol['TGL_SPPBMCP_KONSOLIDASI']);

            if($tgBilling->year > 0)
            {
              $BillConsol['TGL_BILLING'] = ($BillConsol['TGL_BILLING'] == '1970-01-01 07:00:00') ? $tgBilling->copy()->startOfDay()->format('Y-m-d H:i:s') : $BillConsol['TGL_BILLING'];
              $BillConsol['TGL_JT_TEMPO'] = ($BillConsol['TGL_JT_TEMPO'] == '1970-01-01 07:00:00') ? $tgBilling->copy()->addDay()->format('Y-m-d') . ' 22:00:00' : $BillConsol['TGL_JT_TEMPO'];
            }

            $billing->update([
              'NO_SPPBMCP_KONSOLIDASI' => $BillConsol['NO_SPPBMCP_KONSOLIDASI'],
              'TGL_SPPBMCP_KONSOLIDASI' => $BillConsol['TGL_SPPBMCP_KONSOLIDASI'],
              'KD_RESPON' => $BillConsol['KD_RESPON'],
              'KODE_BILLING' => $BillConsol['KODE_BILLING'],
              'TGL_BILLING' =>  $BillConsol['TGL_BILLING'],
              'TGL_JT_TEMPO' => $BillConsol['TGL_JT_TEMPO'],
            ]);

            $bcToUpdate = Arr::except($BillConsol, ['ID_PEMBERITAHU', 'KODE_BILLING','TGL_BILLING']);

            $billingConsol = BillingConsolidation::updateOrCreate([
                                'ID_PEMBERITAHU' => $BillConsol['ID_PEMBERITAHU'],
                                'KODE_BILLING' => $BillConsol['KODE_BILLING'],
                                'TGL_BILLING' => $BillConsol['TGL_BILLING'],
                              ], $bcToUpdate);

            $bConsol[$k] = $BillConsol;

            if (property_exists($hdr, 'DETIL_PUNGUTAN')) {
              foreach ($hdr->DETIL_PUNGUTAN->PUNGUTAN as $Pungutan) {
                $BillConsolPungutan = [
                    'BillingID' => $billingConsol->id,
                    'KD_PUNGUTAN'   => (string)$Pungutan->KD_PUNGUTAN,
                    'NILAI' => $Pungutan->NILAI,
                ];

                $billingConsol->details()->updateOrCreate([
                  'KD_PUNGUTAN' => $BillConsolPungutan['KD_PUNGUTAN']
                ],[
                  'NILAI' => $BillConsolPungutan['NILAI']
                ]);

                $bConsol[$k]['PUNGUTAN'][] = $BillConsolPungutan;              
              }
            }

            if (property_exists($hdr, 'DETIL_SPPBMCP')) {
              foreach ($hdr->DETIL_SPPBMCP->SPPBMCP as $SPPBMCP) {
                $BillConsolSPPBMCP = [
                    'BillingID' => $billingConsol->id,
                    'NO_BARANG' => (string)$SPPBMCP->NO_BARANG,
                    'TGL_HOUSE_BLAWB' => date('Y-m-d',strtotime((string)$SPPBMCP->TGL_HOUSE_BLAWB)),
                    'TOTAL_TAGIHAN' => $SPPBMCP->TOTAL_TAGIHAN
                ];

                $billingConsol->sppbmcp()->updateOrCreate([
                  'NO_BARANG' => $BillConsolSPPBMCP['NO_BARANG'],
                  'TGL_HOUSE_BLAWB' => $BillConsolSPPBMCP['TGL_HOUSE_BLAWB'],
                ],[
                  'TOTAL_TAGIHAN' => $BillConsolSPPBMCP['TOTAL_TAGIHAN'],
                ]);

                $bConsol[$k]['SPPBMC'][] = $BillConsolSPPBMCP;
              }
            }

            DB::commit();

            $npwp = $billingConsol->ID_PEMBERITAHU;

            $branch = GlbBranch::whereHas('pjt', function($p) use ($npwp){
                                  return $p->where('NPWP', $npwp);
                                })
                                ->first();
            if($branch)
            {
              $billingConsol->update([
                'BRANCH' => $branch->id
              ]);

              DB::commit();
            }

            \Log::info('Create Billing Success, Kode Billing '.$billingConsol->KODE_BILLING);
            $k++;
          }
          echo "Finish create ".$k." Billing";
        }        
      } catch (\Throwable $th) {
        DB::rollback();
        // throw $th;
        \Log::error($th);
        echo $th;
      }      
    }

    public function respon()
    {
      $exc = [401,403,404,405,408];
      $houses = House::whereNotNull('BC_201')
                      ->whereIn('JNS_AJU', [1,2])
                      ->where(function($hb) use ($exc){
                        $hb->whereNull('BC_CODE')
                          ->orWhereNotIn('BC_CODE', $exc);
                      })
                      //->where('BRANCH', 2)  Temporary
                      ->pluck('id')
                      ->toArray();

      $t = 20;
      $s = 0;
      $count = count($houses);
      \Log::notice('Cron Tarik Respon for '.$count.' Houses.');
      if($count > 0)
      {        
        $barkir = new Barkir;
        while ($s < $count)
        {
          $ids = array_slice($houses, $s, $t);
          
          $barkir->mintarespon($ids, 1, 0, false);
          
          $s += $t;
        }
      }
    }

    public function createInXML(House $house, Carbon $time)
    {
      $giwiaTxt = '<UniversalEvent xmlns="http://www.cargowise.com/Schemas/Universal/2011/11">
                  <Event>
                      <DataContext>
                          <Company>
                              <Code>ID1</Code>
                          </Company>
                    <EnterpriseID>B52</EnterpriseID>
                    <ServerID>TS2</ServerID>
                          <DataTargetCollection>
                              <DataTarget>
                                  <Type>ForwardingShipment</Type>
                                  <Key>'.$house->ShipmentNumber.'</Key>
                              </DataTarget>
                          </DataTargetCollection>
                      </DataContext>
                      <EventTime>'.$time->toDateTimeLocalString().'</EventTime>
                      <EventType>FUL</EventType>
                      <EventReference>|EXT_SOFTWARE=TPS|FAC=CFS|LNK=GIWIA|LOC=IDJKT|</EventReference>
                      <IsEstimate>false</IsEstimate>
                  </Event>
              </UniversalEvent>
              ';
              
      $micro = $time->format('u');

      $giwiName = 'XUE_TPSID_'.$house->ShipmentNumber.'_GIWIA_'.$time->format('YmdHms').substr($micro, 0,3).'_'.Str::uuid().'.xml';

      DB::beginTransaction();

      try {        

        $giwia = Storage::disk('sftp')->put($giwiName, $giwiaTxt);
        
        $this->createLog('\App\Models\House', $house->id, 'sendGiwia', $giwiaTxt, $giwiName, 'Upload Success.');

        DB::commit();
        
        return $giwiName;

      } catch (\FilesystemException | \UnableToWriteFile $th) {
        
        $this->createLog('\App\Models\House', $house->id, 'sendGiwia', $giwiaTxt, $giwiName, 'FAILED : '.$th->getMessage());

        DB::commit();

        if($request->ajax()){
          return response()->json([
            'status' => 'ERROR',
            'message' => $th->getMessage(),
          ]);
        }
      } 

    }

    public function createOutXML(House $house, Carbon $time)
    {
      $gowiaTxt = '<UniversalEvent xmlns="http://www.cargowise.com/Schemas/Universal/2011/11">
                  <Event>
                      <DataContext>
                          <Company>
                              <Code>ID1</Code>
                          </Company>
                    <EnterpriseID>B52</EnterpriseID>
                    <ServerID>TS2</ServerID>
                          <DataTargetCollection>
                              <DataTarget>
                                  <Type>ForwardingShipment</Type>
                                  <Key>'.$house->ShipmentNumber.'</Key>
                              </DataTarget>
                          </DataTargetCollection>
                      </DataContext>
                      <EventTime>'.$time->toDateTimeLocalString().'</EventTime>
                      <EventType>FLO</EventType>
                      <EventReference>|EXT_SOFTWARE=TPS|FAC=CFS|LNK=GOWIA|LOC=IDJKT|</EventReference>
                      <IsEstimate>false</IsEstimate>
                  </Event>
              </UniversalEvent>
              ';
      
      $micro = $time->format('u');

      $gowiName = 'XUE_TPSID_'.$house->ShipmentNumber.'_GOWIA_'.$time->format('YmdHms').substr($micro, 0,3).'_'.Str::uuid().'.xml';

      DB::beginTransaction();

      try {

        $gowia = Storage::disk('sftp')->put($gowiName, $gowiaTxt);

        $this->createLog('\App\Models\House', $house->id, 'sendGowia', $gowiaTxt, $gowiName, 'Upload Success.');

        DB::commit();

        return $gowiName;

      } catch (\FilesystemException | \UnableToWriteFile $th) {

        $this->createLog('\App\Models\House', $house->id, 'sendGowia', $gowiaTxt, $gowiName, 'FAILED : '.$th->getMessage());

        DB::commit();

        if($request->ajax()){
          return response()->json([
            'status' => 'ERROR',
            'message' => $th->getMessage(),
          ]);
        }

      } 
    }

    public function sendGateIn(Request $request, House $house, $force = false)
    {
      $sh = new SoapHelper;
      if($house->TGL_TIBA){
        $tgl = Carbon::parse($house->TGL_TIBA);
      } else{
        $tgl = Carbon::parse($house->SCAN_IN_DATE);
      }

      $DocType = '3';//Persetujuan PLP Kode 3 perhatian, sementara 22 paket pos
      $DocNumber = $house->master->PLPNumber;
      $DocDate = date('Ymd', strtotime($house->master->PLPDate));

      if(!$DocNumber || !$DocDate)
      {
        return [
          'status' => "ERROR",
          'info' => 'Gate In '.$house->NO_BARANG.' failed, NO_DOK_INOUT kosong.'
        ];
      }

      if($house->TPS_GateInREF && $force == false){
        $ref_num = $house->TPS_GateInREF;
      } else {
        $ref_run = getRunning('TPS', 'GATE_IN', $tgl->format('Y-m-d'));
        $ref_num = $house->branch?->CB_WhCode . $ref_run;
      }

      $xmlArray = [
        'COCOKMS' => [
          'HEADER' => [
              'KD_DOK' => 5, // 5 => Gate In Import, 6=> Gate Out Import
              'KD_TPS' => config('app.tps.kode_tps'),
              'NM_ANGKUT' => $house->NM_PENGANGKUT,
              'NO_VOY_FLIGHT' => $house->NO_FLIGHT,
              'CALL_SIGN' => '',
              'TGL_TIBA' => $tgl->format('Ymd'),
              'KD_GUDANG' => $house->branch?->CB_WhCode ?? "",
              'REF_NUMBER' => $ref_num
          ],
          'DETIL' => [
            'KMS' => [
              'NO_BL_AWB' => $house->NO_BARANG,
              'TGL_BL_AWB' => date('Ymd', strtotime($house->TGL_HOUSE_BLAWB)),
              'NO_MASTER_BL_AWB' => $house->mawb_parse,
              'TGL_MASTER_BL_AWB' => date('Ymd', strtotime($house->TGL_MASTER_BLAWB)),
              'ID_CONSIGNEE' => "000000000000000",
              'CONSIGNEE' => \Str::replace('&', '', $house->NM_PENERIMA),
              'BRUTO' => number_format($house->BRUTO,2,'.',''),
              'NO_BC11' => $house->NO_BC11,
              'TGL_BC11' => date('Ymd', strtotime($house->TGL_BC11)),
              'NO_POS_BC11' => str_pad($house->NO_POS_BC11, 4, '0', STR_PAD_LEFT) . str_pad($house->NO_SUBPOS_BC11, 4, '0', STR_PAD_LEFT) . '0000',
              'CONT_ASAL' => '',
              'SERI_KEMAS' => 1, //Kalo nggak salah index kemasan per master
              'KD_KEMAS' => 'PK',
              'JML_KEMAS' => 1,
              'KD_TIMBUN' => '', //PR UNTUK KLINE
              //Kode Dok InOut 1 = > SPPB 2=> SPPB BC23, 3  => Persetujuan PLP
              'KD_DOK_INOUT' => $DocType, 
              'NO_DOK_INOUT' => $DocNumber, // Nomor persetujuan PLP / nomor SPPB
              // Tanggal dokumen PLP
              'TGL_DOK_INOUT' => date('Ymd', strtotime($DocDate)),
              //MANDATORY WAKTU DOKUMEN PLP / SPPB
              'WK_INOUT' => date('YmdHis', strtotime($house->SCAN_IN_DATE)), 
              // 'KD_SAR_ANGKUT_INOUT' => '1',
              //'NO_POL' => 'B1234ABC',
              'PEL_MUAT' => $house->master->Origin,
              'PEL_TRANSIT' => (strlen($house->master->Transit)>3
                                ? $house->master->Transit
                                : $house->master->Origin),
              'PEL_BONGKAR' => $house->master->Destination,
              'KODE_KANTOR' => '050100',
              'NO_DAFTAR_PABEAN' => '', // NO DAFTAR PABEAN ( NOMOR PIB / BC2.3)
              'TGL_DAFTAR_PABEAN' => date('Ymd', strtotime($house->TGL_HOUSE_BLAWB)), // TANGGAL PENDAFTARAN PIB / BC2.3,
              'NO_SEGEL_BC' => $house->master->PLPNumber, // JIKA ADA SEGEL WAJIB formatnya
              'TGL_SEGEL_BC' => date('Ymd', strtotime($house->master->PLPDate)), // jika ada segel wajib yyyymmdd,
              'NO_IJIN_TPS' => $house->NO_BARANG,
              'TGL_IJIN_TPS' => date('Ymd', strtotime($DocDate))
            ]
          ]
        ]
      ];

      $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><DOCUMENT xmlns=\"cocokms.xsd\"></DOCUMENT>", LIBXML_NOWARNING);

      $xml = $sh->arrayToXml($xmlArray, $xml);
      $soap = $sh->soap();
      
      DB::beginTransaction();

      try {
        $sResponse = $soap->CoarriCodeco_Kemasan([
          'fStream' => $xml->asXML(),
          'Username' => config('app.tps.user'),
          'Password' => config('app.tps.password')
        ]);

        $LastResponse =  $soap->__getLastResponse();
        $LastRequest =  $soap->__getLastRequest();

        $log = $this->createLog('\App\Models\House', $house->id, 'sendGateIn', $LastRequest, $LastResponse, 'PENDING');

        DB::commit();

        if ($LastResponse) {
          if (strpos($LastResponse, 'Berhasil') !== false) {

            $status = 'OK';
            $info = 'Gate In Succes, Ref No : '.$xmlArray['COCOKMS']['HEADER']['REF_NUMBER'];
            
            $house->update([
              'TPS_GateInStatus' => 'Y',
              'TPS_GateInDateTime' => date("Y-m-d H:i:s"),
              'TPS_GateInREF' => $xmlArray['COCOKMS']['HEADER']['REF_NUMBER']
            ]);
            
            DB::commit();
          } else {
            $status = 'ERROR';
            $reason = $this->getResults('CoarriCodeco_KemasanResult', $LastResponse);
            $info = 'Gate In Failed, Ref No : '.$xmlArray['COCOKMS']['HEADER']['REF_NUMBER'].', Reason : '.$reason;  
          }

          $log->update(['info' => $info]);

          DB::commit();
        }

        if($request->ajax()){
          return response()->json([
            'status' => $status,
            'message' => $info,
          ]);
        }

        return [
          'status' => 'OK',
          'info' => $info,
          'xml' => $xml
        ];

      } catch (\SoapFault $fault) {

        DB::rollback();

        $this->createLog('\App\Models\House', $house->id, 'sendGateIn', NULL, NULL, 'FAILED : '.$fault->getMessage());

        DB::commit();

        if($request->ajax()){
          return response()->json([
            'status' => 'ERROR',
            'message' => 'Request : <br/><xmp>',
                          $soap->__getLastRequest(),
                          '</xmp><br/><br/> Error Message : <br/>',
                          $fault->getMessage()
          ]);
        }

        return [
          'status' => 'ERROR',
          'info' => $fault->getMessage()
        ];

        echo 'Request : <br/><xmp>',
                $soap->__getLastRequest(),
                '</xmp><br/><br/> Error Message : <br/>',
                $fault->getMessage();
      }

    }

    public function sendGateOut(Request $request, House $house, $force = false)
    {
      $sh = new SoapHelper;
      if($house->TGL_TIBA){
        $tgl = Carbon::parse($house->TGL_TIBA);
      } else{
        $tgl = Carbon::parse($house->SCAN_IN_DATE);
      }

      $SPPBNumber = NULL;
      $SPPBDate   = NULL;

      $noPabean = (!in_array($house->JNS_AJU, [1,2])) ? $house->NO_DAFTAR_PABEAN : '';
      $tglPabean = (!in_array($house->JNS_AJU, [1,2])) 
                    ? $house->TGL_DAFTAR_PABEAN 
                    : $house->TGL_HOUSE_BLAWB;

      if(in_array($house->JNS_AJU, [1,2]) && !$house->SPPBNumber){
        $house->load(['bclog' => function($l){
          $l->whereIn('BC_CODE', [401,403,404]);
        }]);

        if($house->bclog)
        {
          foreach($house->bclog as $LOGS)
          {
            $R = simplexml_load_string(base64_decode($LOGS->XML));
            if($LOGS->BC_CODE == 404) {              
              $SPPBNumber = strlen($R->HEADER->NO_SPPB) > 0 ?$R->HEADER->NO_SPPB:$R->HEADER->NO_PIBK;
              $SPPBDate = $R->HEADER->TGL_SPPB;
  
            } else {
              $SPPBNumber=$R->HEADER->NO_SPPBMCP;
              $SPPBDate = $R->HEADER->TGL_SPPBMCP;
            }
          }          
        }
      } else {
        $SPPBNumber = ($house->BCF15_Status == "Yes"
                        ? $house->BCF15_Number
                        : $house->SPPBNumber);
        $SPPBDate = ($house->BCF15_Status == "Yes"
                      ? date('Ymd', strtotime($house->BCF15_Date))
                      : date('Ymd', strtotime($house->SPPBDate)));
      }

      $DocNumber = $SPPBNumber;

      $DocDate = $SPPBDate;

      if(!$DocNumber || !$DocDate)
      {
        return [
          'status' => "ERROR",
          'info' => 'Gate In '.$house->NO_BARANG.' failed, NO_DOK_INOUT kosong.'
        ];
      }

      if($house->TPS_GateOutREF && $force == false){
        $ref_num = $house->TPS_GateOutREF;
      } else {
        $ref_run = getRunning('TPS', 'GATE_OUT', $tgl->format('Y-m-d'));
        $ref_num = $house->branch?->CB_WhCode . $ref_run;
      }      

      $DocType = $house->KD_DOC;

      $xmlArray = [
        'COCOKMS' => [
            'HEADER' => [
                'KD_DOK' => 6, // 5 => Gate In Import, 6=> Gate Out Import
                'KD_TPS' => config('app.tps.kode_tps'),
                'NM_ANGKUT' => $house->NM_PENGANGKUT,
                'NO_VOY_FLIGHT' => $house->NO_FLIGHT,
                'CALL_SIGN' => '',
                'TGL_TIBA' => $tgl->format('Ymd'),
                'KD_GUDANG' => $house->branch?->CB_WhCode ?? "",
                'REF_NUMBER' => $ref_num
            ],
            'DETIL' => [
              'KMS' => [
                'NO_BL_AWB' => $house->NO_BARANG,
                'TGL_BL_AWB' => date('Ymd', strtotime($house->TGL_HOUSE_BLAWB)),
                'NO_MASTER_BL_AWB' => $house->mawb_parse,
                'TGL_MASTER_BL_AWB' => date('Ymd', strtotime($house->TGL_MASTER_BLAWB)),
                'ID_CONSIGNEE' => \Str::replace(['-','.',' '], '', $house->NO_ID_PENERIMA),
                'CONSIGNEE' => \Str::replace('&', '', $house->NM_PENERIMA),
                'BRUTO' => number_format($house->BRUTO,2,'.',''),
                'NO_BC11' => $house->NO_BC11,
                'TGL_BC11' => date('Ymd', strtotime($house->TGL_BC11)),
                'NO_POS_BC11' => str_pad($house->NO_POS_BC11, 4, '0', STR_PAD_LEFT) . str_pad($house->NO_SUBPOS_BC11, 4, '0', STR_PAD_LEFT) . '0000',
                'CONT_ASAL' => '',
                'SERI_KEMAS' => 1, //Kalo nggak salah index kemasan per master
                'KD_KEMAS' => 'PK',
                'JML_KEMAS' => 1,
                'KD_TIMBUN' => '', //PR UNTUK KLINE
                //Kode Dok InOut 1 = > SPPB 2=> SPPB BC23, 3  => Persetujuan PLP
                'KD_DOK_INOUT' => $DocType, 
                'NO_DOK_INOUT' => $DocNumber, // Nomor persetujuan PLP / nomor SPPB
                // Tanggal dokumen PLP
                'TGL_DOK_INOUT' => date('Ymd', strtotime($DocDate)),
                //MANDATORY WAKTU DOKUMEN PLP / SPPB
                'WK_INOUT' => date('YmdHis', strtotime($house->SCAN_OUT_DATE)),
                // 'KD_SAR_ANGKUT_INOUT' => '1',
                //'NO_POL' => 'B1234ABC',
                'PEL_MUAT' => $house->master->Origin,
                'PEL_TRANSIT' => (strlen($house->master->Transit)>3
                                  ? $house->master->Transit
                                  : $house->master->Origin),
                'PEL_BONGKAR' => $house->master->Destination,
                'KODE_KANTOR' => '050100',
                'NO_DAFTAR_PABEAN' => $noPabean, // NO DAFTAR PABEAN ( NOMOR PIB / BC2.3)
                'TGL_DAFTAR_PABEAN' => date('Ymd', strtotime($tglPabean)), // TANGGAL PENDAFTARAN PIB / BC2.3,
                // 'NO_SEGEL_BC' => $house->master->PLPNumber, // JIKA ADA SEGEL WAJIB formatnya
                // 'TGL_SEGEL_BC' => date('Ymd', strtotime($house->master->PLPDate)), // jika ada segel wajib yyyymmdd,
                // 'NO_IJIN_TPS' => $house->NO_BARANG,
                // 'TGL_IJIN_TPS' => date('Ymd', strtotime($DocDate))
              ]
            ]
        ]
      ];

      if($house->SEAL_NO){
        $xmlArray['COCOKMS']['DETIL']['KMS']['NO_SEGEL_BC'] = $house->SEAL_NO;
        $xmlArray['COCOKMS']['DETIL']['KMS']['TGL_SEGEL_BC'] = date('Ymd', strtotime($house->SPPBDate));
      }

      $xmlArray['COCOKMS']['DETIL']['KMS']['NO_IJIN_TPS'] = $house->NO_BARANG;
      $xmlArray['COCOKMS']['DETIL']['KMS']['TGL_IJIN_TPS'] = date('Ymd', strtotime($DocDate));

      $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><DOCUMENT xmlns=\"cocokms.xsd\"></DOCUMENT>", LIBXML_NOWARNING);

      $xml = $sh->arrayToXml($xmlArray, $xml);
      $soap = $sh->soap();
      
      DB::beginTransaction();

      try {
        $sResponse = $soap->CoarriCodeco_Kemasan([
          'fStream' => $xml->asXML(),
          'Username' => config('app.tps.user'),
          'Password' => config('app.tps.password')
        ]);

        $LastResponse =  $soap->__getLastResponse();
        $LastRequest =  $soap->__getLastRequest();

        $log = $this->createLog('\App\Models\House', $house->id, 'sendGateOut', $LastRequest, $LastResponse, 'PENDING');

        DB::commit();

        if ($LastResponse) {
          if (strpos($LastResponse, 'Berhasil') !== false) {

            $status = 'OK';
            $info = 'Gate Out Succes, Ref No : '.$xmlArray['COCOKMS']['HEADER']['REF_NUMBER'];
            
            $house->update([
              'TPS_GateOutStatus' => 'Y',
              'TPS_GateOutDateTime' => date("Y-m-d H:i:s"),
              'TPS_GateOutREF' => $xmlArray['COCOKMS']['HEADER']['REF_NUMBER'],
              'BC_CODE' => 408,
              'BC_STATUS' => 'BARANG KELUAR DARI GUDANG'
            ]);
            
            $house->bclog()->updateOrCreate([
              'BC_CODE' => 408
            ],[
              'MAWB' => $house->mawb_parse,
              'BC_TEXT' => 'BARANG KELUAR DARI GUDANG',
              'METHOD' => 'CoarriCodeco_Kemasan',
              'XML' => NULL,
              'BC_DATE' => $house->SCAN_OUT_DATE,
              'SENTON' => now(),
              'SENTBY' => 'Scheduler',
              'NO_BARANG' => $house->NO_BARANG
            ]);

          } else {
            $status = 'ERROR';
            $reason = $this->getResults('CoarriCodeco_KemasanResult', $LastResponse);
            $info = 'Gate Out Failed, Ref No : '.$xmlArray['COCOKMS']['HEADER']['REF_NUMBER'].', Reason : '.$reason;
          }          

          $log->update(['info' => $info]);

          DB::commit();
        }

        if($request->ajax()){
          return response()->json([
            'status' => $status,
            'message' => $info,
          ]);
        }

        return [
          'status' => $status,
          'info' => $info,
          'xml' => $xml
        ];

      } catch (\SoapFault $fault) {

        DB::rollback();

        $this->createLog('\App\Models\House', $house->id, 'sendGateOut', NULL, NULL, 'FAILED : '.$fault->getMessage());

        DB::commit();

        if($request->ajax()){
          return response()->json([
            'status' => 'ERROR',
            'message' => 'Request : <br/><xmp>',
                          $soap->__getLastRequest(),
                          '</xmp><br/><br/> Error Message : <br/>',
                          $fault->getMessage()
          ]);
        }

        return [
          'status' => 'ERROR',
          'info' => $fault->getMessage()
        ];

        echo 'Request : <br/><xmp>',
                $soap->__getLastRequest(),
                '</xmp><br/><br/> Error Message : <br/>',
                $fault->getMessage();
      }

    }

    public function updatePermit()
    {
        $sh = new SoapHelper;

        $soap = $sh->soap();
        $logs = SchedulerLog::where('process', 'PIB')
                            ->where('info', 'PENDING')
                            ->limit(10)
                            ->get();

        foreach($logs as $log){
          $soapResponse = $log->response;
          \Log::info('Process Scheduler Log '.$log->id);
          $response = $sh->getResults('GetImporPermit_FASPResult', $soapResponse);
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          if(strpos($hasil, 'DOCUMENT')){
            $res = simplexml_load_string($hasil);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);
            
            DB::beginTransaction();

            try {
              $p = 0;
              foreach($data as $d){
                if(array_key_exists('HEADER', $d)){
                  $c = $this->updatePib($d, 'Scheduler');
                  if($c > 0)
                  {
                    $dataToSave = $this->getDataPIB($d);
    
                    $sppbSave = Sppb::updateOrCreate([
                                      'CAR' => $d["HEADER"]["CAR"]
                                    ], $dataToSave);
                  }
                  $p += $c;
                } else {
                  foreach($d as $sppb){
                    $c = $this->updatePib($sppb, 'Scheduler');
                    if($c > 0)
                    {
                      $dataToSave = $this->getDataPIB($sppb);
      
                      $sppbSave = Sppb::updateOrCreate([
                                        'CAR' => $sppb["HEADER"]["CAR"]
                                      ], $dataToSave);
                    }
                    $p += $c;
                  }
                } 
              }              
    
              DB::commit();
              
              $log->update(['info' => ($p > 0) ? 'COMPLETE' : 'CANCELLED']);
    
              DB::commit();

              echo 'Update Permit Success';
              \Log::info('Update Permit Success');
            } catch (\Throwable $th) {
              // throw $th;
              DB::rollback();
              \Log::error($th);
              echo "Update Permit Error : ".$log->id."-".$th->getMessage();
            }
            echo "Fetch Permit Complete.";
          } else {
            DB::beginTransaction();

            try {
              $log->update([
                'info' => $hasil,
              ]);

              DB::commit();
              echo $hasil;

            } catch (\Throwable $th) {
              DB::rollback();
              \Log::error($th);
              echo "Update Permit Error : ".$log->id."-".$th->getMessage();
            }
          }    
        }
    }

    public function updatePermitBc()
    {
        $sh = new SoapHelper;

        $soap = $sh->soap();
        $logs = SchedulerLog::where('process', 'BC23')
                            ->where('info', 'PENDING')
                            ->limit(10)
                            ->get();

        foreach($logs as $log){
          $soapResponse = $log->response;
          \Log::info('Process Scheduler Log '.$log->id);
          $response = $sh->getResults('GetBC23Permit_FASPResult', $soapResponse);
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          if(strpos($hasil, 'DOCUMENT')){
            // $sppb = $sh->getResults('DOCUMENT', $hasil);
            $res = simplexml_load_string($hasil);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);
            
            DB::beginTransaction();
  
            try {
              $p = 0;
              foreach($data as $d){
                if(array_key_exists('HEADER', $d)){
                  $c = $this->updateBc($d, 'Scheduler');
                  if($c > 0)
                  {
                    $dataToSave = $this->getDataBC23($d);
    
                    $sppbSave = Sppb::updateOrCreate([
                                      'CAR' => $d["HEADER"]["CAR"]
                                    ], $dataToSave);
                  }
                  $p += $c;
                } else {
                  foreach($d as $sppb){
                    $c = $this->updateBc($sppb, 'Scheduler');
                    if($c > 0)
                    {
                      $dataToSave = $this->getDataBC23($sppb);
      
                      $sppbSave = Sppb::updateOrCreate([
                                        'CAR' => $sppb["HEADER"]["CAR"]
                                      ], $dataToSave);
                    }
                    $p += $c;
                  }
                }
              }
    
              DB::commit();
    
              $log->update(['info' => ($p > 0) ? 'COMPLETE' : 'CANCELLED']);
    
              DB::commit();

              echo "Update Permit Success";
  
            } catch (\Throwable $th) {
              // throw $th;
              DB::rollback();
              \Log::error($th);
              echo "Update Permit Error : ".$log->id."-".$th->getMessage();
            }  
            echo "Fetch Permit Complete.";
          } else {
            DB::beginTransaction();

            try {
              $log->update([
                'info' => $hasil,
              ]);

              DB::commit();
              echo $hasil;

            } catch (\Throwable $th) {
              DB::rollback();
              \Log::error($th);
              echo "Update Permit Error : ".$log->id."-".$th->getMessage();
            }
          }
        }
    }

    public function updatePermitBc16()
    {
        $sh = new SoapHelper;

        $soap = $sh->soap();
        $logs = SchedulerLog::where('process', 'BC16')
                            ->where('info', 'PENDING')
                            ->limit(10)
                            ->get();

        foreach($logs as $log){
          $soapResponse = $log->response;
          \Log::info('Process Scheduler Log '.$log->id);
          $response = $sh->getResults('GetDokumenPabeanPermit_FASPResult', $soapResponse);
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          if(strpos($hasil, 'DOCUMENT')){
            $res = simplexml_load_string($hasil);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);
            
            DB::beginTransaction();
  
            try {
              $p = 0;
              foreach($data as $d){
                if(array_key_exists('HEADER', $d)){
                  $c = $this->updateBc16($d, 'Scheduler');
                  if($c > 0)
                  {
                    $dataToSave = $this->getDataBC16($d);
    
                    $sppbSave = Sppb::updateOrCreate([
                                      'CAR' => $d["HEADER"]["CAR"]
                                    ], $dataToSave);
                  }
                  $p += $c;
                } else {
                  foreach($d as $sppb){
                    $c = $this->updateBc16($sppb, 'Scheduler');
                    if($c > 0)
                    {
                      $dataToSave = $this->getDataBC16($sppb);
      
                      $sppbSave = Sppb::updateOrCreate([
                                        'CAR' => $sppb["HEADER"]["CAR"]
                                      ], $dataToSave);
                    }
                    $p += $c;
                  }
                }
              }
    
              DB::commit();
    
              $log->update(['info' => ($p > 0) ? 'COMPLETE' : 'CANCELLED']);
    
              DB::commit();

              echo "Fetch Permit Complete.";
  
            } catch (\Throwable $th) {
              // throw $th;
              DB::rollback();
              \Log::error($th);
              echo "Update Permit Error : ".$log->id."-".$th->getMessage();
            }

          } else {
            DB::beginTransaction();

            try {
              $log->update([
                'info' => $hasil,
              ]);

              DB::commit();
              echo $hasil;

            } catch (\Throwable $th) {
              DB::rollback();
              \Log::error($th);
              echo "Update Permit Error : ".$log->id."-".$th->getMessage();
            }
          }
        }
    }

    public function updatePib($data, $from = NULL)
    {
        if(is_array($data['HEADER']['NO_BL_AWB']) || is_array($data['HEADER']['TG_BL_AWB']))
        {
          return 0;
        }
        $houses = House::where('NO_HOUSE_BLAWB', $data['HEADER']['NO_BL_AWB'])
                      ->where('TGL_HOUSE_BLAWB', date('Y-m-d', strtotime($data['HEADER']['TG_BL_AWB'])))
                      ->whereNotIn('JNS_AJU', [1,2])
                      ->whereNull('SCAN_OUT_DATE')
                      ->get();
        $count = count($houses);
        if($houses->isNotEmpty())
        {
          foreach($houses as $house){
            $bccode = $house->BC_CODE ?? '401';
  
            $house->update([
              'KD_DOC' => 1,
              'SPPBNumber' => $data['HEADER']['NO_SPPB'],
              'SPPBDate' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
              'NO_SPPB' => $data['HEADER']['NO_SPPB'],
              'TGL_SPPB' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
              'NO_DAFTAR_PABEAN' => $data['HEADER']['NO_PIB'],
              'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($data["HEADER"]["TGL_PIB"])),
              'BC_CODE' => $bccode,
              'BC_STATUS' => 'SPPB PIB No. '.$data['HEADER']['NO_SPPB'].' TGL : '.date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])).' AJU : '.$data['HEADER']['CAR'],
              'BC_DATE' => now(),
              'BC_401_DATE' => now(),
            ]);
            $house->master->update([
              'CAR' => $data['HEADER']['CAR'],
            ]);
            createLog('App\Models\House', $house->id, 'Update SPPB '. $house->NO_BARANG . ' to '. $data['HEADER']['NO_SPPB']);

            if($house->kerry)
            {
              $kerry = $house->kerry;
              
              $kerry->logs()->updateOrCreate([
                'STATUS' => 'I2210.01',
                'BC_CODE' => 401,
              ],[                  
                'BC_DATE' => now()->format('Y-m-d H:i:s'),
                'Remarks' => 'approval to issued sppbmcp / customs clearance completed (waiting payment from cnee if ddu)'
              ]);
              \Log::info('Created I2210.01 for '.$house->NO_BARANG.' Completed.');
            }
          }
        }

        return $count;
    }

    public function updateBc($data, $from = NULL)
    {
      if(is_array($data['HEADER']['NO_BL_AWB']) || is_array($data['HEADER']['TGL_BL_AWB']))
      {
        return 0;
      }
      $houses = House::where('NO_BARANG', $data['HEADER']['NO_BL_AWB'])
                    ->where('TGL_HOUSE_BLAWB', date('Y-m-d', strtotime($data['HEADER']['TGL_BL_AWB'])))
                    ->whereNotIn('JNS_AJU', [1,2])
                    ->whereNull('SCAN_OUT_DATE')
                    ->get();
      $count = count($houses);
      if($houses->isNotEmpty())
      {
        foreach($houses as $house){
          $bccode = $house->BC_CODE ?? '401';
          $house->update([
            'KD_DOC' => 2,
            'SPPBNumber' => $data['HEADER']['NO_SPPB'],
            'SPPBDate' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
            'NO_SPPB' => $data['HEADER']['NO_SPPB'],
            'TGL_SPPB' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
            'NO_DAFTAR_PABEAN' => $data['HEADER']['NO_PIB'],
            'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($data["HEADER"]["TGL_PIB"])),
            'BC_CODE' => '401',
            'BC_STATUS' => 'SPPB BC23 No. '.$data['HEADER']['NO_SPPB'].' TGL : '.date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])).' AJU : '.$data['HEADER']['CAR'],
            'BC_DATE' => now(),
            'BC_401_DATE' => now(),
          ]);

          $house->master->update([
            'CAR' => $data['HEADER']['CAR'],
          ]);

          createLog('App\Models\House', $house->id, 'Update SPPB '. $house->NO_BARANG . ' to '. $data['HEADER']['NO_SPPB']);

          if($house->kerry)
          {
            $kerry = $house->kerry;
            
            $kerry->logs()->updateOrCreate([
              'STATUS' => 'I2210.01',
              'BC_CODE' => 401,
            ],[                  
              'BC_DATE' => now()->format('Y-m-d H:i:s'),
              'Remarks' => 'approval to issued sppbmcp / customs clearance completed (waiting payment from cnee if ddu)'
            ]);
            \Log::info('Created I2210.01 for '.$house->NO_BARANG.' Completed.');
          }
          echo "Update Permit ".$house->NO_BARANG." Completed.";
        }
      }
      
      return $count;
    }

    public function updateBc16($data, $from = NULL)
    {
      if(is_array($data['HEADER']['NO_BL_AWB']) || is_array($data['HEADER']['TGL_BL_AWB']))
      {
        return 0;
      }
      $houses = House::where('NO_BARANG', $data['HEADER']['NO_BL_AWB'])
                      ->where('TGL_HOUSE_BLAWB', date('Y-m-d', strtotime($data['HEADER']['TGL_BL_AWB'])))
                      ->whereNotIn('JNS_AJU', [1,2])
                      ->whereNull('SCAN_OUT_DATE')
                    ->get();
      $count = count($houses);
      if($houses->isNotEmpty())
      {
        foreach($houses as $house){
          $bccode = $house->BC_CODE ?? '401';
          $house->update([
            'KD_DOC' => 41,
            'SPPBNumber' => $data['HEADER']['NO_DOK_INOUT'],
            'SPPBDate' => date('Y-m-d', strtotime($data["HEADER"]["TGL_DOK_INOUT"])),
            'NO_SPPB' => $data['HEADER']['NO_DOK_INOUT'],
            'TGL_SPPB' => date('Y-m-d', strtotime($data["HEADER"]["TGL_DOK_INOUT"])),
            'NO_DAFTAR_PABEAN' => $data['HEADER']['NO_DAFTAR'],
            'TGL_DAFTAR_PABEAN' => (!is_array($data["HEADER"]["TGL_DAFTAR"])) ? date('Y-m-d', strtotime($data["HEADER"]["TGL_DAFTAR"])) : NULL,
            'BC_CODE' => '401',
            'BC_STATUS' => 'SPPB BC 1.6 No. '.$data['HEADER']['NO_DOK_INOUT'].' TGL : '.date('Y-m-d', strtotime($data["HEADER"]["TGL_DOK_INOUT"])).' AJU : '.$data['HEADER']['CAR'],
            'BC_DATE' => now(),
            'BC_401_DATE' => now(),
          ]);
          $house->master->update([
            'CAR' => $data['HEADER']['CAR'],
          ]);
          createLog('App\Models\House', $house->id, 'Update SPPB '. $house->NO_BARANG . ' to '. $data['HEADER']['NO_DOK_INOUT']);

          if($house->kerry)
          {
            $kerry = $house->kerry;
            
            $kerry->logs()->updateOrCreate([
              'STATUS' => 'I2210.01',
              'BC_CODE' => 401,
            ],[                  
              'BC_DATE' => now()->format('Y-m-d H:i:s'),
              'Remarks' => 'approval to issued sppbmcp / customs clearance completed (waiting payment from cnee if ddu)'
            ]);
            \Log::info('Created I2210.01 for '.$house->NO_BARANG.' Completed.');
          }
        }
      }
      return $count;
    }

    public function updatePlp($data, $from = NULL)
    {
      $nobl = (is_array($data["DETIL"]["KMS"]["NO_BL_AWB"]) 
                ? "" 
                : $data["DETIL"]["KMS"]["NO_BL_AWB"]);
      $noPlp = $data["HEADER"]["NO_PLP"];
      $tglPlp = date('Y-m-d', strtotime($data["HEADER"]["TGL_PLP"]));

      if($nobl){
        $houses = House::where('NO_BARANG', $nobl)
                        ->with('master.latestPlp')
                        ->get();

        foreach($houses as $house){
          $house->update(['PLP_SETUJU_DATE' => $tglPlp]);
          $master = $house->master;
          $latest = $master->latestPlp->first();
          $master->update([
            'NO_SEGEL' => $noPlp,
            'PLPNumber' => $noPlp,
            'PLPDate' => $tglPlp,
            'ApprovedPLP' => now(),
          ]);
          $latest->update([
            'NO_PLP' => $noPlp,
            'TGL_PLP' => $tglPlp,
            'FL_SETUJU' => 'Y',
            'STATUS' => 'Approved'
          ]);
          $this->plpLog($house->id, $latest->REF_NUMBER, 'GetResponPLP_Tujuan', NULL, NULL, NULL, NULL, 1);
        }
      }          
      
    }

    public function createLog($model, $id, $process, $request, $response, $status)
    {
      $log = SchedulerLog::create([
              'logable_type' => $model,
              'logable_id' => $id,
              'process' => $process,
              'request' => $request,
              'response' => $response,
              'info' => $status
            ]);

      return $log;
    }

    public function getDataPIB(array $sppb)
    {
        $data = [
          // 'CAR' => $sppb["HEADER"]["CAR"],
          'NO_SPPB' => $sppb["HEADER"]["NO_SPPB"],
          'TGL_SPPB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_SPPB"])),
          'KD_KPBC' => $sppb["HEADER"]["KD_KPBC"],
          'NO_PIB' => $sppb["HEADER"]["NO_PIB"],
          'TGL_PIB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_PIB"])),
          'NO_DAFTAR_PABEAN' => $sppb["HEADER"]["NO_PIB"],
          'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_PIB"])),
          'NPWP_IMP' => $sppb["HEADER"]["NPWP_IMP"],
          'NAMA_IMP' => $sppb["HEADER"]["NAMA_IMP"],
          'ALAMAT_IMP' => $sppb["HEADER"]["ALAMAT_IMP"],
          'NPWP_PPJK' => (is_array($sppb["HEADER"]["NPWP_PPJK"]) ? NULL : $sppb["HEADER"]["NPWP_PPJK"]),
          'NAMA_PPJK' => (is_array($sppb["HEADER"]["NAMA_PPJK"]) ? NULL : $sppb["HEADER"]["NAMA_PPJK"]),
          'ALAMAT_PPJK' => (is_array($sppb["HEADER"]["ALAMAT_PPJK"]) ? NULL : $sppb["HEADER"]["ALAMAT_PPJK"]),
          'NM_ANGKUT' => $sppb["HEADER"]["NM_ANGKUT"],
          'NO_VOY_FLIGHT' => $sppb["HEADER"]["NO_VOY_FLIGHT"],
          'BRUTO' => $sppb["HEADER"]["BRUTO"],
          'NETTO' => $sppb["HEADER"]["NETTO"],
          'GUDANG' => $sppb["HEADER"]["GUDANG"],
          'STATUS_JALUR' => $sppb["HEADER"]["STATUS_JALUR"],
          'JML_CONT' => (is_array($sppb["HEADER"]["JML_CONT"]) ? NULL : $sppb["HEADER"]["JML_CONT"]),
          'NO_BC11' => (is_array($sppb["HEADER"]["NO_BC11"])) ? NULL : $sppb["HEADER"]["NO_BC11"],
          'TGL_BC11' => (is_array($sppb["HEADER"]["TGL_BC11"])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BC11"])),
          'NO_POS_BC11' => (is_array($sppb["HEADER"]["NO_POS_BC11"])) ? NULL : $sppb["HEADER"]["NO_POS_BC11"],
          'NO_BL_AWB' => (is_array($sppb["HEADER"]["NO_BL_AWB"])) ? NULL : $sppb["HEADER"]["NO_BL_AWB"],
          'TGL_BL_AWB' =>(is_array($sppb["HEADER"]["TG_BL_AWB"])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TG_BL_AWB"])),
          'NO_MASTER_BL_AWB' => (is_array($sppb["HEADER"]["NO_MASTER_BL_AWB"])) ? NULL : $sppb["HEADER"]["NO_MASTER_BL_AWB"],
          'TGL_MASTER_BL_AWB' =>(is_array($sppb["HEADER"]["TG_MASTER_BL_AWB"])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TG_MASTER_BL_AWB"])),
          'JNS_KMS' => (is_array($sppb["DETIL"]["KMS"]["JNS_KMS"]) ? NULL : $sppb["DETIL"]["KMS"]["JNS_KMS"]),
          'MERK_KMS' => (is_array($sppb["DETIL"]["KMS"]["MERK_KMS"]) ? NULL : $sppb["DETIL"]["KMS"]["MERK_KMS"]),
          'JML_KMS' => $sppb["DETIL"]["KMS"]["JML_KMS"],
        ];

        return $data;
    }

    public function getDataBC23(array $sppb)
    {
      $data = [
        // 'CAR' => $sppb["HEADER"]["CAR"],
        'NO_SPPB' => $sppb["HEADER"]["NO_SPPB"],
        'TGL_SPPB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_SPPB"])),
        'KD_KPBC' => $sppb["HEADER"]["KD_KANTOR_PENGAWAS"],
        'NO_PIB' => $sppb["HEADER"]["NO_PIB"],
        'TGL_PIB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_PIB"])),
        'NO_DAFTAR_PABEAN' => $sppb["HEADER"]["NO_PIB"],
        'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_PIB"])),
        'NPWP_IMP' => $sppb["HEADER"]["NPWP_IMP"],
        'NAMA_IMP' => $sppb["HEADER"]["NAMA_IMP"],
        'ALAMAT_IMP' => $sppb["HEADER"]["ALAMAT_IMP"],
        'NPWP_PPJK' => (is_array($sppb["HEADER"]["NPWP_PPJK"]) ? NULL : $sppb["HEADER"]["NPWP_PPJK"]),
        'NAMA_PPJK' => (is_array($sppb["HEADER"]["NAMA_PPJK"]) ? NULL : $sppb["HEADER"]["NAMA_PPJK"]),
        'ALAMAT_PPJK' => (is_array($sppb["HEADER"]["ALAMAT_PPJK"]) ? NULL : $sppb["HEADER"]["ALAMAT_PPJK"]),
        'NM_ANGKUT' => $sppb["HEADER"]["NM_ANGKUT"],
        'NO_VOY_FLIGHT' => $sppb["HEADER"]["NO_VOY_FLIGHT"],
        'BRUTO' => $sppb["HEADER"]["BRUTTO"],
        'NETTO' => $sppb["HEADER"]["NETTO"],
        'GUDANG' => $sppb["HEADER"]["GUDANG"],
        'STATUS_JALUR' => (is_array($sppb["HEADER"]["STATUS_JALUR"]) ? NULL : $sppb["HEADER"]["STATUS_JALUR"]),
        'JML_CONT' => (is_array($sppb["HEADER"]["JML_CONT"]) ? NULL : $sppb["HEADER"]["JML_CONT"]),
        'NO_BC11' => (is_array($sppb["HEADER"]["NO_BC11"])) ? NULL : $sppb["HEADER"]["NO_BC11"],
        'TGL_BC11' => (!is_array($sppb["HEADER"]["TGL_BC11"])) ? date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BC11"])) : NULL,
        'NO_POS_BC11' => $sppb["HEADER"]["NO_POS_BC11"],
        'NO_BL_AWB' => (is_array($sppb["HEADER"]["NO_BL_AWB"])) ? NULL : $sppb["HEADER"]["NO_BL_AWB"],
        'TGL_BL_AWB' => (is_array($sppb["HEADER"]["TGL_BL_AWB"])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BL_AWB"])),
        'NO_MASTER_BL_AWB' => (is_array($sppb["HEADER"]["NO_MASTER_BL_AWB"])) ? NULL : $sppb["HEADER"]["NO_MASTER_BL_AWB"],
        'TGL_MASTER_BL_AWB' => (is_array($sppb["HEADER"]["TGL_MASTER_BL_AWB"])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TGL_MASTER_BL_AWB"])),
        'JNS_KMS' => NULL,
        'MERK_KMS' => NULL,
        'JML_KMS' => NULL,
      ];

      if(array_key_exists('DETIL', $sppb) 
          && array_key_exists('KMS', $sppb["DETIL"]))
      {
        $data['JNS_KMS'] = (is_array($sppb["DETIL"]["KMS"]["JNS_KMS"]) ? NULL : $sppb["DETIL"]["KMS"]["JNS_KMS"]);
        $data['JML_KMS'] = $sppb["DETIL"]["KMS"]["JML_KMS"];
      }

      return $data;
    }

    public function getDataBC16(array $sppb)
    {
      $data = [
        'NO_SPPB' => $sppb["HEADER"]["NO_DOK_INOUT"],
        'TGL_SPPB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DOK_INOUT"])),
        'KD_KPBC' => $sppb["HEADER"]["KD_KANTOR_PENGAWAS"],
        'NO_PIB' => $sppb["HEADER"]["NO_DOK_INOUT"],
        'TGL_PIB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DOK_INOUT"])),
        'NO_DAFTAR_PABEAN' => (is_array($sppb["HEADER"]["NO_DAFTAR"])) ? NULL : $sppb["HEADER"]["NO_DAFTAR"],
        'TGL_DAFTAR_PABEAN' => (is_array($sppb['HEADER']['TGL_DAFTAR'])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DAFTAR"])),
        'NPWP_IMP' => $sppb["HEADER"]["NPWP_IMP"],
        'NAMA_IMP' => $sppb["HEADER"]["NM_IMP"],
        'ALAMAT_IMP' => $sppb["HEADER"]["AL_IMP"],
        'NPWP_PPJK' => (is_array($sppb["HEADER"]["NPWP_PPJK"]) ? NULL : $sppb["HEADER"]["NPWP_PPJK"]),
        'NAMA_PPJK' => (is_array($sppb["HEADER"]["NM_PPJK"]) ? NULL : $sppb["HEADER"]["NM_PPJK"]),
        'ALAMAT_PPJK' => (is_array($sppb["HEADER"]["AL_PPJK"]) ? NULL : $sppb["HEADER"]["AL_PPJK"]),
        'NM_ANGKUT' => $sppb["HEADER"]["NM_ANGKUT"],
        'NO_VOY_FLIGHT' => $sppb["HEADER"]["NO_VOY_FLIGHT"],
        'BRUTO' => $sppb["HEADER"]["BRUTTO"],
        'NETTO' => $sppb["HEADER"]["NETTO"],
        'GUDANG' => $sppb["HEADER"]["GUDANG"],
        'STATUS_JALUR' => (is_array($sppb["HEADER"]["STATUS_JALUR"])) ? NULL : $sppb["HEADER"]["STATUS_JALUR"],
        'JML_CONT' => (is_array($sppb["HEADER"]["JML_CONT"]) ? NULL : $sppb["HEADER"]["JML_CONT"]),
        'NO_BC11' => (is_array($sppb["HEADER"]["NO_BC11"])) ? NULL : $sppb["HEADER"]["NO_BC11"],
        'TGL_BC11' => (is_array($sppb["HEADER"]["TGL_BC11"])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BC11"])),
        'NO_POS_BC11' => $sppb["HEADER"]["NO_POS_BC11"],
        'NO_BL_AWB' => (is_array($sppb["HEADER"]["NO_BL_AWB"])) ? NULL : $sppb["HEADER"]["NO_BL_AWB"],
        'TGL_BL_AWB' => (is_array($sppb["HEADER"]["TGL_BL_AWB"])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BL_AWB"])),
        'NO_MASTER_BL_AWB' => (is_array($sppb["HEADER"]["NO_MASTER_BL_AWB"])) ? NULL : $sppb["HEADER"]["NO_MASTER_BL_AWB"],
        'TGL_MASTER_BL_AWB' => (is_array($sppb["HEADER"]["TGL_MASTER_BL_AWB"])) ? NULL : date('Y-m-d', strtotime($sppb["HEADER"]["TGL_MASTER_BL_AWB"])),
        'FL_SEGEL' => (is_array($sppb["HEADER"]["FL_SEGEL"])) ? NULL : $sppb["HEADER"]["FL_SEGEL"],
        'JNS_KMS' => NULL,
        'JML_KMS' => NULL,
      ];

      if(array_key_exists('DETIL', $sppb) 
          && array_key_exists('KMS', $sppb["DETIL"]))
      {
        $data['JNS_KMS'] = (is_array($sppb["DETIL"]["KMS"]["JNS_KMS"]) ? NULL : $sppb["DETIL"]["KMS"]["JNS_KMS"]);
        $data['JML_KMS'] = $sppb["DETIL"]["KMS"]["JML_KMS"];
      }

      return $data;
    }

    public function getDataManual(array $sppb)
    {
      $data = [
        // 'CAR' => $sppb["HEADER"]["CAR"],
        'NO_SPPB' => $sppb["HEADER"]["NO_DOK_INOUT"],
        'TGL_SPPB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DOK_INOUT"])),
        'KD_KPBC' => $sppb["HEADER"]["KD_KANTOR"],
        'NO_PIB' => $sppb["HEADER"]["NO_DOK_INOUT"],
        'TGL_PIB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DOK_INOUT"])),
        'NO_DAFTAR_PABEAN' => $sppb["HEADER"]["NO_DOK_INOUT"],
        'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DOK_INOUT"])),
        'NAMA_IMP' => $sppb["HEADER"]["CONSIGNEE"],
        'NPWP_PPJK' => (is_array($sppb["HEADER"]["NPWP_PPJK"]) ? NULL : $sppb["HEADER"]["NPWP_PPJK"]),
        'NAMA_PPJK' => (is_array($sppb["HEADER"]["NM_PPJK"]) ? NULL : $sppb["HEADER"]["NM_PPJK"]),
        'NM_ANGKUT' => $sppb["HEADER"]["NM_ANGKUT"],
        'NO_VOY_FLIGHT' => $sppb["HEADER"]["NO_VOY_FLIGHT"],
        'JML_CONT' => (is_array($sppb["HEADER"]["JML_CONT"]) ? NULL : $sppb["HEADER"]["JML_CONT"]),
        'NO_BC11' => $sppb["HEADER"]["NO_BC11"],
        'TGL_BC11' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BC11"])),
        'NO_POS_BC11' => $sppb["HEADER"]["NO_POS_BC11"],
        'NO_BL_AWB' => $sppb["HEADER"]["NO_BL_AWB"],
        'TGL_BL_AWB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BL_AWB"])),
        'FL_SEGEL' => $sppb["HEADER"]["FL_SEGEL"],
        'NO_CONT' => (is_array($sppb["DETIL"]["CONT"]["NO_CONT"]) ? "" : $sppb["DETIL"]["CONT"]["NO_CONT"]),
      ];

      return $data;
    }    

    public function getResults($service, $string)
    {
      preg_match('/<'.$service.'>(.*)<\/'.$service.'>/', $string, $match);

      return $match[1] ?? "-";
    }

    public function plpLog($id, $ref, $service, $request, $response, $xml, $reason = null, $main = 0)
    {
      PlpOnlineLog::create([
        'user_id' => Auth::id(),
        'plp_id' => $id,
        'REF_NUMBER' => $ref,
        'Service' => $service,
        'Request' => $request,
        'Response' => $response,
        'Date' => now(),
        'XML' => $xml,
        'reason' => $reason,
        'is_main' => $main
      ]);
    } 
}
