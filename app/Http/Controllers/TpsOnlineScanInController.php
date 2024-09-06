<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Events\ScanHouse;
use App\Helpers\SoapHelper;
use App\Models\House;
use Carbon\Carbon;
use Crypt, Str, DB;

class TpsOnlineScanInController extends Controller
{    
    public function index()
    {
        $item = new House;
        $type = 'in';

        return view('pages.tpsonline.scan', compact(['item', 'type']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
          'NO_HOUSE_BLAWB' => 'required'
        ]);

        if($data){
          $house = House::with(['master.houses:id,MasterID,SCAN_IN_DATE'])
                        ->where('NO_BARANG', $data['NO_HOUSE_BLAWB'])
                        ->first();

          if(!$house){
            $info = 'House '.$data['NO_HOUSE_BLAWB'].' Tidak Ditemukan!';

            // event( new ScanHouse('in', '', 'gagal', $info) );

            if($request->ajax())
            {
              return response()->json([
                'status' => 'ERROR',
                'message' => $info,
                'mawb' => '-',
                'houses' => 0,
                'complete' => 0
              ]);
            }

            return redirect()->route('tps-online.scan-in')
                             ->with('gagal-scan', $info);
          }

          $mawb = $house->mawb_parse;
          $master = $house->master;
          $hCount = $master->houses->count();
          $complete = $master->houses->whereNotNull('SCAN_IN_DATE')->count();

          if(!$master->PLPNumber){
            $info = 'House '.$house->NO_BARANG.' ( '.$house->JML_BRG.' '.$house->JNS_KMS. ' ) ini belum mendapatkan persetujuan PLP / Nomor PLP Kosong';

            // event( new ScanHouse('in', $house->id, 'gagal', $info) );

            if($request->ajax())
            {
              return response()->json([
                'status' => 'ERROR',
                'message' => $info,
                'mawb' => $mawb,
                'houses' => $hCount,
                'complete' => $complete
              ]);
            }

            return redirect()->route('tps-online.scan-in')
                             ->with('gagal-scan', $info);
          }

          if($house->SCAN_IN_DATE){
            $info = 'House '.$house->NO_BARANG.' ( '.$house->JML_BRG.' '.$house->JNS_KMS.' ) sudah pernah discan sebelumnya';

            // event( new ScanHouse('in', $house->id, 'gagal', $info) );

            if($request->ajax())
            {
              return response()->json([
                'status' => 'ERROR',
                'message' => $info,
                'mawb' => $mawb,
                'houses' => $hCount,
                'complete' => $complete
              ]);
            }

            return redirect()->route('tps-online.scan-in.show', [
                                      'scan_in' => Crypt::encrypt($house->id)
                                    ])
                            ->with('gagal-scan', $info);
          }

          DB::beginTransaction();

          try {
            $now = now();

            $house->update([
              'SCAN_IN_DATE' => $now,
              'SCAN_IN' => 'Y',
              'EnteranceDate' => $now->format('Y-m-d')
            ]);

            if(!$house->master->MasukGudang){
              $house->master->update([
                'MasukGudang' => $now
              ]);
            }

            createLog('App\Models\House', $house->id, $house->NO_BARANG . ' SCAN IN');

            DB::commit();

            $info = $house->mawb_parse . ' - ' . $house->NO_BARANG . ' ( '.$house->JML_BRG.' '.$house->JNS_KMS.' )<br>'
                    . Str::upper($house->NM_PENERIMA);

            // event( new ScanHouse('in', '', 'success', $info) );

            if($request->ajax())
            {
              return response()->json([
                'status' => 'OK',
                'message' => $info,
                'mawb' => $mawb,
                'houses' => $hCount,
                'complete' => ($complete + 1)
              ]);
            }

            return redirect()->route('tps-online.scan-in.show', [
                            'scan_in' => Crypt::encrypt($house->id)
                          ])
                          ->with('sukses-scan', $info);

          } catch (\Throwable $th) {
            DB::rollback();
            
            if($request->ajax())
            {
              return response()->json([
                'status' => 'ERROR',
                'message' => $th->getMessage(),
                'mawb' => $mawb,
                'houses' => $hCount,
                'pending' => $pending
              ]);
            }

            return redirect()->route('tps-online.scan-in.show', [
                                      'scan_in' => Crypt::encrypt($house->id)
                                    ])
                            ->with('gagal-scan', $th->getMessage());
          }
        }
    }

