<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Helpers\SoapHelper;
use App\Events\ScanHouse;
use App\Models\House;
use Carbon\Carbon;
use Crypt, Str, DB;

class TpsOnlineScanOutController extends Controller
{    
    public function index()
    {
        $item = new House;
        $type = 'out';

        return view('pages.tpsonline.scan', compact(['item', 'type']));
    }
    
    public function store(Request $request)
    {
        $data = $request->validate([
          'NO_HOUSE_BLAWB' => 'required'
        ]);

        if($data){
          $house = House::where('NO_HOUSE_BLAWB', $data['NO_HOUSE_BLAWB'])
                        ->first();

          if(!$house){
            $info = 'House Number not Found!';

            event( new ScanHouse('out', '', 'gagal', $info) );

            return redirect()->route('tps-online.scan-out')
                            ->with('gagal-scan', $info);
          }

          if(!$house->SCAN_IN_DATE){
            $info = 'This house is not yet Scan In!';

            event( new ScanHouse('out', $house->id, 'gagal', $info) );

            return redirect()->route('tps-online.scan-out')
                            ->with('gagal-scan', $info);
          }

          if($house->SCAN_OUT_DATE){
            $info = 'House was already Scanned.';

            event( new ScanHouse('out', $house->id, 'gagal', $info) );

            return redirect()->route('tps-online.scan-out.show', [
                                      'scan_out' => Crypt::encrypt($house->id)
                                    ])
                            ->with('gagal-scan', $info);
          }

          if(!$house->SPPBNumber){
            $info = 'SPPB Number not found.';

            event( new ScanHouse('out', $house->id, 'gagal', $info) );

            return redirect()->route('tps-online.scan-out.show', [
                                      'scan_out' => Crypt::encrypt($house->id)
                                    ])
                            ->with('gagal-scan', $info);
          }
          // dd($house->activeTegah);
          if(!$house->activeTegah->isEmpty()){
            $info = 'This house is restricted by Customs.';

            event( new ScanHouse('out', $house->id, 'gagal', $info) );

            return redirect()->route('tps-online.scan-out.show', [
                                      'scan_out' => Crypt::encrypt($house->id)
                                    ])
                            ->with('gagal-scan', $info);
          }

          DB::beginTransaction();

          try {

            $now = now();

            // $gowia = $this->createXML($house, $now->setTimeZone('UTC'));

            // createLog('App\Models\House', $house->id, 'Create file '.$gowia.' at '.$now->translatedFormat('l d F Y H:i'));

            $house->update([
              'SCAN_OUT_DATE' => $now,
              'SCAN_OUT' => 'Y',
              'ExitDate' => $now->format('Y-m-d'),
              'ExitTime' => $now->format('H:i:s'),
              // 'CW_Ref_GateOut' => $gowia
            ]);

            // $xmlGateOut = $this->sendGateOut($house, $now);

            createLog('App\Models\House', $house->id, 'SCAN OUT');

            DB::commit();

            $info = $house->mawb_parse . ' - ' . $house->NO_BARANG . '<br>'
                    . Str::upper($house->NM_PENERIMA);

            event( new ScanHouse('out', '', 'success', $info) );

            return redirect()->route('tps-online.scan-out.show', [
                            'scan_out' => Crypt::encrypt($house->id)
                          ])
                          ->with('sukses-scan', $info);

          } catch (\Throwable $th) {
            DB::rollback();
            
            return redirect()->route('tps-online.scan-out.show', [
                              'scan_out' => Crypt::encrypt($house->id)
                            ])
                            ->with('gagal-scan', $th->getMessage());
          }
        }
    }
    
    public function show(House $scan_out)
    {
        $item = $scan_out;
        $type = 'out';

        return view('pages.tpsonline.scan', compact(['item', 'type']));
    }

    public function createXML(House $house, Carbon $time)
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

