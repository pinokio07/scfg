@if($jenis == 'xls')
<table style="width: 100%;">
  <tr>
    <th colspan="26" style="text-align: center;height: 35px; background-color: #3A75C4;color:white;font-size:14pt;vertical-align:middle;">
      INVENTORY TPS {{ Str::upper($company->GC_Name) }}
    </th>
  </tr>
  <tr>
    <th colspan="26" style="text-align: center;height: 25px; background-color: #3A75C4;color:white;font-size:14pt;vertical-align:middle;">
      @if($mawb != '')
        BY MASTER {{ $items->first()->mawb_parse }}
      @else
      PERIODE {{ $start->translatedFormat('d F Y') }} - {{ $end->translatedFormat('d F Y') }} 
      @endif
    </th>
  </tr>
</table>
@endif
<table class="table table-sm table-bordered table-striped" style="width: 100%;">
  <thead>
    <tr>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        NO</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        KODE TPS</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        KODE GUDANG</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        NO FLIGHT</th>
      <th colspan="4" style="text-align: center; vertical-align:middle;">
        BC 1.1</th>
      <th colspan="3" style="text-align: center; vertical-align:middle;">
        PLP</th>
      <th colspan="2" style="text-align: center; vertical-align:middle;">
        Jumlah</th>
      <th colspan="2" style="text-align: center; vertical-align:middle;">
        Nomor</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Uraian Barang</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Consignee</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Alamat</th>
      <th colspan="3" style="text-align: center; vertical-align:middle;">
        Dokumen Kepabeanan</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        No SPPB</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Status</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Tanggal Masuk TPS</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Tanggal Keluar TPS</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Keterangan</th>
    </tr>
    <tr>
      <th style="text-align: center;">No</th>
      <th style="text-align: center;">Tanggal</th>
      <th style="text-align: center;">Pos</th>
      <th style="text-align: center;">Sub Pos</th>
      <th style="text-align: center;">Nomor</th>
      <th style="text-align: center;">Tanggal</th>
      <th style="text-align: center;">Segel</th>
      <th style="text-align: center;">Koli</th>
      <th style="text-align: center;">Bruto</th>
      <th style="text-align: center;">MAWB</th>
      <th style="text-align: center;">HAWB</th>
      <th style="text-align: center;">Jenis</th>
      <th style="text-align: center;">Nomor</th>
      <th style="text-align: center;">Tanggal</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $item)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>
          {{ optional(optional($item->master)->warehouseLine1)->tps_code ?? "-" }}
        </td>
        <td>
          {{ optional(optional($item->master)->warehouseLine1)->warehouse_code ?? "-" }}
        </td>
        <td>
          {{ $item->NO_FLIGHT ?? "-" }}
        </td>
        <td>
          {{ $item->NO_BC11 ?? "-" }}
        </td>
        <td style="white-space: nowrap !important;">
          @php
            $tgl = '-';
            if($item->TGL_BC11){
              $tgl = \Carbon\Carbon::parse($item->TGL_BC11)->format('d-m-Y');
            }
          @endphp
          {{ $tgl }}
        </td>
        <td>
          {{ $item->NO_POS_BC11 ?? "-" }}
        </td>
        <td>
          {{ $item->NO_SUBPOS_BC11 ?? "-" }}
        </td>
        <td>
          NO PLP
        </td>
        <td>
          TGL PLP
        </td>
        <td>
          {{ optional($item->master)->NO_SEGEL ?? "-" }}
        </td>
        <td>{{ $item->JML_BRG ?? "-" }}</td>
        <td>{{ $item->BRUTO ?? "-" }}</td>
        <td>{{ $item->mawb_parse ?? "-" }}</td>
        <td>{{ $item->NO_HOUSE_BLAWB ?? "-" }}</td>
        <td>
          @php
            $brg = '';
            $count = $item->details->count();

            if($count > 0){
              foreach ($item->details as $key => $detail) {
                $brg .= $detail->UR_BRG;
                (($key + 1) < $count) ? $brg .= ', ' : '';
              }
            }
          @endphp
          {{ $brg }}
        </td>
        <td>{{ $item->NM_PENERIMA ?? "-" }}</td>
        <td>
          {{ $item->AL_PENERIMA ?? "-" }}
        </td>
        <td>JENIS</td>
        <td>NOMOR</td>
        <td>TANGGAL</td>
        <td>NO SPPBS</td>
        <td>STATUS</td>
        <td>
          @php
            $tglMasukTps = '-';
            if($item->SCAN_IN_DATE){
              $tglMasukTps = \Carbon\Carbon::parse($item->SCAN_IN_DATE)->format('d-m-Y');
            }
          @endphp
          {{ $tglMasukTps }}
        </td>
        <td>
          @php
            $tglKeluarTps = '-';
            if($item->SCAN_OUT_DATE){
              $tglKeluarTps = \Carbon\Carbon::parse($item->SCAN_OUT_DATE)->format('d-m-Y');
            }
          @endphp
          {{ $tglKeluarTps }}
        </td>
        <td>
          {{ $item->AlasanTegah ?? "-" }}
        </td>
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>