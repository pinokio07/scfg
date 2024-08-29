<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Scheduler</title>
</head>
<body>
  <h1>Berhasil kirim {{ $jenis ?? "-" }}, refresh halaman dalam 10 detik.</h1>
  <!-- jQuery -->
  <script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
  <script>
    var url = "{{ $url ?? '/dashboard' }}";
    jQuery(document).ready(function(){
      setTimeout(() => {
        location.reload();
      }, 10000);
    });
  </script>
</body>
</html>