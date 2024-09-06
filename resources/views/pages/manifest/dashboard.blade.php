@extends('layouts.master')
@section('title') Shipments Status @endsection
@section('page_name') Shipments Status || ExRate {{ $exRate->RE_SellRate ?? 0 }} @endsection

@section('header')
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <script src="https://cdn.datatables.net/plug-ins/2.1.2/dataRender/datetime.js" charset="utf8"></script>
  <style>
    .informasi:hover{
      color: blue !important;
    }
  </style>
@endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <!-- PLP Status -->
      <div class="col-lg-5">
        <!-- small box -->
        <div class="small-box bg-warning">
          <div class="inner">
            <h5>Pending Gate In (Master)</h5>

            <div class="row">
              <div class="col-4 text-center">
                <h3 id="info-pendingInWoPlp">0</h3>
              </div>
              <div class="col-4 text-center">
                <h3 id="info-pendingPlp">0</h3>
              </div>
              <div class="col-4 text-center">
                <h3 id="info-pendingInPlp">0</h3>
              </div>
            </div>
          </div>
          <div class="icon">
            <i class="ion ion-log-in"></i>
          </div>
          <div class="small-box-footer">
            <div class="row">
              <div class="col-4">
                <a href="#"
                    data-jenis="pending-in-wo-plp"
                    data-judul="Pending Gate In Without PLP" 
                    class="text-white btn-block informasi">
                  Without PLP <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-4">
                <a href="#" 
                    data-jenis="pending-plp"
                    data-judul="Pending PLP"
                    class="text-white btn-block informasi">
                    Waiting Approval PLP 
                    <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-4">
                <a href="#" 
                    data-jenis="pending-in-plp"
                    data-judul="Pending Gate In Approved PLP"
                    class="text-white btn-block informasi">
                  Approved <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>          
        </div>
      </div>
      <!-- Inventory -->
      <!-- ./col -->
      <div class="col-lg-2">
        <!-- small box -->
        <div class="small-box bg-success">
          <div class="inner">
            <h5>Current Inventory</h5>
            <h3 id="info-current" class="text-center">0</h3>
          </div>
          <div class="icon">
            <i class="ion ion-clipboard"></i>
          </div>
          <a href="#"
             data-jenis="current-now"
             data-judul="Current Inventory"
             class="small-box-footer informasi">
            More Info
            <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <!-- Abandoned -->
      <!-- ./col -->
      <div class="col-lg-2">
        <!-- small box -->
        <div class="small-box bg-danger">
          <div class="inner">
            <h5>Abandoned</h5>
            <h3 id="info-abandon" class="text-center">0</h3>
          </div>          
          <div class="icon">
            <i class="ion ion-archive"></i>
          </div>
          <a href="#"
             data-jenis="abandon"
             data-judul="Abandoned"
             class="small-box-footer informasi">
            More Info
            <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <!-- ./col -->
      <!-- Buttons -->
      <div class="col-lg-3">
        <a href="{{ route('manifest.batch-tracking') }}"
           class="btn btn-sm btn-primary btn-block elevation-2">
           <i class="fas fa-search"></i> Batch Tracking</a>
        <button id="advSearch"
           class="btn btn-sm btn-info btn-block elevation-2">
           <i class="fas fa-search"></i> Advance Search</button>
        @can('multi_tenant')
        <form action="{{ url()->current() }}" method="get">
          <input type="hidden" name="aws" value="{{ \Str::random(10) }}">
          <input type="hidden" name="idx" value="{{ \Str::random(15) }}">
          <input type="hidden" name="wkc" value="{{ \Str::random(20) }}">
          <div class="form-group elevation-2 mt-2">
            <select name="idm" id="idm"
                    class="select2bs4"
                    style="width: 100%;"
                    onchange="getCount();">
                <option value="all" @selected(Request::get('idm') === 'all')>All Branches</option>
              @forelse ($branches as $br)
                <option value="{{ base64_encode($br->id) }}"
                        @selected((!Request::has('idm') && activeCompany()->id == $br->id)
                                  || (base64_decode(Request::get('idm')) == $br->id))>
                  {{ Str::upper($br->CB_Code) .' - '. Str::upper($br->CB_FullName.' | '.$br->company->GC_Name) }}
                </option>
              @empty                
              @endforelse
            </select>
            <p></p>
          </div>
          <input type="hidden" name="agt" value="{{ \Str::random(25) }}">
          <input type="hidden" name="ght" value="{{ \Str::random(30) }}">
        </form>
        @endcan
      </div>
      <!-- ./col -->
    </div>
     <!-- House State -->
     <div class="row">
      <div class="col-lg-6">
        <!-- small box -->
        <div class="small-box bg-teal">
          <div class="inner">
            <h5>House State</h5>

            <div class="row">
              <div class="col-2 text-center">
                <h3 id="info-pendingScanIn">0</h3>
              </div>
              <div class="col-3 text-center">
                <h3 id="info-pendingSppb">0</h3>
              </div>
              <div class="col-2 text-center">
                <h3 id="info-pendingXray">0</h3>
              </div>
              <div class="col-2 text-center">
                <h3 id="info-sppb">0</h3>
              </div>
              <div class="col-2 text-center">
                <h3 id="info-delivered">0</h3>
              </div>
            </div>
          </div>
          <div class="icon">
            <i class="ion ion-document"></i>
          </div>
          <div class="small-box-footer">
            <div class="row">
              <div class="col-2">
                <a href="#"
                    data-jenis="pending-in"
                    data-judul="Pending Gate In" 
                    class="text-white btn-block informasi">
                  Pending Scan In <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-3">
                <a href="#" 
                    data-jenis="pending-sppb"
                    data-judul="Custom Clearance Process"
                    class="text-white btn-block informasi">
                    Custom Clearance Process 
                    <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-2">
                <a href="#" 
                    data-jenis="pending-x-ray"
                    data-judul="Pending X-Ray"
                    class="text-white btn-block informasi">
                  Pending X-Ray <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-2">
                <a href="#" 
                    data-jenis="sppb"
                    data-judul="Ready Scan Out"
                    class="text-white btn-block informasi">
                  Ready Scan Out <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-2">
                <a href="#" 
                    data-jenis="delivered"
                    data-judul="Delivered"
                    class="text-white btn-block informasi">
                  Delivered <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>
          
        </div>
      </div>
      <div class="col-12 col-md-2">
        <div class="small-box bg-info">
          <div class="inner">
            <h5>Physical Inspection</h5>
            <h3 id="info-periksaFisik" class="text-center">0</h3>
          </div>
          <div class="icon">
            <i class="ion ion-alert"></i>
          </div>
          <a href="#"
             data-jenis="periksa-fisik"
             data-judul="Periksa Fisik"
             class="small-box-footer informasi">
            More Info
            <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <div class="col-12 col-md-2">
        <div class="small-box bg-orange">
          <div class="inner">
            <h5>NPD</h5>
            <h3 id="info-npd" class="text-center">0</h3>
          </div>
          <div class="icon">
            <i class="ion ion-link"></i>
          </div>
          <a href="#"
             data-jenis="npd"
             data-judul="NPD"
             class="small-box-footer informasi">
            More Info
            <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <div class="col-12 col-md-2">
        <div class="small-box bg-danger">
          <div class="inner">
            <h5>SKIP CN</h5>
            <h3 id="info-skipcn" class="text-center">0</h3>
          </div>
          <div class="icon">
            <i class="ion ion-link"></i>
          </div>
          <a href="#"
             data-jenis="skipcn"
             data-judul="SKIP CN"
             class="small-box-footer informasi">
            More Info
            <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
    </div>
    <!-- /.row -->
    <!-- /.row -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><span id="info-table"></span></h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <form id="formPrintLabel" action="" method="post" target="_blank">
              @csrf
              @method('PUT')
              <input type="hidden" name="jenis" value="label">
              <input type="hidden" name="mt" id="mt" value="legacy">
            </form>
            <div class="table-responsive">
              @include('table.ajax')
            </div>            
          </div>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<div class="modal fade" id="modal-sppb">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">SPPB On Demand</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formSPPB" method="GET">
          <input type="hidden" name="house_id" id="sppb_house_id">
          <div class="form-group form-group-sm">
            <label for="jenis">Jenis Aju</label>
            <select name="jenis" id="jenis"
                    class="form-control form-control-sm"
                    required>
              <option value="sppbpib">PIB</option>
              <option value="sppbbc23">BC23</option>
            </select>
          </div>
          <div class="form-group form-group-sm">
            <label for="no_sppb">Nomor SPPB</label>
            <input type="text" 
                   name="no_sppb" 
                   id="no_sppb" 
                   class="form-control form-control-sm"
                   required>
          </div>
          <div class="form-group form-group-sm">
            <label for="tgl_sppb">Tanggal SPPB</label>
            <input type="date" 
                   name="tgl_sppb" 
                   id="tgl_sppb" 
                   class="form-control form-control-sm"
                   required>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
        <button type="submit"
                form="formSPPB"
                class="btn btn-lg btn-primary">
          <i class="fas fa-save"></i> Submit
        </button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

