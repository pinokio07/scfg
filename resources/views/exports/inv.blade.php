<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Print Sewa Gudang - {{ $shipment->NO_HOUSE_BLAWB }}</title>
  <style>
    @page{ 
      margin: 20mm 15mm 15mm 15mm;   
      font-family: Verdana, Geneva, Tahoma, sans-serif;
      font-size: 8.5pt;
    }
    table{
      width: 100%;
      border-collapse: collapse;
    }
    hr {
      margin-top: 1rem;
      margin-bottom: 1rem;
      border: 0;
      border-top: 1px solid black;
    }
    .table th,
    .table td {
      padding: 0.15rem;
      vertical-align: top;
      /* border-top: 1px solid #565656; */
    }

    .table thead th {
      vertical-align: bottom;
      /* border-bottom: 2px solid #565656; */
    }

    .table tbody + tbody {
      border-top: 2px solid #565656;
    }
    .table-bordered th,
    .table-bordered td {
      border: 1px solid #000000;
    }

    .table-bordered thead th,
    .table-bordered thead td {
      border-bottom-width: 1px;
    }
    .border{
      border:1px solid black;
    }
    .line-100{      
      line-height: 100% !important;
    }
    .text-center{
      text-align: center !important;
    }
    .text-middle{
      vertical-align: middle !important;
      text-align: center !important;
    }
    .text-right{
      text-align: right !important;
    }
    .lbl-hdr{
      font-size: 6pt !important;
      line-height: 100%;      
      padding: 0;
      margin-bottom: 2px;
    }
    .row{
      display: -ms-flexbox;
      display: flex;
      -ms-flex-wrap: wrap;
      flex-wrap: wrap;
      margin-right: -7.5px;
      margin-left: -7.5px;
    }
    .col-6 {
      -ms-flex: 0 0 50%;
      flex: 0 0 50%;
      max-width: 50%;
      position: relative;
      width: 100%;
      padding-right: 7.5px;
      padding-left: 7.5px;
    }
    footer{
      position: fixed;
      bottom: -12mm;
      left: 0; 
      right: 0;      
    }
  </style>
