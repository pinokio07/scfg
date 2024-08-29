<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use App\Helpers\Barkir;
use App\Imports\UploadImport;
use App\Imports\ManifestImport;
use App\Imports\BarkirImport;
use App\Models\Master;
use App\Models\MasterPartial;
use App\Models\House;
use App\Models\HouseDetail;
use App\Models\RefAirline;
use Excel;
use Str;
use DB;

class UploadController extends Controller
{
    public function index(Request $request)
    {
      $jenis = $request->jenis;

      if($jenis == 'master'){
        return $this->getData($request);
      } elseif($jenis == 'barkir') {
        return $this->getBarkir($request);
      }

      Excel::import(new UploadImport($jenis), $request->upload);

      return redirect('/manifest/consolidations')->with('sukses', 'Upload Success.');
    }

    public function getData(Request $request)
    {
        $import = Excel::toCollection(new ManifestImport(), $request->upload);
        $barang = collect([]);
        $house = collect([]);
        $master = collect([]);
        $header = collect([]);

        if($import->has(3)
            && $import->has(2)
            && $import->has(1)
            && $import->has(0)){
              
          foreach($import[3] as $k3 => $hscode){
            if($k3 > 0 && $hscode[1] != ''){
              $barang[] = [
                'ID_DETIL' => $hscode[1],
                'SERI_BRG' => $hscode[2],
                'HS_CODE' => $hscode[3],
                'UR_BRG' => $hscode[4]
              ];
            }
          }

          foreach ($import[2] as $k2 => $hs) {
            if($k2 > 0 && $hs[1] != ''){
              $idhouse = $hs[0];             

              $brg = $barang->where('ID_DETIL', $idhouse);
              if($brg){
                $hs->put('barang', $brg);
              }
              
              $house[] = [
                'ID_MASTER' => $hs[1],
                'JNS_AJU' => $hs[3],
                'NO_POS_BC11' => $hs[4],
                'NO_SUBPOS_BC11' => $hs[5],
                'NO_SUBSUBPOS_BC11' => $hs[6],
                'NO_MASTER_BLAWB' => $hs[7],
                'TGL_MASTER_BLAWB' => (strtotime($hs[8])) 
                                      ? Carbon::createFromFormat('d-m-Y', $hs[8])->format('Y-m-d')
                                      : '',
                'NO_HOUSE_BLAWB' => $hs[9],
                'NO_BARANG' => $hs[9],
                'TGL_HOUSE_BLAWB' => (strtotime($hs[10]))
                                      ? Carbon::createFromFormat('d-m-Y', $hs[10])->format('Y-m-d')
                                      : '',
                'NM_PENGANGKUT' => $hs[11],
                'NO_ID_PENERIMA' => $hs[12],
                'NM_PENERIMA' => $hs[13],
                'AL_PENERIMA' => $hs[14],
                'NM_PENGIRIM' =>  $hs[17],
                'AL_PENGIRIM' => $hs[18],
                'KD_NEG_PENGIRIM' => $hs[19],
                'KD_PEL_MUAT' => $hs[23],
                'KD_PEL_TRANSIT' => $hs[24],
                'KD_PEL_BONGKAR' => $hs[25],
                'KD_PEL_AKHIR' => $hs[26],
                'JML_BRG' => $hs[27],
                'JNS_KMS' => $hs[28],
                'BRUTO' => $hs[31],
                'VOLUME' => $hs[32],
                'hscodes' => $hs['barang']
              ];
            }
          }

          foreach($import[1] as $k1 => $mt) {
            if($k1 > 0 && $mt[1] != ''){
              $idmaster = $mt[0];
              
              $hou = $house->where('ID_MASTER', $idmaster);

              if($hou){
                $mt->put('house', $hou);
              }
              $master[] = [
                'CAR' => $mt[1],
                'MAWBNumber' => Str::replace('-', '', $mt[3]),
                'MAWBDate' => (strtotime($mt[4]))
                               ? Carbon::createFromFormat('d-m-Y', $mt[4])->format('Y-m-d')
                               : '',
                'HAWBCount' => $mt[5],
                'houses' => $mt['house']
              ];
            }
          }

          foreach($import[0] as $k0 => $hd){
            if($k0 > 0 && $hd[0] != ''){
              $aju = $hd[0];
              $mtr = $master->where('CAR', $aju);

              if($mtr){
                $hd->put('master', $mtr);
              }

              $header[] = [
                'AJU' => $hd[0],
                'KPBC' => $hd[5],
                'PUNumber' => $hd[8],
                'PUDate' => (strtotime($hd[9])) 
                              ? Carbon::createFromFormat('d-m-Y', $hd[9])->format('Y-m-d')
                              : '',
                'NM_SARANA_ANGKUT' => $hd[10],
                'FlightNo' => $hd[16],
                'Origin' => $hd[20],
                'Transit' => $hd[21],
                'Destination' => $hd[22],
                'ArrivalDate' => (strtotime($hd[25]))
                                  ? Carbon::createFromFormat('d-m-Y', $hd[25])->format('Y-m-d')
                                  : '',
                'ArrivalTime' => $hd[26],
                'master' => $hd['master']
              ];
            }
          }          
        }

        return $this->processData($header);
    }

