<table>
  <tr>
    <th colspan="13" style="text-align: center;">DAFTAR REKAPITULASI PEMINDAHAN LOKASI PENIMBUNAN</th>
  </tr>
  <tr>
    <th colspan="13" style="text-align: center;">TPS PT. PRIME FREIGHT INDONESIA</th>
  </tr>
  <tr>
    <th colspan="13" style="text-align: center;">TANGGAL {{ Str::upper(\Carbon\Carbon::parse($start)->translatedFormat('d F Y')) }} - {{ Str::upper(\Carbon\Carbon::parse($end)->translatedFormat('d F Y')) }}</th>
  </tr>
</table>
<table>
  <thead>
    <tr style="text-align: center;">
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        NO
      </th>
      <th colspan="3" style="border: 1px solid #0000;text-align:center;">
        KEMASAN
      </th>
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        MASTER BL/AWB
      </th>
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        JOB FILE
      </th>
      <th colspan="3" style="border: 1px solid #0000;text-align:center;">
        BC.11
      </th>
      <th colspan="2" style="border: 1px solid #0000;text-align:center;">
        PLP
      </th>
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        TPS ASAL
      </th>
      <th rowspan="2" style="border: 1px solid #0000;vertical-align:middle;">
        TANGGAL MASUK
      </th>
    </tr>
    <tr>
      <th style="border: 1px solid #0000;text-align:center;">
        JUMLAH
      </th>
      <th style="border: 1px solid #0000;vertical-align:middle;">
        BERAT
      </th>
      <th style="border: 1px solid #0000;vertical-align:middle;">
        CW
      </th>
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
        NO
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        TANGGAL
      </th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $item)
      <tr>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $loop->iteration }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->mNoOfPackages ?? 0 }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->mGrossWeight ?? 0 }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->mChargeableWeight ?? 0 }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->mawb_parse ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->ShipmentNumber ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->PUNumber ?? "-" }}
        </td>        
        <td style="border: 1px solid #0000;">
          @if($item->PUDate)
            {{ \Carbon\Carbon::parse($item->PUDate)->translatedFormat('d F Y') }}
          @else
          -
          @endif
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->POSNumber ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->PLPNumber ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          @if($item->PLPDate)
            {{ \Carbon\Carbon::parse($item->PLPDate)->translatedFormat('d F Y') }}
          @else
          -
          @endif
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ optional($item->warehouseLine1)->company_name ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          @if($item->MasukGudang)
            {{ \Carbon\Carbon::parse($item->MasukGudang)->translatedFormat('d F Y') }}
          @else
          -
          @endif
        </td>
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>