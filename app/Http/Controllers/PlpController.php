<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Running;
use App\Helpers\SoapHelper;
use App\Models\Master;
use App\Models\KodeRes;
use App\Models\PlpOnline;
use App\Models\PlpOnlineLog;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DB, Auth, PDF, Str, DataTables, Crypt;

class PlpController extends Controller
{
    public function index(Request $request, Master $master)
    {
      $data = $request->validate([
        'jenis' => 'required'
      ]);

      if($data){
        $jenis = $request->jenis;
        $today = today();
        $latestPlp = $master->latestPlp->first();

        switch ($jenis) {
          case 'plp-request':
            if($latestPlp
                && (($latestPlp->pengajuan == true
                      && $latestPlp->STATUS == 'Pending')
                    || ($latestPlp->pembatalan == true
                        && $latestPlp->STATUS == 'Pending'))){
              return response()->json([
                'status' => 'GAGAL',
                'message' => 'Anda sedang menunggu respon PLP dari BC, tidak diperkenankan mengirim ulang permohonan PLP. Proses dibatalkan.'
              ]);
            }
    
            $running = getRunning('PLP', 'REF_NUMBER', $today->format('Y-m-d'));
            $nosurat = getRunning('PLP', 'NO_SURAT', $today->format('Y-m-d'));

            return $this->sendAjuPlp($master, $running, $nosurat, $today);
            break;
          case 'plp-response':
            if($latestPlp
                && $latestPlp->pengajuan == true
                && $latestPlp->STATUS == 'Approved'){
              return response()->json([
                'status' => 'GAGAL',
                'message' => 'Anda sudah memperoleh persetujuan PLP atas dokumen ini. Silakan cetak permohonan tersebut.'
              ]);
            }
            if(!$latestPlp
                || ($latestPlp
                    && $latestPlp->pengajuan == false)){
              return response()->json([
                'status' => 'GAGAL',
                'message' => 'Tidak ditemukan permohonan PLP untuk dokumen ini, silahkan ajukan permohonan baru.'
              ]);
            }
            return $this->getResponsePlp($master);
            break;
          case 'plp-batal':
            if(!$latestPlp  
                || ($latestPlp->pengajuan == true
                    && $latestPlp->STATUS != 'Approved')){
              return response()->json([
                'status' => 'GAGAL',
                'message' => 'Anda tidak memiliki PLP yang sudah disetujui untuk dibatalkan, silahkan menunggu respon PLP.'
              ]);
            }
            if(!$request->alasan){
              return response()->json([
                'status' => 'GAGAL',
                'message' => 'Silahkan tuliskan alasan pembatalan.'
              ]);
            }
            $running = getRunning('PLP', 'REF_NUMBER', $today->format('Y-m-d'));
            $nosurat = getRunning('PLP', 'NO_SURAT', $today->format('Y-m-d'));
            return $this->sendBatalPlp($master, $running, $nosurat, $today, $request);
            break;
          case 'plp-resbatal':
            if(!$latestPlp
                || ($latestPlp->pembatalan == false)){
              return response()->json([
                'status' => 'GAGAL',
                'message' => 'Tidak ditemukan permohonan batal PLP untuk dokumen ini, silahkan ajukan permohonan batal.'
              ]);
            }
            return $this->getResponseBatalPlp($master);
            break;
          default:
            return false;
            break;
        }
      }
      
    }

