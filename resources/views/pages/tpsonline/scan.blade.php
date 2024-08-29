@extends('layouts.master')
@section('title') Scan {{ Str::title($type) }} @endsection
@section('page_name') Scan {{ Str::title($type) }} @endsection

@section('header')
  <style>
    #toast-container > .my-toastr-full-page {
      top: 0;
      right: 0;
      max-width: 1200px;
      max-height: 800px;
      width: 90% !important;
      height: 90% !important;
    }
  </style>
@endsection 

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid h-100">
    @if (count($errors) > 0)
      <div class="row">
        <div class="col-12">
          <div class="alert alert-danger">
              <ul>
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
        </div>
      </div>
    @endif
    <div class="row">
      <div class="col-12">
        <div class="card">          
          <div class="card-body">
            <div class="row">
              <div class="col-lg-6 mb-1 mb-md-0">
                <a href="{{ route('tps-online.scan-in') }}" class="btn @if($type == 'in') btn-primary @else btn-secondary @endif btn-sm btn-block elevation-2">
                  <i class="fas fa-sign-in"></i> Scan In
                </a>
              </div>
              <div class="col-lg-6">
                <a href="{{ route('tps-online.scan-out') }}"
                   class="btn @if($type == 'out') btn-primary @else btn-secondary @endif btn-sm btn-block elevation-2">
                  <i class="fas fa-sign-out"></i> Scan Out
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>      
      <div class="col-12">
        <form action="{{ route('tps-online.scan-'.$type.'.store') }}" 
              method="post"
              class="needs-validation"
              novalidate
              autocomplete="off">
          @csrf
          <div class="input-group">
            <input type="search" 
                    class="form-control form-control-sm" 
                    name="NO_HOUSE_BLAWB"
                    id="NO_HOUSE_BLAWB"
                    value="{{ $item->NO_HOUSE_BLAWB ?? '' }}"
                    placeholder="Scan {{ Str::title($type) }}"
                    required>
            <div class="input-group-append">
              <button type="submit" 
                      class="btn btn-sm btn-default">
                <i class="fa fa-search"></i>
              </button>
            </div>
          </div>
        </form>       
      </div>
    </div>
    @php
      if(Session::has('sukses-scan')){
        $bg = 'bg-success';
        $judul = 'SCAN '. Str::upper($type) .' SUKSES';
        $info = Session::get('sukses-scan');
      } elseif(Session::has('gagal-scan')){
        $bg = 'bg-danger';
        $judul = 'SCAN '. Str::upper($type) .' GAGAL';
        $info = Session::get('gagal-scan');
      } else {
        $bg = '';
        $judul = '';
        $info = '';
      }
    @endphp
    <div class="row">
      <div class="col-12">
        <div class="card {{$bg}}" style="height: 85vh;">
          <div class="card-body">
            <h1 class="text-center d-none d-md-block"
                style="font-size: 10em;">{{ $judul }}</h1>            
            <p class="text-center d-none d-md-block"
                style="font-size:7em;">{!! $info !!}</p>
            <h1 class="text-center d-block d-md-none"
                style="font-size: 3em;">{{ $judul }}</h1>            
            <p class="text-center d-block d-md-none"
                style="font-size:2em;">{!! $info !!}</p>
          </div>
        </div>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection

@section('footer')
  <script>  
  jQuery(document).ready(function(){
    $('#NO_HOUSE_BLAWB').val('').focus();
  });
  </script>
@endsection