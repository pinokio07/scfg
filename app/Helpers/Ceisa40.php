<?php
namespace App\Helpers;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use App\Jobs\ProsesKirimJob;
use App\Jobs\ProsesResponJob;
use App\Models\CeisaToken;
use App\Models\Master;
use App\Models\House;
use App\Models\BcLog;
use App\Models\PjtBatch;
use DB;

class Ceisa40
{
  private $link;

  public function __construct()
  {
    $this->link = 'https://apisdev-gw.beacukai.go.id';
  }

  public function getToken($vendor = 'A5')
  {
      $token = CeisaToken::where('valid_until', '>', now())
                         ->where('vendor', $vendor)
                         ->first();

      if(!$token)
      {
        $token = $this->refreshToken($vendor);
      }

      return $token;
  }

  public function refreshToken($vendor)
  {
    $uname = "anrishari";
    $pwd = "Justindo123";
    $url = $this->link.'/nle-oauth/v1/user/login';
    $data = [
                'username' => $uname,
                'password' => $pwd,
            ];

    $response =  \Http::withHeaders([
                          'Content-Type' => 'application/json'
                        ])
                        ->withBasicAuth($uname, $pwd)
                        ->post($url, $data)
                        ->throw()
                        ->json();
    \Log::info($response);
    if($response['status'] == 'success')
    {
      $item = $response['item'];
      $expired = now()->addSeconds(($item['expires_in'] - 500));

      DB::beginTransaction();

      try {
        $token = CeisaToken::create([
          'vendor' => $vendor,
          'valid_until' => $expired,
          'token_type' => $item['token_type'],
          'access_token' => $item['access_token'],
          'refresh_token' => $item['refresh_token'],
          'id_token' => $item['id_token'],
          'session_state' => $item['session_state']
        ]);
        DB::commit();

        return $token;

      } catch (\Throwable $th) {        
        DB::rollback();
        \Log::error($th);
        return [
          'status' => 'ERROR',
          'message' => $th->getMessage()
        ];
      }
      
    }

    return $response;
  }

