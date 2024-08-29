<table>
  <tr>
    <th colspan="10">LAPORAN BARANG {{ Str::title(Str::replace('-', ' ', $jenis)) }} - {{ Str::upper(\Carbon\Carbon::parse($start)->translatedFormat('d-m-Y')) }} s/d {{ Str::upper(\Carbon\Carbon::parse($end)->translatedFormat('d-m-Y')) }}</th>
  </tr>
</table>
<table>
  <thead>
    <tr style="text-align: center;">
      <th style="border: 1px solid #0000;background-color:#bababa;">
        No
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        No Master BL/AWB
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        No House BL/AWB
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        Job Number
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        Jumlah
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        Bruto
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        CW
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        Consignee
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        No Flight
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        No BC 1.1
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        Tanggal BC 1.1
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        Tanggal Masuk TPS
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        Tanggal Keluar TPS
      </th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $item)
      <tr>
        <td style="text-align: center;border: 1px solid #0000;">{{ $loop->iteration }}</td>
        <td style="border: 1px solid #0000;">
          {{ $item->mawb_parse ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_HOUSE_BLAWB ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->ShipmentNumber ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->JML_BRG ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->BRUTO ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->ChargeableWeight ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NM_PENERIMA ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_FLIGHT ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_BC11 ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">

          @if($item->TGL_BC11)
            {{ \Carbon\Carbon::parse($item->TGL_BC11)->format('d-m-Y') }}
          @else
          -
          @endif
        </td>
        <td style="border: 1px solid #0000;">
          @if($item->SCAN_IN_DATE)
            {{ \Carbon\Carbon::parse($item->SCAN_IN_DATE)->format('d-m-Y H:i:s') }}
          @else
          -
          @endif
        </td>
        <td style="border: 1px solid #0000;">
          @if($item->SCAN_OUT_DATE)
            {{ \Carbon\Carbon::parse($item->SCAN_OUT_DATE)->format('d-m-Y H:i:s') }}
          @else
          -
          @endif
        </td>
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>