</head>
<body>
  <table class="table table-bordered" style="width: 100%;">
    <tr>      
      <td rowspan="3" style="width: 70%;text-align:center;">
        <span>
          <h4 style="text-decoration: underline; margin:10 0 0 0;font-size:12pt;">
            TEMPAT PENIMBUNAN SEMENTARA (TPS)
          </h4>
        </span>
        <span>
          <h4 style="margin:0 0 10px 0;font-size:12pt;">
            TEMPORARY STORAGE SITE
          </h4>
        </span>
        <b>
          Keputusan Menteri Keuangan RI Nomor 49/MK.4/2023<br>
          Kantor Pabean: KPU BC TIPE C SOEKARNO HATTA<br>
          Operator Warehouse – PT. Prime Freight Indonesia
        </b>
      </td>
      <td>
        <div class="lbl-hdr">Schema Tariff</div>
        {{ $shipment->schemaTariff->name ?? "-" }}
      </td>      
    </tr>
    <tr>
      <td>
        <div class="lbl-hdr">Document No.</div>
        {{ $shipment->tariff_no ?? "-" }}
      </td>
    </tr>
    <tr>
      <td>
        <div class="lbl-hdr">Issued At</div>
        Jakarta, {{ today()->translatedFormat('d F Y') }}
      </td>
    </tr>
  </table>  
  <p style="text-decoration: underline; font-size:10pt;">
    <b style="color: #548DD4 !important;">
    OFFICIAL RECEIPT<br>
    No: {{ $shipment->tariff_no ?? "-" }}
    </b>
  </p>
  <div style="text-align: center;font-size:10pt;">
    <span>
      <h4 style="text-decoration: underline; margin:0 auto;">
        KALKULASI JASA GUDANG
      </h4>
    </span>
    <span>
      <h4 style="text-decoration: underline; margin:0 auto;">
        STORAGE CALCULATION
      </h4>
    </span>
  </div>
  <table style="width: 100%; margin-top:40px;">    
    <tr style="vertical-align: top;">
      <td>Shipper</td>
      <td>:</td>
      <td colspan="2">{{ $shipment->NM_PENGIRIM ?? "-" }}</td>
      <td>ATA</td>
      <td>:</td>
      <td>
        @php
          if($shipment->TGL_TIBA){
            $tiba = \Carbon\Carbon::parse($shipment->TGL_TIBA);
            $showTiba = $tiba->translatedFormat('d F Y');
          } else {
            $tiba = null;
            $showTiba = '-';
          }
        @endphp
        {{ $showTiba }}
      </td>
    </tr>
    <tr style="vertical-align: top;">
      <td>Airport Origin</td>
      <td>:</td>
      <td colspan="2">{{ $shipment->unlocoOrigin->RL_PortName ?? "-" }}</td>
      <td>Cargo Out</td>
      <td>:</td>
      <td>
        @php
          if($shipment->estimatedExitDate){
            $keluar = \Carbon\Carbon::parse($shipment->estimatedExitDate);
            $showKeluar = $keluar->translatedFormat('d F Y');
          } elseif($shipment->SCAN_OUT_DATE) {
            $keluar = \Carbon\Carbon::parse($shipment->SCAN_OUT_DATE);
            $showKeluar = $keluar->translatedFormat('d F Y');
          } else {
            $keluar = null;
            $showKeluar = '-';
          }
        @endphp
        {{ $showKeluar }}
      </td>
    </tr>
    <tr style="vertical-align: top;">
      <td>Commodity</td>
      <td>:</td>
      <td colspan="2" style="max-width: 400px;">
        @forelse ($shipment->details as $detail)
          {{ Str::replace(',', ', ', $detail->UR_BRG) }} @if(!$loop->last) / @endif
        @empty          
        @endforelse
      </td>
      <td>Total Days</td>
      <td>:</td>
      <td>
        @php
            if($tiba && $keluar){
              $beda = $tiba->diffInDays($keluar);
            } else {
              $beda = 0;
            }
        @endphp
        {{ $beda + 1 }}
      </td>
    </tr>
    <tr style="vertical-align: top;">
      <td>No HAWB</td>
      <td>:</td>
      <td>{{ $shipment->NO_HOUSE_BLAWB ?? "-" }}</td>
      <td style="text-align: right; padding-right:50px;">        
        {{ $shipment->ShipmentNumber ?? "-" }}
      </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>    
  </table>
  <table class="table table-bordered" style="width: 100%;margin-top:5px;">
    <tr>
      <td colspan="4" style="border-right: none;">
        <b>CONSIGNEE : {{ $shipment->NM_PENERIMA ?? "-" }}</b>
      </td>
      <td colspan="2" style="border-left: none;">
        <b>NPWP &nbsp; : &nbsp; {{ $shipment->ID_PENERIMA ?? "-" }}</b>
      </td>
    </tr>
    <tr class="text-center">
      <td><b>No. AWB</b></td>
      <td><b>No. P.U/Pos</b></td>
      <td><b>FLIGHT</b></td>
      <td><b>CARGO IN</b></td>
      <td><b>HARI</b></td>
      <td><b>Coli - Kg/Ch. Weight</b></td>
    </tr>
    <tr>
      <td class="text-middle">
        {{ $shipment->mawb_parse ?? "-" }}
      </td>
      <td class="text-middle">
        {{ $shipment->NO_BC11 ?? "-" }}
      </td>
      <td class="text-middle">
        {{ $shipment->NO_FLIGHT ?? "-" }}
      </td>
      <td class="text-middle">
        @php
          $inDate = $shipment->SCAN_IN_DATE ?? $shipment->TGL_TIBA;
          $tglTiba = \Carbon\Carbon::parse($inDate)->translatedFormat('d F Y');
        @endphp
        {{ $tglTiba ?? "-" }}
      </td>
      <td class="text-middle">
        {{ $beda + 1 }}
      </td>
      <td>
        Coli : {{ $shipment->JML_BRG ?? 0 }}<br>
        KG : {{ $shipment->BRUTO ?? 0 }}<br>
        CH. Weight : {{ $shipment->ChargeableWeight ?? 0 }}
      </td>
    </tr>
  </table>
  <table class="table table-bordered" style="width: 100%;margin-top:20px;">
    <tr class="text-center">
      <td colspan="4"><b>KETERANGAN</b></td>
      <td><b>JUMLAH</b></td>
    </tr>
    <tr>
      <td colspan="4">
        <table>
          <tr style="border: none;">
            <td style="border: none;">Name (Special Rules)</td>
            <td style="border: none;">Rate/Days</td>
            <td class="text-right" style="border: none;">Ch. Weight</td>
            <td class="text-center" style="border: none;">Days</td>
          </tr>          
          @forelse ($shipment->estimatedTariff->where('is_vat', false) as $tariff)
            @php
              $rateShow = '';
              
              if($tariff->rate){
                
                if($tariff->rate < 1){
                  $rateShow = ($tariff->rate * 100);
                } else {
                  $rateShow = number_format($tariff->rate, 0, ',', '.');
                }
              }
            @endphp
            <tr style="border: none;">
              <td style="border: none;">{{ $tariff->item }}</td>
              <td style="border: none;">
                {{ ($rateShow == 0) ? '' : $rateShow }}
              </td>
              <td class="text-center" style="border: none;">
                @if($tariff->rate != $tariff->total && $tariff->item != 'Minimum Charge')
                  {{ $shipment->ChargeableWeight ?? 0 }}
                @endif
              </td>
              <td class="text-center" style="border: none;width:80px;">
                {{ ($tariff->days < 1) ? '' : $tariff->days }}
              </td>
            </tr>
          @empty            
          @endforelse
        </table>
      </td>
      <td>
        <table>
          <tr style="border: none;">
            <td style="border: none;">&nbsp;</td>
          </tr>
          @php
            $subTotal = 0;
            $min = $shipment->estimatedTariff->where('item', 'Minimum Charge')->sum('total') ?? 0;
          @endphp
          @forelse ($shipment->estimatedTariff->where('is_vat', false) as $tariff)
            @php
              if($tariff->days && $min > 0){
                $harga = 0;
              } else {
                $harga = $tariff->total;
              }
              $subTotal += $harga;
            @endphp
            <tr style="border: none;">
              <td class="text-right" style="border: none;">
                @php
                  
                @endphp
                {{ number_format($harga, 0, ',', '.') }}
              </td>
            </tr>
          @empty            
          @endforelse
        </table>
      </td>     
    </tr>
    <tr>
      <td colspan="4" class="text-center"><b>SUB TOTAL</b></td>
      <td class="text-right"><b>Rp. {{ number_format($subTotal, 0, ',','.') }}</b></td>
    </tr>
    @php
      $vatTariff = $shipment->estimatedTariff->where('is_vat', true)->first();
    @endphp
    <tr>
      <td colspan="4">
        <table>
          <tr style="border: none;">
            <td style="border: none;">VAT</td>
            <td class="text-right" style="border: none;">
              {{ ($shipment->schemaTariff->vat + 0) }} %
            </td>
            <td style="border: none;width:80px;"></td>
          </tr>
        </table>
      </td>
      <td class="text-right">{{ number_format(round(($vatTariff?->total ?? 0)), 0, ',', '.') }}</td>
    </tr>
    <tr>
      @php
        $grandTotal = $subTotal + round(($vatTariff?->total ?? 0));
      @endphp
      <td colspan="4" class="text-center"><b>TOTAL</b></td>
      <td class="text-right"><b>Rp. {{ number_format($grandTotal, 0, ',','.') }}</b></td>
    </tr>
    <tr>
      <td colspan="5" >
        @php
          $number = explode('.', $grandTotal);
          $nf2 = new \NumberFormatter('id_ID', \NumberFormatter::SPELLOUT);
        @endphp
        <table>
          <tr>
            <td style="width:60px;border:none;height:80px;">Terbilang</td>
            <td style="width: 4px;border:none;">:</td>
            <td style="border:none;">
              {{ Str::title($nf2->format($number[0])) }} rupiah
            </td>
          </tr>          
        </table>
      </td>
    </tr>
  </table>
  <div class="row" style="margin-top: 10px;">
    <div class="col-6">
      <p style="border-top: 1px solid black;border-bottom:1px solid black;width:75%;padding: 5px 0px 5px 0px;">
        Transfer Payment to:
      </p>
      <table class="table">        
        <tr>
          <td style="width: 27%;">Bank Name</td>
          <td style="width: 1%;">:</td>
          <td>Bank BNI</td>
        </tr>
        <tr>
          <td style="width: 27%;">Account Name</td>
          <td style="width: 1%;">:</td>
          <td>PT. PRIME FREIGHT INDONESIA</td>
        </tr>
        <tr>
          <td style="width: 27%;">Account Number</td>
          <td style="width: 1%;">:</td>
          <td>154-041-496 (IDR)</td>
        </tr>
      </table>      
    </div>
  </div>
  <table class="table" style="width: 100%;">
    <tr>
      <td style="width: 33.333333%;"></td>
      <td style="width: 33.333333%;"></td>
      <td style="width: 33.333333%;">
        Jakarta, {{ today()->translatedFormat('d F Y') }}<br><br><br><br><br><br><br><br>
        Warehouse Staff
      </td>
    </tr>
  </table>
  <footer>
    <div class="text-center text-primary">
      {{-- <img src="{{ public_path('img/footer.jpg') }}"
           alt="Logo Footer"
           class="footer-img"> --}}
      <p style="line-height: 95%;font-size:8pt;">
        <span><b>{{ $company->GC_Name ?? "-" }}</b></span><br>
        <span style="text-decoration: underline;">
          {{ $company->GC_Address1 ?? "-" }} {{ $company->GC_Address2 ?? "-" }}, {{ $company->GC_City ?? "-" }}, Tel : {{ $company->GC_Phone ?? "-" }}
        </span>
      </p>
    </div>    
  </footer>
</body>
</html>