@if($jenis == 'xls')
<table style="width: 100%;">
  <tr>
    <th colspan="20" style="text-align: center; background-color: #3A75C4;color:white;font-size:14pt;vertical-align:middle;">
      DASHBOARD MASTER BARANG TIMBUN / INVENTORY
    </th>
  </tr>
  <tr>
    <th colspan="20" style="text-align: center;background-color: #3A75C4;color:white;font-size:14pt;vertical-align:middle;">
      TPS {{ Str::upper($company->GC_Name) }}
    </th>
  </tr>
  <tr>
    <th colspan="20" style="text-align: center;background-color: #3A75C4;color:white;font-size:14pt;vertical-align:middle;">
      PERIODE {{ $start->translatedFormat('d F Y') }} - {{ $end->translatedFormat('d F Y') }}
    </th>
  </tr>
</table>
@endif
<table class="table table-sm table-bordered table-striped" style="width: 100%;">
  <thead>
    <tr>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        NO</th>      
      <th colspan="3" style="text-align: center; vertical-align:middle;">
        BC 1.1</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
          Sarana Pengangkut</th>
      <th colspan="3" style="text-align: center; vertical-align:middle;">
        PLP</th>
      <th colspan="2" style="text-align: center; vertical-align:middle;">
        Jumlah</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        MAWB</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Nama Pemberitahu</th>
      <th colspan="6" style="text-align: center; vertical-align:middle;">
          Jumlah Gate</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Tanggal Masuk TPS</th>
      <th rowspan="2" style="text-align: center; vertical-align:middle;">
        Keterangan</th>
    </tr>
    <tr>
      <th style="text-align: center;">No</th>
      <th style="text-align: center;">Tanggal</th>
      <th style="text-align: center;">Pos</th>
      <th style="text-align: center;">Nomor</th>
      <th style="text-align: center;">Tanggal</th>
      <th style="text-align: center;">Segel</th>
      <th style="text-align: center;">Koli</th>
      <th style="text-align: center;">Bruto</th>
      <th style="text-align: center;">CN Total</th>
      <th style="text-align: center;">Gate In</th>
      <th style="text-align: center;">SPPB</th>
      <th style="text-align: center;">Gate Out</th>
      <th style="text-align: center;">Pending</th>
      <th style="text-align: center;">Current Now</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $item)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $item->PUNumber ?? "-" }}</td>
        <td>
          @php
            $tglPu = '-';
            if($item->PUDate){
              $tglPu= \Carbon\Carbon::parse($item->PUDate)->format('d-m-Y');
            }
          @endphp  
          {{ $tglPu }}
        </td>
        <td>{{ $item->POSNumber ?? "-" }}</td>
        <td>{{ $item->FlightNo ?? "-" }}</td>
        <td>NO PLP</td>
        <td>TGL PLP</td>
        <td>{{ $item->NO_SEGEL ?? "-" }}</td>
        <td>{{ $item->mNoOfPackages ?? "-" }}</td>
        <td>{{ $item->mGrossWeight ?? "-" }}</td>
        <td>{{ $item->mawb_parse ?? "-" }}</td>
        <td>{{ $item->NM_PEMBERITAHU ?? "-" }}</td>
        <td style="text-align: center;">
          {{ $item->houses->count() }}
        </td>
        <td style="text-align: center;">
          {{ $item->houses->where('SCAN_IN', 'Y')->count() }}
        </td>
        <td style="text-align: center;">
          {{ $item->houses->sum('sppb_count') }}
        </td>
        <td style="text-align: center;">
          {{ $item->houses->whereNull('SCAN_OUT')
                          ->where('sppb_count', 0)
                          ->count() }}
        </td>
        <td style="text-align: center;">
          {{ $item->houses->where('SCAN_OUT', 'Y')->count() }}
        </td>
        <td style="text-align: center;">
          {{ $item->houses->where('SCAN_IN', 'Y')
                          ->whereNull('SCAN_OUT')
                          ->count() }}
        </td>
        <td>
          @php
            $tglMasuk = '-';
            if($item->MasukGudang){
              $tglMasuk = \Carbon\Carbon::parse($item->MasukGudang)->format('d-m-Y');
            }
          @endphp
          {{ $tglMasuk }}
        </td>
        @php
          $info = '';
          $class = '';

          if($item->houses->sum('active_tegah_count') > 0){
            $info = 'Restricted';
            $color = '#dc3545';
          } else if($item->IsCompleted == true){
            $info = 'Completed';
            $color = '#28a745';
          }
        @endphp
        <td style="color: $color !important;">
          {{ $info }}
        </td>
      </tr>      
    @empty
      
    @endforelse
  </tbody>
</table>