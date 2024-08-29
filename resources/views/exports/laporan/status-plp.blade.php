<table>
  <tr>
    <th colspan="16" style="text-align: center;">LAPORAN STATUS PLP DI GUDANG " PT. PRIME INDONESIA"</th>
  </tr> 
  <tr>
    <th colspan="16" style="text-align: center;">PERIODE {{ Str::upper(\Carbon\Carbon::parse($start)->translatedFormat('d F Y')) }} - {{ Str::upper(\Carbon\Carbon::parse($end)->translatedFormat('d F Y')) }}</th>
  </tr>
  <tr>
    <th colspan="16"></th>
  </tr>
</table>
<table>
  <thead>
    <tr style="text-align: center;">
      <th style="border: 1px solid #0000;text-align:center;">
        NO
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        TGL TIBA
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        TGL MASUK TPS
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        NO PLP
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        TGL PLP
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        MASTER AWB
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        H AWB
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        JUMLAH KOLI
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        BERAT
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        CW
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        URAIAN BARANG
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        CONSIGNEE
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        TGL KELUAR
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        NOPEN
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        SPPB
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        KETERANGAN
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        JOBFILE
      </th>
      <th style="border: 1px solid #0000;text-align:center;">
        CONSOLIDATION
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
          @php
            if($item->TGL_TIBA){
              $tibaParse = \Carbon\Carbon::parse($item->TGL_TIBA)->translatedFormat('d-F-Y');
            } else {
              $tibaParse = '-';
            }
          @endphp
          {{ $tibaParse }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          @php
            if($item->SCAN_IN_DATE){
              $scanInParse = \Carbon\Carbon::parse($item->SCAN_IN_DATE)->translatedFormat('d-F-Y');
            } else {
              $scanInParse = '-';
            }
          @endphp
          {{ $scanInParse }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->master->PLPNumber ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          @php
            if($item->master->PLPDate){
              $plpParse = \Carbon\Carbon::parse($item->master->PLPDate)->translatedFormat('d-F-Y');
            } else {
              $plpParse = '-';
            }
          @endphp
          {{ $plpParse }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->mawb_parse ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->NO_HOUSE_BLAWB ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->JML_BRG ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->BRUTO ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->ChargeableWeight ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          @forelse ($item->details as $detail)
            {{ $detail->UR_BRG ?? "-" }}
            @if(!$loop->last)
            ;
            @endif
          @empty            
          @endforelse
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->NM_PENERIMA ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          @php
            if($item->SCAN_OUT_DATE){
              if($item->SCAN_OUT_DATE > $end){
                $scanOutParse = '-';
              } else {
                $scanOutParse = \Carbon\Carbon::parse($item->SCAN_OUT_DATE)->translatedFormat('d-F-Y');
              }              
            } else {
              $scanOutParse = '-';
            }
          @endphp
          {{ $scanOutParse }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->NO_DAFTAR_PABEAN ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->SPPBNumber ?? $item->NO_SPPB ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          @if($item->SCAN_OUT_DATE)
            @if($item->SCAN_OUT_DATE > $end)
            BELUM KELUAR
            @else
            SUDAH KELUAR
            @endif
          @else
          BELUM KELUAR
          @endif
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->ShipmentNumber ?? "-" }}
        </td>
        <td style="text-align: center;border: 1px solid #0000;">
          {{ $item->master->ConsolNumber ?? "-" }}
        </td>
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>