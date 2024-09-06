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
        <div class="card">
          <div id="hawb-info" class="card-body">
            <h3>Please insert HAWB Number</h3>
          </div>
        </div>        
      </div>
      <div class="col-12">
        <form id="form-scan"
              action="{{ route('tps-online.scan-'.$type.'.store') }}" 
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
                    placeholder="No HAWB"
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
      // if(Session::has('sukses-scan')){
      //   $bg = 'bg-success';
      //   $judul = 'SCAN '. Str::upper($type) .' SUKSES';
      //   $info = Session::get('sukses-scan');
      // } elseif(Session::has('gagal-scan')){
      //   $bg = 'bg-danger';
      //   $judul = 'SCAN '. Str::upper($type) .' GAGAL';
      //   $info = Session::get('gagal-scan');
      // } else {
      //   $bg = '';
      //   $judul = '';
      //   $info = '';
      // }
    @endphp
    <div class="row">
      <div class="col-12">
        <div id="card-respon" class="card" style="height: 85vh;">
          <div id="body-respon" class="card-body">
            <h1 id="info-judul" class="text-center" style="font-size: 10em;"></h1>            
            <p id="info-isi" class="text-center" style="font-size:6em;"></p>
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

    $(document).on('submit', '#form-scan', function(e){
      e.preventDefault();
      var action = $(this).attr('action');
      var hawb = $('#NO_HOUSE_BLAWB').val();

      $('#NO_HOUSE_BLAWB').val('').focus();
      
      $.ajax({
        url: action,
        type: "POST",
        data: {
          NO_HOUSE_BLAWB: hawb,
        },
        success: function(msg){
          var jdl = 'SCAN {{Str::upper($type)}} ';
          var jml = '<h3>Master <b>'+msg.mawb+'</b>, scan {{ $type }} <b>'+msg.complete+'</b> of <b>'+msg.houses+'</b> House </h3>';

          if(msg.status == 'OK')
          {
            jdl += 'SUCCESS';
            $('#card-respon').removeClass('bg-danger')
                             .addClass('bg-success');
          } else {
            jdl += 'GAGAL';
            $('#card-respon').removeClass('bg-success')
                             .addClass('bg-danger');
          }

          $('#info-judul').text('').text(jdl);
          $('#info-isi').html(msg.message);

          $('#hawb-info').html(jml);

          // $('#NO_HOUSE_BLAWB').val('').focus();
        },
        error: function (jqXHR, exception) {
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          showError(jqXHR.status + ' || ' + jsonValue.message);

          // $('#NO_HOUSE_BLAWB').val('').focus();
        }
      });

    });
  });
  </script>
@endsection