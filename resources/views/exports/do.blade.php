<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Print DO - {{ $shipment->NO_HOUSE_BLAWB }}</title>
  <style>
    @page{    
      font-family: Verdana, Geneva, Tahoma, sans-serif;
      font-size: 8pt;
    }
    table{
      width: 100%;
      border-collapse: collapse;
    }
    .table th,
    .table td {
      padding: 0.35rem;
      vertical-align: top;
      border-top: 1px solid #565656;
    }

    .table thead th {
      vertical-align: bottom;
      border-bottom: 2px solid #565656;
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
    .tbl-hdr{
      text-align: center;
      background-color: #9fa5aa!important;
      opacity: .65;
    }
    .border{
      border:1px solid black;
    }
    hr.s9 {
      background-color:white;
      margin:0 0 15px 0;
      border-width:0;
      width: 100% !important;
      height:2px;
      border-top:.5px solid black;
      border-bottom:.5px solid black;
    }
  </style>
</head>
<body>
  <table style="width: 100%;">
    <tr>
      <td style="width: 15%;height:18mm;padding:2mm;">
        @php
            $imgPath = public_path('/img/companies/'.$company->GC_Logo);
            if(is_dir($imgPath) || !file_exists($imgPath)){
              $imgPath = public_path('/img/default-logo-light.png');
            }
          @endphp
          <img src="{{ $imgPath }}" alt="Company Logo"
               class="img-fluid"
               height="30">
      </td>
      <td style="width: 55%; text-align:center;padding:2mm;line-height:100%;">
          <span style="font-weight: bolder;">
            <b>{{ $company->GC_Name }}</b>
          </span>
          <br>
          <span>
            {{ $company->GC_Address1 }}
          {{ $company->GC_Address2 }}, Kota {{ $company->GC_City }}
          </span>
          <br>
          Tel. : {{ $company->GC_Phone }}
          @if($company->GC_Phone2)
          <br>
          Tel. : {{ $company->GC_Phone2 }}
          @endif
      </td>
      <td style="width: 30%; ;padding:2mm;font-weight:bolder;line-height:100%;">
        <div style="padding: 4px 2px;background-color:#6c757d!important;opacity:.65;" class="border">
          <span style="text-align:left;">No: </span>
          <span style="float:right;text-align:right;">{{ $shipment->DOID ?? "-" }}</span>
        </div>
      </td>
    </tr>
  </table>
  <div style="text-align: center;margin-top:20px;font-size:10pt;margin-bottom:10px;">
    <span><h4 style="text-decoration: underline; margin:0 auto;">PENYERAHAN DOKUMEN BARANG</h4></span>
    <span><h4 style="margin: 0 auto;">DOCUMENT DELIVERY ORDER</h4></span>
  </div>
  <hr class="s9">
  <table class="table table-bordered" style="width: 100%;">
    <tr class="tbl-hdr">
      <td style="width: 13%;vertical-align: middle;">
        Date
      </td>
      <td style="width: 13%;vertical-align: middle;">
        Origin
      </td>
      <td style="width: 13%;vertical-align: middle;">
        Destination
      </td>
      <td style="width: 13%;vertical-align: middle;">
        Pcs/Colly
      </td>
      <td style="width: 13%;vertical-align: middle;">
        GW (Kg)
      </td>
      <td style="width: 13%;vertical-align: middle;">
        CW (Kg)
      </td>
      <td style="width: 22%;vertical-align: middle;">
        Dimension <br>
        (L*W*H in Cm)
      </td>
    </tr>
    <tr style="text-align: center;">
      <td>
        @if($shipment->TGL_TIBA)
        {{ \Carbon\Carbon::parse($shipment->TGL_TIBA)->translatedFormat('d/m/Y') }}
        @else
        -
        @endif
      </td>
      <td>
        {{ $shipment->KD_PEL_MUAT ?? "-" }}
      </td>
      <td>
        {{ $shipment->KD_PEL_BONGKAR ?? "-" }}
      </td>
      <td>
        {{ $shipment->JML_BRG ?? 0 }}
      </td>
      <td>
        {{ $shipment->BRUTO ?? 0 }}
      </td>
      <td>
        {{ $shipment->ChargeableWeight ?? "-" }}
      </td>
      <td>
        {{ $shipment->VOLUME ?? "-" }}
      </td>
    </tr>
    <tr class="tbl-hdr">
      <td colspan="2" style="text-align: center;">
        MAWB
      </td>
      <td colspan="2" style="text-align: center;">
        HAWB
      </td>
      <td colspan="3" style="text-align: center;">
        COMMODITY
      </td>
    </tr>
    <tr>
      <td colspan="2" style="text-align: center;">
        {{ $shipment->mawb_parse ?? "-" }}
      </td>
      <td colspan="2" style="text-align: center;">
        {{ $shipment->NO_HOUSE_BLAWB ?? "-" }}
      </td>
      <td colspan="3" style="text-align: center;">
        @forelse ($shipment->details as $detail)
          {{ $detail->UR_BRG }}
          @if(!$loop->last)
          ;
          @endif
        @empty          
        @endforelse
      </td>
    </tr>
    <tr class="tbl-hdr">
      <td colspan="4">
        SHIPPER
      </td>
      <td colspan="3">
        CONSIGNEE
      </td>
    </tr>    
    <tr class="tbl-hdr">
      <td style="text-align: left !important;"><small>Company Name</small></td>
      <td colspan="3" 
          style="background-color: white !important; text-align:left !important;">
        {{ $shipment->NM_PENGIRIM ?? "-" }}
      </td>
      <td style="text-align: left !important;"><small>Company Name</small></td>
      <td colspan="2" 
          style="background-color: white !important; text-align:left !important;">
        {{ $shipment->NM_PENERIMA ?? "-" }}
      </td>
    </tr>
    <tr class="tbl-hdr">
      <td style="text-align: left !important;"><small>Address</small></td>
      <td colspan="3" 
          style="background-color: white !important;height:100px;text-align: left !important;">
        {{ $shipment->AL_PENGIRIM ?? "-" }}
      </td>
      <td style="text-align: left !important;"><small>Address</small></td>
      <td colspan="2" 
          style="background-color: white !important;height:100px;text-align: left !important;">
        {{ $shipment->AL_PENERIMA ?? "-" }}
      </td>
    </tr>
    <tr class="tbl-hdr">
      <td colspan="3" rowspan="2"
          style="background-color: white !important;text-align: left !important;">
        <div>REMARKS</div>
        <div></div>
      </td>
      <td colspan="2">
        Warehouse Operator Signature <br>
        TTD OPERATOR GUDANG
      </td>
      <td colspan="2">
        Receiver Signature <br>
        TANDA TANGAN PENERIMA
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height: 100px;vertical-align:bottom;text-align:center;">
        <u>{{ Str::upper(auth()->user()->name) }}</u>
      </td>
      <td colspan="2" style="height: 100px;"></td>
    </tr>
  </table>  
</body>
</html>