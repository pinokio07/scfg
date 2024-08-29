<table>
  <tr>
    <th colspan="10">Laporan Konfirmasi Data Timbun - {{ Str::upper(\Carbon\Carbon::parse($start)->translatedFormat('d-m-Y')) }} s/d {{ Str::upper(\Carbon\Carbon::parse($end)->translatedFormat('d-m-Y')) }}</th>
  </tr>
</table>
<table>
  <thead>
    <tr style="text-align: center;">
      <th style="border: 1px solid #0000;background-color:#bababa;">
        NO
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        KODE TPS
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        KODE GUDANG
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        SARANA PENGANGKUT
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        NO BC11
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        TGL BC11
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        NO POS
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        NO SUB POS
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        NO PLP
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        TGL PLP
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        NO SEGEL
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        JUMLAH KOLI
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        BERAT BRUTO/CW (Kg)
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        MAWB
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        HAWB
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        JOB FILE
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        URAIAN BARANG
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        CONSIGNEE
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        ALAMAT
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        JENIS
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        NO PABEAN
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        TGL PABEAN
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        NO SPPB
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        STATUS
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        WAKTU MASUK TPS
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        WAKTU KELUAR TPS
      </th>
      <th style="border: 1px solid #0000;background-color:#bababa;">
        KETERANGAN
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
          {{ config('app.tps.kode_tps') }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ config('app.tps.kode_gudang') }}
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
          {{ $item->NO_POS_BC11 ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_SUBPOS_BC11 ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->master->PLPNumber ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          @php
              $plpdate = optional($item->master)->PLPDate;
          @endphp
          @if($plpdate)
            {{ \Carbon\Carbon::parse($plpdate)->format('d-m-Y') }}
          @else
          -
          @endif
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->master->NO_SEGEL ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->JML_BRG ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->BRUTO ?? "-" }} / {{ $item->ChargeableWeight ?? "-" }}
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
        <td style="border: 1px solid #0000;">
          {{ optional($item->jenisAju)->name ?? $item->JNS_AJU ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_DAFTAR_PABEAN ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          @if($item->TGL_DAFTAR_PABEAN)
            {{ \Carbon\Carbon::parse($item->TGL_DAFTAR_PABEAN)->format('d-m-Y') }}
          @else
          -
          @endif
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->SPPBNumber ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          @if($item->SCAN_OUT_DATE)
          SUDAH
          @else
          BELUM
          @endif
          KELUAR
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
        <td style="border: 1px solid #0000;"></td>
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>