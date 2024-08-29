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
      font-size: 9pt;
      margin-top: 20mm !important;
      margin-left: 5mm !important;
    }
    table{
      width: 100%;
      border-collapse: collapse;
    }
    .table th,
    .table td {
      padding: 0.75rem;
      vertical-align: top;
      /* border-top: 1px solid #565656; */
    }

    .table thead th {
      vertical-align: bottom;
      /* border-bottom: 2px solid #565656; */
    }

    .table tbody + tbody {
      /* border-top: 2px solid #565656; */
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
  </style>
</head>
<body>
  <table style="width: 100%;">
    <tr>
      <td style="width: 25%;height:18mm;padding:2mm;">
        {{-- @php
            $imgPath = public_path('/img/companies/'.$company->GC_Logo);
            if(is_dir($imgPath) || !file_exists($imgPath)){
              $imgPath = public_path('/img/default-logo-light.png');
            }
          @endphp
          <img src="{{ $imgPath }}" alt="Company Logo"
                height="50"> --}}
      </td>
      <td style="width: 75%; text-align:right;padding:2mm;font-weight:bolder;line-height:100%;">
      </td>
    </tr>
  </table>
  <div style="text-align: center;margin-top:10px;font-size:10pt;">
    <span><h4 style="margin:0 auto;">&nbsp;</h4></span>
    <span><h4 style="margin: 0 auto;">&nbsp;</h4></span>
  </div>
  <table style="width: 100%;">
    <tr>
      <td style="width: 15%;">&nbsp;</td>
      <td style="width: 50%">
        {{ $shipment->NM_PENERIMA }}
      </td>
      <td style="width:10%;">
        <b>&nbsp;</b> 
      </td>
      <td style="width:15%;">
        {{ $shipment->DOID ?? "-" }}
      </td>
    </tr>
  </table>
  <table class="table" style="margin-top:25px;">
    <tr style="text-align: center;">
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr style="text-align: center;">
      <td style="height: 200px;padding:0.5em !important;width:120px;">
        {{ $shipment->mawb_parse ?? "-" }} <br>
        {{ $shipment->NO_HOUSE_BLAWB ?? "-" }}
      </td>
      <td style="width:120px;padding-left:40px;">
        {{ $shipment->NO_BC11 ?? "-" }} / {{ $shipment->NO_POS_BC11 ?? "-" }}
      </td>
      <td style="width:100px;">
        {{ $shipment->JML_BRG ?? 0 }} <br>
        GW {{ $shipment->BRUTO ?? 0 }} Kgs<br>
        CW {{ $shipment->ChargeableWeight ?? "-" }} Kgs<br>
      </td>
      <td style="text-align: left !important;width:120px;">
        @forelse ($shipment->details as $detail)
          {{ $detail->UR_BRG }}
          @if(!$loop->last)
          <br>
          @endif
        @empty          
        @endforelse
      </td>
      <td>
        {{ $shipment->NO_FLIGHT ?? "-" }} <br>
        @if($shipment->TGL_TIBA)
        {{ \Carbon\Carbon::parse($shipment->TGL_TIBA)->translatedFormat('d F Y') }}
        @endif
      </td>
    </tr>
  </table>
  <div style="margin-top: 15px;font-size:10pt;">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $shipment->SPPBNumber }}
  </div>
  <div>
    <p style="margin:0 auto;">
      &nbsp;
    </p>
    <p style="margin: 0 auto;">
      &nbsp;
    </p>
  </div>
  <table class="ttd" style="width: 100%;margin-top:15px;">
		<tr>
			<td></td>
			<td style="width: 100mm;"></td>
			<td style="padding-left:180px; width:60mm;">
        @if($shipment->DODATE)
        {{ \Carbon\Carbon::parse($shipment->DODATE)->translatedFormat('d F Y') }}
        @else
        {{ today()->translatedFormat('d F Y') }}
        @endif
      </td>
		</tr>
		<tr>
			<td style="vertical-align: top;">&nbsp;</td>
			<td></td>
			<td>&nbsp;</td>
		</tr>
	</table>
</body>
</html>