    public function table(Master $master)
    {
      $plp = $master->plponline;

      return DataTables::of($plp)
                       ->addIndexColumn()
                       ->addColumn('jenis', function($row){
                        $jenis = '';
                        if($row->pengajuan == true){
                          $jenis = 'Pengajuan';
                        } elseif($row->pembatalan == true){
                          $jenis = 'Pembatalan';
                        }

                        return $jenis;
                       })
                       ->addColumn('status', function($row){
                        $status = '';
                        if($row->FL_SETUJU == 'Y'){
                          $status = 'Disetujui';
                        } elseif($row->FL_SETUJU == 'N'){
                          $status = 'Ditolak';
                        } else {
                          $status = $row->STATUS;
                        }

                        return $status;
                       })
                       ->editColumn('ALASAN_REJECT', function($row){
                        return strip_tags($row->ALASAN_REJECT ?? "-");
                       })
                       ->addColumn('actions', function($row){
                        $btn = '';

                        if($row->STATUS != 'Pending'){
                          $btn = '<a href="'.route('plp.print', ['plp' => $row->id]).'" 
                                     target="_blank"
                                     class="btn btn-primary btn-xs elevation-2">Print</a>';
                        }
                        
                        $btn .= '<a data-href="'.url()->current().'/'.Crypt::encrypt($row->id).'" class="btn btn-xs elevation-2 ml-1 btn-danger delete"><i class="fas fa-trash"></i></a>';

                        return $btn;
                       })
                       ->rawColumns(['actions'])
                       ->toJson();
    }

    public function print(PlpOnline $plp)
    {
      $plp->load(['master.warehouseLine1']);

      $pdf = PDF::setOption([
        'enable_php' => true,
      ]);

      $pdf->loadView('exports.plp', compact(['plp']));

      return $pdf->setPaper('LEGAL', 'portrait')->stream();
    }

