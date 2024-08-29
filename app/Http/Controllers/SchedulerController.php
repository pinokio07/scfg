<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;
use Illuminate\Http\Request;
use App\Helpers\SoapHelper;
use App\Models\SchedulerLog;
use App\Models\KodeRes;
use App\Models\Master;
use App\Models\House;
use App\Models\Sppb;
use App\Models\PlpOnline;
use App\Models\PlpOnlineLog;
use Carbon\Carbon;
use Str, Auth, DB;

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
                        ->whereNull('TPS_GateInDateTime')
                        ->limit(5)
                        ->get();

        if(!$houses){
          echo "No house found for gate out.";
        }

        foreach ($houses as $house) {
          $hasil = $this->sendGateIn($request, $house);

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
                        ->whereNull('TPS_GateOutDateTime')
                        ->whereNotNull('NO_DAFTAR_PABEAN')
                        ->limit(5)
                        ->get();
        if(!$houses){
          echo "No house found for gate out.";
        }
                        
        foreach ($houses as $house) {
          if(!$house->NO_DAFTAR_PABEAN){
            if($request->ajax()){
              return response()->json(['status' => "ERROR", 'message' => 'Pabean No is Empty']);
            }
            echo 'NO PABEAN KOSONG';
          } else {
            $hasil = $this->sendGateOut($request, $house);

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
        }
        $hasil = $this->sendGateOut($request, $house, true);

        if($request->ajax()){
          return response()->json($hasil);
        }

        echo 'Kirim ulang Gate Out '.$house->NO_BARANG.' Status: '.$hasil['status'].'; Info : '.$hasil['info']."<br/>";
      }      
    }

    public function plp()
    {
        $sh = new SoapHelper;  
        $soap = $sh->soap();

        DB::beginTransaction();

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

          DB::commit();
          
        } catch (\Throwable $th) {
          DB::rollback();

          $this->createLog(NULL, NULL, 'GetResponPLP_Tujuan', NULL, NULL, 'FAILED : '.$th->getMessage());

          DB::commit();

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
                $this->updatePib($sppb, 'On Demand');

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
            } else {
              $this->updatePib($data, 'On Demand');

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
                $this->updateBc($sppb, 'On Demand');

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
            } else {
              $this->updateBc($data, 'On Demand');

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
                $this->updateBc16($sppb, 'On Demand');

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
            } else {

              $this->updateBc16($data, 'On Demand');

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

          foreach($data as $d){
            if(array_key_exists('HEADER', $d)){
              $this->updatePib($d, 'Scheduler');
  
              $dataToSave = $this->getDataPIB($d);

              $sppbSave = Sppb::updateOrCreate([
                                'CAR' => $d["HEADER"]["CAR"]
                              ], $dataToSave);
            } else {
              foreach($d as $sppb){
                $this->updatePib($sppb, 'Scheduler');
  
                $dataToSave = $this->getDataPIB($sppb);
  
                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $sppb["HEADER"]["CAR"]
                                ], $dataToSave);
              }
              
            }            
            
          }
          
          DB::commit();

          $log->update(['info' => 'COMPLETE']);

          DB::commit();

          echo "Fetch Permit Complete.";

        } else {
          return $this->updatePermit();
        }

      } catch (\SoapFault $fault) {
        DB::rollback();

        $this->createLog(NULL, NULL, 'GetImporPermit_FASP', NULL, NULL, 'FAILED : '.$fault->getMessage());

        DB::commit();

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

          foreach($data as $d){
            if(array_key_exists('HEADER', $d)){
              $this->updateBc($d, 'Scheduler');
  
              $dataToSave = $this->getDataBC23($d);

              $sppbSave = Sppb::updateOrCreate([
                                'CAR' => $d["HEADER"]["CAR"]
                              ], $dataToSave);
            } else {
              foreach($d as $sppb){
                $this->updateBc($sppb, 'Scheduler');
  
                $dataToSave = $this->getDataBC23($sppb);
  
                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $sppb["HEADER"]["CAR"]
                                ], $dataToSave);
              }
            }

          }

          DB::commit();

          $log->update(['info' => 'COMPLETE']);

          DB::commit();

          echo "Fetch Permit Complete.";

        } else {
          return $this->updatePermitBc();
        }

      } catch (\SoapFault $fault) {
        DB::rollback();

        $this->createLog(NULL, NULL, 'GetBC23Permit_FASP', NULL, NULL, 'FAILED : '.$fault->getMessage());

        DB::commit();

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

          foreach($data as $d){
            if(array_key_exists('HEADER', $d)){
              $this->updateBc16($d, 'Scheduler');
  
              $dataToSave = $this->getDataBC16($d);

              $sppbSave = Sppb::updateOrCreate([
                                'CAR' => $d["HEADER"]["CAR"]
                              ], $dataToSave);
            } else {
              foreach($d as $sppb){
                $this->updateBc16($sppb, 'Scheduler');
  
                $dataToSave = $this->getDataBC16($sppb);
  
                $sppbSave = Sppb::updateOrCreate([
                                  'CAR' => $sppb["HEADER"]["CAR"]
                                ], $dataToSave);
              }
            } 
          }
          
          DB::commit();

          $log->update(['info' => 'COMPLETE']);

          DB::commit();

          echo "Fetch Permit Complete.";

        } else {
          return $this->updatePermitBc16();
        }

      } catch (\SoapFault $fault) {
        DB::rollback();

        $this->createLog(NULL, NULL, 'GetDokumenPabeanPermit_FASP', NULL, NULL, 'FAILED : '.$fault->getMessage());

        DB::commit();

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

      if($house->TPS_GateInREF && $force == false){
        $ref_num = $house->TPS_GateInREF;
      } else {
        $ref_num = getRunning('TPS', 'GATE_IN', $tgl->format('Y-m-d'));
      }      
      
      $DocType = '3';//Persetujuan PLP Kode 3 perhatian, sementara 22 paket pos
      $DocNumber = $house->master->PLPNumber;
      $DocDate = date('Ymd', strtotime($house->master->PLPDate));

      $xmlArray = [
        'COCOKMS' => [
          'HEADER' => [
              'KD_DOK' => 5, // 5 => Gate In Import, 6=> Gate Out Import
              'KD_TPS' => config('app.tps.kode_tps'),
              'NM_ANGKUT' => $house->NM_PENGANGKUT,
              'NO_VOY_FLIGHT' => $house->NO_FLIGHT,
              'CALL_SIGN' => '',
              'TGL_TIBA' => $tgl->format('Ymd'),
              'KD_GUDANG' => config('app.tps.kode_gudang'),
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

      if($house->TPS_GateOutREF && $force == false){
        $ref_num = $house->TPS_GateOutREF;
      } else {
        $ref_num = getRunning('TPS', 'GATE_OUT', $tgl->format('Y-m-d'));
      }      

      $DocType = $house->JNS_AJU;

      $DocNumber = ($house->BCF15_Status == "Yes"
                      ? $house->BCF15_Number
                      : $house->SPPBNumber);

      $DocDate = ($house->BCF15_Status == "Yes"
                    ? date('Ymd', strtotime($house->BCF15_Date))
                    : date('Ymd', strtotime($house->SPPBDate)));

      $xmlArray = [
        'COCOKMS' => [
            'HEADER' => [
                'KD_DOK' => 6, // 5 => Gate In Import, 6=> Gate Out Import
                'KD_TPS' => config('app.tps.kode_tps'),
                'NM_ANGKUT' => $house->NM_PENGANGKUT,
                'NO_VOY_FLIGHT' => $house->NO_FLIGHT,
                'CALL_SIGN' => '',
                'TGL_TIBA' => $tgl->format('Ymd'),
                'KD_GUDANG' => config('app.tps.kode_gudang'),
                'REF_NUMBER' => $ref_num,
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
                'NO_DAFTAR_PABEAN' => $house->NO_DAFTAR_PABEAN, // NO DAFTAR PABEAN ( NOMOR PIB / BC2.3)
                'TGL_DAFTAR_PABEAN' => date('Ymd', strtotime($house->TGL_DAFTAR_PABEAN)), // TANGGAL PENDAFTARAN PIB / BC2.3,
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
              'BC_CODE' => '408',
              'BC_STATUS' => 'BARANG KELUAR DARI GUDANG'
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

          $response = $sh->getResults('GetImporPermit_FASPResult', $soapResponse);
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          if(strpos($hasil, 'DOCUMENT')){
            $res = simplexml_load_string($hasil);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);
            
            DB::beginTransaction();

            try {

              foreach($data as $d){
                if(array_key_exists('HEADER', $d)){
                  $this->updatePib($d, 'Scheduler');
      
                  $dataToSave = $this->getDataPIB($d);
    
                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $d["HEADER"]["CAR"]
                                  ], $dataToSave);
                } else {
                  foreach($d as $sppb){
                    $this->updatePib($sppb, 'Scheduler');
      
                    $dataToSave = $this->getDataPIB($sppb);
      
                    $sppbSave = Sppb::updateOrCreate([
                                      'CAR' => $sppb["HEADER"]["CAR"]
                                    ], $dataToSave);
                  }
                } 
              }              
    
              DB::commit();
    
              $log->update(['info' => 'COMPLETE']);
    
              DB::commit();

              echo 'Update Permit Success';

            } catch (\Throwable $th) {
              echo "Update Permit Error : ".$th->getMessage();
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

              echo 'Update Permit Error, Message: '.$th->getMessage();
            }
          }

          sleep(1);          
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

          $response = $sh->getResults('GetBC23Permit_FASPResult', $soapResponse);
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          if(strpos($hasil, 'DOCUMENT')){
            $res = simplexml_load_string($hasil);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);
            
            DB::beginTransaction();
  
            try {
              foreach($data as $d){
                if(array_key_exists('HEADER', $d)){
                  $this->updateBc($d, 'Scheduler');
      
                  $dataToSave = $this->getDataBC23($d);
    
                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $d["HEADER"]["CAR"]
                                  ], $dataToSave);
                } else {
                  foreach($d as $sppb){
                    $this->updateBc($sppb, 'Scheduler');
      
                    $dataToSave = $this->getDataBC23($sppb);
      
                    $sppbSave = Sppb::updateOrCreate([
                                      'CAR' => $sppb["HEADER"]["CAR"]
                                    ], $dataToSave);
                  }
                }
              }
    
              DB::commit();
    
              $log->update(['info' => 'COMPLETE']);
    
              DB::commit();

              echo "Update Permit Success";
  
            } catch (\Throwable $th) {
              echo "Update Permit Error : ".$th->getMessage();
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

              echo 'Update Permit Error, Message: '.$th->getMessage();
            }
          }          

          sleep(1);
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

          $response = $sh->getResults('GetDokumenPabeanPermit_FASPResult', $soapResponse);
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          if(strpos($hasil, 'DOCUMENT')){
            $res = simplexml_load_string($hasil);
            $json = json_encode($res);
            $data = json_decode($json, TRUE);
            
            DB::beginTransaction();
  
            try {
              foreach($data as $d){
                if(array_key_exists('HEADER', $d)){
                  $this->updateBc16($d, 'Scheduler');
      
                  $dataToSave = $this->getDataBC16($d);
    
                  $sppbSave = Sppb::updateOrCreate([
                                    'CAR' => $d["HEADER"]["CAR"]
                                  ], $dataToSave);
                } else {
                  foreach($d as $sppb){
                    $this->updateBc16($sppb, 'Scheduler');
      
                    $dataToSave = $this->getDataBC16($sppb);
      
                    $sppbSave = Sppb::updateOrCreate([
                                      'CAR' => $sppb["HEADER"]["CAR"]
                                    ], $dataToSave);
                  }
                }
              }
    
              DB::commit();
    
              $log->update(['info' => 'COMPLETE']);
    
              DB::commit();

              echo "Fetch Permit Complete.";
  
            } catch (\Throwable $th) {
              echo "Update Permit Error : ".$th->getMessage();
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

              echo 'Update Permit Error, Message: '.$th->getMessage();
            }
          }          

          sleep(1);
        }
    }

    public function updatePib($data, $from = NULL)
    {
        $houses = House::where('NO_HOUSE_BLAWB', $data['HEADER']['NO_BL_AWB'])
                      ->where('TGL_HOUSE_BLAWB', date('Y-m-d', strtotime($data['HEADER']['TG_BL_AWB'])))
                      ->get();

        foreach($houses as $house){
          $bccode = $house->BC_CODE ?? '401';

          $house->update([
            'JNS_AJU' => 1,
            'SPPBNumber' => $data['HEADER']['NO_SPPB'],
            'SPPBDate' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
            'NO_SPPB' => $data['HEADER']['NO_SPPB'],
            'TGL_SPPB' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
            'NO_DAFTAR_PABEAN' => $data['HEADER']['NO_PIB'],
            'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($data["HEADER"]["TGL_PIB"])),
            'BC_CODE' => $bccode,
            'BC_STATUS' => 'SPPB PIB No. '.$data['HEADER']['NO_SPPB'].' TGL : '.date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])).' AJU : '.$data['HEADER']['CAR'],
          ]);
          $house->master->update([
            'CAR' => $data['HEADER']['CAR'],
          ]);
          createLog('App\Models\House', $house->id, 'Update SPPB '. $house->NO_BARANG . ' to '. $data['HEADER']['NO_SPPB']);
        }
    }

    public function updateBc($data, $from = NULL)
    {
        $houses = House::where('NO_BARANG', $data['HEADER']['NO_BL_AWB'])
                      ->where('TGL_HOUSE_BLAWB', date('Y-m-d', strtotime($data['HEADER']['TGL_BL_AWB'])))
                      ->get();

        foreach($houses as $house){
          $bccode = $house->BC_CODE ?? '401';
          $house->update([
            'JNS_AJU' => 2,
            'SPPBNumber' => $data['HEADER']['NO_SPPB'],
            'SPPBDate' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
            'NO_SPPB' => $data['HEADER']['NO_SPPB'],
            'TGL_SPPB' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
            'NO_DAFTAR_PABEAN' => $data['HEADER']['NO_PIB'],
            'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($data["HEADER"]["TGL_PIB"])),
            'BC_CODE' => '401',
            'BC_STATUS' => 'SPPB BC23 No. '.$data['HEADER']['NO_SPPB'].' TGL : '.date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])).' AJU : '.$data['HEADER']['CAR'],
            'BC_DATE' => date('Y-m-d', strtotime($data["HEADER"]["TGL_SPPB"])),
          ]);

          $house->master->update([
            'CAR' => $data['HEADER']['CAR'],
          ]);

          createLog('App\Models\House', $house->id, 'Update SPPB '. $house->NO_BARANG . ' to '. $data['HEADER']['NO_SPPB']);
        }
    }

    public function updateBc16($data, $from = NULL)
    {
        $houses = House::where('NO_BARANG', $data['HEADER']['NO_BL_AWB'])
                        ->where('TGL_HOUSE_BLAWB', date('Y-m-d', strtotime($data['HEADER']['TGL_BL_AWB'])))
                      ->get();

        foreach($houses as $house){
          $bccode = $house->BC_CODE ?? '401';
          $house->update([
            'JNS_AJU' => 41,
            'SPPBNumber' => $data['HEADER']['NO_DOK_INOUT'],
            'SPPBDate' => date('Y-m-d', strtotime($data["HEADER"]["TGL_DOK_INOUT"])),
            'NO_SPPB' => $data['HEADER']['NO_DOK_INOUT'],
            'TGL_SPPB' => date('Y-m-d', strtotime($data["HEADER"]["TGL_DOK_INOUT"])),
            'NO_DAFTAR_PABEAN' => $data['HEADER']['NO_DAFTAR'],
            'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($data["HEADER"]["TGL_DAFTAR"])),
            'BC_CODE' => '401',
            'BC_STATUS' => 'SPPB BC 1.6 No. '.$data['HEADER']['NO_DOK_INOUT'].' TGL : '.date('Y-m-d', strtotime($data["HEADER"]["TGL_DOK_INOUT"])).' AJU : '.$data['HEADER']['CAR'],
          ]);
          $house->master->update([
            'CAR' => $data['HEADER']['CAR'],
          ]);
          createLog('App\Models\House', $house->id, 'Update SPPB '. $house->NO_BARANG . ' to '. $data['HEADER']['NO_SPPB']);
        }
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
          'NPWP_PPJK' => (is_array($sppb["HEADER"]["NPWP_PPJK"]) ? '' : $sppb["HEADER"]["NPWP_PPJK"]),
          'NAMA_PPJK' => (is_array($sppb["HEADER"]["NAMA_PPJK"]) ? '' : $sppb["HEADER"]["NAMA_PPJK"]),
          'ALAMAT_PPJK' => (is_array($sppb["HEADER"]["ALAMAT_PPJK"]) ? '' : $sppb["HEADER"]["ALAMAT_PPJK"]),
          'NM_ANGKUT' => $sppb["HEADER"]["NM_ANGKUT"],
          'NO_VOY_FLIGHT' => $sppb["HEADER"]["NO_VOY_FLIGHT"],
          'BRUTO' => $sppb["HEADER"]["BRUTO"],
          'NETTO' => $sppb["HEADER"]["NETTO"],
          'GUDANG' => $sppb["HEADER"]["GUDANG"],
          'STATUS_JALUR' => $sppb["HEADER"]["STATUS_JALUR"],
          'JML_CONT' => (is_array($sppb["HEADER"]["JML_CONT"]) ? '' : $sppb["HEADER"]["JML_CONT"]),
          'NO_BC11' => $sppb["HEADER"]["NO_BC11"],
          'TGL_BC11' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BC11"])),
          'NO_POS_BC11' => $sppb["HEADER"]["NO_POS_BC11"],
          'NO_BL_AWB' => $sppb["HEADER"]["NO_BL_AWB"],
          'TGL_BL_AWB' => date('Y-m-d', strtotime($sppb["HEADER"]["TG_BL_AWB"])),
          'NO_MASTER_BL_AWB' => $sppb["HEADER"]["NO_MASTER_BL_AWB"],
          'TGL_MASTER_BL_AWB' => date('Y-m-d', strtotime($sppb["HEADER"]["TG_MASTER_BL_AWB"])),
          'JNS_KMS' => (is_array($sppb["DETIL"]["KMS"]["JNS_KMS"]) ? "" : $sppb["DETIL"]["KMS"]["JNS_KMS"]),
          'MERK_KMS' => (is_array($sppb["DETIL"]["KMS"]["MERK_KMS"]) ? "" : $sppb["DETIL"]["KMS"]["MERK_KMS"]),
          'JML_KMS' => $sppb["DETIL"]["KMS"]["JML_KMS"],
        ];

        return $data;
    }

    public function getDataBC23(array $sppb)
    {
      $data = [
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
        'NPWP_PPJK' => (is_array($sppb["HEADER"]["NPWP_PPJK"]) ? '' : $sppb["HEADER"]["NPWP_PPJK"]),
        'NAMA_PPJK' => (is_array($sppb["HEADER"]["NAMA_PPJK"]) ? '' : $sppb["HEADER"]["NAMA_PPJK"]),
        'ALAMAT_PPJK' => (is_array($sppb["HEADER"]["ALAMAT_PPJK"]) ? '' : $sppb["HEADER"]["ALAMAT_PPJK"]),
        'NM_ANGKUT' => $sppb["HEADER"]["NM_ANGKUT"],
        'NO_VOY_FLIGHT' => $sppb["HEADER"]["NO_VOY_FLIGHT"],
        'BRUTO' => $sppb["HEADER"]["BRUTTO"],
        'NETTO' => $sppb["HEADER"]["NETTO"],
        'GUDANG' => $sppb["HEADER"]["GUDANG"],
        'STATUS_JALUR' => (is_array($sppb["HEADER"]["STATUS_JALUR"]) ? '' : $sppb["HEADER"]["STATUS_JALUR"]),
        'JML_CONT' => (is_array($sppb["HEADER"]["JML_CONT"]) ? '' : $sppb["HEADER"]["JML_CONT"]),
        'NO_BC11' => $sppb["HEADER"]["NO_BC11"],
        'TGL_BC11' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BC11"])),
        'NO_POS_BC11' => $sppb["HEADER"]["NO_POS_BC11"],
        'NO_BL_AWB' => $sppb["HEADER"]["NO_BL_AWB"],
        'TGL_BL_AWB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BL_AWB"])),
        'NO_MASTER_BL_AWB' => $sppb["HEADER"]["NO_MASTER_BL_AWB"],
        'TGL_MASTER_BL_AWB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_MASTER_BL_AWB"])),
        'JNS_KMS' => (is_array($sppb["DETIL"]["KMS"]["JNS_KMS"]) ? "" : $sppb["DETIL"]["KMS"]["JNS_KMS"]),
        'MERK_KMS' => NULL,
        'JML_KMS' => $sppb["DETIL"]["KMS"]["JML_KMS"],
      ];

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
        'NO_DAFTAR_PABEAN' => $sppb["HEADER"]["NO_DAFTAR"],
        'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DAFTAR"])),
        'NPWP_IMP' => $sppb["HEADER"]["NPWP_IMP"],
        'NAMA_IMP' => $sppb["HEADER"]["NM_IMP"],
        'ALAMAT_IMP' => $sppb["HEADER"]["AL_IMP"],
        'NPWP_PPJK' => (is_array($sppb["HEADER"]["NPWP_PPJK"]) ? '' : $sppb["HEADER"]["NPWP_PPJK"]),
        'NAMA_PPJK' => (is_array($sppb["HEADER"]["NM_PPJK"]) ? '' : $sppb["HEADER"]["NM_PPJK"]),
        'ALAMAT_PPJK' => (is_array($sppb["HEADER"]["AL_PPJK"]) ? '' : $sppb["HEADER"]["AL_PPJK"]),
        'NM_ANGKUT' => $sppb["HEADER"]["NM_ANGKUT"],
        'NO_VOY_FLIGHT' => $sppb["HEADER"]["NO_VOY_FLIGHT"],
        'BRUTO' => $sppb["HEADER"]["BRUTTO"],
        'NETTO' => $sppb["HEADER"]["NETTO"],
        'GUDANG' => $sppb["HEADER"]["GUDANG"],
        'STATUS_JALUR' => (is_array($sppb["HEADER"]["STATUS_JALUR"])) ? '' : $sppb["HEADER"]["STATUS_JALUR"],
        'JML_CONT' => (is_array($sppb["HEADER"]["JML_CONT"]) ? '' : $sppb["HEADER"]["JML_CONT"]),
        'NO_BC11' => $sppb["HEADER"]["NO_BC11"],
        'TGL_BC11' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BC11"])),
        'NO_POS_BC11' => $sppb["HEADER"]["NO_POS_BC11"],
        'NO_BL_AWB' => $sppb["HEADER"]["NO_BL_AWB"],
        'TGL_BL_AWB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_BL_AWB"])),
        'NO_MASTER_BL_AWB' => $sppb["HEADER"]["NO_MASTER_BL_AWB"],
        'TGL_MASTER_BL_AWB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_MASTER_BL_AWB"])),
        'FL_SEGEL' => $sppb["HEADER"]["FL_SEGEL"],
        'JNS_KMS' => (is_array($sppb["DETIL"]["KMS"]["JNS_KMS"]) ? "" : $sppb["DETIL"]["KMS"]["JNS_KMS"]),
        'JML_KMS' => $sppb["DETIL"]["KMS"]["JML_KMS"],
      ];

      return $data;
    }

    public function getDataManual(array $sppb)
    {
      $data = [
        'NO_SPPB' => $sppb["HEADER"]["NO_DOK_INOUT"],
        'TGL_SPPB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DOK_INOUT"])),
        'KD_KPBC' => $sppb["HEADER"]["KD_KANTOR"],
        'NO_PIB' => $sppb["HEADER"]["NO_DOK_INOUT"],
        'TGL_PIB' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DOK_INOUT"])),
        'NO_DAFTAR_PABEAN' => $sppb["HEADER"]["NO_DOK_INOUT"],
        'TGL_DAFTAR_PABEAN' => date('Y-m-d', strtotime($sppb["HEADER"]["TGL_DOK_INOUT"])),
        'NAMA_IMP' => $sppb["HEADER"]["CONSIGNEE"],
        'NPWP_PPJK' => (is_array($sppb["HEADER"]["NPWP_PPJK"]) ? '' : $sppb["HEADER"]["NPWP_PPJK"]),
        'NAMA_PPJK' => (is_array($sppb["HEADER"]["NM_PPJK"]) ? '' : $sppb["HEADER"]["NM_PPJK"]),
        'NM_ANGKUT' => $sppb["HEADER"]["NM_ANGKUT"],
        'NO_VOY_FLIGHT' => $sppb["HEADER"]["NO_VOY_FLIGHT"],
        'JML_CONT' => (is_array($sppb["HEADER"]["JML_CONT"]) ? '' : $sppb["HEADER"]["JML_CONT"]),
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
