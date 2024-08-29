@extends('errors::minimal')

@section('title', __('Not Found'))
@section('code')
<h2 class="headline text-warning"> 403</h2>
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
    <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Access Denied.</h3>
    <p>You don't have access to open this page. Meanwhile, you may return to <a href="{{url()->previous()}}" style="color: blue !important;">previous page</a>, or try to <a href="/" style="color: blue;">Dashboard</a> page.</p>
  </div>  
@endsection