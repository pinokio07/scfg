<table>
  <tr>
    <th colspan="13" style="text-align: center;">DAFTAR BARANG TIMBUNAN</th>
  </tr>
  <tr>
    <th colspan="13" style="text-align: center;">YANG DITIMBUN MELEWATI JANGKA WAKTU TIMBUN</th>
  </tr>
  <tr>
    <th colspan="13" style="text-align: center;">TPS PT. PRIME FREIGHT INDONESIA</th>
  </tr>
  <tr>
    <th colspan="13" style="text-align: center;">PERIODE TIMBUN {{ Str::upper(\Carbon\Carbon::parse($end)->translatedFormat('F Y')) }}</th>
  </tr>
</table>
<table>
  <thead>
    <tr style="text-align: center;">
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        NO
      </th>
      <th colspan="3" style="border: 1px solid #0000;text-align:center;">
        BC.11
      </th>
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        TANGGAL TIMBUN
      </th>
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        SARANA PENGANGKUT
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        KEMASAN
      </th>
      <th colspan="2" style="border: 1px solid #0000;text-align:center;">
        BERAT
      </th>
      <th colspan="3" style="border: 1px solid #0000;text-align:center;">
        NOMOR AWB
      </th>
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        URAIAN BARANG
      </th>
      <th colspan="2" style="border: 1px solid #0000;text-align:center;">
        IMPORTIR
      </th>
    </tr>
    <tr>
      <th style="border: 1px solid #0000;text-align:center;">
        NOMOR
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        TANGGAL
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        POS
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        JUMLAH
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        BRUTO
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        CW
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        MAWB
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        HAWB
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        JOB FILE
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        NAMA
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        ALAMAT
      </th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $item)
      <tr>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $loop->iteration }}
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
          {{ $item->NO_POS_BC11 ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          @if($item->SCAN_IN_DATE)
            {{ \Carbon\Carbon::parse($item->SCAN_IN_DATE)->format('d-m-Y H:i:s') }}
          @else
          -
          @endif
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_FLIGHT ?? "-" }}
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
          {{ $item->mawb_parse ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_HOUSE_BLAWB ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->ShipmentNumber ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ optional($item->details)->first()->UR_BRG ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NM_PENERIMA ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->AL_PENERIMA ?? "-" }}
        </td>
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>