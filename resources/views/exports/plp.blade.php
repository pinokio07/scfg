<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>PLP - {{ $plp->NO_PLP ?? "-" }}</title>
  <style>
    @page{
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 11pt;
    }
    p{
      line-height: 120%;
    }
    .text-right{
      text-align: right !important;
    }
    .text-center{
      text-align: center !important;
    }
    .text-bold{
      font-weight: bold;
    }
    .text-small{
      font-size:10pt;
    }
    .text-smaller{
      font-size: 9pt;
    }
    table{
      width: 100%;
      border-collapse: collapse;
    }
    .table th,
    .table td {
      padding: 0.20rem;
      vertical-align: top;
      border-top: 1px solid #565656;
    }

    .table thead th {
      vertical-align: bottom;
      border-bottom: 1px solid #565656;
    }

    .table tbody + tbody {
      border-top: 1px solid #565656;
    }
    .table-bordered th,
    .table-bordered td {
      border: 1px solid #000000;
    }

    .table-bordered thead th,
    .table-bordered thead td {
      border-bottom-width: 1px;
    }
    .no-border th,
    .no-border td {
      border: none !important;
    }
    .w-1{
      width: 1% !important;
    }
  </style>
</head>
<body>
  <?php $master = $plp->master; $company = activeCompany(); ?>
  <table>
    <tr>
      <td style="text-align:right;">
        @php
            $imgPath = public_path('/img/companies/'.$company->GC_Logo);
            if(is_dir($imgPath) || !file_exists($imgPath)){
              $imgPath = public_path('/img/default-logo-light.png');
            }
          @endphp
          <img src="{{ $imgPath }}" alt="Company Logo"
                height="50">
      </td>
    </tr>
  </table>
  <table style="width: 100%;margin-top:20px;">
    <tr>
      <td>Nomor</td>
      <td>:</td>
      <td>{{ $plp->NO_SURAT ?? "-" }}</td>
      <td class="text-right">{{ today()->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
      <td>Lampiran</td>
      <td>:</td>
      <td>Surat Pernyataan, Fotocopy MAWB, HAWB, Manifes,</td>
      <td></td>
    </tr>
    <tr>
      <td>Hal</td>
      <td>:</td>
      <td>{{ ($plp->pembatalan == true) ? 'Pembatalan' : 'Permohonan' }} Pindah Lokasi Penimbunan</td>
      <td></td>
    </tr>
  </table>
  <pre style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">
Yth. Kepala KPU Bea dan Cukai Tipe C Soekarno - Hatta
      u.p. Kepala Bidang Pelayanan dan Fasilitas Pabean dan Cukai II </pre>
    <p style="text-indent: 20mm;">Dengan ini kami mengajukan {{ ($plp->pembatalan == true) ? 'pembatalan' : 'permohonan' }} Pindah Lokasi Penimbunan barang impor yang
    belum diselesaikan kewajiban pabeannya (PLP) sebagai berikut :</p>
    <table style="width:100%;">
      <tr>
        <td style="width: 50px;">BC 1.1</td>
        <td>:</td>
        <td style="width: 150px !important">{{ $plp->NO_BC11 ?? "-" }}</td>
        <td style="width: 80px;">POS BC 1.1</td>
        <td>:</td>
        <td style="width: 150px !important">{{ $master->POSNumber ?? "-" }}</td>
        <td style="text-align:right;">Tanggal</td>
        <td>:</td>
        <td>
          @if($plp->TGL_BC11)
          {{ \Carbon\Carbon::parse($plp->TGL_BC11)->translatedFormat('d F Y') }}
          @else
          {{ today()->translatedFormat('d F Y') }}
          @endif
        </td>
      </tr>
    </table>
    <table class="table table-bordered" style="margin-top: 20px;">
      <tr class="text-bold text-center">
        <td rowspan="2">No.<br> Urut</td>
        <td colspan="2">Kemasan</td>
        <td colspan="2">Dokumen AWB</td>
        <td rowspan="2">Keputusan <br> Pejabat BC</td>
      </tr>
      <tr class="text-bold text-center">
        <td>Jenis</td>
        <td>Jumlah</td>
        <td>Nomor</td>
        <td>Tanggal</td>
      </tr>
      <tr class="text-center">
        <td>1</td>
        <td>Consolidations</td>
        <td>
          {{ $master->mNoOfPackages ?? 0 }} Colly / {{ $master->mGrossWeight ?? 0 }} Kg
        </td>
        <td>
          {{ $master->mawb_parse ?? "-" }}
        </td>
        <td>
          @if($master->MAWBDate)
          {{ \Carbon\Carbon::parse($master->MAWBDate)->translatedFormat('d F Y') }}
          @else
          -
          @endif
        </td>
        <td>
          @if($plp->FL_SETUJU == 'Y')
          Disetujui
          @elseif($plp->FL_SETUJU == 'T')
          Tidak Disetujui
          @else
          -
          @endif          
        </td>
      </tr>
      <tr class="text-center">
        <td colspan="6">&nbsp;</td>
      </tr>
    </table>
    <table>
      <tr>
        <td style="width: 80px;">TPS Asal</td>
        <td class="w-1">:</td>
        <td style="width: 280px;">{{ $master->warehouseLine1->company_name ?? "-" }}</td>
        <td style="width: 60px;">kode</td>
        <td class="w-1">:</td>
        <td style="width: 90px;">{{ $plp->GUDANG_ASAL ?? "-" }}</td>
        <td style="width: 60px;">YOR/SOR</td>
        <td class="w-1">:<td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; %</td>
      </tr>
      <tr>
        <td style="width: 80px;">TPS Tujuan</td>
        <td class="w-1">:</td>
        <td style="width: 320px;">{{ \Str::upper($company->GC_Name) }}</td>
        <td style="width: 60px;">kode</td>
        <td class="w-1">:</td>
        <td style="width: 90px;">{{ $plp->GUDANG_TUJUAN ?? "-" }}</td>
        <td style="width: 60px;">YOR/SOR</td>
        <td class="w-1">:<td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; %</td>
      </tr>
      <tr>
        <td style="width: 80px;">Alasan</td>
        <td class="w-1">:</td>
        <td colspan="7">
          @if($plp->pembatalan == true)
          {{ $plp->ALASAN_BATAL ?? "-" }}
          @else
          Barang Impor Konsolidasi dalam 1(satu) Masterairway Bill atau Master Bill of Lading.
          @endif
        </td>
      </tr>      
    </table>
    <p>Demikian kami sampaikan untuk dapat pertimbangan.</p>
    <table>
      <tr>
        <td></td>
        <td>Pemohon,<br><br><br><br></td>
      </tr>
      <tr>
        <td>Keputusan Pejabat Bea dan Cukai :</td>
        <td>{{ $plp->NM_PEMOHON ?? "-" }}</td>
      </tr>
    </table>
    <table style="width:50%;">
      <tr>
        <td style="width:80px;">Nomor</td>
        <td class="w-1">:</td>
        <td>{{ $plp->NO_PLP ?? "-" }}</td>
      </tr>
      <tr>
        <td style="width:80px;">Tanggal</td>
        <td class="w-1">:</td>
        <td>
          @if(strtotime($plp->TGL_PLP))
          {{ \Carbon\Carbon::parse($plp->TGL_PLP)->translatedFormat('d F Y') }}
          @else
          -
          @endif
        </td>
      </tr>
    </table>
    <p style="line-height:100%;padding-bottom:0px;margin-bottom:0px;">a.n. Kepala Kantor</p>
    <table style="width:30%;">
      <tr class="text-center">
        <td>Kepala Seksi Administrasi <br><br><br></td>
      </tr>
      <tr class="text-center">
        <td>...........................................</td>
      </tr>
      <tr>
        <td style="padding-left:20px;">NIP. </td>
      </tr>
    </table>
    <table class="table table-bordered">
      <tr>
        <td style="width: 280px; padding:0.50rem;">
          <table class="no-border">
            <tr>
              <td colspan="3">Pengeluaran dari TPS Asal :</td>
            </tr>
            <tr>
              <td style="width: 80px;">Tanggal</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td style="width: 80px;">Pukul</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td style="width: 80px;">No Segel</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3">Pejabat Bea dan Cukai :</td>
            </tr>
            <tr>
              <td style="width: 80px;">Nama</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td style="width: 80px;">NIP</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td style="width: 80px;">Tanda</td>
              <td class="w-1">:</td>
              <td><br><br><br></td>
            </tr>
          </table>
        </td>
        <td style="border-top: none;border-bottom:none;"></td>
        <td style="width: 280px; padding:0.50rem;">
          <table class="no-border">
            <tr>
              <td colspan="3">Pemasukan ke TPS Tujuan :</td>
            </tr>
            <tr>
              <td style="width: 80px;">Tanggal</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td style="width: 80px;">Pukul</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td style="width: 80px;">&nbsp;</td>
              <td class="w-1"></td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3">Pejabat Bea dan Cukai :</td>
            </tr>
            <tr>
              <td style="width: 80px;">Nama</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td style="width: 80px;">NIP</td>
              <td class="w-1">:</td>
              <td></td>
            </tr>
            <tr>
              <td style="width: 80px;">Tanda</td>
              <td class="w-1">:</td>
              <td><br><br><br></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <p>*) Coret yang tidak perlu / diisi oleh Pejabat Bea dan Cukai</p>
</body>
</html>