    public function sendAjuPlp(Master $master, $running, $nosurat, Carbon $today)
    {
      $warehouse = $master->warehouseLine1;
      $soap = new SoapHelper;

      DB::beginTransaction();

      try {
        
        $plp = PlpOnline::updateOrCreate([
                          'master_id' => $master->id,
                          'pengajuan' => true,
                          'STATUS'    => 'Pending',
                        ],[
                          'KD_KANTOR' => $master->KPBC,
                          'TIPE_DATA' => 1,
                          'KD_TPS_ASAL' => $warehouse->tps_code ?? NULL,
                          'REF_NUMBER' => $running,
                          'NO_SURAT' => ( $nosurat . '/PLP/' . $warehouse->warehouse_code . '/' . $warehouse->warehouse_code . '-'.config('app.tps.kode_gudang'). '/' . $today->format('Y') ),
                          'TGL_SURAT' => $today->format('Y-m-d'),
                          'GUDANG_ASAL' => $warehouse->warehouse_code,
                          'KD_TPS_TUJUAN' => config('app.tps.kode_tps'),
                          'GUDANG_TUJUAN' => config('app.tps.kode_gudang'),
                          'KD_ALASAN_PLP' => 5,
                          'NM_ANGKUT' => $master->NM_SARANA_ANGKUT,
                          'NO_VOY_FLIGHT' => $master->FlightNo,
                          'TGL_TIBA' => $master->ArrivalDate,
                          'NO_BC11' => $master->PUNumber, 
                          'TGL_BC11' => $master->PUDate, 
                          'NO_BL_AWB' => $master->MAWBNumber,
                          'TGL_BL_AWB' => $master->MAWBDate,
                          'JNS_KMS' => $master->houses->first()->JNS_KMS, 
                          'JML_KMS' => $master->mNoOfPackages,
                          'NM_PEMOHON' => Auth::user()->name,
                          'CABANG' => activeCompany()->branches->first()->id,
                          'LAST_SENT' => now(),                          
                        ]);
                        
        $mohonPLP = [
          'LOADPLP' => [
              'HEADER' => [
                'KD_KANTOR' => $plp->KD_KANTOR,
                'TIPE_DATA' => $plp->TIPE_DATA,
                'KD_TPS_ASAL' => $plp->KD_TPS_ASAL,
                'REF_NUMBER' => $plp->REF_NUMBER,
                'NO_SURAT'  => $plp->NO_SURAT,
                'TGL_SURAT' => date('Ymd',strtotime($plp->TGL_SURAT)),
                'GUDANG_ASAL'   => $plp->GUDANG_ASAL,
                'KD_TPS_TUJUAN' => $plp->KD_TPS_TUJUAN,
                'GUDANG_TUJUAN' => $plp->GUDANG_TUJUAN,
                'KD_ALASAN_PLP' => $plp->KD_ALASAN_PLP,
                'YOR_ASAL' => $plp->YOR_ASAL,
                'YOR_TUJUAN' => $plp->YOR_TUJUAN,
                'CALL_SIGN' => '',
                'NM_ANGKUT' => $plp->NM_ANGKUT,
                'NO_VOY_FLIGHT' => $plp->NO_VOY_FLIGHT,
                'TGL_TIBA' => date('Ymd',strtotime($plp->TGL_TIBA)),
                'NO_BC11' => $plp->NO_BC11,
                'TGL_BC11' => date('Ymd',strtotime($plp->TGL_BC11)),
                'NM_PEMOHON' => $plp->NM_PEMOHON,
              ],
              'DETIL' => [
                'KMS' => [
                  'JNS_KMS'   => $plp->JNS_KMS,
                  'JML_KMS'   => $plp->JML_KMS,
                  'NO_BL_AWB' => $master->mawb_parse,
                  'TGL_BL_AWB'    => date('Ymd',strtotime($plp->TGL_BL_AWB))
                ]
              ]
            ]
        ];
        
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><DOCUMENT xmlns="loadplp.xsd"></DOCUMENT>', LIBXML_NOWARNING);

        $xml = $soap->arrayToXml($mohonPLP, $xml);
        
        // Storage::disk('local')->put('AJU_PLP '. $running .'.xml', $xml->asXML());

        $soap = $soap->soap();
        
        $sResponse = $soap->uploadMohonPLP(
          [
            'fStream' => $xml->asXML(),
            'Username' => config('app.tps.user'),
            'Password' => config('app.tps.password')
          ]
        );

        $response =  $soap->__getLastResponse();
        $request =  $soap->__getLastRequest();
        
        /*
        $response = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><UploadMohonPLPResponse xmlns="http://services.beacukai.go.id/"><UploadMohonPLPResult>Proses Berhasil '.$running.'</UploadMohonPLPResult></UploadMohonPLPResponse></soap:Body></soap:Envelope>';        
        
        $response = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><UploadMohonPLPResponse xmlns="http://services.beacukai.go.id/"><UploadMohonPLPResult>&lt;DOCUMENT&gt;&lt;GAGAL&gt;&lt;KD_RES&gt;004&lt;/KD_RES&gt;&lt;/GAGAL&gt;&lt;/DOCUMENT&gt;</UploadMohonPLPResult></UploadMohonPLPResponse></soap:Body></soap:Envelope>';
        */

        $reason = $this->getResults('UploadMohonPLPResult', $response);

        if(!strpos($reason, 'Berhasil')){
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $reason);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          $res = $this->getResults('KD_RES', $hasil);

          $kodeRes = KodeRes::where('kode', $res)->first();

          $status = 'ERROR';
          
          if($kodeRes){            
            $info = $kodeRes->kode . ' - ' .$kodeRes->uraian;
            $plp->update(['STATUS' => $kodeRes->kode, 'ALASAN_REJECT' => $kodeRes->uraian]);
          } else {
            $info = $reason;
            $plp->update(['STATUS' => $reason, 'ALASAN_REJECT' => $reason]);
          }
        } else {
          $status = 'OK';
          $info = $reason;
        }

        $this->plpLog($plp->id, $running, 'UploadMohonPLP', $xml, $response, $xml->asXML(), $info, 1);

        DB::commit();

        return response()->json(['status' => $status, 'message' => $info]);
        
      } catch (\Throwable $th) {

        DB::rollback();
        
        return response()->json(['status' => 'ERROR', 'message' => $th->getMessage()]);
      }

    }

    public function getResponsePlp(Master $master)
    {
        $pending = $master->latestPlp->first();
        $soap = new SoapHelper;

        DB::beginTransaction();

        try {

          $soap = $soap->soap();
          
          $sResponse = $soap->GetResponPlp_onDemands(
            [
              'UserName' => config('app.tps.user'),
              'Password' => config('app.tps.password'), 
              'KdGudang' => config('app.tps.kode_gudang'),
              'RefNumber' => $pending->REF_NUMBER, 
            ]);

          $response =  $soap->__getLastResponse();
          $request =  $soap->__getLastRequest();
          
          /*
          $response = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><GetResponPlp_onDemandsResponse xmlns="http://services.beacukai.go.id/"><GetResponPlp_onDemandsResult>&lt;?xml version="1.0"?&gt;&lt;DOCUMENT&gt;&lt;RESPONPLP&gt;&lt;HEADER&gt;&lt;KD_KANTOR&gt;050100&lt;/KD_KANTOR&gt;&lt;KD_TPS_ASAL&gt;WHD1&lt;/KD_TPS_ASAL&gt;&lt;KD_TPS_TUJUAN&gt;JGE1&lt;/KD_TPS_TUJUAN&gt;&lt;REF_NUMBER&gt;JGE1210913000003&lt;/REF_NUMBER&gt;&lt;GUDANG_ASAL&gt;GDWD&lt;/GUDANG_ASAL&gt;&lt;GUDANG_TUJUAN&gt;JGE1&lt;/GUDANG_TUJUAN&gt;&lt;NO_PLP&gt;166221&lt;/NO_PLP&gt;&lt;TGL_PLP&gt;20210913&lt;/TGL_PLP&gt;&lt;ALASAN_REJECT&gt;&lt;/ALASAN_REJECT&gt;&lt;CALL_SIGN&gt;&lt;/CALL_SIGN&gt;&lt;NM_ANGKUT&gt;MY INDO AIRLINES&lt;/NM_ANGKUT&gt;&lt;NO_VOY_FLIGHT&gt;2Y923&lt;/NO_VOY_FLIGHT&gt;&lt;TGL_TIBA&gt;20210913&lt;/TGL_TIBA&gt;&lt;NO_BC11&gt;014293&lt;/NO_BC11&gt;&lt;TGL_BC11&gt;20210913&lt;/TGL_BC11&gt;&lt;NO_SURAT&gt;00682/PLP/GDWD/JGE1/2021&lt;/NO_SURAT&gt;&lt;TGL_SURAT&gt;20210913&lt;/TGL_SURAT&gt;&lt;/HEADER&gt;&lt;DETIL&gt;&lt;KMS&gt;&lt;JNS_KMS&gt;PK&lt;/JNS_KMS&gt;&lt;JML_KMS&gt;4&lt;/JML_KMS&gt;&lt;NO_BL_AWB&gt;585-1118 8752&lt;/NO_BL_AWB&gt;&lt;TGL_BL_AWB&gt;20210901&lt;/TGL_BL_AWB&gt;&lt;NO_POS_BC11&gt;000700000000&lt;/NO_POS_BC11&gt;&lt;CONSIGNEE&gt;PT JUSTINDO GLOBAL EKSPRES&lt;/CONSIGNEE&gt;&lt;FL_SETUJU&gt;Y&lt;/FL_SETUJU&gt;&lt;/KMS&gt;&lt;/DETIL&gt;&lt;/RESPONPLP&gt;&lt;/DOCUMENT&gt;</GetResponPlp_onDemandsResult></GetResponPlp_onDemandsResponse></soap:Body></soap:Envelope>';            
          
          $response = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><GetResponPlp_onDemandsResponse xmlns="http://services.beacukai.go.id/"><GetResponPlp_onDemandsResult>Data tidak ditemukan</GetResponPlp_onDemandsResult></GetResponPlp_onDemandsResponse></soap:Body></soap:Envelope>';
          */

          $resParse = $this->getResults('GetResponPlp_onDemandsResult', $response);
  
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $resParse);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          $noPlp = $this->getResults('NO_PLP', $hasil);
          $tglPlp = $this->getResults('TGL_PLP', $hasil);
          $reason = $this->getResults('ALASAN_REJECT', $hasil);
          $setuju = $this->getResults('FL_SETUJU', $hasil);

          if(strtotime($tglPlp)){
            $tgl = Carbon::parse($tglPlp)->format('Y-m-d');
          } else {
            $tgl = $tglPlp;
          }
          
          if($setuju == 'Y'){
            $status = 'Approved';
            $reason = 'Disetujui';

            $st = 'OK';
            $message = 'No PLP disetujui '.$noPlp;

            $master->update([
              'NO_SEGEL' => $noPlp,
              'PLPNumber' => $noPlp,
              'PLPDate' => $tgl,
              'ApprovedPLP' => now()
            ]);

            $master->houses()->update([
              'PLP_SETUJU_DATE' => now()
            ]);
            
          } elseif($setuju == 'T'){
            $status = 'Rejected';
            $st = 'REJECT';
            $message = ($reason == '-') ? $resParse : $reason;
            $reason = $message;
          } else {
            $status = 'Pending';
            $st = 'Data Tidak Ditemukan';
            $message = ($reason == '-') ? $resParse : $reason;
            $reason = $message;
          }

          $pending->update([
                    'NO_PLP' => $noPlp,
                    'TGL_PLP' => $tgl,
                    'LAST_SENT' => now()->format('Y-m-d H:i:s'),
                    'FL_SETUJU' => $setuju,
                    'STATUS' => $status,
                    'ALASAN_REJECT' => $reason
                  ]);

          $this->plpLog($pending->id, $pending->REF_NUMBER, 'GetResponPlp_onDemands', $request, $response, null, $reason, 1);

          DB::commit();

          return response()->json(['status' => $st, 'message' => $message]);

        } catch (\Throwable $th) {
          DB::rollback();
          // throw $th;
          return response()->json(['status' => 'ERROR', 'message' => $th->getMessage()]);
        }       

    }

    public function sendBatalPlp(Master $master, $running, $nosurat, Carbon $today, Request $request)
    {
      if(!$request->alasan){
        return response()->json([
          'status' => 'GAGAL',
          'message' => 'Silahkan tuliskan alasan pembatalan.'
        ]);
      }
      $warehouse = $master->warehouseLine1;
      $soap = new SoapHelper;

      DB::beginTransaction();

      try {
        $plp = PlpOnline::updateOrCreate([
                  'master_id' => $master->id,
                  'pembatalan' => true,
                  'STATUS'    => 'Pending',
                ],[
                  'KD_KANTOR' => $master->KPBC,
                  'TIPE_DATA' => 1,
                  'KD_TPS_ASAL' => $warehouse->tps_code ?? NULL,
                  'REF_NUMBER' => $running,
                  'NO_SURAT' => ( $nosurat . '/PLP/' . $warehouse->warehouse_code . '/' . $warehouse->warehouse_code . '-'. config('app.tps.kode_gudang') . '/' . $today->format('Y') ),
                  'TGL_SURAT' => $today->format('Y-m-d'),
                  'GUDANG_ASAL' => $warehouse->warehouse_code,
                  'KD_TPS_TUJUAN' => config('app.tps.kode_tps'),
                  'GUDANG_TUJUAN' => config('app.tps.kode_gudang'),
                  'KD_ALASAN_PLP' => 5,
                  'NM_ANGKUT' => $master->NM_SARANA_ANGKUT,
                  'NO_VOY_FLIGHT' => $master->FlightNo,
                  'TGL_TIBA' => $master->ArrivalDate,
                  'NO_BC11' => $master->PUNumber, 
                  'TGL_BC11' => $master->PUDate, 
                  'NO_BL_AWB' => $master->MAWBNumber,
                  'TGL_BL_AWB' => $master->MAWBDate,
                  'JNS_KMS' => $master->houses->first()->JNS_KMS, 
                  'JML_KMS' => $master->mNoOfPackages,
                  'NM_PEMOHON' => Auth::user()->name,
                  'CABANG' => activeCompany()->branches->first()->id,
                  'LAST_SENT' => now(),
                  'ALASAN_BATAL' => $request->alasan
                ]);
                
        $mohonPLP = [
          'BATALPLP' => [
            'HEADER' => [
              'KD_KANTOR' => $plp->KD_KANTOR,
              'TIPE_DATA' => $plp->TIPE_DATA,
              'KD_TPS'    => $plp->KD_TPS_TUJUAN,
              'REF_NUMBER' => $plp->REF_NUMBER,
              'NO_SURAT'  => str_replace(' ','',$plp->NO_SURAT),
              'TGL_SURAT' => date('Ymd',strtotime($plp->TGL_SURAT)),
              'NO_PLP'    => $master->PLPNumber,
              'TGL_PLP' => date('Ymd',strtotime($master->PLPDate)),
              'ALASAN'    => $request->alasan,
              'NO_BC11'    => $plp->NO_BC11,
              'TGL_BC11'    => date('Ymd',strtotime($plp->TGL_BC11)),
              'NM_PEMOHON' => $plp->NM_PEMOHON,
            ],
            'DETIL' => [
              'KMS' => [
                'JNS_KMS'   => $plp->JNS_KMS,
                'JML_KMS'   => $plp->JML_KMS,
                'NO_BL_AWB' => $master->mawb_parse,
                'TGL_BL_AWB'    => date('Ymd',strtotime($plp->TGL_BL_AWB))
              ]
            ]
          ]
        ];

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><DOCUMENT xmlns="loadbatalplp.xsd"></DOCUMENT>', LIBXML_NOWARNING);

        $xml = $soap->arrayToXml($mohonPLP, $xml);

        // Storage::disk('local')->put('AJU_BATAL_PLP '. $running .'.xml', $xml->asXML());

        $soap = $soap->soap();
          
        $sResponse = $soap->uploadBatalPLP(
          [
            'fStream' => $xml->asXML(),
            'Username' => config('app.tps.user'),
            'Password' => config('app.tps.password')
          ]);

        $response =  $soap->__getLastResponse();
        $request =  $soap->__getLastRequest();

        /*
        $response = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><UploadBatalPLPResponse xmlns="http://services.beacukai.go.id/"><UploadBatalPLPResult>Proses Berhasil '.$running.'</UploadBatalPLPResult></UploadBatalPLPResponse></soap:Body></soap:Envelope>';
        */

        $reason = $this->getResults('UploadBatalPLPResult', $response);

        if(!strpos($reason, 'Berhasil')){
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $reason);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          $res = $this->getResults('KD_RES', $hasil);

          $kodeRes = KodeRes::where('kode', $res)->first();
          $status = 'ERROR';
          if($kodeRes){            
            $info = $kodeRes->kode . ' - ' .$kodeRes->uraian;
            $plp->update(['STATUS' => $kodeRes->kode, 'ALASAN_REJECT' => $kodeRes->uraian]);
          } else {
            $info = $reason;
            $plp->update(['STATUS' => $reason, 'ALASAN_REJECT' => $reason]);
          }
        } else {
          $status = 'OK';
          $info = $reason;
        }

        $this->plpLog($plp->id, $running, 'uploadBatalPLP', $xml, $response, $xml->asXML(), $info, 1);

        DB::commit();

        return response()->json(['status' => $status, 'message' => $info]);
        
      } catch (\Throwable $th) {
        DB::rollback();
        
        return response()->json(['status' => 'ERROR', 'message' => $th->getMessage()]);
      }
    }

    public function getResponseBatalPlp(Master $master)
    {
        $pending = $master->latestPlp->first();
        $soap = new SoapHelper;

        DB::beginTransaction();

        try {

          $soap = $soap->soap();
          
          $sResponse = $soap->GetResponBatalPlp_onDemands(
            [
              'UserName' => config('app.tps.user'),
              'Password' => config('app.tps.password'), 
              'KdGudang' => config('app.tps.kode_gudang'),
              'RefNumber' => $pending->REF_NUMBER, 
            ]);

          $response =  $soap->__getLastResponse();
          $request =  $soap->__getLastRequest();
          
          /*
          $response = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><GetResponBatalPlp_onDemandsResponse xmlns="http://services.beacukai.go.id/"><GetResponBatalPlp_onDemandsResult>&lt;?xml version="1.0"?&gt;&lt;DOCUMENT&gt;&lt;RESPONPLP&gt;&lt;HEADER&gt;&lt;KD_KANTOR&gt;050100&lt;/KD_KANTOR&gt;&lt;KD_TPS_ASAL&gt;WHD1&lt;/KD_TPS_ASAL&gt;&lt;KD_TPS_TUJUAN&gt;JGE1&lt;/KD_TPS_TUJUAN&gt;&lt;REF_NUMBER&gt;JGE1210913000031&lt;/REF_NUMBER&gt;&lt;GUDANG_ASAL&gt;GDWD&lt;/GUDANG_ASAL&gt;&lt;GUDANG_TUJUAN&gt;JGE1&lt;/GUDANG_TUJUAN&gt;&lt;NO_PLP&gt;166221&lt;/NO_PLP&gt;&lt;TGL_PLP&gt;20210913&lt;/TGL_PLP&gt;&lt;NO_BATAL_PLP&gt;001500&lt;/NO_BATAL_PLP&gt;&lt;TGL_BATAL_PLP&gt;20210913&lt;/TGL_BATAL_PLP&gt;&lt;ALASAN_BATAL&gt;Salah kode gudang asal&lt;/ALASAN_BATAL&gt;&lt;/HEADER&gt;&lt;DETIL&gt;&lt;KMS&gt;&lt;JNS_KMS&gt;PK&lt;/JNS_KMS&gt;&lt;JML_KMS&gt;4&lt;/JML_KMS&gt;&lt;NO_BL_AWB&gt;585-1118 8752&lt;/NO_BL_AWB&gt;&lt;TGL_BL_AWB&gt;9/1/2021&lt;/TGL_BL_AWB&gt;&lt;FL_SETUJU&gt;Y&lt;/FL_SETUJU&gt;&lt;/KMS&gt;&lt;/DETIL&gt;&lt;/RESPONPLP&gt;&lt;/DOCUMENT&gt;</GetResponBatalPlp_onDemandsResult></GetResponBatalPlp_onDemandsResponse></soap:Body></soap:Envelope>';
          */
  
          $resParse = $this->getResults('GetResponBatalPlp_onDemandsResult', $response);
  
          $strResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $resParse);
          $hasil = Str::replace('&lt;', '<', Str::replace('&gt;', '>', $strResult));
          $noPlp = $this->getResults('NO_BATAL_PLP', $hasil);
          $tglPlp = $this->getResults('TGL_BATAL_PLP', $hasil);
          $reason = $this->getResults('ALASAN_REJECT', $hasil);
          $setuju = $this->getResults('FL_SETUJU', $hasil);

          if(strtotime($tglPlp)){
            $tgl = Carbon::parse($tglPlp)->format('Y-m-d');
          } else {
            $tgl = $tglPlp;
          }
          
          if($setuju == 'Y'){
            $status = 'Approved';
            $reason = 'Disetujui';
            
            $st = 'OK';
            $message = 'No PLP disetujui '.$noPlp;

            $master->update([
              'NO_SEGEL' => NULL,
              'PLPNumber' => $noPlp,
              'PLPDate' => $tgl,
              'ApprovedPLP' => now()->format('Y-m-d H:i:s')
            ]);
          } elseif($setuju == 'T') {
            $status = 'Rejected';
            $st = 'REJECT';
            $message = ($reason == '-') ? $resParse : $reason;
            $reason = $message;
          } else {
            $status = 'Pending';
            $st = 'Data Tidak Ditemukan';
            $message = ($reason == '-') ? $resParse : $reason;
            $reason = $message;
          }        

          $pending->update([
                      'NO_PLP' => $noPlp,
                      'TGL_PLP' => $tgl,
                      'LAST_SENT' => now()->format('Y-m-d H:i:s'),
                      'FL_SETUJU' => $setuju,
                      'STATUS' => $status,
                      'ALASAN_REJECT' => $reason
                  ]);

          $this->plpLog($pending->id, $pending->REF_NUMBER, 'GetResponBatalPlp_onDemands', $request, $response, null, $reason, 1);

          DB::commit();

          return response()->json(['status' => $st, 'message' => $message]);

        } catch (\Throwable $th) {
          DB::rollback();
          // throw $th;
          return response()->json(['status' => 'ERROR', 'message' => $th->getMessage()]);
        }       

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

    public function getResults($service, $string)
    {
      // preg_match('/<'.$service.'>(.*)<\/'.$service.'>/', $string, $match);
      preg_match('~<'.$service.'>([^{]*)</'.$service.'>~i', $string, $match);

      return $match[1] ?? "-";
    }
}