  public function sendBarkir($id, $type)
  {
      if(!$id)
      {
        return [
          'status' => 'ERROR',
          'message' => 'No CN Found!'
        ];
      }

      $query = House::with(['details']);

      if($type == 'mawb')
      {
        $query->where('MasterID', $id);
      } else {
        $query->where('id', $id);
      }

      $dtCN = [];
      $data = $query->get();

      foreach($data as $h)
      {
        DB::beginTransaction();
        
        if(!$h->batch)
        {          
          try {
            $h->batch()->create([
              'LastTry' => now(),
              'Status' => 'PENDING'
            ]);
            DB::commit();
          } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            \Log::error($th);
            
          }
        }
        $failedCif = 0;
        $send = true;
        $batch = $h->batch;

        foreach($h->details as $detail)
          {      
            if($h->KD_VAL == 'USD') {
              $cif = $detail->CIF;
            } else if($h->KD_VAL == 'IDR') {
              $cif = $detail->CIF / $h->NDPBM;
            }
  
            $detail->update([
              'CIF_USD' => $cif
            ]);
  
            DB::commit();
  
            if($detail->CIF_USD == 0)
            {
             $failedCif++;
             $send = false;
            }
          }
  
          if($failedCif > 0) {
            $batch->update([
              'Status' => 'Dibatalkan',
              'Info' => 'Proses dibatalkan, karena terdapat '.$failedCif.' detil barang dengan CIF 0 untuk No Barang '.$h->NO_BARANG.', harap periksa kembali file template import Anda.'
            ]);
            $send = false;
          }

          if(!in_array($h->JNS_AJU, [1, 2])){
            $info = 'Jenis AJU dari C/N ' .$h->NO_BARANG. ' bukan untuk barkir, proses dibatalkan.';
            $send = false;
          }

          if($h->SKIP == 'Y')
          {
            $info = 'C/N dari ' .$h->NO_BARANG. ' ditandai untuk skip, proses dibatalkan.';
            $send = false;
          }

          if(!$h->NO_BC11 || !$h->TGL_BC11)
          {
            $info = 'Belum ada No/Tgl BC 1.1 untuk mawb '.$h->NO_BARANG.', proses dibatalkan.';
            $send = false;
          }

          if($h->HEstimatedPPN == NULL)
          {
            $batch->update([
              'Status' => 'Dibatalkan',
              'Info' => 'CN '.$h->NO_BARANG.' belum dilakukan perhitungan PDRI proses dibatalkan.'
            ]);
            $send = false;
          }

          if($send == false)
          {
            $batch->update([
              'Status' => 'Dibatalkan',
              'Info' => $info
            ]);
            \Log::error($info);
          } else {

            $barang = [];

            foreach($h->details as $d)
            {
              $barang[] = [
                "asuransiBarang" => $d->ASURANSI ?? "0.00",
                "cifBarang" => $d->CIF ?? "0.00",
                "flagBebas" => ($d->SKEP_BEBAS) ? 'Y' : '-',
                "fobBarang" => $d->FOB ?? "0.00",
                "freightBarang" => $h->FREIGHT ?? "0.00",
                "hargaSatuan" => $d->JML_SAT_HRG ?? "0.00",
                "hsCode" => $d->HS_CODE ?? "",
                "jenisKemasan" => $d->JNS_KMS ?? "",
                "jenisTarifBm" => "1",
                "jenisTarifBmad" => "1",
                "jenisTarifBmtp" => "1",
                "jumlahKemasan" => $d->JML_KMS ?? "0",
                "jumlahSatuan" =>  $d->JML_SAT_HRG ?? "1",
                "kodeNegaraAsal" => $h->KD_NEG_PENGIRIM ?? "",
                "kodeSatuan" => $d->KD_SAT_HRG ?? "",
                "nettoBarang" => number_format(($h->NETTO ?? 0), 2, '.', ''),
                "nilaiBm" => $d->BEstimatedBM ?? "0.00",
                "nilaiBmad" => "0.00",
                "nilaiBmtp" => $d->BEstimatedBMTP ?? "0.00",
                "nilaiCea" => "0.00",
                "nilaiCmea" => "0.00",
                "nilaiCtem" => "0.00",
                "nilaiPph" => $d->BEstimatedPPH ?? "0.00",
                "nilaiPpn" => $d->BEstimatedPPN ?? "0.00",
                "nilaiPpnbm" => "0.00",
                "nomorImei1" => $d->IMEI1 ?? "-",
                "nomorImei2" => $d->IMEI2 ?? "-",
                "nomorSkep" => ($d->NO_SKEP) ? $d->NO_SKEP : "-",
                "seriBarang" => $d->SERI_BRG ?? "1",
                "tarifBm" => $d->BM_TRF ?? "0.00",
                "tarifBmad" => "0.00",
                "tarifBmtp" => $d->BMTP_TRF ?? "0.00",
                "tarifCea" => "0.00",
                "tarifCmea" => "0.00",
                "tarifCtem" => "0.00",
                "tarifPph" => $d->PPH_TRF ?? "0.00",
                "tarifPpn" => $d->PPN_TRF ?? "0.00",
                "tarifPpnbm" => $d->PPNBM_TRF ?? "0.00",
                "tglSkep" => ($d->TGL_SKEP && $d->TGL_SKEP->year > 0) ? $d->TGL_SKEP : "-",
                "uraianBarang" => $d->UR_BRG ?? "",
                "kondisiBarang" => "1"
              ];
            }
    
            $dtCN[] = [
              "alamatPenerima" => $h->AL_PENERIMA,
              "alamatPengirim" => $h->AL_PENGIRIM,
              "asalData" => "H",
              "asuransiTotal" => $h->ASURANSI ?? "0.00",
              "barang" => $barang,
              "bruto" => number_format(( $h->BRUTO ?? 0), 2, '.', ''),
              "cifTotal" => $h->CIF ?? "0.00",
              "fobTotal" => $h->FOB ?? "0.00",
              "freightTotal" => $h->FREIGHT ?? "0.00",
              "jenisAju" => (string)$h->JNS_AJU ?? "1",
              "kategoriBarangKiriman" => (string)$h->KATEGORI_BARANG_KIRIMAN ?? "1",
              "kodeGudang" => $h->branch?->CB_WhCode ?? "",
              "kodeJenisAngkut" => "4",
              "kodeJenisIdentitasPenerima" => $h->JNS_ID_PENERIMA ?? "4",
              "kodeJenisIdentitasPengirim" => $h->JNS_ID_PENGIRIM ?? "4",
              "kodeJenisPibk" => $h->KD_JNS_PIBK ?? "2",
              "kodeKantor" => $h->KD_KANTOR ?? "050100",
              "kodeMarketplace" => "",
              "kodeNegaraAsal" => $h->KD_NEGARA_ASAL ?? "",
              "kodeNegaraPengirim" => $h->KD_NEG_PENGIRIM ?? "",
              "kodeNegaraTujuan" => \Str::substr(($h->KD_PEL_BONGKAR ?? "ID"), 0, 2),
              "kodePelBongkar" => $h->KD_PEL_BONGKAR ?? "IDCGK",
              "kodePelMuat" => $h->KD_PEL_MUAT ?? "IDCGK",
              "kodeValuta" => $h->KD_VAL ?? "IDR",
              "namaMarketplace" => "",
              "namaPenerima" => $h->NM_PENERIMA ?? "",
              "namaPengangkut" => $h->NM_PENGANGKUT ?? "",
              "namaPengirim" => $h->NM_PENGIRIM ?? "",
              "ndpbm" => $h->NDPBM ?? "0",
              "netto" => number_format(($h->NETTO ?? 0), 2, '.', ''),
              "nomorBarang" => $h->NO_BARANG ?? "",
              "nomorBC11" => $h->NO_BC11 ?? "",
              "nomorFlight" => $h->NO_FLIGHT ?? "",
              "nomorHouse" => $h->NO_HOUSE_BLAWB ?? "",
              "nomorIdentitasPenerima" => $h->NO_ID_PENERIMA ?? "000000000000000",
              "nomorIdentitasPengirim" => $h->NO_ID_PENGIRIM ?? "-",
              "nomorInvoice" => $h->NO_INVOICE ?? "",
              "nomorKantong" => "-",
              "nomorMaster" => $h->mawb_parse ?? "",
              "nomorTelpPenerima" => $h->TELP_PENERIMA ?? "",
              "npwpBilling" => $h->NPWP_BILLING ?? "000000000000000",
              "npwpPemberitahu" => $h->NO_ID_PEMBERITAHU ?? "000000000000000",
              "posBC11" => $h->NO_POS_BC11.$h->NO_SUBPOS_BC11.$h->NO_SUBPOS_BC11,
              "tanggalBC11" => ($h->TGL_BC11 && $h->TGL_BC11->year > 0) ? $h->TGL_BC11->format('Y-m-d') : "-",
              "tanggalHouse" => ($h->TGL_HOUSE_BLAWB && $h->TGL_HOUSE_BLAWB->year > 0) ? $h->TGL_HOUSE_BLAWB->format('Y-m-d') : "-",
              "tanggalInvoice" => $h->TGL_INVOICE ?? "",
              "tanggalMaster" => ($h->TGL_MASTER_BLAWB && $h->TGL_MASTER_BLAWB->year > 0) ? $h->TGL_MASTER_BLAWB->format('Y-m-d') : "-",
              "totalBm" => $h->HEstimatedBM ?? "0.00",
              "totalBmad" => "0.00",
              "totalBmtp" => $h->HEstimatedBMTP ?? "0.00",
              "totalCea" => "0.00",
              "totalCmea" => "0.00",
              "totalCtem" => "0.00",
              "totalPph" => $h->HEstimatedPPH ?? "0.00",
              "totalPpn" => $h->HEstimatedPPN ?? "0.00",
              "totalPpnbm" => "0.00",
              "totalTagihan" => number_format(($h->HEstimatedBM+$h->HEstimatedPPH+$h->HEstimatedPPN), 2, '.', ''),
            ];
          }
      }
      $dtCN = json_encode($dtCN);
      
