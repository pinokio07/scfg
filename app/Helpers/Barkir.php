<?php
namespace App\Helpers;
use App\Models\Master;
use App\Models\MasterLegacy;
use App\Models\MasterPartial;
use App\Models\MasterBatch;
use App\Models\House;
use App\Models\HouseDetail;
use App\Models\GlbBranch;
use App\Models\IdModul;
use App\Models\BillingNotul;
use App\Models\BillingConsolidation;
use App\Models\BillingConsolidationLegacy;
use App\Models\BillingConsolidationDetail;
use App\Models\BillingConsolidationSppbmcp;
use App\Models\BillingConsolidationSppbmcpLegacy;
use App\Models\BillingConsolBatch;
use App\Models\BcLog;
use App\Models\RefExchangeRate;
use App\Models\RefExchangeRateLegacy;
use App\Models\SchedulerLog;
use App\Jobs\TarikResponJob;
use App\Jobs\KirimDataJob;
use Carbon\Carbon;
use DB, Config, Crypt;

class Barkir
{
  public function soap_options()
  {
    $location = 'https://esbbcext01.beacukai.go.id:9082/BarangKirimanOnline/WSBarangKiriman';
    $url = asset('WSBarangKirimanOnline.wsdl');
    $setting = [
                #'trace' => 1,
                #'exceptions' => 1,
                'stream_context' => stream_context_create(
                    ['ssl'=> 
                      [
                        'verify_peer' => false,
                        'verify_peer_name' => false, 
                        'allow_self_signed' => true 
                      ]
                    ]
                ),
                #'proxy_host' => 'idarray.in.tnt.com',
                #'proxy_port' => '8080',
              ];

    return compact(['location', 'url', 'setting']);
  }
  public function tibanrespon()
  {
    return ['501','503','901','902','201','203','ERR','303'];
  }
  public function fetch401($id, $nbs = null)
  {
    if(!$nbs)
    {
      $nbs = BillingConsolidationSppbmcp::where('BillingID', $id)
                                        ->pluck('NO_BARANG')
                                        ->toArray();
    }    
  
    $bclogs = BcLog::whereIn('NO_BARANG', $nbs)
                    ->where('BC_CODE', '401')
                    ->with(['house.details'])
                    ->get();

    DB::beginTransaction();

    try {
      foreach($bclogs as $bc)
      {
        $xml =  simplexml_load_string(base64_decode($bc->XML));
    
        foreach ($xml->HEADER as $sppb) {
          $BM=0;
          $PPN=0;
          $PPH=0;
          $pindex=0;
          $BMHeader=0;
          $PPNHeader=0;
          $PPHHeader=0;
          $BMTPHeader=0;
          $DendaHeader=0;
          $BMADHeader=0;
          if(isset($sppb->HEADER_PUNGUTAN_TOTAL)) {
            foreach ($sppb->HEADER_PUNGUTAN_TOTAL->PUNGUTAN_TOTAL as $PUNGUT) {
              // dd($PUNGUT);
              if (strpos($PUNGUT->KD_PUNGUTAN,'412111') !== false) {//bea masuk
                $BMHeader = $PUNGUT->NILAI;              
              } 
              if (strpos($PUNGUT->KD_PUNGUTAN,'411123') !== false ) { //PPH
                $PPHHeader =$PUNGUT->NILAI;              
              }          
              if (strpos($PUNGUT->KD_PUNGUTAN,'411212') !== false ) { //PPN
                $PPNHeader = $PUNGUT->NILAI;              
              }
              if (strpos($PUNGUT->KD_PUNGUTAN,'412123') !== false) { //BMTP
                $BMTPHeader = $PUNGUT->NILAI;              
              }
              if (strpos($PUNGUT->KD_PUNGUTAN,'412113') !== false) { //DENDA
                $DendaHeader = $PUNGUT->NILAI;              
              }
              if (strpos($PUNGUT->KD_PUNGUTAN,'412121') !== false) { //BMAD
                $BMADHeader = $PUNGUT->NILAI;
              }
            }

            $bc->house()->update([
              'BillingFinal' => ($BMHeader+$PPHHeader+$PPNHeader+$BMTPHeader+$DendaHeader),
              'BillFetchStatus' => 1,
              'HActualBMAD' => $BMADHeader,
              'HActualBMTP' => $BMTPHeader,
              'HActualBM' => $BMHeader,
              'HActualPPN' => $PPNHeader,
              'HActualPPH' => $PPHHeader,
              'HActualDenda' => $DendaHeader
            ]);

            DB::commit();
          }

          foreach ($sppb->DETIL_PUNGUTAN->PUNGUTAN as $PUNGUT) {
            $Pungut[$pindex]["BActualBM"] = 0;
            $Pungut[$pindex]["BActualPPH"] = 0;
            $Pungut[$pindex]["BActualPPN"] = 0;

            if (strpos($PUNGUT->KD_PUNGUTAN,'412111') !== false 
                || strpos($PUNGUT->KD_PUNGUTAN,'seqKdPungutan=1') !== false) {//bea masuk
              $BM += $PUNGUT->NILAI;
              $Pungut[$pindex]["BActualBM"] = $PUNGUT->NILAI;
            } 
            if (strpos($PUNGUT->KD_PUNGUTAN,'411123') !== false 
                || strpos($PUNGUT->KD_PUNGUTAN,'seqKdPungutan=2') !== false) { //PPH
               $PPH += $PUNGUT->NILAI;
               $Pungut[$pindex]["BActualPPH"] = $PUNGUT->NILAI;
            }

            if (strpos($PUNGUT->KD_PUNGUTAN,'411212') !== false 
                || strpos($PUNGUT->KD_PUNGUTAN,'seqKdPungutan=3') !== false) { //PPN
               $PPN += $PUNGUT->NILAI;
               $Pungut[$pindex]["BActualPPN"] = $PUNGUT->NILAI;
            }
            $pindex += 1;
          }

          $tindex=0;
          foreach ($sppb->DETIL_PENETAPAN->PENETAPAN as $TETAP) {
            $Pungut[$tindex]["NILAI_PABEAN"] = $TETAP->NILAI_PABEAN;

            $bc->house->details()->where('SERI_BRG', $TETAP->SERI_BRG)
                               ->update([
                                'NILAI_PABEAN' => $Pungut[$tindex]["NILAI_PABEAN"],
                                'BActualBM' => $Pungut[$tindex]["BActualBM"],
                                'BActualPPH' => $Pungut[$tindex]["BActualPPH"],
                                'BActualPPN' => $Pungut[$tindex]["BActualPPN"],
                               ]);

            $tindex += 1;
          }
          DB::commit();
          
          if(isset($sppb->NO_SPPBMCP)){
            $bc->house()->update([
              'SPPBNumber' => $sppb->NO_SPPBMCP,
              'SPPBDate' => $sppb->TGL_SPPBMCP
            ]);

            DB::commit();
          }
        }
      }

      $billing = BillingConsolidation::find($id);

      if($billing)
      {
        $billing->update([
          'BillFetchStatus' => 1
        ]);

        DB::commit();
      }

      return [
        'status' => 'OK'
      ];

    } catch (\Throwable $th) {
      DB::rollback();

      return [
        'status' => 'ERROR',
        'message' => $th->getMessage()
      ];
      //throw $th;
    }    
  }
  public function fetch303($id, $nbs = null)
  {
    if(!$nbs)
    {
      $nbs = BillingNotul::where('id', $id)
                          ->pluck('NO_BARANG')
                          ->toArray();
    }    
  
    $bclogs = BcLog::whereIn('NO_BARANG', $nbs)
                    ->where('BC_CODE', '303')
                    ->with(['house'])
                    ->get();
    if(count($bclogs) == 0)
    {
      \Log::warning('Process 303 Failed, no respon found');

      return [
        'status' => 'ERROR',
        'message' => 'No Respon Found'
      ];
    }

    \Log::notice('Proces 303 for '.count($bclogs).' respon.');

    DB::beginTransaction();
    
    try {
      foreach($bclogs as $bc)
      {
        $BillingXML = simplexml_load_string(base64_decode($bc->XML));
  
        foreach ($BillingXML->HEADER as $Hdr) {
          $billing = BillingNotul::updateOrCreate([
            'KODE_BILLING' => $Hdr->KODE_BILLING
          ],[
            'NO_BARANG' => $bc->house?->NO_BARANG,
            'TGL_HOUSE_BLAWB' => $bc->house?->TGL_HOUSE_BLAWB,
            'KD_RESPON' => 303,
            'WK_REKAM' => $bc->BC_DATE,
            'TGL_BILLING' => (Carbon::parse($Hdr->TGL_BILLING)->year > 1) ? Carbon::parse($Hdr->TGL_BILLING)->format('Y-m-d') : NULL,
            'TGL_JT_TEMPO' => (Carbon::parse($Hdr->TGL_JT_TEMPO)->year > 1) ? Carbon::parse($Hdr->TGL_JT_TEMPO)->format('Y-m-d') : NULL,
            'KD_DOK_BILLING' => $Hdr->KD_DOK_BILLING,
            'TOTAL_BILLING' => $Hdr->TOTAL_BILLING,
          ]);

          if(!$bc->house->BillingFinal)
          {
            $BMHeader = 0;
            $PPHHeader = 0;
            $PPNHeader = 0;
            $BMTPHeader = 0;
            $DendaHeader = 0;
            $BMADHeader = 0;
            
            foreach ($Hdr->DETIL_PUNGUTAN->PUNGUTAN as $PUNGUT) {
              // dd($PUNGUT);
              if (strpos($PUNGUT->KD_PUNGUTAN,'412111') !== false) {//bea masuk
                $BMHeader = $PUNGUT->NILAI;              
              } 
              if (strpos($PUNGUT->KD_PUNGUTAN,'411123') !== false ) { //PPH
                $PPHHeader = $PUNGUT->NILAI;              
              }          
              if (strpos($PUNGUT->KD_PUNGUTAN,'411212') !== false ) { //PPN
                $PPNHeader = $PUNGUT->NILAI;              
              }
              if (strpos($PUNGUT->KD_PUNGUTAN,'412123') !== false) { //BMTP
                $BMTPHeader = $PUNGUT->NILAI;              
              }
              if (strpos($PUNGUT->KD_PUNGUTAN,'412113') !== false) { //DENDA
                $DendaHeader = $PUNGUT->NILAI;              
              }
              if (strpos($PUNGUT->KD_PUNGUTAN,'412121') !== false) { //BMAD
                $BMADHeader = $PUNGUT->NILAI;
              }
    
              $billing->details()->updateOrCreate([
                'KODE_BILLING' => $Hdr->KODE_BILLING,
                'KD_PUNGUTAN' => strval($PUNGUT->KD_PUNGUTAN),            
              ],[
                'NILAI' => $PUNGUT->NILAI
              ]);
            }
    
            $bc->house()->update([
              'BillingFinal' => ($BMHeader+$PPHHeader+$PPNHeader+$BMTPHeader+$DendaHeader),
              'BillFetchStatus' => 1,
              'HActualBMAD' => $BMADHeader,
              'HActualBMTP' => $BMTPHeader,
              'HActualBM' => $BMHeader,
              'HActualPPN' => $PPNHeader,
              'HActualPPH' => $PPHHeader,
              'HActualDenda' => $DendaHeader
            ]);
          }
  
          
        }
        DB::commit();

        \Log::info('Process 303 success for '.$bc->NO_BARANG.'.');
      }

      return [
        'status' => 'OK',
        'message' => 'Process 303 Success for '.count($bclogs).' respon'
      ];
    } catch(\Throwable $th) {
      DB::rollback();

      \Log::error('Update 303 Failed: <br>'.$th);

      return [
        'status' => 'ERROR',
        'message' => $th->getMessage()
      ];
    }
  }
  public function kirimdata($id, $direct = false, $bps = 0)
  {
    if(is_array($id))
    {
      $ids = $id;
    } else {
      $ids = explode(',', \Str::replace(['[',']'], '', $id));
    }    

    $houses = House::with(['details', 'batch', 'branch'])->findOrFail($ids);

    if(!$houses)
    {
      return [
        'status' => 'ERROR',
        'message' => 'No Houses Found'
      ];
    }
    $xml = [];
    $jobs = [];

    DB::beginTransaction();

    if(\Auth::check())
    {
      createLog('App\Models\Master', $houses->first()->MasterID, 'Kirim Data '.$houses->first()->mawb_parse);
    }

    foreach($houses as $house)
    {
      if(!$house->batch)
      {
        try {
          $house->batch()->create([
            'LastTry' => now(),
            'Status' => 'PENDING'
          ]);
          DB::commit();
  
          $house->refresh();
          
        } catch (\Throwable $th) {
          DB::rollback();
          // return [
          //   'status' => 'ERROR',
          //   'message' => $th->getMessage()
          // ];
          \Log::error($th);
        }      
      }

      $batch = $house->batch;
      $send = true;
  
      $skipBc = ["901","902","903","912","915","906","908","ERR"];
  
      if($house->KD_VAL) {
        $failedCif = 0;
        try {
          foreach($house->details as $detail)
          {      
            if($house->KD_VAL == 'USD') {
              $cif = $detail->CIF;
            } else if($house->KD_VAL == 'IDR') {
              $cif = $detail->CIF / $house->NDPBM;
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
              'Info' => 'Proses dibatalkan, karena terdapat '.$failedCif.' detil barang dengan CIF 0 untuk No Barang '.$house->NO_BARANG.', harap periksa kembali file template import Anda.'
            ]);
            $send = false;
          }

          if(!in_array($house->JNS_AJU, [1, 2])){
            $info = 'Jenis AJU dari C/N ' .$house->NO_BARANG. ' bukan untuk barkir, proses dibatalkan.';
            $send = false;
          }

          if($house->SKIP == 'Y')
          {
            $info = 'C/N dari ' .$house->NO_BARANG. ' ditandai untuk skip, proses dibatalkan.';
            $send = false;
          }

          if(!$house->NO_BC11 || !$house->TGL_BC11)
          {
            $info = 'Belum ada No/Tgl BC 1.1 untuk mawb '.$house->NO_BARANG.', proses dibatalkan.';
            $send = false;
          }

          if($bps == 0)
          {
            if($house->BC_201 != NULL)
            {
              $info = 'C/N dari ' .$house->NO_BARANG. ' sudah terkirim, proses dibatalkan.';
              $send = false;
            }
  
            if($house->BC_CODE != NULL && !in_array($house->BC_CODE, $skipBc)) 
            {
              $info = 'C/N dari ' .$house->NO_BARANG. ' sudah terkirim, proses dibatalkan.';
              $send = false;
            }        
          }
  
          if($house->HEstimatedPPN == NULL)
          {
            $batch->update([
              'Status' => 'Dibatalkan',
              'Info' => 'CN '.$house->NO_BARANG.' belum dilakukan perhitungan PDRI proses dibatalkan.'
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
          }

          DB::commit();

          if($send)
          {
            if($direct == true) {
              $this->kirimsatuan($house);
            } else {
              $jobs[] = new KirimDataJob($house);
            }            
          }
          
        } catch (\Throwable $th) {
          DB::rollback();
          
          \Log::error($th->getMessage());
        }
        
      }
    }

    if(!empty($jobs) && $direct == false){
      \Bus::chain($jobs)->dispatch();
    }

    return [
      'status' => 'OK',
      'xml' => $xml
    ];
  }

  public function kirimsatuan(House $house)
  {
    $branch = $house->branch;
  
    $TODAYRATE = $this->todayrate();

    $KD_GUDANG = $branch->CB_WhCode;
    $NO_MASTER_BLAWB= str_replace(' ','',$house->NO_MASTER_BLAWB);
    $TGL_MASTER_BLAWB = bc_date($house->TGL_MASTER_BLAWB);
    $TGL_INVOICE = bc_date($house->TGL_INVOICE);
    $TGL_BC11 = bc_date($house->TGL_BC11);
    $NO_SUBPOS_BC11 = str_pad($house->NO_SUBPOS_BC11, 4, '0', STR_PAD_LEFT);
    $TGL_HOUSE_BLAWB = bc_date($house->TGL_HOUSE_BLAWB);
    $TGL_IZIN_PEMBERITAHU = bc_date($house->TGL_IZIN_PEMBERITAHU);
    $NDPBM = $TODAYRATE;
    $AL_PENGIRIM = (string)$house->AL_PENGIRIM;
    $NO_IZIN_PJT = $house->pjt?->NO_IZIN_PJT ?? $house->NO_IZIN_PEMBERITAHU;
    $TGL_IZIN_PJT = $house->pjt?->TGL_IZIN_PJT ?? $house->TGL_IZIN_PEMBERITAHU;

    if($house->FOB > 0 && $house->FREIGHT > 0) {
        $FOB_USD = $house->FOB;
        $ASURANSI = $house->ASURANSI;
        $FREIGHT = $house->FREIGHT;
        $CIFHeader = $house->CIF;
    } else {
        $FOB_USD = $house->sum_cif_dtl;
        $ASURANSI =0 ;
        $FREIGHT = 0; 
        $CIFHeader = $FOB_USD;
    }  

    $KATEGORI_BARANG_KIRIMAN = ($house->KATEGORI_BARANG_KIRIMAN > 0
                              ? $house->KATEGORI_BARANG_KIRIMAN
                              : 1);

    $AL_PENERIMA = (string)substr($house->AL_PENERIMA,0,200);                       
    $NPWP_BILLING = $house->JNS_ID_PENERIMA == 5?$house->NO_ID_PENERIMA:'000000000000000';
    $NAMA_BILLING = $house->JNS_ID_PENERIMA == 5?$house->NM_PENERIMA:$house->NM_PEMBERITAHU;

    $KD_GUDANG = ($house->BCF15_Status==="Yes"?"TPP":$KD_GUDANG);

    if($house->BCF15_Status==="Yes"){
        $NPWP_BILLING=$house->NO_ID_PEMBERITAHU;
        $NAMA_BILLING = $house->NM_PEMBERITAHU;
    }

    $DATA = "<CN_PIBK>
              <HEADER>
                  <JNS_AJU>{$house->JNS_AJU}</JNS_AJU>
                  <KD_JNS_PIBK>{$house->KD_JNS_PIBK}</KD_JNS_PIBK>
                  <KATEGORI_BARANG_KIRIMAN>{$KATEGORI_BARANG_KIRIMAN}</KATEGORI_BARANG_KIRIMAN>
                  <NO_BARANG>{$house->NO_HOUSE_BLAWB}</NO_BARANG>
                  <KD_KANTOR>{$house->KD_KANTOR}</KD_KANTOR>
                  <KD_JNS_ANGKUT>{$house->KD_JNS_ANGKUT}</KD_JNS_ANGKUT>
                  <NM_PENGANGKUT>{$house->NM_PENGANGKUT}</NM_PENGANGKUT>
                  <NO_FLIGHT>{$house->NO_FLIGHT}</NO_FLIGHT>
                  <KD_PEL_MUAT>{$house->KD_PEL_MUAT}</KD_PEL_MUAT>
                  <KD_PEL_BONGKAR>{$house->KD_PEL_BONGKAR}</KD_PEL_BONGKAR>
                  <KD_GUDANG>".$KD_GUDANG."</KD_GUDANG>
                  <NO_INVOICE>{$house->NO_INVOICE}</NO_INVOICE>
                  <TGL_INVOICE>{$TGL_INVOICE}</TGL_INVOICE>
                  <KD_NEGARA_ASAL>{$house->KD_NEGARA_ASAL}</KD_NEGARA_ASAL>
                  <JML_BRG>{$house->JML_BRG}</JML_BRG>
                  <NO_BC11>{$house->NO_BC11}</NO_BC11>
                  <TGL_BC11>{$TGL_BC11}</TGL_BC11>
                  <NO_POS_BC11>{$house->NO_POS_BC11}</NO_POS_BC11>
                  <NO_SUBPOS_BC11>{$NO_SUBPOS_BC11}</NO_SUBPOS_BC11>
                  <NO_SUBSUBPOS_BC11>{$house->NO_SUBSUBPOS_BC11}</NO_SUBSUBPOS_BC11>
                  <NO_MASTER_BLAWB>{$NO_MASTER_BLAWB}</NO_MASTER_BLAWB>
                  <TGL_MASTER_BLAWB>{$TGL_MASTER_BLAWB}</TGL_MASTER_BLAWB>
                  <NO_HOUSE_BLAWB>{$house->NO_HOUSE_BLAWB}</NO_HOUSE_BLAWB>
                  <TGL_HOUSE_BLAWB>{$TGL_HOUSE_BLAWB}</TGL_HOUSE_BLAWB>
                  <KD_NEG_PENGIRIM>{$house->KD_NEG_PENGIRIM}</KD_NEG_PENGIRIM>
                  <NM_PENGIRIM>{$house->NM_PENGIRIM}</NM_PENGIRIM>
                  <AL_PENGIRIM>{$AL_PENGIRIM}</AL_PENGIRIM>
                  <JNS_ID_PENGIRIM>5</JNS_ID_PENGIRIM>
                  <NO_ID_PENGIRIM>000000000000000</NO_ID_PENGIRIM>
                  <NO_ID_PPMSE></NO_ID_PPMSE>
                  <NM_PPMSE></NM_PPMSE>

                  <JNS_ID_PENERIMA>{$house->JNS_ID_PENERIMA}</JNS_ID_PENERIMA>
                  <NO_ID_PENERIMA>{$house->NO_ID_PENERIMA}</NO_ID_PENERIMA>
                  <NM_PENERIMA>{$house->NM_PENERIMA}</NM_PENERIMA>
                  <AL_PENERIMA>{$AL_PENERIMA}</AL_PENERIMA>
                  <TELP_PENERIMA>{$house->TELP_PENERIMA}</TELP_PENERIMA>
                  <JNS_ID_PEMBERITAHU>5</JNS_ID_PEMBERITAHU>
                  <NO_ID_PEMBERITAHU>{$house->NO_ID_PEMBERITAHU}</NO_ID_PEMBERITAHU>
                  <NM_PEMBERITAHU>{$house->NM_PEMBERITAHU}</NM_PEMBERITAHU>
                  <AL_PEMBERITAHU>{$house->AL_PEMBERITAHU}</AL_PEMBERITAHU>
                  <NO_IZIN_PEMBERITAHU>".$NO_IZIN_PJT."</NO_IZIN_PEMBERITAHU>
                  <TGL_IZIN_PEMBERITAHU>".bc_date($TGL_IZIN_PJT)."</TGL_IZIN_PEMBERITAHU>
                  <KD_VAL>USD</KD_VAL>
                  <NDPBM>{$house->NDPBM}</NDPBM>
                  <FOB>".number_format($FOB_USD,2,'.','')."</FOB>
                  <ASURANSI>".$ASURANSI."</ASURANSI>
                  <FREIGHT>".$FREIGHT."</FREIGHT>
                  <CIF>".number_format($CIFHeader,2,'.','')."</CIF>
                  <NETTO>".number_format($house->BRUTO,2,'.','')."</NETTO>
                  <BRUTO>".number_format($house->BRUTO,2,'.','')."</BRUTO>
                  <TOT_DIBAYAR>".intval($house->HEstimatedBM+$house->HEstimatedPPH+$house->HEstimatedPPN+$house->HEstimatedBMTP)."</TOT_DIBAYAR>
                    <NPWP_BILLING>{$NPWP_BILLING}</NPWP_BILLING>
                  <NAMA_BILLING>{$NAMA_BILLING}</NAMA_BILLING>
                  ";

                  $DATA .= "<DETIL>";

                  $TOTAL_BM = 0;
                  $TOTAL_BMTP = 0;
                  $TOTAL_PPN = 0;
                  $TOTAL_PPH = 0;
                  $TOTAL_PPNBM = 0;

                  foreach($house->details as $keyd => $detail)
                  {
                    $SERI = $keyd + 1;

                    $BM = $detail->BM_TRF * $detail->CIF_USD ;
                    $BMTP = $detail->BMTP_TRF*$detail->JML_SAT_HRG ;
                    $PPN = $detail->PPN_TRF * $detail->CIF_USD;
                    $PPH = $detail->PPH_TRF * $detail->CIF_USD;
                    $PPNBM = $detail->PPNBM_TRF * $detail->CIF_USD;

                    $TOTAL_BM += $BM;
                    $TOTAL_PPN += $PPN;
                    $TOTAL_PPH += $PPH;
                    $TOTAL_PPNBM += $PPNBM;
                    $TOTAL_BMTP += $BMTP;

                    $TGL_SKEP = '';

                    $tglSkep = \Carbon\Carbon::parse($detail->TGL_SKEP);
                    if($tglSkep->year > 1)
                    {
                      $TGL_SKEP = bc_date($detail->TGL_SKEP);
                    }
                    
                    $NO_SKEP = ($detail->NO_SKEP);
                    $FL_BEBAS = ($detail->FL_BEBAS);

                    $DATA .= "
                        <BARANG>
                            <SERI_BRG>".$SERI."</SERI_BRG>
                            <HS_CODE>".$detail->HS_CODE."</HS_CODE>
                            <UR_BRG>".$detail->UR_BRG."</UR_BRG>
                            <IMEI1>".$detail->IMEI1."</IMEI1>
                            <IMEI2>".$detail->IMEI2."</IMEI2>
                            <KD_NEG_ASAL>".$house->KD_NEGARA_ASAL."</KD_NEG_ASAL>
                            <JML_KMS>".$house->JML_BRG."</JML_KMS>
                            <JNS_KMS>".$house->JNS_KMS."</JNS_KMS>
                            <CIF>".(float)$detail->CIF_USD."</CIF>
                            <KD_SAT_HRG>".$detail->KD_SAT_HRG."</KD_SAT_HRG>
                            <JML_SAT_HRG>".$detail->JML_SAT_HRG."</JML_SAT_HRG>
                            <FL_BEBAS>".$FL_BEBAS."</FL_BEBAS>
                            <NO_SKEP>".$NO_SKEP."</NO_SKEP>
                            <TGL_SKEP>".$TGL_SKEP."</TGL_SKEP>
                            <DETIL_PUNGUTAN>
                                <KD_PUNGUTAN>1</KD_PUNGUTAN>
                                <NILAI>".number_format(($detail->BEstimatedBM ?? 0),2,'.','')."</NILAI>
                                <JNS_TARIF>1</JNS_TARIF>
                                <KD_TARIF>1</KD_TARIF>
                                <KD_SAT_TARIF>1</KD_SAT_TARIF>
                                <JML_SAT></JML_SAT>
                                <TARIF>".number_format(($detail->BM_TRF ?? 0),2,'.','')."</TARIF>
                            </DETIL_PUNGUTAN>
                            <DETIL_PUNGUTAN>
                                <KD_PUNGUTAN>2</KD_PUNGUTAN>
                                <NILAI>".number_format(($detail->BEstimatedPPH ?? 0),2,'.','')."</NILAI>
                                <JNS_TARIF>1</JNS_TARIF>
                                <KD_TARIF>1</KD_TARIF>
                                <KD_SAT_TARIF>1</KD_SAT_TARIF>
                                <JML_SAT></JML_SAT>
                                <TARIF>".number_format(($detail->PPH_TRF ?? 0),2,'.','')."</TARIF>
                            </DETIL_PUNGUTAN>
                            <DETIL_PUNGUTAN>
                                <KD_PUNGUTAN>3</KD_PUNGUTAN>
                                <NILAI>".number_format(($detail->BEstimatedPPN ?? 0),2,'.','')."</NILAI>
                                <JNS_TARIF>1</JNS_TARIF>
                                <KD_TARIF>1</KD_TARIF>
                                <KD_SAT_TARIF>1</KD_SAT_TARIF>
                                <JML_SAT></JML_SAT>
                                <TARIF>".number_format(($detail->PPN_TRF ?? 0),2,'.','')."</TARIF>
                            </DETIL_PUNGUTAN>
                            <DETIL_PUNGUTAN>
                                <KD_PUNGUTAN>4</KD_PUNGUTAN>
                                <NILAI>".number_format(($PPNBM ?? 0),2,'.','')."</NILAI>
                                <JNS_TARIF>1</JNS_TARIF>
                                <KD_TARIF>1</KD_TARIF>
                                <KD_SAT_TARIF>1</KD_SAT_TARIF>
                                <JML_SAT></JML_SAT>
                                <TARIF>".number_format(($detail->PPNBM_TRF ?? 0),2,'.','')."</TARIF>
                            </DETIL_PUNGUTAN>
                            <DETIL_PUNGUTAN>
                                <KD_PUNGUTAN>9</KD_PUNGUTAN>
                                <NILAI>".number_format(($BMTP ?? 0),2,'.','')."</NILAI>
                                <JNS_TARIF>1</JNS_TARIF>
                                <KD_TARIF>2</KD_TARIF>
                                <KD_SAT_TARIF>".$detail->KD_SAT_HRG."</KD_SAT_TARIF>
                                <JML_SAT>".$detail->JML_SAT_HRG."</JML_SAT>
                                <TARIF>".number_format(($detail->BMTP_TRF ?? 0),2,'.','')."</TARIF>
                            </DETIL_PUNGUTAN>
                        </BARANG>
                        ";
                  }
                  $DATA .= "</DETIL>";
                  
                $PUNGUTAN = "<HEADER_PUNGUTAN>
                      <PUNGUTAN_TOTAL>
                          <KD_PUNGUTAN>1</KD_PUNGUTAN>
                          <NILAI>".intval($house->HEstimatedBM)."</NILAI>
                      </PUNGUTAN_TOTAL>
                      <PUNGUTAN_TOTAL>
                          <KD_PUNGUTAN>2</KD_PUNGUTAN>
                          <NILAI>".intval($house->HEstimatedPPH)."</NILAI>
                      </PUNGUTAN_TOTAL>
                      <PUNGUTAN_TOTAL>
                          <KD_PUNGUTAN>3</KD_PUNGUTAN>
                          <NILAI>".intval($house->HEstimatedPPN)."</NILAI>
                      </PUNGUTAN_TOTAL>
                      <PUNGUTAN_TOTAL>
                          <KD_PUNGUTAN>4</KD_PUNGUTAN>
                          <NILAI>".intval($TOTAL_PPNBM)."</NILAI>
                      </PUNGUTAN_TOTAL>
                        <PUNGUTAN_TOTAL>
                          <KD_PUNGUTAN>9</KD_PUNGUTAN>
                          <NILAI>".intval($house->HEstimatedBMTP)."</NILAI>
                      </PUNGUTAN_TOTAL>
                  </HEADER_PUNGUTAN>";

                    $DATA .= $PUNGUTAN."
              </HEADER>
          </CN_PIBK>
          ";

    $DATA = str_replace('&','',$DATA);

    $fileName = $house->NO_BARANG . '-'.date('Ymd'). '.xml';

    DB::beginTransaction();

    try {
      $batch = $house->batch;

      if(subDomain() == 'uat')
      {
        \Storage::disk('public')->put('/file/xml/'.$fileName, $DATA);  

        $batch->update([
          'Status' => 'Sending',
          'Info' => 'Created file '.$fileName,
          'xml' => $fileName
        ]);

        DB::commit();

        return false;
      }
      
      \Log::info('Create xml '.$fileName);
  
      try {

        $batch->update([
          'request' => str_replace('&','',$DATA)
        ]);

        DB::commit();

        $soapOptions = $this->soap_options();
        $SOAP_LOCATION = $soapOptions['location'];
        $SOAP_URL = $soapOptions['url'];
        $SOAP_SETTING = $soapOptions['setting'];
        $SOAP_USER = $house->pjt?->User_BarangKiriman;
        $SOAP_SIGN = $house->pjt?->Token_BarangKiriman;
        $SOAP_PASS = $house->pjt?->Password_BarangKiriman;
        $METHOD = 'kirimData';
  
        $webServiceClient = new \SoapClient($SOAP_URL, $SOAP_SETTING);
        $requestData = array(
            "data" => str_replace('&','',$DATA),
            "id" => $SOAP_USER.'^$'.$SOAP_PASS,
            "sign" => $SOAP_SIGN
        );
  
        $response = $webServiceClient->__soapCall($METHOD, array($METHOD => $requestData), array('location' => $SOAP_LOCATION));
  
        $RESP = isset($response->return) ? $response->return : '';
  
        // $cont = '';
        // $log_folder = date('Y-m-d');
        // $log_fn = $house->NO_BARANG .'-'.date("d-M-Y").".txt";
  
        // if(!empty($RESP))
        // {                
        //     $cont .= "\n " . date('H:i')."  ".$house->NO_BARANG. $house->TGL_HOUSE_BLAWB.str_replace(array("\r\n", "\n", "\r"),"",$RESP);
        //     \Storage::disk('public')->put('/logs/kirim/'.$log_folder.'/'.$log_fn, $cont);

        //     \Log::info('Get Kirim Data Response xml '.$log_folder.'/'.$log_fn);            
        // }

        if($RESP == 'Server Error'){
          $batch->update([
            'Status' => 'Server Error'
          ]);

          \Log::info('Server Error, info : '.$RESP); 
          
          DB::commit();
        } else {
          $E_RESP = base64_encode($RESP);

          \Log::info($RESP);
  
          $batch->update([
            'response' => $RESP
          ]);
  
          DB::commit();  
          
          $R = simplexml_load_string($RESP);
          $KD_RESPON = isset($R->HEADER->KD_RESPON) ? $R->HEADER->KD_RESPON : '';
          $KET_RESPON = isset($R->HEADER->KET_RESPON) ? $R->HEADER->KET_RESPON : '';
          $WK_RESPON = isset($R->HEADER->WK_RESPON) ? date('Y-m-d H:i:s',strtotime($R->HEADER->WK_RESPON)) : '';
          $WK_RESPON = isset($R->HEADER->WK_REKAM) ? date('Y-m-d H:i:s',strtotime($R->HEADER->WK_REKAM)) : $WK_RESPON;
          if( ! isset($R->HEADER)){
              $KD_RESPON = isset($R->KD_RESPON) ? $R->KD_RESPON : '';
              $KET_RESPON = isset($R->KET_RESPON) ? $R->KET_RESPON : '';
              $WK_RESPON = isset($R->WK_RESPON) ? date('Y-m-d H:i:s',strtotime($R->WK_RESPON)) : '';
              $WK_RESPON = isset($R->WK_REKAM) ? date('Y-m-d H:i:s',strtotime($R->WK_REKAM)) : $WK_RESPON;
          }
    
          $BCLOGSData = [
            'HouseID'   => $house->id,
            'NO_BARANG'   => $house->NO_BARANG,
            'MAWB'  => $house->NO_MASTER_BLAWB,
            'METHOD'    => $METHOD,
            'XML' => $E_RESP,
            'BC_CODE' => (string)$KD_RESPON === "ERR"?(string)$KD_RESPON:$KD_RESPON,
            'BC_TEXT' => (string)$KET_RESPON,
            'BC_DATE' => $WK_RESPON,
            'SENTON' => date('Y-m-d H:i:s'),
            'SENTBY' => \Auth::user()->name ?? "BATCH"
          ];
    
          $bclog = BcLog::firstOrCreate([
                          'HouseID' => $house->id,
                          'BC_CODE' => (string)$KD_RESPON === "ERR"?(string)$KD_RESPON:$KD_RESPON,
                        ],[
                          'XML'   => $E_RESP,
                          'MAWB'  => $house->NO_MASTER_BLAWB,
                          'METHOD'    => $METHOD,
                          'NO_BARANG' => $house->NO_BARANG,
                          'BC_TEXT' => (string)$KET_RESPON,
                          'BC_DATE' => $WK_RESPON,
                          'SENTON' => date('Y-m-d H:i:s'),
                          'SENTBY'    => \Auth::user()->name ?? "BATCH"
                        ]);
    
          DB::commit();
          
          if($KD_RESPON == '201' OR $KD_RESPON == '901'){
              $t201 = $house->BC_201_DATE ?? now();
              $KD_RESPON = '201';
              $KET_RESPON = 'CEK DATA WAJIB SELESAI';
              $house->update([
                'BC_201' => 1,
                'BC_201_DATE' => $t201
              ]);
          }
                  
          $FLAG_PDF = "''";
          if($KD_RESPON=='303') $FLAG_PDF = "'#BILL#'";
          if($KD_RESPON=='304') $FLAG_PDF = "'#NPBL#'";
          if($KD_RESPON=='305') $FLAG_PDF = "'#NPD#'";
          if($KD_RESPON=='306') $FLAG_PDF = "'#SPBL#'";
          if($KD_RESPON=='401') $FLAG_PDF = "'#SPPBMCP#'";
          if($KD_RESPON=='402') $FLAG_PDF = "'#SPTNP#'";
          if($KD_RESPON=='403') $FLAG_PDF = "'#KELUAR#'";
          if($KD_RESPON=='404') $FLAG_PDF = "'#SPPB#'";
    
          if($FLAG_PDF != "''")
          {
            $house->update([
              'BC_PDF' => 'BC_PDF+'.$FLAG_PDF
            ]);
          }
    
          $house->update([
            'BC_CODE' => $KD_RESPON,
            'BC_STATUS' => $KET_RESPON,
            'BC_DATE' => $WK_RESPON,                
          ]);
    
          $batch->update([
            'Status' => ((string)$KD_RESPON === "ERR" ? 'Error' : 'Completed'),
            'Info' => 'KD RESPON :'.$KD_RESPON
          ]);
    
          $info = 'Kirim data berhasil, house '.$house->NO_BARANG.' BC_CODE: '.(string) $KD_RESPON.'; BC_STATUS: '.(string) $KET_RESPON;
    
          \Log::info($info);
    
          DB::commit();
        }
  
      } catch (\SoapFault $th) {
        DB::rollback();
  
        $msg = 'Kirim data gagal, house '.$house->NO_BARANG.' Gagal terhubung ke BC : '.$th->getMessage();
  
        \Log::error($msg);
  
        $house->update([
          'BC_CODE' => 'ERR',
          'BC_STATUS' => 'Gagal terhubung ke BC : Server Error'
        ]);
  
        $bclog = BcLog::firstOrCreate([
                        'HouseID' => $house->id,
                        'BC_CODE' => 'ERR',
                      ],[
                        'XML'   => null,
                        'MAWB'  => $house->NO_MASTER_BLAWB,
                        'METHOD'    => $METHOD,
                        'NO_BARANG' => $house->NO_BARANG,
                        'BC_TEXT' => 'Gagal terhubung ke BC',
                        'BC_DATE' => now(),
                        'SENTON' => date('Y-m-d H:i:s'),
                        'SENTBY'    => \Auth::user()->name ?? "BATCH"
                      ]);
      }
    } catch (\Throwable $th) {
      DB::rollback();

      $msg = 'Kirim data gagal, house '.$house->NO_BARANG.' reason'.$th;
  
      \Log::error($msg);
    }
   
  }

  public function mintarespon($id, $limit = 10, $offset = 0, $res = false)
  {      
      session_write_close();
      $exc = [401,403,408,404];
      $disallowedBCode = [100,102,202,303,406,409,901,902,903];
      $query = House::with('pjt')
                    // ->whereNotNull('BC_201')
                    ->whereIn('JNS_AJU', [1,2])
                    ->where(function($h) use ($exc){
                      $h->whereNull('BC_CODE')
                        ->orWhereNotIn('BC_CODE', $exc);
                    })
                    ->orderBy('BC_DATE');

      if(is_array($id))
      {
        $query->whereIn('id', $id);
      } else {
        $query->where('MasterID', $id)
              ->skip($offset)
              ->take($limit);
      }

      $houses = $query->get();
      
      if(!$houses || $houses->count() == 0)
      {
        $i1 = \Log::warning('No House selected for mintarespon queue.');
        if($res == true)
        {
          return [
            'status' => 'ERROR',
            'message' => 'No House selected for mintarespon queue.'
          ];
        }
      } else {
        $i1 = \Log::notice('Process mintarespon '.$houses->count().' houses sedang Berlangsung.');
        // if($res == true)
        // {
        //   return [
        //     'status' => 'OK',
        //     'message' => 'Process '.$houses->count().' houses sedang Berlangsung.'
        //   ];
        // }
      }

      // header("Content-Encoding: none");
      // header('Content-Type: application/json; charset=utf-8');
      // header("Content-Length: ".ob_get_length());
      // header("Connection: close");
      
      // flush();

      $SENDING_USER = 'MINTA'; //isset($CU->username) ? $CU->username : '';
      $SENDING_DATE = date('Y-m-d H:i:s');
      $soapOptions = $this->soap_options();

      try {
        foreach($houses as $house)
        {
          $lg1 = \Log::notice('Queue mintarespon for House '.$house->NO_BARANG.' Started.');

          $NO_IZIN_PJT = $house->pjt?->NO_IZIN_PJT ?? $house->NO_IZIN_PEMBERITAHU;
          $TGL_IZIN_PJT = $house->pjt?->TGL_IZIN_PJT ?? $house->NO_IZIN_PEMBERITAHU;
          $SOAP_USER = $house->pjt?->User_BarangKiriman;
          $SOAP_SIGN = $house->pjt?->Token_BarangKiriman;
          $SOAP_PASS = $house->pjt?->Password_BarangKiriman;
          $SOAP_URL       = $soapOptions['url'];
          $SOAP_LOCATION  = $soapOptions['location'];
          $SOAP_SETTING = $soapOptions['setting'];
          $PJT_NPWP       = $house->NO_ID_PEMBERITAHU;
          $NO_ID_PEMBERITAHU = $PJT_NPWP;
          
          $NO_BARANG = $house->NO_BARANG;
          $TGL_HOUSE_BLAWB = bc_date($house->TGL_HOUSE_BLAWB);
      
          $webServiceClient = new \SoapClient($SOAP_URL, $SOAP_SETTING);
          $DATA = "<CEK_STATUS><HEADER><NPWP>{$NO_ID_PEMBERITAHU}</NPWP><NO_BARANG>{$NO_BARANG}</NO_BARANG><TGL_HOUSE_BLAWB>{$TGL_HOUSE_BLAWB}</TGL_HOUSE_BLAWB></HEADER></CEK_STATUS>";
      
          $requestData = [
              "data" => $DATA,
              "id" => $SOAP_USER.'^$'.$SOAP_PASS,
              "sign" => $SOAP_SIGN
          ];
      
          DB::beginTransaction();
      
          try {
            $response = $webServiceClient->__soapCall("getResponByAwb", ["getResponByAwb" => $requestData], ['location' => $SOAP_LOCATION]);
            $RESP = isset($response->return) ? $response->return : '';

            // $log = SchedulerLog::create([
            //   'logable_type' => '\App\Models\House',
            //   'logable_id' => $house->id,
            //   'process' => "getResponByAwb",
            //   'request' => $DATA,
            //   'response' => $RESP,
            //   'info' => 'Get Respon Success.'
            // ]);
      
            $_RESP = str_replace('</CEK_STATUS><CEK_STATUS>','###',$RESP);
            $_RESP = str_replace(array('</CEK_STATUS>','<CEK_STATUS>'),'',$_RESP);
            $A_RESP = explode('###',$_RESP);
            
            if(empty($RESP)) $A_RESP = [];
            $MAN_ID = [];
      
            if(count($A_RESP)>0) {
              foreach($A_RESP as $RS){
                $XML = '<CEK_STATUS>'.$RS.'</CEK_STATUS>';
                $_XML = simplexml_load_string($XML);
                $_XML = json_encode($_XML);
                $R = json_decode($_XML);
                $E_RESP = base64_encode($XML);
        
                $NO_BARANG = isset($R->HEADER->NO_BARANG) ? trim($R->HEADER->NO_BARANG) : '';
                $KD_RESPON = isset($R->HEADER->KD_RESPON) ? trim($R->HEADER->KD_RESPON) : '';
                $KET_RESPON = isset($R->HEADER->KET_RESPON) ? trim($R->HEADER->KET_RESPON) : '';
                $PDF = isset($R->HEADER->PDF) ? trim($R->HEADER->PDF) : '';
                $WK_RESPON = isset($R->HEADER->WK_REKAM) ? date('Y-m-d H:i:s',strtotime($R->HEADER->WK_REKAM)) : '0000-00-00 00:00:00';
                    
                $MANIFEST_ID = $house->id;
                $MAWB = $house->NO_MASTER_BLAWB;
      
                $BCLOG = [
                    'HouseID' => $house->id,
                    'XML'   => $E_RESP,
                    'MAWB'  => $house->NO_MASTER_BLAWB,
                    'METHOD'    => 'getResponByAwb',
                    'BC_CODE' => $KD_RESPON,
                    'NO_BARANG' => $NO_BARANG,
                    'BC_TEXT' => $KET_RESPON,
                    'BC_DATE' => $WK_RESPON,
                    'SENTON' => $SENDING_DATE,
                    'SENTBY'    => $SENDING_USER
                ];
      
                $bclog = BcLog::updateOrCreate([
                                'HouseID' => $house->id,
                                'BC_CODE' => $KD_RESPON ?? NULL
                              ],[
                                'XML'   => $E_RESP,
                                'MAWB'  => $house->NO_MASTER_BLAWB,
                                'METHOD'    => 'getResponByAwb',
                                'NO_BARANG' => $NO_BARANG ?? NULL,
                                'BC_TEXT' => $KET_RESPON ?? NULL,
                                'BC_DATE' => $WK_RESPON,
                                'SENTON' => $SENDING_DATE,
                                'SENTBY'    => $SENDING_USER
                              ]);
      
                DB::commit();
      
                if($KD_RESPON !== '')
                {
                  if (!in_array(intval($KD_RESPON), $disallowedBCode)) {
      
                    if ($house->BC_DATE <= $WK_RESPON 
                        || intval($house->BC_CODE) < intval($KD_RESPON) 
                        || in_array($house->BC_CODE, $this->tibanrespon())) {
      
                        if(!in_array($house->BC_CODE, [401,403,404,408])) {
                          $uh = true;
                          if ($KD_RESPON != '408') {
                            if(($KD_RESPON == '201' && $house->BC_CODE == '203')
                                || ($KD_RESPON == 'ERR' && $house->BC_CODE != NULL)) {
                              $uh = false;
                            }
                            if($uh === true) {
                              $house->update([
                                'BC_CODE' => $KD_RESPON,
                                'BC_DATE' => $BCLOG["BC_DATE"],
                                'BC_STATUS' => $BCLOG["BC_TEXT"]
                              ]);
                            }
                          } else {
                            $house->update([
                              'BC_CODE' => $KD_RESPON,
                              'BC_DATE' => $BCLOG["BC_DATE"],
                              'BC_STATUS' => $BCLOG["BC_TEXT"],
                              'BC_408_DATE' => $BCLOG["BC_DATE"]
                            ]);
                          }   
                        }
                    }
                  } else {                    
                    switch ($KD_RESPON) {
                      case '403':
                        $house->update(['BC_403_DATE' => $BCLOG['BC_DATE']]);
                        break;
                      case '401':
                        $house->update(['BC_401_DATE' => $BCLOG['BC_DATE']]);
                        break;
                      case '303':
                        $house->update(['BC_303_DATE' => $BCLOG['BC_DATE']]);
                        break;                      
                      default:
                        # code...
                        break;
                    }
                    DB::commit();
                  }
                }
        
                $FLAG_PDF = "''";
                if($KD_RESPON=='303') $FLAG_PDF = "'#BILL#'";
                if($KD_RESPON=='304') $FLAG_PDF = "'#NPBL#'";
                if($KD_RESPON=='305') $FLAG_PDF = "'#NPD#'";
                if($KD_RESPON=='306') $FLAG_PDF = "'#SPBL#'";
                if($KD_RESPON=='401') $FLAG_PDF = "'#SPPBMCP#'";
                if($KD_RESPON=='402') $FLAG_PDF = "'#SPTNP#'";
                if($KD_RESPON=='403') $FLAG_PDF = "'#KELUAR#'";
                if($KD_RESPON=='404') $FLAG_PDF = "'#SPPB#'";
      
                $house->update([
                  'FLAG_PDF' => $FLAG_PDF
                ]);
      
                DB::commit();

                $lg2 = \Log::info('Queue mintarespon for '.$house->NO_BARANG.' Finished. KD_RESPON :'.$KD_RESPON);

                if($KD_RESPON == '401')
                {
                  $res = $this->fetch401(1, [$house->NO_BARANG]);
                  if($res['status'] != 'OK') {
                    \Log::error('Fetch 401 for '.$house->NO_BARANG.' failed, reason '.$res['message']);
                  }
                }
                if($KD_RESPON == '403'){
                  $SPPBNumber = isset($R->HEADER->NO_SPPBMCP) ? $R->HEADER->NO_SPPBMCP : NULL;
                  $SPPBDate = isset($R->HEADER->TGL_SPPBMCP) ? $R->HEADER->TGL_SPPBMCP : NULL;

                  $house->update([
                    'SPPBNumber' => $SPPBNumber,
                    'SPPBDate' => $SPPBDate
                  ]);
                }
                if($KD_RESPON == '404')
                {
                  $SPPBNumber = strlen($R->HEADER?->NO_SPPB) > 0 
                                ? $R->HEADER?->NO_SPPB 
                                : $R->HEADER?->NO_PIBK;
                  $SPPBDate = $R->HEADER?->TGL_SPPB;
                  
                  $house->update([
                    'SPPBNumber' => $SPPBNumber,
                    'SPPBDate' => $SPPBDate
                  ]);
                }
                if($KD_RESPON == '303')
                {
                  $res = $this->fetch303(1, [$house->NO_BARANG]);
                  if($res['status'] != 'OK') {
                    \Log::error('Fetch 303 for '.$house->NO_BARANG.' failed, reason '.$res['message']);
                  }
                }

                DB::commit();

                if($res == true)
                {
                  return [
                    'status' => 'OK',
                    'message' => 'Queue mintarespon for '.$house->NO_BARANG.' Finished. KD_RESPON :'.$KD_RESPON
                  ];
                }
              }          
            } else {
              $lgk = \Log::warning('Respon mintarespon for '.$house->NO_BARANG.' is Empty.');

              if($res == true)
                {
                  return [
                    'status' => 'OK',
                    'message' => 'Respon mintarespon for '.$house->NO_BARANG.' is Empty.'
                  ];
                }
            }
      
          } catch (\SoapFault $th) {
            DB::rollback();

            $lge = \Log::error('Queue mintarespon for '.$house->NO_BARANG.' error, reason: '.$th->getMessage());

            if($res == true)
            {
              return [
                'status' => 'ERROR',
                'message' => 'Queue mintarespon for '.$house->NO_BARANG.' error, reason: '.$th->getMessage()
              ];
            }

            // return [
            //   'status' => 'ERROR',
            //   'message' => $th->getMessage()
            // ];
          }
          $lg3 = \Log::info('Queue mintarespon for '.$NO_BARANG.' Completed.');
        }

        if($res == true)
        {
          return [
            'status' => 'OK',
            'message' => 'Berhasil menarik data '.$houses->count().' CN'
          ];
        }
        // return [
        //   'status' => 'OK',
        //   'message' => 'Berhasil menarik data '.$houses->count().' CN'
        // ];
      } catch (\Throwable $th) {
        DB::rollback();
        $lg2 = \Log::error('Queue mintarespon error. Reason :'.$th->getMessage());
        //throw $th;
        // return [
        //   'status' => 'ERROR',
        //   'message' => $th->getMessage()
        // ];

        if($res == true)
        {
          return [
            'status' => 'ERROR',
            'message' => $th->getMessage()
          ];
        }
      }

      // return [
      //   'status' => 'OK',
      //   'message' => 'Tarik Respon Success'
      // ];
  }

  public function getrespon($npwp)
  {
      $pjt = IdModul::where('NPWP', $npwp)->first();

      if(!$pjt)
      {
        \Log::error('No PJT Info found for NPWP: '.$npwp);

        return false;
      }

      $disallowedBCode = [405,406,409,100,102,202,901,902,201,203];

      $SENDING_USER = 'AutoPooling'; //isset($CU->username) ? $CU->username : '';
      $SENDING_DATE = date('Y-m-d H:i:s');
      $soapOptions = $this->soap_options();

      $SOAP_USER = $pjt->User_BarangKiriman;
      $SOAP_SIGN = $pjt->Token_BarangKiriman;
      $SOAP_PASS = $pjt->Password_BarangKiriman;
      $SOAP_URL = $soapOptions['url'];
      $SOAP_LOCATION = $soapOptions['location'];
      $SOAP_SETTING = $soapOptions['setting'];
      $PJT_NPWP = $pjt->NPWP;

      $webServiceClient = new \SoapClient($SOAP_URL, $SOAP_SETTING);
      $requestData = [
        "npwp" => $PJT_NPWP,
        "id" => $SOAP_USER.'^$'.$SOAP_PASS,
        "sign" => $SOAP_SIGN
      ];

      DB::beginTransaction();

      try {
        $response = $webServiceClient->__soapCall("requestRespon", ["requestRespon" => $requestData], ['location' => $SOAP_LOCATION]);

        $RESP = isset($response->return) ? $response->return : '';

        $cont = '';
        $log_folder = date('Y-m-d');
        $log_fn = 'ALL_STATUS_'.date("d-M-Y_His").".txt";

        if(!empty($RESP))
        {                
            $cont .= "\n " . date('H:i')."  ".str_replace(array("\r\n", "\n", "\r"),"",$RESP);
            \Storage::disk('public')->put('/logs/requestRespon/'.$PJT_NPWP.'/'.$log_folder.'/'.$log_fn, $cont);

            \Log::info('Create Response xml /logs/requestRespon/'.$PJT_NPWP.'/'.$log_folder.'/'.$log_fn);            
        }

        $_RESP = str_replace('</RESPONSE><RESPONSE>','###',$RESP);
        $_RESP = str_replace(array('</RESPONSE>','<RESPONSE>'),'',$_RESP);
        $A_RESP = explode('###',$_RESP);
        
        if(empty($RESP)) $A_RESP = array();

        $info = 'Get 30 Respon started: ';

        if(count($A_RESP) > 0)
        {
          foreach($A_RESP as $RS)
          {
            $XML = '<CEK_STATUS>'.$RS.'</CEK_STATUS>';
            $_XML = simplexml_load_string($XML);
            $_XML = json_encode($_XML);
            $R = json_decode($_XML);
            $E_RESP = base64_encode($XML);

            $NO_BARANG = isset($R->HEADER->NO_BARANG) ? trim($R->HEADER->NO_BARANG) : '';
            $KD_RESPON = isset($R->HEADER->KD_RESPON) ? trim($R->HEADER->KD_RESPON) : '';
            $KET_RESPON = isset($R->HEADER->KET_RESPON) ? trim($R->HEADER->KET_RESPON) : '';
            $PDF = isset($R->HEADER->PDF) ? trim($R->HEADER->PDF) : '';
            $WK_RESPON = isset($R->HEADER->WK_REKAM) ? date('Y-m-d H:i:s',strtotime($R->HEADER->WK_REKAM)) : '0000-00-00 00:00:00';

            $house = House::where('NO_BARANG', $NO_BARANG)->first();
            
            if($house)
            {
              $HouseID = $house->id;
              $MAWB = $house->NO_MASTER_BLAWB;

              if (!in_array(intval($KD_RESPON), $disallowedBCode)) {
                if ($house->BC_DATE < $WK_RESPON) {  
                  if(!in_array($house->BC_CODE, [401,403,404,408])) {
                    $uh = true;
                    if ($KD_RESPON != '408') {
                      if(($KD_RESPON == '201' && $house->BC_CODE == '203')
                          || ($KD_RESPON == 'ERR' && $house->BC_CODE != NULL)) {
                        $uh = false;
                      }
                      if($uh === true) {
                        $house->update([
                          'BC_CODE' => $KD_RESPON,
                          'BC_DATE' => $WK_RESPON,
                          'BC_STATUS' =>$KET_RESPON
                        ]);
                      }                  
                    } else {
                      $house->update([
                        'BC_408_DATE' => $WK_RESPON,
                        'BC_CODE' => $KD_RESPON,
                        'BC_DATE' => $WK_RESPON,
                        'BC_STATUS' => $KET_RESPON,
                        'BC_408_DATE' => $WK_RESPON
                      ]);
                    }   
                  }
                }
              }

              $bclog = BcLog::updateOrCreate([
                        'HouseID' => $HouseID,
                        'BC_CODE' => $KD_RESPON ?? NULL
                      ],[
                        'XML'   => $E_RESP,
                        'MAWB'  => $MAWB,
                        'METHOD'    => 'requestRespon',
                        'NO_BARANG' => $NO_BARANG ?? NULL,
                        'BC_TEXT' => $KET_RESPON ?? NULL,
                        'BC_DATE' => $WK_RESPON,
                        'SENTON' => $SENDING_DATE,
                        'SENTBY'    => $SENDING_USER
                      ]);

              DB::commit();

              if($KD_RESPON == '401')
              {
                $res = $this->fetch401(1, [$house->NO_BARANG]);
                if($res['status'] != 'OK') {
                  \Log::error('Fetch 401 for '.$house->NO_BARANG.' failed, reason '.$res['message']);
                }
              }
              if($KD_RESPON == '403'){
                $SPPBNumber = isset($R->HEADER->NO_SPPBMCP) ? $R->HEADER->NO_SPPBMCP : NULL;
                $SPPBDate = isset($R->HEADER->TGL_SPPBMCP) ? $R->HEADER->TGL_SPPBMCP : NULL;

                $house->update([
                  'SPPBNumber' => $SPPBNumber,
                  'SPPBDate' => $SPPBDate
                ]);
              }
              if($KD_RESPON == '404')
              {
                $SPPBNumber = strlen($R->HEADER?->NO_SPPB) > 0 
                              ? $R->HEADER?->NO_SPPB 
                              : $R->HEADER?->NO_PIBK;
                $SPPBDate = $R->HEADER?->TGL_SPPB;
                
                $house->update([
                  'SPPBNumber' => $SPPBNumber,
                  'SPPBDate' => $SPPBDate
                ]);
              }
              if($KD_RESPON == '303')
                {
                  $res = $this->fetch303(1, [$house->NO_BARANG]);
                  if($res['status'] != 'OK') {
                    \Log::error('Fetch 303 for '.$house->NO_BARANG.' failed, reason '.$res['message']);
                  }
                }

              DB::commit();

              $info .= PHP_EOL.'HAWB '.$NO_BARANG.' : '.$KD_RESPON.' - '.$KET_RESPON;
            } else {
              $info .= 'House Not Found '.$NO_BARANG.'; ';
            }
          }
        } else {
          $info .= 'No New Response';
        }

        \Log::info($info);
      } catch (\SoapFault $th) {
        DB::rollback();

        \Log::error($th);
      }      
  }

  public function todayrate()
  {
      $sub = \Illuminate\Support\Arr::first(explode('.', request()->getHost()));

      if($sub == 'localhost')
      {
        return 15000;
      }

      $today = today();
      $exchange = RefExchangeRate::where('RE_ExRateType', 'TAX')
                                  ->where('RE_ExpiryDate', '>=', $today->format('Y-m-d'))
                                  ->first();

      // if(!$exchange)
      // {
      //   $dbName = 'new_justindo_co_id';
      //   $dbUName = 'tps_new_justindo_co_id';
      //   $dbPass = 'b6D47aea2022!@#$';

      //   DB::purge('tpslama');

      //   Config::set('database.connections.tpslama.database', $dbName);
      //   Config::set('database.connections.tpslama.username', $dbUName);
      //   Config::set('database.connections.tpslama.password', $dbPass);

      //   DB::reconnect('tpslama'); 

      //   $exchange = RefExchangeRateLegacy::where('TaxStartDate', '<=', $today->format('Y-m-d'))
      //                                 ->where('TaxEndDate', '>=', $today->format('Y-m-d'))
      //                                 ->first();
      // }        

      $exrate = $exchange->TaxRate ?? $exchange->RE_SellRate ?? 0;

      return $exrate;
  }

  public function updateBC11($m, $h = NULL)
  {
      session_write_close();

      $query = House::with(['pjt', 'branch'])
                    ->whereNotIn('BC_CODE', [307,401,408])
                    ->whereNotNull('BC_CODE');
      if($h)
      {
        $query->where('id', $h);
      } else {
        $query->where('MasterID', $m);
      }
                      
      $houses = $query->orderBy('id')
                      ->get();
      
      if(count($houses) == 0)
      {
        return [
          'status' => 'ERROR',
          'message' => 'House kosong'
        ];
      }

      $SENDING_USER = 'MINTA'; //isset($CU->username) ? $CU->username : '';
      $SENDING_DATE = date('Y-m-d H:i:s');
      $soapOptions = $this->soap_options();
      $res = [];

      foreach ($houses as $CN) {       

        $branch = $CN->branch;

        $KD_GUDANG = $branch->CB_WhCode;
        $NO_IZIN_PJT = $CN->pjt?->NO_IZIN_PJT ?? $CN->NO_IZIN_PEMBERITAHU;
        $TGL_IZIN_PJT = $CN->pjt?->TGL_IZIN_PJT ?? $CN->NO_IZIN_PEMBERITAHU;
        $SOAP_USER = $CN->pjt?->User_BarangKiriman;
        $SOAP_SIGN = $CN->pjt?->Token_BarangKiriman;
        $SOAP_PASS = $CN->pjt?->Password_BarangKiriman;
        $SOAP_URL       = $soapOptions['url'];
        $SOAP_LOCATION  = $soapOptions['location'];
        $SOAP_SETTING = $soapOptions['setting'];
        $PJT_NPWP       = $CN->NO_ID_PEMBERITAHU;
        $NO_ID_PEMBERITAHU = $PJT_NPWP;

        $NO_BARANG = $CN->NO_BARANG;
        $NO_BC11 = $CN->NO_BC11;
        $TGL_HOUSE_BLAWB = bc_date($CN->TGL_HOUSE_BLAWB);
        $TGL_BC11 = bc_date($CN->TGL_BC11);
        $NO_POS_BC11 = ($CN->NO_POS_BC11);
        $NO_SUBPOS_BC11 = ($CN->NO_SUBPOS_BC11);
        $NO_SUBSUBPOS_BC11 = ($CN->NO_SUBSUBPOS_BC11);

        $webServiceClient = new \SoapClient($SOAP_URL, $SOAP_SETTING);
        $DATA = "<PIBK_UPDATE><HEADER>
        <NO_BARANG>{$NO_BARANG}</NO_BARANG>
        <TGL_HOUSE_BLAWB>{$TGL_HOUSE_BLAWB}</TGL_HOUSE_BLAWB>
        <NO_BC11>{$NO_BC11}</NO_BC11>
        <TGL_BC11>{$TGL_BC11}</TGL_BC11>
        <NO_POS_BC11>{$NO_POS_BC11}</NO_POS_BC11>
        <NO_SUBPOS_BC11>{$NO_SUBPOS_BC11}</NO_SUBPOS_BC11>
        <NO_SUBSUBPOS_BC11>{$NO_SUBSUBPOS_BC11}</NO_SUBSUBPOS_BC11>
        <KD_GUDANG>{$KD_GUDANG}</KD_GUDANG>
        </HEADER></PIBK_UPDATE>";

        if(subDomain() == 'uat')
        {
          \Storage::disk('public')->put('/file/xml-updatebc/'.$CN->NO_BARANG.'.xml', $DATA); 

          $res[] = 'Create XML '.$CN->NO_BARANG;
          
        } else {
          $requestData = [
              "data" => $DATA,
              "id" => $SOAP_USER.'^$'.$SOAP_PASS,
              "sign" => $SOAP_SIGN
          ];
            
          $response = $webServiceClient->__soapCall("updateBC11", ["updateBC11" => $requestData], ['location' => $SOAP_LOCATION]);
          $RESP = isset($response->return) ? $response->return : '';

          createLog('App\Models\House', $CN->id, 'Update BC_11, RESPON:'. $RESP);

          $res[] = $RESP;
        }
      }

      return [
        'respon' => $res
      ];
  }

  public function updateSubPos(MasterPartial $partial)
    {
      $partial->load(['houses']);

      $count = $partial->houses->count();

      if($count < 10000)
      {
        foreach($partial->houses->sortBy('id') as $ky => $h)
        {
            $SUBPOS = str_pad(($ky+1),4,'0',STR_PAD_LEFT);

            $h->update([
              'NO_SUBPOS_BC11' => $SUBPOS
            ]);
        }
      } else {
        $Urut = 1;
        $SUB_POS = 1;

        foreach($partial->houses->sortBy('id') as $ky => $h)
        {
          if (($ky+1) % 9000 !== 0) {
            $SUBSUBPOS = str_pad(($Urut),4,'0',STR_PAD_LEFT);
            $SUBPOS = str_pad(($SUB_POS),4,'0',STR_PAD_LEFT);
            $h->update([
              'NO_SUBPOS_BC11' => $SUBPOS,
              'NO_SUBSUBPOS_BC11' => $SUBSUBPOS
            ]);
          } else {
            $SUB_POS++;
            $Urut =1;
            $SUBSUBPOS = str_pad(($Urut),4,'0',STR_PAD_LEFT);
            $SUBPOS = str_pad(($SUB_POS),4,'0',STR_PAD_LEFT);
            $h->update([
              'NO_SUBPOS_BC11' => $SUBPOS,
              'NO_SUBSUBPOS_BC11' => $SUBSUBPOS
            ]);
          }
        }
      }
    }
}

