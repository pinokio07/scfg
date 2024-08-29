<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Monitoring Scan</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- @vite('resources/css/app.css') --}}

  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/dist/css/adminlte.min.css">
  <!-- jQuery -->
  <script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="{{ asset('adminlte') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <div class="row">
    <div class="col-12">
      <div id="card" class="card" style="height: 100vh;">
        <div class="card-body">
          <h1 id="judul" class="text-center" style="font-size: 8em;">TPS Online</h1>            
          <p id="info" class="text-center" style="font-size:3em;"> {{ activeCompany()->GC_Name ?? "-" }} <br>-- Menunggu Proses Scan --</p>
        </div>
      </div>
    </div>
  </div>
  @vite('resources/js/app.js')
  <script type="module">
    Echo.channel('scan-in')
        .listen('ScanHouse', (e) => {
          var id = e.id;
          var status = e.status;
          var judul = e.judul;
          var info = e.info;

          if(status == 'gagal'){
            $('#card').removeClass('bg-success').addClass('bg-danger');
            $('#judul').text('SCAN IN GAGAL');
            $('#info').text(info);
          } else {
            $('#card').removeClass('bg-danger').addClass('bg-success');
            $('#judul').text('SCAN IN SUCCESS');
            $('#info').html(info);
          }

        });
    Echo.channel('scan-out')
        .listen('ScanHouse', (e) => {
          var id = e.id;
          var status = e.status;
          var judul = e.judul;
          var info = e.info;

          if(status == 'gagal'){
            $('#card').removeClass('bg-success').addClass('bg-danger');
            $('#judul').text('SCAN OUT GAGAL');
            $('#info').text(info);
          } else {
            $('#card').removeClass('bg-danger').addClass('bg-success');
            $('#judul').text('SCAN OUT SUCCESS');
            $('#info').html(info);
          }
        });
    // console.log(Echo.options)
  </script>  
</body>
</html>