      $url = '/kirim-cn-pibk-barkir-public/kirim-cn-pibk-barkir/cnpibk/kirim-data-cnpibk';

      return $this->processSend($dtCN, $url);
  }

  public function processSend($data, $url)
  {
      $token = $this->getToken();

      $response = \Http::withToken($token->access_token)
                        ->withBody($data, 'application/json')
                        ->post($this->link.$url)
                        ->throw(function (Response $response, RequestException $e) {
                          \Log::warning($response);
                          \Log::error($e);
                        })->json();
      \Log::info($response);
      $ds = ProsesKirimJob::dispatchAfterResponse($response);

      return [
        'status' => 'OK',
        'message' => 'Send CN Success.'
      ];
  }

  public function tarikRespon($id, $type)
  {
      $query = House::whereNotNull('noAju');
      $data = [];
      if($type == 'hawb')
      {
        $query->whereIn('id', $id);
      } else {
        $query->where('MasterID', $id);
      }

      $houses = $query->get();

      if(count($houses) == 0)
      {
        return [
          'status' => 'ERROR',
          'message' => 'House tidak ditemukan untuk Tarik Respon'
        ];
      }

      $token = $this->getToken();

      $url = '/respon-cn-pibk-barkir-public/tarik-respon-barkir/respon/tarik-respon';

      foreach($houses as $h)
      {
        $response = \Http::withToken($token->access_token)
                          ->get($this->link.$url, [
                            'nomorAju' => $h->noAju
                          ])
                          ->throw(function (Response $response, RequestException $e) {
                            \Log::warning($response);
                            \Log::error($e);
                          })->json();
        \Log::info($response);
        $data[] = $response;
      }
      
      $ds = ProsesResponJob::dispatchAfterResponse($data);

      return [
        'status' => 'OK',
        'message' => 'Tarik Respon Success.'
      ];
  }

  public function updateBC11($id, $type = 'hawb')
  {
      $query = House::query();

      if($type == 'hawb')
      {
        $query->where('id', $id);
      } else {
        $query->where('MasterID', $id);
      }
      $houses = $query->get();

      $token = $this->getToken();
      $url = '/barkir-public-service/public-barkir/bc11/update-bc11';

      if(count($houses) == 0)
      {
        return [
          'status' => 'ERROR',
          'message' => 'No House found for Update BC11'
        ];
      }

      $res = [];

      foreach($houses as $h)
      {
        $data = [
          "noBarang" => $h->NO_BARANG,
          "tglHouse" => ($h->TGL_HOUSE_BLAWB && $h->TGL_HOUSE_BLAWB->year > 0) ? $h->TGL_HOUSE_BLAWB->format('Y-m-d') : "-",
          "noBc11" => $h->NO_BC11 ?? "",
          "tglBc11" => ($h->TGL_BC11 && $h->TGL_BC11->year > 0) ? $h->TGL_BC11->format('Y-m-d') : "-",
          "posBC11" => $h->NO_POS_BC11.$h->NO_SUBPOS_BC11.$h->NO_SUBPOS_BC11,
          "kdGudang" => $h->branch?->CB_Warehouse,
        ];
  
        
        $data = json_encode($data);
  
        $response = \Http::withToken($token->access_token)
                          ->withBody($data, 'application/json')
                          ->post($this->link.$url)
                          ->throw(function (Response $response, RequestException $e) {
                            \Log::warning($response);
                            \Log::error($e);
                          })->json();
  
        $res[] = $response;
      }
      \Log::info($res);
      return [
        'status' => 'OK',
        'message' => $res
      ];
  }
}