@endsection

@section('footer')
  <script>
    var _token = "{{ csrf_token() }}";
    function getDataAjax(jenis, judul) {
      var sch = true;
      var ft = 'YYYY-MM-DDTHH:mm:ss.SSSSSSZ';
      var srt = true;

      if(jenis === 'pending-in-wo-plp'
          || jenis === 'pending-plp'
          || jenis === 'pending-in-plp'
      ) {
        sch = false;
        srt = false;
        ft = 'YYYY-MM-DD';
      }      
      if ( $.fn.DataTable.isDataTable('#dataAjax') ) {
        $('#dataAjax').DataTable().destroy();
      }

      $('#dataAjax tbody').empty();
      
      $('.informasi').prop('disabled', true);
      var user = "{{ \Crypt::encrypt(auth()->user()->id) }}";

      $('#dataAjax').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 350,
        pageLength: parseInt("{{ config('app.page_length') }}"),
        ajax:{
          url: "{{ route('dashboard.shipment') }}",
          data: function (d) {
              d.jenis = jenis;
              d.user = user;
              d.idm = $('#idm').find(':selected').val();
          },
        },        
        columns:[
          @forelse ($items as $keys => $item)
            @if($keys == 'id')
              {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, orderable: false},
            @elseif($keys == 'TGL_TIBA')
            {
              data:"{{$keys}}",
              name: "{{$keys}}",
              render: $.fn.dataTable.render.moment( ft, 'DD/MM/YYYY' ),
              searchable: false,
            },
            @elseif(in_array($keys, ['SCAN_IN_DATE', 'BC_DATE']))
            {
              data:"{{$keys}}",
              name: "{{$keys}}",
              render: $.fn.dataTable.render.moment( 'YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY HH:mm:ss' ),
              searchable: sch,
              orderable: srt,
            },
            @elseif($keys == 'ExitDate')
            {
              data:"{{$keys}}",
              name: "{{$keys}}",
              render: $.fn.dataTable.render.moment( 'YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY' ),
              searchable: sch,
              orderable: srt,
            },
            @elseif(in_array($keys, ['BRUTO', 'ChargeableWeight', 'NETTO']))
            {data:"{{$keys}}", name: "{{$keys}}",searchable: false, className:'text-right', 
              render: $.fn.dataTable.render.number( ',', '.', 2 )
            },
            @elseif(in_array($keys, ['JML_BRG', 'actions']))
            {data: "{{ $keys }}",name: "{{$keys}}",searchable: false, className:'text-center'},
            @elseif(in_array($keys, ['BC_CODE']))
            {data: "{{ $keys }}",name: "{{$keys}}",searchable: sch,orderable: srt, className:'text-center'},
            @elseif(in_array($keys, $skips))
            {data: "{{ $keys }}",name: "{{$keys}}", searchable: sch, orderable: srt},
            @else
            {data: "{{$keys}}", name: "{{$keys}}"},
            @endif                
          @empty
          @endforelse              
        ],
        dom: 'Blfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            action: function ( e, dt, node, config ) {                    
              window.open("{{ route('dashboard.shipment') }}?tipe=xls&jenis="+jenis+"&user="+user);
            }
          },
        ],
        initComplete: function () {             
          var api = this.api();
      
          if ( jenis != 'periksa-fisik' ) {
            // Hide Office column
            api.column(5).visible( false );
          }
          $('.informasi').prop('disabled', false);
          $('#info-table').html(judul);
          
        }, 
      });
    }
    function getPlp() { 
      $('#info-pendingPlp').html('<i class="fas fa-sync fa-spin"></i>');
      $('#info-pendingInWoPlp').html('<i class="fas fa-sync fa-spin"></i>');
      $('#info-pendingInPlp').html('<i class="fas fa-sync fa-spin"></i>');
      var idm = $('#idm').find(':selected').val();
      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        data:{
          count: 1,
          idm: idm,
          plp: 1,
        },
        success: function(msg) {
          $('#info-pendingPlp').html(formatAsMoney(msg.pendingPlp, 0));
          $('#info-pendingInWoPlp').html(formatAsMoney(msg.pendingInWoPlp, 0));
          $('#info-pendingInPlp').html(formatAsMoney(msg.pendingInPlp, 0));
        }
      });
    }
    function getCurrent() {
      $('#info-current').html('<i class="fas fa-sync fa-spin"></i>');
      var idm = $('#idm').find(':selected').val();
      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        data:{
          count: 1,
          idm: idm,
          current: 1,
        },
        success: function(msg) {         
          $('#info-current').html(formatAsMoney(msg.current, 0));
        }
      });
    }
    function getAbandon() {      
      $('#info-abandon').html('<i class="fas fa-sync fa-spin"></i>');
      var idm = $('#idm').find(':selected').val();
      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        data:{
          count: 1,
          idm: idm,
          abandon: 1,
        },
        success: function(msg) {  
          $('#info-abandon').html(formatAsMoney(msg.abandon, 0));
        }
      });
    }
    function getStatus() {
      $('#info-pendingScanIn').html('<i class="fas fa-sync fa-spin"></i>');
      $('#info-pendingSppb').html('<i class="fas fa-sync fa-spin"></i>');
      $('#info-sppb').html('<i class="fas fa-sync fa-spin"></i>');
      $('#info-pendingXray').html('<i class="fas fa-sync fa-spin"></i>'); 
      var idm = $('#idm').find(':selected').val();
      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        data:{
          count: 1,
          idm: idm,
          stat: 1,
        },
        success: function(msg) {          
          $('#info-pendingScanIn').html(formatAsMoney(msg.pendingScanIn, 0));
          $('#info-pendingSppb').html(formatAsMoney(msg.pendingSppb, 0));
          $('#info-sppb').html(formatAsMoney(msg.sppb, 0));
          $('#info-pendingXray').html(formatAsMoney(msg.pendingXray, 0));          
        }
      });
    }
    function getOther() {      
      $('#info-periksaFisik').html('<i class="fas fa-sync fa-spin"></i>');
      $('#info-npd').html('<i class="fas fa-sync fa-spin"></i>');
      $('#info-skipcn').html('<i class="fas fa-sync fa-spin"></i>');
      var idm = $('#idm').find(':selected').val();
      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        data:{
          count: 1,
          idm: idm,
          oth: 1,
        },
        success: function(msg) {
          $('#info-periksaFisik').html(formatAsMoney(msg.periksaFisik, 0));
          $('#info-npd').html(formatAsMoney(msg.npd, 0));
          $('#info-skipcn').html(formatAsMoney(msg.skipcn, 0));
        }
      });
    }
    function getCompleted() {
      $('#info-delivered').html('<i class="fas fa-sync fa-spin"></i>');
      var idm = $('#idm').find(':selected').val();
      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        data:{
          count: 1,
          idm: idm,
          wc: 1,
        },
        success: function(msg) { 
          $('#info-delivered').html(formatAsMoney(msg.delivered, 0));
        }
      })
    }
    function getCount() {
      $('#info-table').html('');
      $('#dataAjax').DataTable().clear().destroy();
      getPlp();
      getCurrent();
      getAbandon();
      getStatus();
      getOther();
      getCompleted();
    }
    jQuery(document).ready(function(){
      getCount();
      $(document).on('click', '.informasi', function(){
        var jenis = $(this).attr('data-jenis');
        var judul = $(this).attr('data-judul');
        getDataAjax(jenis, judul);
      });
      $(document).on('click', '.actions', function(){
        var id = $(this).attr('data-id');
        var href = $(this).attr('data-href');
        var method = $(this).attr('data-method');
        var jenis = $(this).attr('data-jenis');
        var judul = $(this).attr('data-judul');
        var parameter = $(this).attr('data-parameter');
        var untuk = $(this).attr('data-untuk');
        var nama = $(this).html();
        var htm = '';

        if(parameter == 'plp-request')
        {
          htm += '<input type="text" class="form-control form-control-sm"' +
                    ' name="pemohon" id="pemohon" placeholder="Pemohon" value="{{ Str::upper(Auth::user()->name ?? "") }}">';
        } else {
          htm += "Proceed "+nama+"!";
        }

        if(untuk){
          $('#'+untuk).val(id);
        } else {
          Swal.fire({			
            title: 'Are you sure?',			
            html: htm,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, proceed!'
          }).then((result) => {
            if (result.value) {
              if(parameter == 'plp-request')
              {
                var pemohon = $('#pemohon').val();
              } else {
                var pemohon = '';
              }
              $('.btn').prop('disabled', true);
              $.ajax({
                url: href,
                type: method,
                data:{
                  _token: _token,
                  jenis: parameter,
                  pemohon:pemohon
                },
                success:function(msg){
                  if(msg.status == 'OK'){
                    toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                    getDataAjax(jenis, judul);
                  } else {
                    toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                  }

                  $('.btn').prop('disabled', false);
                },
                error:function(jqXHR){
                  jsonValue = jQuery.parseJSON( jqXHR.responseText );
                  toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

                  $('.btn').prop('disabled', false);
                }
              });
            }
          });
        }
      });
      $(document).on('submit', '#formSPPB', function(e){
        e.preventDefault();
        var jenis = $('#jenis').val();
        var data = $(this).serialize();

        $('.btn').prop('disabled', true);

        $.ajax({
          url: "{{ route('scheduler') }}",
          type: "GET",
          data: data,
          success: function(msg){
            if(msg.status == 'OK'){
              toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

              $('#modal-sppb').modal('toggle');
              $('#no_sppb').val('');
              $('#tgl_sppb').val('');
              getDataAjax('pending-sppb');
            } else {
              toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
            }
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        });
      });
      $(document).on('click', '.resend', function(){
        var href = $(this).attr('data-href');

        $.ajax({
          url: href,
          type: "GET",
          success: function(msg){
            console.log(msg);
            var status = '';
            var message = '';
            if(msg.hasOwnProperty('original')) //or msg.original!==undefined
            {
              status = msg.original.status;
              message = msg.original.message;
            } else {
              status = msg.status;
              message = msg.message;
            }
            if(status == 'OK'){
              toastr.success(message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
            } else {
              toastr.error(message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
            }
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
          }
        })
      });
      $(document).on('click', '.printlabel', function(){
        var action = $(this).attr('data-href');

        $('#formPrintLabel').attr('action', action);

        $('#formPrintLabel').submit();
      });
    });   
  </script>
@endsection
