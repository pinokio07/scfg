@if($jenis == 'xls')
<table style="width: 100%;">
  <tr>
    <th colspan="26" style="text-align: center;height: 35px; background-color: #3A75C4;color:white;font-size:14pt;vertical-align:middle;">
      DAFTAR BARANG DI TEGAH {{ Str::upper($company->GC_Name) }}
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
          {{ optional(optional($item->house->master)->warehouseLine1)->tps_code ?? "-" }}
        </td>
        <td>
          {{ optional(optional($item->house->master)->warehouseLine1)->warehouse_code ?? "-" }}
        </td>
        <td>
          {{ $item->house->NO_FLIGHT ?? "-" }}
        </td>
        <td>
          {{ $item->house->NO_BC11 ?? "-" }}
        </td>
        <td style="white-space: nowrap !important;">
          @php
            $tgl = '-';
            if($item->house->TGL_BC11){
              $tgl = \Carbon\Carbon::parse($item->house->TGL_BC11)->format('d-m-Y');
            }
          @endphp
          {{ $tgl }}
        </td>
        <td>
          {{ $item->house->NO_POS_BC11 ?? "-" }}
        </td>
        <td>
          {{ $item->house->NO_SUBPOS_BC11 ?? "-" }}
        </td>
        <td>
          NO PLP
        </td>
        <td>
          TGL PLP
        </td>
        <td>
          {{ optional($item->house->master)->NO_SEGEL ?? "-" }}
        </td>
        <td>{{ $item->Koli ?? "-" }}</td>
        <td>{{ $item->Bruto ?? "-" }}</td>
        <td>{{ $item->house->mawb_parse ?? "-" }}</td>
        <td>{{ $item->HAWBNumber ?? "-" }}</td>
        <td>
          @php
            $brg = '';
            $count = $item->house->details->count();

            if($count > 0){
              foreach ($item->house->details as $key => $detail) {
                $brg .= $detail->UR_BRG;
                (($key + 1) < $count) ? $brg .= ', ' : '';
              }
            }
          @endphp
          {{ $brg }}
        </td>
        <td>{{ $item->Consignee ?? "-" }}</td>
        <td>
          {{ $item->house->AL_PENERIMA ?? "-" }}
        </td>
        <td>JENIS</td>
        <td>NOMOR</td>
        <td>TANGGAL</td>
        <td>NO SPPBS</td>
        <td>STATUS</td>
        <td>
          @php
            $tglMasukTps = '-';
            if($item->house->SCAN_IN_DATE){
              $tglMasukTps = \Carbon\Carbon::parse($item->house->SCAN_IN_DATE)->format('d-m-Y');
            }
          @endphp
          {{ $tglMasukTps }}
        </td>
        <td>
          @php
            $tglKeluarTps = '-';
            if($item->house->SCAN_OUT_DATE){
              $tglKeluarTps = \Carbon\Carbon::parse($item->house->SCAN_OUT_DATE)->format('d-m-Y');
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