    public function show(House $scan_in)
    {
        $item = $scan_in;
        $type = 'in';

        return view('pages.tpsonline.scan', compact(['item', 'type']));
    }

    public function createXML(House $house, Carbon $time)
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

      try {        

        $giwia = Storage::disk('sftp')->put($giwiName, $giwiaTxt);
        // $giwia = Storage::disk('ftp')->put($giwiName, $giwiaTxt);
        
        return $giwiName;

      } catch (\FilesystemException | \UnableToWriteFile $th) {
        
        return redirect()->route('tps-online.scan-in.show', [
                                  'scan_in' => Crypt::encrypt($house->id)
                                ])
                        ->withErrors($th->getMessage());
      } 

    }

    public function sendGateIn(House $house, Carbon $now)
    {
      $soap = new SoapHelper;
      $running = getRunning('TPS', 'GATE_IN', $now->format('Y-m-d'));
      $tgl = $house->TGL_TIBA->format('Ymd');
      $DocType = '3';//Persetujuan PLP Kode 3 perhatian, sementara 22 paket pos
      $DocNumber = $house->master->PLPNumber;
      $DocDate = $house->master->PLPDate;

      $xmlArray = [
        'COCOKMS' => [
          'HEADER' => [
              'KD_DOK' => 5, // 5 => Gate In Import, 6=> Gate Out Import
              'KD_TPS' => config('app.kode_tps'),
              'NM_ANGKUT' => $house->NM_PENGANGKUT,
              'NO_VOY_FLIGHT' => $house->NO_FLIGHT,
              'CALL_SIGN' => '',
              'TGL_TIBA' => $tgl,
              'KD_GUDANG' => config('app.kode_gudang'),
              'REF_NUMBER' => config('app.kode_tps') . $tgl . $running
          ],
          'DETIL' => [
            'KMS' => [
              'NO_BL_AWB' => $house->NO_BARANG,
              'TGL_BL_AWB' => date('Ymd', strtotime($house->TGL_HOUSE_BLAWB)),
              'NO_MASTER_BL_AWB' => $house->NO_MASTER_BLAWB,
              'TGL_MASTER_BL_AWB' => date('Ymd', strtotime($house->TGL_MASTER_BLAWB)),
              'ID_CONSIGNEE' => "000000000000000",
              'CONSIGNEE' => $house->NM_PENERIMA,
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
              'WK_INOUT' => date('YmdHis', strtotime($house->master->MasukGudang)), 
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

      $xml = $soap->arrayToXml($xmlArray, $xml);
      $soap = $soap->soap();

      Storage::disk('local')->put($running. '-' .$house->NO_BARANG . '-' . $now->format('YmdHis') . '.xml', $xml->asXML());
      
      DB::beginTransaction();

      try {
        $sResponse = $soap->CoCoKms_Tes([
          'fStream' => $xml->asXML(),
          'Username' => config('app.tps.user'),
          'Password' => config('app.tps.password')
        ]);

        $LastResponse =  $soap->__getLastResponse();
        $LastRequest =  $soap->__getLastRequest();

        if ($LastResponse) {
          if (strpos($LastResponse, 'Berhasil') !== false) {
            $house->update([
              'TPS_GateInStatus' => 'Y',
              'TPS_GateInDateTime' => date("Y-m-d H:i:s"),
              'TPS_GateInRef' => $xmlArray['COCOKMS']['HEADER']['REF_NUMBER']
            ]);

            DB::commit();
          }
        }

        return $LastResponse;

      } catch (\SoapFault $fault) {
        echo 'Request : <br/><xmp>',
                $sClient->__getLastRequest(),
                '</xmp><br/><br/> Error Message : <br/>',
                $fault->getMessage();
      }

    }

    public function download(Request $request)
    {
      return Storage::disk('sftp')->download($request->file);
    }
}
