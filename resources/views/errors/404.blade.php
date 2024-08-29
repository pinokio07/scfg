@extends('errors::minimal')

@section('title', __('Not Found'))

@section('code')
<h2 class="headline text-warning"> 404</h2>
@endsection

@section('message')
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/dist/css/adminlte.min.css">
  <style>
    p{
      font-size: 1rem !important;
    }
    .isi{
      letter-spacing: 0 !important;
      text-transform: none !important;
    }
  </style>
  <div class="isi">
    <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Page not found.</h3>
    <p>We could not find the page you were looking for. Meanwhile, you may return to <a href="{{url()->previous()}}" style="color: blue !important;">previous page</a>, or try to <a href="/" style="color: blue;">Dashboard</a> page.</p>
  </div>  
@endsection