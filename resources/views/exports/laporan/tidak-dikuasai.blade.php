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
      <th style="border: 1px solid #0000;vertical-align:middle;">
        NO
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        BC.11
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        TGL BC.11
      </th>
      <th style="border: 1px solid #0000;vertical-align:middle;">
        TGL GATE IN
      </th>
      <th style="border: 1px solid #0000;vertical-align:middle;">
        NO POS
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        NO VOYAGE
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        JUMLAH KEMASAN
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        BRUTO
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        NO BL
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        NO HOST_BL
      </th>
      <th style="border: 1px solid #0000;vertical-align:middle;">
        URAIAN BARANG
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        NAMA PEMILIK
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        ALAMAT PEMILIK
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        KET
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
            {{-- {{ \Carbon\Carbon::parse($item->TGL_BC11)->format('d-m-Y') }} --}}
            {{ $item->TGL_BC11->format('d-m-Y') }}
          @else
          -
          @endif
        </td>
        <td style="border: 1px solid #0000;">
          @if($item->SCAN_IN_DATE)
            {{-- {{ \Carbon\Carbon::parse($item->SCAN_IN_DATE)->format('d-m-Y H:i:s') }} --}}
            {{ $item->SCAN_IN_DATE->format('d-m-Y') }}
          @else
          -
          @endif
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_POS_BC11 ?? "-" }}
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
          {{ $item->mawb_parse ?? "-" }}
        </td>
        <td style="border: 1px solid #0000;">
          {{ $item->NO_HOUSE_BLAWB ?? "-" }}
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
          {{ config('app.tps.kode_tps') }}
        </td>
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>