      try {

        // $gowia = Storage::disk('sftp')->put($gowiName, $gowiaTxt);
        // $gowia = Storage::disk('ftp')->put($gowiName, $gowiaTxt);

        return $gowiName;

      } catch (\FilesystemException | \UnableToWriteFile $th) {

        return redirect()->route('tps-online.scan-out.show', [
                          'scan_out' => Crypt::encrypt($house->id)
                        ])
                        ->withErrors($th->getMessage());
      } 

    }

    public function sendGateOut(House $house, Carbon $now)
    {
      $soap = new SoapHelper;
      $running = getRunning('TPS', 'TPSONLINE_REF', $now->format('Y-m-d'));
      $tgl = $house->TGL_TIBA->format('Ymd');
      $SPPBNumber = $house->master->SPPBNumber;
      $SPPBDate = date('Ymd',strtotime($house->master->SPPBDate));
      switch ($house->JNS_AJU) {
          case '4':
          $DocType = '1';
          break;
          case '5':
          $DocType = '2';
          break;
          default :
          $DocType = $house->JNS_AJU;
          break;
      }
      $DocNumber = ($house->BCF15_Status == "Yes"
                      ? $house->BCF15_Number
                      : $SPPBNumber);
      $DocDate = ($house->BCF15_Status == "Yes"
                    ? date('Ymd', strtotime($house->BCF15_Date))
                    : $SPPBDate);

      $xmlArray = [
        'COCOKMS' => [
          'HEADER' => [
              'KD_DOK' => 6, // 5 => Gate In Import, 6=> Gate Out Import
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
              'WK_INOUT' => date('YmdHis', strtotime($house->BC_DATE)), 
              // 'KD_SAR_ANGKUT_INOUT' => '1',
              //'NO_POL' => 'B1234ABC',
              'PEL_MUAT' => $house->Origin,
              'PEL_TRANSIT' => (strlen($house->Transit)>3
                                ? $house->Transit
                                : $house->Origin),
              'PEL_BONGKAR' => $house->Destination,
              'KODE_KANTOR' => '050100',
              'NO_DAFTAR_PABEAN' => '', // NO DAFTAR PABEAN ( NOMOR PIB / BC2.3)
              'TGL_DAFTAR_PABEAN' => date('Ymd', strtotime($house->TGL_HOUSE_BLAWB)), // TANGGAL PENDAFTARAN PIB / BC2.3,
              'NO_SEGEL_BC' => $house->PLPNumber, // JIKA ADA SEGEL WAJIB formatnya
              'TGL_SEGEL_BC' => date('Ymd', strtotime($house->PLPDate)), // jika ada segel wajib yyyymmdd,
              'NO_IJIN_TPS' => $house->NO_BARANG,
              'TGL_IJIN_TPS' => date('Ymd', strtotime($DocDate))
            ]
          ]
        ]
      ];

      $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><DOCUMENT xmlns=\"cocokms.xsd\"></DOCUMENT>", LIBXML_NOWARNING);

      $xml = $soap->arrayToXml($xmlArray, $xml);
      $soap = $soap->soap();

      Storage::disk('local')->put($running. '-' .$house->NO_BARANG .'.xml', $xml->asXML());
      
      // DB::beginTransaction();

      // try {
      //   $sResponse = $soap->CoarriCodeco_Kemasan([
      //     'fStream' => $xml->asXML(),
      //     'Username' => config('app.tps.user'),
      //     'Password' => config('app.tps.password')
      //   ]);

      //   $LastResponse =  $soap->__getLastResponse();
      //   $LastRequest =  $soap->__getLastRequest();

      //   if ($LastResponse) {
      //     if (strpos($LastResponse, 'Berhasil') !== false) {
      //       $house->update([
      //         'TPS_GateInStatus' => 'Y',
      //         'TPS_GateInDateTime' => date("Y-m-d H:i:s"),
      //         'TPS_GateInRef' => $xmlArray['COCOKMS']['HEADER']['REF_NUMBER']
      //       ]);

      //       DB::commit();
      //     }
      //   }

      //   return $xml;

      // } catch (\SoapFault $fault) {
      //   echo 'Request : <br/><xmp>',
      //           $sClient->__getLastRequest(),
      //           '</xmp><br/><br/> Error Message : <br/>',
      //           $fault->getMessage();
      // }

    }

    public function download(Request $request)
    {
      return Storage::disk('sftp')->download($request->file);
    }
}