    public function getBarkir(Request $request)
    {
      $barkir = new Barkir;  

      $import = Excel::toCollection(new BarkirImport(), $request->upload);

      $filenameWithExt = $request->file('upload')->getClientOriginalName();
      $request->file('upload')->move('storage/file/manifest', $filenameWithExt);
      
      $houses = collect([]);
      $details = collect([]);

      $branch = activeCompany();

      if($import->has(0) && $import->has(1))
      {
        $headerHouse = $import[0][0];
        $headerDetail = $import[1][0];
        
        foreach ($import[0] as $kh => $house) {
          if($kh > 0)
          {
            $houses[] = $headerHouse->combine($house);
          }
        }
        foreach($import[1] as $kd => $detail) {
          if($kd > 0)
          {
            $details[] = $headerDetail->combine($detail);
          }
        }

        DB::beginTransaction();

        try {
          foreach($houses->whereNotNull('NO_HOUSE_BLAWB') as $hk => $house)
          {
            $num = str_replace(' ', '', $house['NO_MASTER_BLAWB']);
            $first = substr($num, 0, 3);
            $second = substr($num, 3, 11);
            $KATEGORI_BARANG_KIRIMAN = 1;

            $nobarang = trim($house['NO_BARANG']);
            $hawb = trim($house['NO_HOUSE_BLAWB']);

            if(count($details->where('NO_HOUSE_BLAWB', $hawb)) == 0)
            {
              $errInfo = 'HS Code Kosong!';
              $errDetail = 'HS Code untuk '.$hawb.' kosong.';
              
              return view('errors.custom', compact(['errInfo', 'errDetail']));
            }

            if($house['CIF'] != $details->where('NO_HOUSE_BLAWB', $hawb)->sum('CIF'))
            {
              $errInfo = 'CIF Tidak Sesuai!';
              $errDetail = 'Nilai CIF untuk '.$hawb.' tidak sesuai dengan HS Code';
              
              return view('errors.custom', compact(['errInfo', 'errDetail']));
            }

            if(count($details->where('FL_BEBAS', '>', 1)) > 0)
            {
              $errInfo = 'Flag Bebas tidak sesuai!';
              $errDetail = 'Flag Bebas hanya bisa di isi dengan angka 1 atau 0!';
              
              return view('errors.custom', compact(['errInfo', 'errDetail']));
            }

            if(count($houses->where('NO_MASTER_BLAWB', '<>', $house['NO_MASTER_BLAWB'])) > 0)
            {
              $beda = $houses->where('NO_MASTER_BLAWB', '<>', $house['NO_MASTER_BLAWB'])->first();
              $errInfo = 'No Master berbeda!';
              $errDetail = 'Ditemukan 2 nomor MAWB berbeda di excel, silahkan cek kembali file anda!</b>';
              
              return view('errors.custom', compact(['errInfo', 'errDetail']));
            }

            if($house['JML_BRG'] != $details->where('NO_HOUSE_BLAWB', $hawb)->sum('JML_KMS'))
            {
              $errInfo = 'Jumlah Barang Tidak Sesuai!';
              $errDetail = 'Jumlah Barang untuk '.$hawb.' tidak sesuai dengan HS Code';
              
              return view('errors.custom', compact(['errInfo', 'errDetail']));
            }

            $cekNoBarang = House::where('NO_BARANG', $nobarang)->first();

            if($cekNoBarang 
                && \Str::replace(' ','', $cekNoBarang->NO_MASTER_BLAWB) != $num)
            {
              $mid = \Crypt::encrypt($cekNoBarang->MasterID);
              $errInfo = 'No Barang Sudah digunakan!';
              $errDetail = 'No Barang '.$nobarang.' sudah digunakan di <a href="'.route('manifest.consolidations.edit', ['consolidation' => $mid]).'">'.$cekNoBarang->mawb_parse.'</a>';
              // abort(405, $info);
              return view('errors.custom', compact(['errInfo', 'errDetail']));
            }

            $cekHawb = House::where('NO_BARANG', $hawb)->first();

            if($cekHawb 
                && \Str::replace(' ','', $cekHawb->NO_MASTER_BLAWB) != $num)
            {
              $mid = \Crypt::encrypt($cekHawb->MasterID);
              $errInfo = 'No House AWB Sudah digunakan!';
              $errDetail = 'No House AWB '.$hawb.' sudah digunakan di <a href="'.route('manifest.consolidations.edit', ['consolidation' => $mid]).'">'.$cekHawb->mawb_parse.'</a>';
              
              return view('errors.custom', compact(['errInfo', 'errDetail']));
            }

            $cekMaster = Master::where('MAWBNumber', $num)
                                ->whereNotNull('PLPNumber')
                                ->first();

            if($cekMaster)
            {
              $mid = \Crypt::encrypt($cekMaster->id);
              $errInfo = 'MAWB sudah diproses Barang Kiriman!';
              $errDetail = 'No Master AWB '.$num.' sudah di proses PLP. <a href="'.route('manifest.consolidations.edit', ['consolidation' => $mid]).'">'.$cekMaster->mawb_parse.'</a>';
              
              return view('errors.custom', compact(['errInfo', 'errDetail']));
            }

            $KATEGORI_BARANG_KIRIMAN = 2;
            
            $master = Master::updateOrCreate([
              'MAWBNumber' => $num,                
            ],[
              'MAWBDate' => Date::excelToDateTimeObject($house['TGL_MASTER_BLAWB'])
              ->format('Y-m-d'),
              'AirlineCode' => substr($house['NO_FLIGHT'], 0, 2),
              'FlightNo' => $house['NO_FLIGHT'],
              'Origin' => $house['KD_PEL_MUAT'],
              'Transit' => $house['KD_PEL_TRANSIT'],
              'Destination' => $house['KD_PEL_AKHIR'],
              'ArrivalDate' => Date::excelToDateTimeObject($house['TGL_TIBA'])
              ->format('Y-m-d'),
              'ArrivalTime' => Date::excelToDateTimeObject($house['JAM_TIBA'])
              ->format('H:i:s'),
              'OriginCountry' => $house['KD_NEGARA_ASAL'],
              'KPBC' => $house['KD_KANTOR'],
              'NPWP' => $house['NO_ID_PEMBERITAHU'],
              'NM_PEMBERITAHU' => $house['NM_PEMBERITAHU'],
              'NM_SARANA_ANGKUT' => $house['NM_PENGANGKUT'],
              'NO_MBLAWB' => $first .'-'. $second,
              'mBRANCH' => $branch->id,
            ]);

            $master->load(['pjt']);

            $STRPAD = substr(str_pad(date('Hi').$master->id,6,'0',STR_PAD_LEFT),0,6);
            $car = $master->pjt?->ID_MODUL . date('Ymd') . $STRPAD;

            $partial = MasterPartial::updateOrCreate([
              'MasterID' => $master->id,                
            ],[
              'CAR' => $car,
              'NM_ANGKUT' => $house['NM_PENGANGKUT'],
              'NO_FLIGHT' => $house['NO_FLIGHT'],
              'TGL_TIBA' => Date::excelToDateTimeObject($house['TGL_TIBA'])
              ->format('Y-m-d'),
              'JAM_TIBA' => Date::excelToDateTimeObject($house['JAM_TIBA'])
              ->format('H:i:s'),
              'TOTAL_BRUTO' => 0
            ]);

            switch ($house['JNS_AJU']) {
              case '1':
                $DocType = '43';
                break;
              case '2':
                $DocType ='43';
                break;
              case '3':
                $DocType ='43';
                break;
              case '4':
                $DocType = '1';
                break;
              case '5':
                $DocType = '2';
                break;
              default:
                $DocType = $house['JNS_AJU'];
                break;
            }
            
            // if($branch->CB_Code == 'QCN')
            // {
            //   $house['ChargeableWeight'] = ($house['NETTO'] < 1) ? 1 : round($house['NETTO'], 0 , PHP_ROUND_HALF_UP);
            // } else {
            //   $house['ChargeableWeight'] = $house['BRUTO'];
            // }

            $house['ChargeableWeight'] = $house['NETTO'];

            $hs = House::updateOrCreate([
              'MasterID' => $master->id,
              'NO_BARANG' => $nobarang,                
            ],[
              'NO_HOUSE_BLAWB' => $hawb,
              'TGL_HOUSE_BLAWB' => Date::excelToDateTimeObject($house['TGL_HOUSE_BLAWB'])
              ->format('Y-m-d'),
              'NO_MASTER_BLAWB' => $master->MAWBNumber,
              'TGL_MASTER_BLAWB' => $master->MAWBDate,
              'PartialID' => $partial->PartialID,
              'JNS_AJU' => $house['JNS_AJU'],
              'KD_DOC' => $DocType,
              'KD_JNS_PIBK' => $house['KD_JNS_PIBK'],
              'KD_KANTOR' => $house['KD_KANTOR'],
              'KD_JNS_ANGKUT' => $house['KD_JNS_ANGKUT'],
              'NM_PENGANGKUT' => $house['NM_PENGANGKUT'],
              'NO_FLIGHT' => $house['NO_FLIGHT'],
              'KD_PEL_MUAT' => $house['KD_PEL_MUAT'],
              'KD_PEL_BONGKAR' => $house['KD_PEL_BONGKAR'],
              'KD_GUDANG' => $house['KD_GUDANG'],
              'NO_INVOICE' => $house['NO_INVOICE'],
              'TGL_INVOICE' => Date::excelToDateTimeObject($house['TGL_INVOICE'])
              ->format('Y-m-d'),
              'KD_NEGARA_ASAL' => $house['KD_NEGARA_ASAL'],
              'JML_BRG' => $house['JML_BRG'],              
              'NO_SUBPOS_BC11' => $house['NO_SUBPOS_BC11'],
              'NO_SUBSUBPOS_BC11' => $house['NO_SUBSUBPOS_BC11'],
              'KD_NEG_PENGIRIM' => $house['KD_NEG_PENGIRIM'],
              'NM_PENGIRIM' => $house['NM_PENGIRIM'],
              'AL_PENGIRIM' => $house['AL_PENGIRIM'],
              'JNS_ID_PENERIMA' => $house['JNS_ID_PENERIMA'],
              'NO_ID_PENERIMA' => $house['NO_ID_PENERIMA'],
              'NM_PENERIMA' => $house['NM_PENERIMA'],
              'AL_PENERIMA' => $house['AL_PENERIMA'],
              'TELP_PENERIMA' => $house['TELP_PENERIMA'],
              'JNS_ID_PEMBERITAHU' => $house['JNS_ID_PEMBERITAHU'],
              'NO_ID_PEMBERITAHU' => $house['NO_ID_PEMBERITAHU'],
              'NM_PEMBERITAHU' => $house['NM_PEMBERITAHU'],
              'AL_PEMBERITAHU' => $house['AL_PEMBERITAHU'],
              'NO_IZIN_PEMBERITAHU' => $house['NO_IZIN_PEMBERITAHU'],
              'TGL_IZIN_PEMBERITAHU' => Date::excelToDateTimeObject($house['TGL_IZIN_PEMBERITAHU'])
              ->format('Y-m-d'),
              'KD_VAL' => $house['KD_VAL'],
              'NDPBM' => $house['NDPBM'],
              'FOB' => $house['FOB'],
              'ASURANSI' => $house['ASURANSI'],
              'FREIGHT' => $house['FREIGHT'],
              'CIF' => $house['CIF'],
              'NETTO' => $house['NETTO'],
              'BRUTO' => $house['BRUTO'],
              'ChargeableWeight' => $house['ChargeableWeight'],
              'TOT_DIBAYAR' => $house['TOT_DIBAYAR'],
              'NPWP_BILLING' => $house['NPWP_BILLING'],
              'NAMA_BILLING' => $house['NAMA_BILLING'],
              'TGL_TIBA' => Date::excelToDateTimeObject($house['TGL_TIBA'])
              ->format('Y-m-d'),
              'JAM_TIBA' => Date::excelToDateTimeObject($house['JAM_TIBA'])
              ->format('H:i:s'),
              'PART_SHIPMENT' => $house['PART_SHIPMENT'],
              'KD_PEL_TRANSIT' => $house['KD_PEL_TRANSIT'],
              'KD_PEL_AKHIR' => $house['KD_PEL_AKHIR'],
              'VOLUME' => $house['VOLUME'],
              'JNS_KMS' => $house['JNS_KMS'],
              'TOTAL_PARTIAL' => $house['TOTAL_PARTIAL'],
              'MARKING' => $house['MARKING'],
              'BRANCH' => $master->mBRANCH,
              'INCO' => $house['SERVICE_CODE'],
              'KATEGORI_BARANG_KIRIMAN' => $KATEGORI_BARANG_KIRIMAN
            ]);

            $NO_BC11 = ($house['NO_BC11']) ? $house['NO_BC11'] : $hs->NO_BC11;
            $NO_POS_BC11 = ($house['NO_POS_BC11']) ? $house['NO_POS_BC11'] : $hs->NO_POS_BC11;
            $TGL_BC11 = ($house['TGL_BC11']) 
                        ? Date::excelToDateTimeObject($house['TGL_BC11'])->format('Y-m-d')
                        : $hs->TGL_BC11;

            $hs->update([
              'NO_BC11' => $NO_BC11,
              'NO_POS_BC11' => $NO_POS_BC11,
              'TGL_BC11' => $TGL_BC11
            ]);
            
            $kd = 0;
            
            foreach($details->where('NO_HOUSE_BLAWB', $house['NO_HOUSE_BLAWB']) as $dtl)
            {              
              if(count($hs->details) > 0 && $hs->details->has($kd))
              {
                $detail = $hs->details[$kd];                  

                $detail->update([
                  'HS_CODE' => trim($dtl['HS_CODE']),
                  'NO_HOUSE_BLAWB' => trim($dtl['NO_HOUSE_BLAWB']),
                  'SERI_BRG' => $dtl['SERI_BRG'],
                  'DECLARED_NAME' => $dtl['DECLARED_NAME'],
                  'UR_BRG' => $dtl['UR_BRG'],
                  'KD_NEG_ASAL' => $dtl['KD_NEG_ASAL'],
                  'JML_KMS' => $dtl['JML_KMS'],
                  'JNS_KMS' => $dtl['JNS_KMS'],
                  'CIF' => $dtl['CIF'],
                  'KD_SAT_HRG' => $dtl['KD_SAT_HRG'],
                  'JML_SAT_HRG' => $dtl['JML_SAT_HRG'],
                  'FL_BEBAS' => $dtl['FL_BEBAS'],
                  'NO_SKEP' => $dtl['NO_SKEP'],
                  'TGL_SKEP' => ($dtl['TGL_SKEP'] != 0) ? Date::excelToDateTimeObject($dtl['TGL_SKEP'])->format('Y-m-d') : NULL,
                  'JNS_TARIF' => $dtl['JNS_TARIF'],
                  'KD_TARIF' => $dtl['KD_TARIF'],
                  'KD_SAT_TARIF' => $dtl['KD_SAT_TARIF'],
                  'JML_SAT' => $dtl['JML_SAT'],
                  'BM_TRF' => $dtl['BM_TRF(%)'],
                  'PPH_TRF' => $dtl['PPH_TRF(%)'],
                  'PPN_TRF' => $dtl['PPN_TRF(%)'],
                  'PPNBM_TRF' => $dtl['PPNBM_TRF(%)'],
                ]);
              } else {
                $detail = HouseDetail::updateOrCreate([
                  'HouseID' => $hs->id,
                  'NO_HOUSE_BLAWB' => trim($dtl['NO_HOUSE_BLAWB']),
                ],[                  
                  'HS_CODE' => trim($dtl['HS_CODE']),
                  'SERI_BRG' => $dtl['SERI_BRG'],
                  'DECLARED_NAME' => $dtl['DECLARED_NAME'],
                  'UR_BRG' => $dtl['UR_BRG'],
                  'KD_NEG_ASAL' => $dtl['KD_NEG_ASAL'],
                  'JML_KMS' => $dtl['JML_KMS'],
                  'JNS_KMS' => $dtl['JNS_KMS'],
                  'CIF' => $dtl['CIF'],
                  'KD_SAT_HRG' => $dtl['KD_SAT_HRG'],
                  'JML_SAT_HRG' => $dtl['JML_SAT_HRG'],
                  'FL_BEBAS' => $dtl['FL_BEBAS'],
                  'NO_SKEP' => $dtl['NO_SKEP'],
                  'TGL_SKEP' => ($dtl['TGL_SKEP'] != 0) ? Date::excelToDateTimeObject($dtl['TGL_SKEP'])->format('Y-m-d') : NULL,
                  'JNS_TARIF' => $dtl['JNS_TARIF'],
                  'KD_TARIF' => $dtl['KD_TARIF'],
                  'KD_SAT_TARIF' => $dtl['KD_SAT_TARIF'],
                  'JML_SAT' => $dtl['JML_SAT'],
                  'BM_TRF' => $dtl['BM_TRF(%)'],
                  'PPH_TRF' => $dtl['PPH_TRF(%)'],
                  'PPN_TRF' => $dtl['PPN_TRF(%)'],
                  'PPNBM_TRF' => $dtl['PPNBM_TRF(%)'],
                ]);
              }
              $kd++;
            }
          }

          $mNoOfPackages = 0;
          $HAWBCount = $master->houses()->count();
          $mGrossWeight = $master->houses()->sum('BRUTO');
          $mChargeableWeight = $master->houses()->sum('ChargeableWeight');

          $url = route('global.download').'?file=storage/file/manifest/'. $filenameWithExt;

          $master->update([
            'PUNumber' => (!$master->PUNumber && $NO_BC11) ? $NO_BC11 : $master->PUNumber, 
            'PUDate' => (!$master->PUDate && $TGL_BC11) ? $TGL_BC11 : $master->PUDate,
            'POSNumber' => (!$master->POSNumber && $NO_POS_BC11) ? $NO_POS_BC11 : $master->POSNumber,
            'mNoOfPackages' => $mNoOfPackages,
            'HAWBCount' => $HAWBCount,
            'mGrossWeight' => $mGrossWeight,
            'mChargeableWeight' => $mChargeableWeight,
            'UploadStatus' => '<a href="'.$url.'" target="_blank"><font color="green">Successful</font></a>'
          ]);

          $partial->update([
            'TOTAL_BRUTO' => $mGrossWeight
          ]);

          DB::commit();

          // if($branch->CB_Code != 'QCN')
          // {
            $barkir->updateSubPos($partial);
          // }         

          createLog('App\Models\Master', $master->id, 'Upload Excel');

          $mid = \Crypt::encrypt($master->id);

          return redirect()->route('manifest.consolidations.edit', ['consolidation' => $mid])
                           ->with('sukses', 'Import Excel Success');

        } catch (\Throwable $th) {
          DB::rollback();
          throw $th;
        }
      }
      
    }

