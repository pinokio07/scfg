<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }}</title>
  <!-- Icon -->
  <link rel="icon" href="{{ asset('/img/default-logo-dark.png')}}"/>
  <link rel="shortcut icon" href="{{ asset('/img/default-logo-dark.png')}}"/>  
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/dist/css/adminlte.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" type="text/css" href="{{ asset('adminlte') }}/plugins/toastr/toastr.min.css">
  <style>
    body {
            background-image:url("{{ asset('/img/default-bg.jpg') }}");
            background-size: cover;
            background-color: #ffffff;            
        }
    .bottom-logo{
      position: fixed;
      width:100%; 
      bottom: 0; 
      margin-top: -100px; 
      left: 30px; 
      z-index: -1;

    }
    .pic-logo{
      /* display: block; */
      float: left;
      vertical-align: middle;
      border: 0;
      /* box-sizing: border-box; */
      height: auto;
      max-width: 52px;
      margin: 0 auto;
      padding-top: 55px;
      padding-bottom: 20px;
    }
    .copy{
      width: auto;
      padding: 30px 30px 12px;
      font-family: Open Sans, sans-serif;
      color: #ffffff;
    }
    .copy h1{      
      display: inline-block;
      vertical-align: middle;
      text-transform: uppercase;
      font-size: 20px;
      font-weight: 700;
      margin: 30px 0 0 14px;
    }
    .copy p{
      font-size: 13px;
      max-width: 650px;
      opacity: .8;
      font-weight: 300;
      margin-top: 0;
      position: relative;
      left: 15px;
      /* top: -8px; */
    }
  </style>
</head>
<body class="hold-transition login-page">
  <div class="wrapper" style="padding-top: 25vh;">
    <video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop" style="position: fixed;right: 0;bottom: 0;min-width: 100%;min-height: 100%;z-index:-100;">
      <source src="https://www.bollore-logistics.com/app/assets-bollorelgs/uploads/2020/07/video-test.mp4" type="video/mp4">
    </video> 
    <div class="login-box">
      <!-- /.login-logo -->
      <div class="card card-outline card-primary">
        
        <div class="card-body">
          <p class="login-box-msg">Sign in to start your session</p>
  
          <form id="formLogin" action="{{ route('login') }}" method="post">
            @csrf
            <div class="input-group mb-3">
              <input type="text" name="email" id="email" value="{{ old('email') }}" placeholder="Email" class="form-control" required>
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="use fas fa-envelope"></span> 
                </div>            
              </div>
            </div>
            <div class="input-group mb-3">
              <input type="password" name="password" id="password" placeholder="Password" class="form-control" required>
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-eye"></span>
                  <span class="fas fa-eye-slash d-none"></span> 
                </div>
              </div>
            </div>
            @if(Session::has('gagal'))
            <small class="text-danger text-sm">{{Session::get('gagal')}}</small>
            @endif
            <hr class="w-100">
            <div class="row">          
              <div class="col-12">
                <button type="submit"
                        id="btnSubmit" 
                        class="btn btn-primary btn-block elevation-2">Login</button>
              </div>
              <!-- /.col -->
            </div>
          </form>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
    <!-- /.login-box -->
    <div class="bottom-logo">
      <img class="hidden-xs pic-logo" src="{{ asset('/img/default-logo-light.png') }}" alt="Logo Icon">  
      <div class="copy">
          <h1>{{ config('app.name') }}</h1>
          <p>{{ config('app.desc') }}</p>
      </div>  
    </div>
  </div>
  
  <!-- jQuery -->
  <script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="{{ asset('adminlte') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="{{ asset('adminlte') }}/dist/js/adminlte.min.js"></script>
  <!-- Toastr -->
  <script src="{{asset('adminlte')}}/plugins/toastr/toastr.min.js"></script>

  <script>
    @if(Session::has('sukses'))
      toastr.success("{{Session::get('sukses')}}", "Sukses!", {timeOut: 2000, closeButton: true})   
    @elseif(Session::has('gagal'))
      toastr.error("{{Session::get('gagal')}}", "Gagal!", {timeOut: 2000, closeButton: true})     
    @endif  

    jQuery(document).ready(function(){
      $('.fa-eye').click(function(){
        $('#password').get(0).type = 'text';      
        $(this).addClass('d-none');
        $('.fa-eye-slash').removeClass('d-none');
      });
      $('.fa-eye-slash').click(function(){
        $('#password').get(0).type = 'password';
        $(this).addClass('d-none');
        $('.fa-eye').removeClass('d-none');
      });
      $(document).on('submit', '#formLogin', function(e){
        // e.preventDefault();
        var val = $('#password').val();
        $('#password').get(0).type = 'password';
        $('#password').val(btoa(val));
        // $('#formLogin').submit();
      });
    });
  </script>
</body>
</html>
