<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\LaporanExport;
use App\Models\Master;
use App\Models\House;
use Excel, DataTables;

class InventoryReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        $items = collect([]);
        $thead = '';
        $tbody = collect([]);
        $start = '';
        $end = '';
        $jenis = $request->jenis ?? "";

        if($request->jenis
            && $request->period){
          $period = explode(' - ', $request->period);

          $start = $period[0] . ' 00:00:00';
          $end = $period[1] . ' 23:59:59';

          $query = House::with(['master']);

          switch ($jenis) {
            case 'barang-keluar':
              $query->whereBetween('SCAN_OUT_DATE', [$start, $end])
                    ->orderBy('SCAN_OUT_DATE');

              $thead = $this->barangKeluar();
              $tbody = $this->bodyBarangKeluar();
              break;
            case 'barang-masuk':
              $query->whereBetween('SCAN_IN_DATE', [$start, $end])
                    ->orderBy('SCAN_IN_DATE');
              $thead = $this->barangMasuk();
              $tbody = $this->bodyBarangMasuk();
              break;
            case 'tidak-dikuasai':
              $abdate = Carbon::parse($start)->subDays(30)
                                             ->endOfDay()
                                             ->format('Y-m-d H:i:s');
              $query->where('SCAN_IN_DATE', '<', $abdate)
                    ->whereNull('SCAN_OUT_DATE')
                    ->with(['details'])
                    ->orderBy('SCAN_IN_DATE');

              $thead = $this->tidakDikuasai();
              $tbody = $this->bodyTidakDikuasai();
              break;
            case 'tidak-dikuasai2':
              $abdate = Carbon::parse($start)->subDays(30)
                                              ->endOfDay()
                                              ->format('Y-m-d H:i:s');
              $query->where('SCAN_IN_DATE', '<', $abdate)
                    ->whereNull('SCAN_OUT_DATE')
                    ->with(['details'])
                    ->orderBy('SCAN_IN_DATE');

              $thead = $this->tidakDikuasai2();
              $tbody = $this->bodyTidakDikuasai2();
              break;
            case 'monev':
              $query->whereBetween('SCAN_IN_DATE', [$start, $end])
                    ->with(['master', 'details'])
                    ->orderBy('SCAN_IN_DATE');
              $thead = $this->monev();
              $tbody = $this->bodyMonev();
              break;
            case 'rekap-plp':
              $query = Master::whereBetween('PLPDate', [$start, $end])
                             ->orderBy('PLPDate');
              $thead = $this->rekapPlp();
              $tbody = $this->bodyRekapPlp();
              break;
            case 'status-plp':
              $query->whereHas('master', function($query){
                      return $query->whereNotNull('PLPNumber');
                    })
                    ->with(['master', 'details'])
                    ->whereNotNull('SCAN_IN_DATE')
                    ->where('SCAN_IN_DATE', '<=', $end)
                    ->where(function($q) use ($start, $end){
                      $q->whereNull('SCAN_OUT_DATE')
                        ->orWhereBetween('SCAN_OUT_DATE', [$start, $end])
                        ->orWhere('SCAN_OUT_DATE', '>', $end);
                    });
              $thead = $this->statusPlp();
              $tbody = $this->bodyStatusPlp();
              break;
            case 'timbun':
              $query->where('SCAN_IN_DATE', '<=', $end)
                    ->whereNull('SCAN_OUT_DATE');
              $thead = $this->timbun();
              $tbody = $this->bodyTimbun();
              break;
            default:
              # code...
              break;
          }

          if($request->ajax()){
            return $this->datatable($query);
          }

          $items = $query->get();
        }

        if($request->download > 0){
          return Excel::download(new LaporanExport($items, $jenis, $start, $end), 'Laporan '.$jenis.' '.$request->period.'.xlsx');
        }

        return view('pages.inventory.report', compact(['items','jenis', 'thead', 'tbody', 'start', 'end']));
    }

    public function barangKeluar()
    {
      $head = '<tr>
                <th>No</th>
                <th>No Master BL/AWB</th>
                <th>No House BL/AWB</th>
                <th>Job Number</th>
                <th>Jumlah</th>
                <th>Bruto</th>
                <th>CW</th>
                <th>Consignee</th>
                <th>No Flight</th>
                <th>No BC 1.1</th>
                <th>Tanggal BC 1.1</th>
                <th>Tanggal Masuk TPS</th>
                <th>Tanggal Keluar TPS</th>
              </tr>';

      return $head;
    }

    public function bodyBarangKeluar()
    {
      $data = collect([
        'id','mawb_parse','NO_HOUSE_BLAWB','ShipmentNumber','JML_BRG','BRUTO','ChargeableWeight','NM_PENERIMA','NO_FLIGHT','NO_BC11','TGL_BC11','SCAN_IN_DATE','SCAN_OUT_DATE'
      ]);

      return $data;
    }

    public function barangMasuk()
    {
      $head = '<tr>
                <th>No</th>
                <th>No Master BL/AWB</th>
                <th>No House BL/AWB</th>
                <th>Job File</th>
                <th>Jumlah</th>
                <th>Bruto</th>
                <th>CW</th>
                <th>Consignee</th>
                <th>No Flight</th>
                <th>No BC 1.1</th>
                <th>Tanggal BC 1.1</th>
                <th>Tanggal Masuk TPS</th>
              </tr>';

      return $head;
    }

    public function bodyBarangMasuk()
    {
      $data = collect([
        'id','mawb_parse','NO_HOUSE_BLAWB','ShipmentNumber','JML_BRG','BRUTO','ChargeableWeight','NM_PENERIMA','NO_FLIGHT','NO_BC11','TGL_BC11','SCAN_IN_DATE'
      ]);

      return $data;
    }

    public function tidakDikuasai()
    {
      $head = '<tr class="text-center">
                <th>NO</th>
                <th>NO BC.11</th>
                <th>TGL BC.11</th>
                <th>TGL GATE IN</th>
                <th>NO POS</th>
                <th>NO VOYAGE</th>
                <th>JUMLAH KEMASAN</th>
                <th>BRUTO</th>
                <th>NO BL</th>
                <th>NO HOST_BL</th>
                <th>URAIAN BARANG</th>
                <th>NAMA PEMILIK</th>
                <th>ALAMAT PEMILIK</th>
                <th>KET</th>
              </tr>';

      return $head;
    }

    public function bodyTidakDikuasai()
    {
      $data = collect([
        'id','NO_BC11','TGL_BC11','SCAN_IN_DATE','NO_POS_BC11','NO_FLIGHT','JML_BRG','BRUTO','mawb_parse','NO_HOUSE_BLAWB','UR_BRG','NM_PENERIMA','AL_PENERIMA','KODE_GUDANG'
      ]);

      return $data;
    }

    public function tidakDikuasai2()
    {
      $head = '<tr class="text-center">
                <th rowspan="2">NO</th>
                <th colspan="3">BC.11</th>
                <th rowspan="2">TANGGAL TIMBUN</th>
                <th rowspan="2">SARANA PENGANGKUT</th>
                <th>KEMASAN</th>
                <th colspan="2">BERAT</th>
                <th colspan="3">NOMOR AWB</th>
                <th rowspan="2">URAIAN BARANG</th>
                <th colspan="2">IMPORTIR</th>
              </tr>
              <tr class="text-center">
                <th>NOMOR</th>
                <th>TANGGAL</th>
                <th>POS</th>
                <th>JUMLAH</th>
                <th>BRUTO</th>
                <th>CW</th>
                <th>MAWB</th>
                <th>HAWB</th>
                <th>JOB FILE</th>
                <th>NAMA</th>
                <th>ALAMAT</th>
              </tr>';

      return $head;
    }

    public function bodyTidakDikuasai2()
    {
      $data = collect([
        'id','NO_BC11','TGL_BC11','NO_POS_BC11','SCAN_IN_DATE','NO_FLIGHT','JML_BRG','BRUTO','ChargeableWeight','mawb_parse','NO_HOUSE_BLAWB','ShipmentNumber','UR_BRG','NM_PENERIMA','AL_PENERIMA'
      ]);

      return $data;
    }

    public function monev()
    {
      $head = '<tr class="text-center">
                <th>NO</th>
                <th>KODE TPS</th>
                <th>KODE GUDANG</th>
                <th>SARANA PENGANGKUT</th>
                <th>NO BC11</th>
                <th>TGL BC11</th>
                <th>NO POS</th>
                <th>NO SUB POS</th>
                <th>NO PLP</th>
                <th>TGL PLP</th>
                <th>NO SEGEL</th>
                <th>JUMLAH KOLI</th>
                <th>BERAT BRUTO/CW (Kg)</th>
                <th>MAWB</th>
                <th>HAWB</th>
                <th>JOB FILE</th>
                <th>URAIAN BARANG</th>
                <th>CONSIGNEE</th>
                <th>ALAMAT</th>
                <th>JENIS</th>
                <th>NO PABEAN</th>
                <th>TGL PABEAN</th>
                <th>NO SPPB</th>
                <th>STATUS</th>
                <th>WAKTU MASUK TPS</th>
                <th>WAKTU KELUAR TPS</th>
                <th>KETERANGAN</th>
              </tr>';

      return $head;
    }

    public function bodyMonev()
    {
      $data = collect([
        'id','KODE_TPS','KODE_GUDANG','NO_FLIGHT','NO_BC11','TGL_BC11','NO_POS_BC11','NO_SUBPOS_BC11','PLPNumber','PLPDate','NO_SEGEL','JML_BRG','BRUTO/CW','mawb_parse','NO_HOUSE_BLAWB','ShipmentNumber','UR_BRG','NM_PENERIMA','AL_PENERIMA','JNS_AJU','NO_DAFTAR_PABEAN','TGL_DAFTAR_PABEAN','SPPBNumber','OUT_STATUS','SCAN_IN_DATE','SCAN_OUT_DATE','KETERANGAN'
      ]);

      return $data;
    }

    public function rekapPlp()
    {
      $head = '<tr class="text-center">
                <th rowspan="2">NO</th>
                <th colspan="3">KEMASAN</th>
                <th rowspan="2">MASTER BL/AWB</th>
                <th rowspan="2">JOB FILE</th>
                <th colspan="3">BC.11</th>
                <th colspan="2">PLP</th>
                <th rowspan="2">TPS ASAL</th>
                <th rowspan="2">TANGGAL MASUK</th>
              </tr>
              <tr>
                <th>JUMLAH</th>
                <th>BERAT</th>
                <th>CW</th>
                <th>NOMOR</th>
                <th>TANGGAL</th>
                <th>POS</th>      
                <th>NO</th>
                <th>TANGGAL</th>
              </tr>';

      return $head;
    }

    public function bodyRekapPlp()
    {
      $data = collect([
        'id','mNoOfPackages','mGrossWeight','mChargeableWeight','mawb_parse','shipment_parse','PUNumber','PUDate','POSNumber','PLPNumber','PLPDate','warehouseLine1','MasukGudang'
      ]);

      return $data;
    }

    public function statusPlp()
    {
      $head = '<tr class="text-center">
                <th>NO</th>
                <th>TGL TIBA</th>
                <th>TGL MASUK TPS</th>
                <th>NO PLP</th>
                <th>TGL PLP</th>
                <th>MASTER AWB</th>
                <th>H AWB</th>
                <th>JUMLAH KOLI</th>
                <th>BERAT</th>
                <th>CW</th>
                <th>URAIAN BARANG</th>
                <th>CONSIGNEE</th>
                <th>TGL KELUAR</th>
                <th>NOPEN</th>
                <th>SPPB</th>
                <th>KETERANGAN</th>
                <th>JOBFILE</th>
                <th>CONSOLIDATION</th>
              </tr>';

      return $head;
    }

    public function bodyStatusPlp()
    {
      $data = collect([
        'id','TGL_TIBA','SCAN_IN_DATE','PLPNumber','PLPDate','mawb_parse','NO_HOUSE_BLAWB','JML_BRG','BRUTO','ChargeableWeight','UR_BRG','NM_PENERIMA','SCAN_OUT_DATE','NO_DAFTAR_PABEAN','NO_SPPB','OUT_STATUS','ShipmentNumber','ConsolNumber'
      ]);

      return $data;
    }

    public function timbun()
    {
      $head = '<tr style="text-align: center;">
                <th>No</th>
                <th>No Master BL/AWB</th>
                <th>No House BL/AWB</th>
                <th>Job File</th>
                <th>Jumlah</th>
                <th>Bruto</th>
                <th>CW</th>
                <th>Consignee</th>
                <th>No Flight</th>
                <th>No BC 1.1</th>
                <th>Tanggal BC 1.1</th>
                <th>Tanggal Masuk TPS</th>
              </tr>';

      return $head;
    }

    public function bodyTimbun()
    {
      $data = collect([
        'id','mawb_parse','NO_HOUSE_BLAWB','ShipmentNumber','JML_BRG','BRUTO','ChargeableWeight','NM_PENERIMA','NO_FLIGHT','NO_BC11','TGL_BC11','SCAN_IN_DATE'
      ]);

      return $data;
    }

    public function datatable($query)
    { 
        return DataTables::eloquent($query)
                         ->addIndexColumn()
                         ->addColumn('mawb', function($row){
                          return $row->mawb_parse;
                         })
                         ->addColumn('hawb', function($row){
                          $hawb = '';
                          if($row->MasterID){
                            $hawb = $row->NO_BARANG;
                          } else {
                            foreach ($row->houses as $house) {
                              $hawb .= $house->NO_BARANG.';';
                            }
                          }

                          return $hawb;
                         })
                         ->addColumn('no_bc', function($row){
                          $nobc = '';
                          if($row->MasterID){
                            $nobc = $row->NO_BC11;
                          } else {
                            $nobc = $row->PUNumber;
                          }

                          return $nobc;
                         })
                         ->addColumn('tgl_bc', function($row){
                          $tglbc = $row->PUDate ?? $row->TGL_BC11;
                          if($tglbc){
                            $time = Carbon::parse($tglbc);
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
                         ->addColumn('no_plp', function($row){
                          $noplp = '';
                          if($row->MasterID){
                            $noplp = $row->master->PLPNumber ?? "-";
                          } else {
                            $noplp = $row->PLPNumber ?? "-";
                          }

                          return $noplp;
                         })
                         ->addColumn('tgl_plp', function($row){
                          $tglplp = $row->PLPDate ?? $row->master->PLPDate;
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
                         ->addColumn('no_sppb', function($row){
                          $nosppb = '';
                          if($row->MasterID){
                            $nosppb = $row->NO_SPPB;
                          } else {
                            foreach ($row->houses as $house) {
                              $nosppb .= ($house->NO_SPPB)
                                          ? $house->NO_SPPB.';'
                                          :'';
                            }
                          }

                          return $nosppb;
                         })
                         ->addColumn('tgl_sppb', function($row){
                          if($row->MasterID){
                            $tglsppb = $row->TGL_SPPB;
                            if($tglsppb){
                              $time = Carbon::parse($tglsppb);
                              $display = $time->format('d/m/Y');
                              $timestamp = $time->timestamp;
                            } else {
                              $display = "-";
                              $timestamp = 0;
                            }
                          } else {
                            $display = '';
                            $timestamp = '';
                            foreach ($row->houses as $house) {
                              $tglsppb = $house->TGL_SPPB;                              
                              if($tglsppb){
                                $time = Carbon::parse($tglsppb);
                                $display .= $time->format('d/m/Y').';';
                                $timestamp .= $time->timestamp.';';
                              } else {
                                $display .= "";
                                $timestamp .= "";
                              }
                            }
                          }

                          $show = [
                            'display' => $display,
                            'timestamp' => $timestamp
                          ];

                          return $show; 
                         })
                         ->addColumn('no_pabean', function($row){
                          $nopabean = '';
                          if($row->MasterID){
                            $nopabean = $row->NO_DAFTAR_PABEAN;
                          } else {
                            foreach ($row->houses as $house) {
                              $nopabean .= ($house->NO_DAFTAR_PABEAN)
                                          ? $house->NO_DAFTAR_PABEAN.';'
                                          :'';
                            }
                          }

                          return $nopabean;
                         })
                         ->addColumn('tgl_pabean', function($row){
                          if($row->MasterID){
                            $tglpabean = $row->TGL_DAFTAR_PABEAN;
                            if($tglpabean){
                              $time = Carbon::parse($tglpabean);
                              $display = $time->format('d/m/Y');
                              $timestamp = $time->timestamp;
                            } else {
                              $display = "-";
                              $timestamp = 0;
                            }
                          } else {
                            $display = '';
                            $timestamp = '';
                            foreach ($row->houses as $house) {
                              $tglpabean = $house->TGL_DAFTAR_PABEAN;                              
                              if($tglpabean){
                                $time = Carbon::parse($tglpabean);
                                $display .= $time->format('d/m/Y').';';
                                $timestamp .= $time->timestamp.';';
                              } else {
                                $display .= "";
                                $timestamp .= "";
                              }
                            }
                          }

                          $show = [
                            'display' => $display,
                            'timestamp' => $timestamp
                          ];

                          return $show; 
                         })
                         ->addColumn('no_segel', function($row){
                          $sealno = '';
                          if($row->MasterID){
                            $sealno = $row->SEAL_NO;
                          } else {
                            foreach ($row->houses as $house) {
                              $sealno .= ($house->SEAL_NO)
                                          ? $house->SEAL_NO.';'
                                          :'';
                            }
                          }

                          return $sealno;
                         })
                         ->addColumn('tgl_segel', function($row){
                          if($row->MasterID){
                            $sealdate = $row->SEAL_DATE;
                            if($sealdate){
                              $time = Carbon::parse($sealdate);
                              $display = $time->format('d/m/Y');
                              $timestamp = $time->timestamp;
                            } else {
                              $display = "-";
                              $timestamp = 0;
                            }
                          } else {
                            $display = '';
                            $timestamp = '';
                            foreach ($row->houses as $house) {
                              $sealdate = $house->SEAL_DATE;                              
                              if($sealdate){
                                $time = Carbon::parse($sealdate);
                                $display .= $time->format('d/m/Y').';';
                                $timestamp .= $time->timestamp.';';
                              } else {
                                $display .= "";
                                $timestamp .= "";
                              }
                            }
                          }

                          $show = [
                            'display' => $display,
                            'timestamp' => $timestamp
                          ];

                          return $show; 
                         })
                         ->addColumn('jumlah', function($row){
                          if($row->MasterID){
                            $jumlah = $row->JML_BRG;
                          } else {
                            $jumlah = '';
                            foreach ($row->houses as $house) {
                              $jumlah .= $house->JML_BRG . ';';
                            }
                          }

                          return $jumlah;
                         })
                         ->addColumn('berat', function($row){
                          if($row->MasterID){
                            $berat = $row->BRUTO;
                          } else {
                            $berat = '';
                            foreach ($row->houses as $house) {
                              $berat .= $house->BRUTO . ';';
                            }
                          }

                          return $berat;
                         })
                         ->addColumn('cw', function($row){
                          if($row->MasterID){
                            $cw = $row->ChargeableWeight;
                          } else {
                            $cw = '';
                            foreach ($row->houses as $house) {
                              $cw .= $house->ChargeableWeight . ';';
                            }
                          }

                          return $cw;
                         })
                         ->addColumn('ur_brg', function($row){
                          if($row->MasterID){
                            $ur_brg = $row->details->first()->UR_BRG ?? "-";
                          } else {
                            $ur_brg = '';
                            foreach ($row->houses as $house) {
                              $ur_brg .= ($house->details->first()->UR_BRG ?? "-") . ';';
                            }
                          }

                          return $ur_brg;
                         })
                         ->addColumn('consignee', function($row){
                          if($row->MasterID){
                            $consignee = $row->NM_PENERIMA;
                          } else {
                            $consignee = '';
                            foreach ($row->houses as $house) {
                              $consignee .= $house->NM_PENERIMA . ';';
                            }
                          }

                          return $consignee;
                         })
                         ->addColumn('wk_in', function($row){
                          if($row->MasterID){
                            $wk_in = $row->SCAN_IN_DATE;
                            if($wk_in){
                              $time = Carbon::parse($wk_in);
                              $display = $time->format('d/m/Y H:i');
                              $timestamp = $time->timestamp;
                            } else {
                              $display = "-";
                              $timestamp = 0;
                            }
                          } else {
                            $display = '';
                            $timestamp = '';
                            foreach ($row->houses as $house) {
                              $wk_in = $house->SCAN_IN_DATE;                              
                              if($wk_in){
                                $time = Carbon::parse($wk_in);
                                $display .= $time->format('d/m/Y H:i').';';
                                $timestamp .= $time->timestamp.';';
                              } else {
                                $display .= "";
                                $timestamp .= "";
                              }
                            }
                          }

                          $show = [
                            'display' => $display,
                            'timestamp' => $timestamp
                          ];

                          return $show; 
                         })
                         ->addColumn('wk_out', function($row){
                          if($row->MasterID){
                            $wk_out = $row->SCAN_OUT_DATE;
                            if($wk_out){
                              $time = Carbon::parse($wk_out);
                              $display = $time->format('d/m/Y H:i');
                              $timestamp = $time->timestamp;
                            } else {
                              $display = "-";
                              $timestamp = 0;
                            }
                          } else {
                            $display = '';
                            $timestamp = '';
                            foreach ($row->houses as $house) {
                              $wk_out = $house->SCAN_OUT_DATE;                              
                              if($wk_out){
                                $time = Carbon::parse($wk_out);
                                $display .= $time->format('d/m/Y H:i').';';
                                $timestamp .= $time->timestamp.';';
                              } else {
                                $display .= "";
                                $timestamp .= "";
                              }
                            }
                          }

                          $show = [
                            'display' => $display,
                            'timestamp' => $timestamp
                          ];

                          return $show; 
                         })
                         ->toJson();
    }

    public function tblHeader()
    {
      $data = collect([
        'id' => 'id',
        'mawb' => 'MAWB',
        'hawb' => 'HAWB',
        'no_bc' => 'NO BC',
        'tgl_bc' => 'TGL BC',
        'no_plp' => 'NO PLP',
        'tgl_plp' => 'TGL PLP',
        'no_sppb' => 'NO SPPB',
        'tgl_sppb' => 'TGL SPPB',
        'no_pabean' => 'NO PABEAN',
        'tgl_pabean' => 'TGL PABEAN',
        'no_segel' => 'NO SEGEL',
        'tgl_segel' => 'TGL SEGEL',
        'jumlah' => 'JUMLAH',
        'berat' => 'BRUTO',
        'cw' => 'CW',
        'ur_brg' => 'URAIAN BARANG',
        'consignee' => 'CONSIGNEE',
        'wk_in' => 'WAKTU MASUK',
        'wk_out' => 'WAKTU KELUAR'
      ]);

      return $data;
    }
        
}