    public function processData(Collection $header)
    {
        $company = activeCompany();

        DB::beginTransaction();

        try {
          foreach ($header as $k0 => $hdr) {
            foreach($hdr['master'] as $k1 => $master){
              $airName = $hdr['NM_SARANA_ANGKUT'];
              $airline = RefAirline::where('RM_AirlineName1', 'LIKE', "%$airName%")->first();
              $inputMaster = Master::updateOrCreate([
                'MAWBNumber' => $master['MAWBNumber'],
                'MAWBDate' => $master['MAWBDate']
              ],[
                'HAWBCount' => $master['HAWBCount'],
                'KPBC' => $hdr['KPBC'],
                'PUNumber' => $hdr['PUNumber'],
                'PUDate' => $hdr['PUDate'],
                'POSNumber' => $master['houses'][0]['NO_POS_BC11'] ?? null,
                'AirlineCode' => ($airline) ? $airline->RM_TwoCharacterCode : "",
                'NM_SARANA_ANGKUT' => $hdr['NM_SARANA_ANGKUT'],
                'FlightNo' => $hdr['FlightNo'],
                'Origin' => $hdr['Origin'],
                'Transit' => $hdr['Transit'],
                'Destination' => $hdr['Destination'],
                'ArrivalDate' => $hdr['ArrivalDate'],
                'ArrivalTime' => $hdr['ArrivalTime']
              ]);
              foreach ($master['houses'] as $k2 => $house) {
                $inputHouse = $inputMaster->houses()->updateOrCreate([
                                'NO_MASTER_BLAWB' => $house['NO_MASTER_BLAWB'],
                                'TGL_MASTER_BLAWB' => $house['TGL_MASTER_BLAWB'],
                                'NO_HOUSE_BLAWB' => $house['NO_HOUSE_BLAWB'],
                                'TGL_HOUSE_BLAWB' => $house['TGL_HOUSE_BLAWB']
                              ],[
                                'JNS_AJU' => $house['JNS_AJU'],
                                'NO_POS_BC11' => $house['NO_POS_BC11'],
                                'NO_SUBPOS_BC11' => $house['NO_SUBPOS_BC11'],
                                'NO_SUBSUBPOS_BC11' => $house['NO_SUBSUBPOS_BC11'],
                                'NO_BARANG' => $house['NO_BARANG'],
                                'NM_PENGANGKUT' => $house['NM_PENGANGKUT'],
                                'NO_ID_PENERIMA' => $house['NO_ID_PENERIMA'],
                                'NM_PENERIMA' => $house['NM_PENERIMA'],
                                'AL_PENERIMA' => $house['AL_PENERIMA'],
                                'NM_PENGIRIM' =>  $house['NM_PENGIRIM'],
                                'AL_PENGIRIM' => $house['AL_PENGIRIM'],
                                'KD_NEG_PENGIRIM' => $house['KD_NEG_PENGIRIM'],
                                'KD_NEGARA_ASAL' => $house['KD_NEG_PENGIRIM'],
                                'KD_PEL_MUAT' => $house['KD_PEL_MUAT'],
                                'KD_PEL_TRANSIT' => $house['KD_PEL_TRANSIT'],
                                'KD_PEL_BONGKAR' => $house['KD_PEL_BONGKAR'],
                                'KD_PEL_AKHIR' => $house['KD_PEL_AKHIR'],
                                'JML_BRG' => $house['JML_BRG'],
                                'JNS_KMS' => $house['JNS_KMS'],
                                'BRUTO' => $house['BRUTO'],
                                'VOLUME' => $house['VOLUME'],
                                'KD_KANTOR' => $hdr['KPBC'],
                                'NO_FLIGHT' => $hdr['FlightNo'],
                                'NO_BC11' => $hdr['PUNumber'],
                                'TGL_BC11' => $hdr['PUDate'],
                                'JNS_ID_PENERIMA' => 5,
                                'NO_ID_PEMBERITAHU' => $company->GC_TaxID,
                                'NM_PEMBERITAHU' => \Str::upper($company->GC_Name ?? "-"),
                                'AL_PEMBERITAHU' => \Str::upper($company->GC_Address1 ?? "-"),
                                'TGL_TIBA' => $hdr['ArrivalDate'],
                                'JAM_TIBA' => $hdr['ArrivalTime'],
                                'BRANCH' => 1,
                              ]);

                foreach ($house['hscodes'] as $k3 => $hscode) {
                  $inputHsCode = $inputHouse->details()->updateOrCreate([
                                      'NO_HOUSE_BLAWB' => $house['NO_HOUSE_BLAWB'],
                                      'SERI_BRG' => $hscode['SERI_BRG'],
                                      'HS_CODE' => $hscode['HS_CODE'],
                                      'UR_BRG' => $hscode['UR_BRG'],
                                  ],[
                                    'KD_NEG_ASAL' => $house['KD_NEG_PENGIRIM'],
                                    'JML_KMS' => $house['JML_BRG'],
                                    'JNS_KMS' => $house['JNS_KMS'],
                                  ]);
                }
              }
            }
          } 
          DB::commit();

          return redirect()->route('manifest.consolidations')
                           ->with('sukses', 'Import Data Success.');
        } catch (\Throwable $th) {
          DB::rollback();
          throw $th;
        }
         
    }
    
}
