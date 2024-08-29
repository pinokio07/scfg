<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Label for {{ $house->NO_BARANG }}</title>  

  {{-- <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Inconsolata:wdth,wght@50,600&display=swap" rel="stylesheet"> --}}
 
  <style>
    @page{
      size: 100mm 80mm portrait;
      margin: 5mm !important;
      /* width: 80mm !important;
      height: 100mm !important;
      max-width:80mm !important;
      max-height: 100mm !important; */
    }
    body{
      /* margin: 0 !important; */
      /* width: 80mm !important;
      height: 100mm !important;
      max-width:80mm !important;
      max-height: 100mm !important; */
      font-family: Verdana, Geneva, Tahoma, sans-serif;
    } 
    .table{
      width:100%;
      border-spacing: 0;
      border-collapse: collapse;
    }
    .table tr, td{     
      vertical-align: top !important;
    }
    .table tbody + tbody {
      border-top: 2px solid #565656;
    }
    .table-bordered th,
    .table-bordered td {
      border: 1px solid #000000;
    }

    .table-bordered thead th,
    .table-bordered thead td {
      border-bottom-width: 1px;
    }
    .border-bottom{
      border-bottom: 1.5pt solid #000 !important;
    }
    .border-left{
      border-left: 1.5pt solid #000 !important;
    }
    .desc{
      line-height: 5px !important;
    }
    .text-center{
      text-align: center !important;
    }
    .text-right{
      text-align: right !important;
    }
    .text-sm{
      line-height: 0.5;
    }
    .bold{
      font-weight: bold;
    }
    .h3.{
      height: 3mm !important;
    }
    .h5{
      height: 5mm !important;
    }
    .h7{
      height: 7mm !important;
    }
    .h10{
      height: 10mm !important;
    }
    .h13{
      height: 13mm !important;
    }
    .h15{
      height: 15mm !important;
    }
    .h20{
      height: 20mm !important;
    }
    .h25{
      height: 25mm !important;
    }
    .h35{
      height: 35mm !important;
    }    
    .h43{
      height: 43mm !important;
    }
    .f6{
      font-size: 6pt !important;
    }
    .f7{
      font-size: 7pt !important;
    }
    .f8{
      font-size: 8pt !important;
    }
    .f9{
      font-size: 9pt !important;
    }
    .f10{
      font-size: 10pt !important;
    }
    .f12{
      font-size: 12pt !important;
    }
    .f16{
      font-size: 16pt !important;
    }
    .f18{
      font-size: 18pt !important;
    }
    .f32{
      font-size: 32pt !important;
      font-weight: bold !important;
      line-height: 32px !important;
    }
    .f39{
      font-size: 39pt !important;
      font-weight: bold !important;
      line-height: 35px !important;
    }
    .text-nowrap{
      white-space: nowrap !important;
    }

    @media print {
      html, body {        
        height: 99%;
        page-break-after: avoid !important;
        page-break-before: avoid !important;
      }
      .print-display-none,
      .print-display-none * {
        display: none !important;
      }
      .print-visibility-hide,
      .print-visibility-hide * {
        visibility: hidden !important;
      }
      .printme,
      .printme * {
        visibility: visible !important;
      }
      .printme {
        position: absolute;
        left: 0;
        top: 0;
      }

    }
  </style>
</head>
<body>
  <?php $company = activeCompany(); ?>
  <table class="table table-bordered" style="border-collapse: collapse;">
    <tr>
      <td colspan="2" class="text-right h7" style="padding-top: 1mm;padding-right:1mm;padding-bottom:0px;">
        @php
            // $imgPath = asset('/img/companies/'.$company->GC_Logo);
            // if(is_dir($imgPath) || !file_exists($imgPath)){
            //   $imgPath = asset('/img/default-logo-light.png');
            // }
            $imgPath = public_path('/img/companies/'.$company->GC_Logo);
            if(is_dir($imgPath) || !file_exists($imgPath)){
              $imgPath = public_path('/img/default-logo-light.png');
            }
          @endphp
          <img src="{{ $imgPath }}" alt="Company Logo"
                height="20">
      </td>
    </tr>
    <tr>
      <td colspan="2" class="text-center h10 f16 bold">
        <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($house->NO_BARANG, 'C128', 1, 25, array(0, 0, 0))}}" alt="barcode" style="width:90% !important;height:12mm !important;" /><br>
        {{ $house->NO_BARANG ?? "-" }}
      </td>
      {{-- <td colspan="2" class="text-center h35">
        <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($house->NO_BARANG, 'QRCODE')}}" alt="barcode" style="padding-top: 3mm !important; width:90% !important;" />
      </td> --}}
    </tr>
    <tr>
      <td class="h3 f9 text-nowrap" style="border-bottom: none !important;">
        MASTER AIRWAY BILL No.
      </td>
      <td rowspan="2" 
          style="padding-top: 3mm;border-bottom: none !important;vertical-align:bottom !important;" 
          class="text-center f9">
        TOTAL <br> No. OF PIECES        
      </td>
    </tr>
    <tr>
      <td class="h7 f16 text-center bold" style="border-top: none !important;">
        {{ $house->mawb_parse ?? "-" }}
      </td>
    </tr>
    <tr>
      <td class="h3 f9" style="border-bottom: none !important;">
        JOBFILE No
      </td>
      <td rowspan="2" class="text-center f10 bold" style="border-top: none !important;">
        {{ $house->JML_BRG ?? 0 }}
      </td>
    </tr>
    <tr>
      <td class="h7 f16 text-center bold" style="border-top: none !important;">
        {{ $house->ShipmentNumber ?? "-" }}
      </td>
    </tr>
    <tr>
      <td class="h3 f9" style="border-bottom: none !important;">
        BC 1.1
      </td>
      <td rowspan="4" style="text-align:center;vertical-align:middle !important;">
        <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($house->NO_BARANG, 'QRCODE', 4, 4, array(0,0,0))}}" alt="barcode"/>
      </td>
    </tr>
    <tr>
      <td class="h7 f16 text-center bold" style="border-top: none !important;">
        {{ $house->NO_BC11 ?? "-" }}
      </td>
    </tr>
    <tr>
      <td class="h3 f9" style="border-bottom: none !important;">
        CONSIGNEE NAME
      </td>
      </td>
    </tr>
    <tr>
      <td class="h7 f7 text-center bold" style="border-top: none !important;">
        {{ $house->NM_PENERIMA ?? "-" }}
      </td>
    </tr>
  </table>

  <!-- jQuery -->
  {{-- <script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
  <script>
    jQuery(document).ready(function(){
      window.print();
    });
  </script> --}}
</body>
</html>