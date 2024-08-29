<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Print Sewa Gudang - {{ $shipment->NO_HOUSE_BLAWB }}</title>
  <style>
    @page{ 
      margin: 0;   
      font-family: Verdana, Geneva, Tahoma, sans-serif;
      font-size: 12pt;
    }
    table{
      width: 100%;
      border-collapse: collapse;
    }
    .table th,
    .table td {
      padding: 0.15rem;
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
  </style>
</head>
<body>
  <table style="width: 100%; margin-top:70mm;">
    <tr style="vertical-align: top;">
      <td style="width: 12%;">Scheme</td>
      <td style="width: 1%;">:</td>
      <td colspan="2" style="width: 53%;">{{ $shipment->schemaTariff->name ?? "-" }}</td>
      <td style="width: 10%;">ISSUED</td>
      <td style="width: 1%;">:</td>
      <td style="width: 20%;">Jakarta, {{ today()->translatedFormat('d F Y') }}</td>
    </tr>
    <tr style="vertical-align: top;">
      <td>Document Number</td>
      <td>:</td>
      <td colspan="2">{{ $shipment->tariff_no ?? "-" }}</td>
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
      <td>Shipper</td>
      <td>:</td>
      <td colspan="2">{{ $shipment->NM_PENGIRIM ?? "-" }}</td>
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
      <td>Airport Origin</td>
      <td>:</td>
      <td colspan="2">{{ $shipment->unlocoOrigin->RL_PortName ?? "-" }}</td>
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
      <td>Commodity</td>
      <td>:</td>
      <td>
        @forelse ($shipment->details as $detail)
          {{ $detail->UR_BRG }} @if(!$loop->last) / @endif
        @empty          
        @endforelse
      </td>
      <td style="text-align: right; padding-right:50px;">
        {{-- @forelse ($shipment->details as $detail)
          {{ $detail->HS_CODE ?? 00000000 }} @if(!$loop->last) / @endif
        @empty          
        @endforelse --}}
        {{ $shipment->ShipmentNumber ?? "-" }}
      </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
    <tr style="vertical-align: top;">
      <td>No HAWB</td>
      <td>:</td>
      <td>{{ $shipment->NO_HOUSE_BLAWB ?? "-" }}</td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </table>
</body>
</html>