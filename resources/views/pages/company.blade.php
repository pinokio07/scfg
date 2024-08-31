<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ env('APP_NAME', 'Cargo')}}</title>
  <!-- Icon -->
  <link rel="icon" href="{{ asset('/img/default-logo-dark.png')}}"/>
  <link rel="shortcut icon" href="{{ asset('/img/default-logo-dark.png')}}"/>  
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/dist/css/adminlte.min.css">
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
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    
    <div class="card-body">
      <p class="login-box-msg">Select Active Branch-Company</p>

      <form action="{{ route('active-company.set') }}" method="post">
        @csrf
        <select name="branch_id" id="branch_id" 
                class="custom-select"
                required>
          @forelse ($branches as $branch)
            <option value="{{ $branch->id }}">{{ Str::upper($branch->CB_Code) .' - '. Str::upper($branch->CB_FullName.' | '.$branch->company->GC_Name) }}</option>
          @empty
            
          @endforelse
        </select>      
        @if(Session::has('gagal'))
        <small class="text-danger text-sm">{{Session::get('gagal')}}</small>
        @endif
        <hr class="w-100">
        <div class="row">          
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Set Active</button>
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
      <h1>{{ env('APP_NAME', 'Cargo') }}</h1>
      <p>{{ env('APP_DESC', 'Welcome') }}</p>
  </div>  
</div>
<!-- jQuery -->
<script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('adminlte') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="{{ asset('adminlte') }}/dist/js/adminlte.min.js"></script>

</body>
</html>
