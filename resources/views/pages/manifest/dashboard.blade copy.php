@extends('layouts.master')
@section('title') Shipments Status @endsection
@section('page_name') Shipments Status @endsection

@section('header')
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
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
      <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ number_format($pendingPlp, 0, ',', '.') }}</h3>

            <p>Pending PLP</p>
          </div>
          <div class="icon">
            <i class="ion ion-document"></i>
          </div>
          <a href="#" 
             data-jenis="pending-plp"
             data-judul="Pending PLP"
             class="small-box-footer informasi">
             More info 
             <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
          <div class="inner">
            <h3>
              {{ number_format($pendingInWoPlp, 0, ',', '.') }} <sup><small>No PLP</small></sup>
              /
               {{ number_format($pendingInPlp, 0, ',', '.') }} <sup><small>Has PLP</small></sup>
            </h3>
            <p>Pending Gate In</p>
          </div>
          <div class="icon">
            <i class="ion ion-log-in"></i>
          </div>
          <div class="small-box-footer">
            <div class="row">
              <div class="col-6">
                <a href="#"
                   data-jenis="pending-in-wo-plp"
                   data-judul="Pending Gate In Without PLP" 
                   class="text-white btn-block informasi">
                  Without PLP <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-6">
                <a href="#" 
                   data-jenis="pending-in-plp"
                   data-judul="Pending Gate In With PLP"
                   class="text-white btn-block informasi">
                  With PLP <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>
              {{ number_format($pendingSppb, 0, ',', '.') }} <sup><small>Pending SPPB</small></sup>
              /
               {{ number_format($sppb, 0, ',', '.') }} <sup><small>SPPB</small></sup>
            </h3>
            <p>Cargo On Hand</p>
          </div>          
          <div class="icon">
            <i class="ion ion-clipboard"></i>
          </div>
          <div class="small-box-footer">
            <div class="row">
              <div class="col-6">
                <a href="#"
                   data-jenis="pending-sppb"
                   data-judul="Pending SPPB" 
                   class="text-white btn-block informasi">
                  Pending SPPB <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-6">
                <a href="#" 
                   data-jenis="sppb"
                   data-judul="SPPB"
                   class="text-white btn-block informasi">
                  SPPB <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>
              {{ number_format($pendingTmsIn, 0, ',', '.') }} <sup><small>IN</small></sup>
              /
               {{ number_format($pendingTmsOut, 0, ',', '.') }} <sup><small>OUT</small></sup>
            </h3>

            <p>Pending OneTMS</p>
          </div>
          <div class="icon">
            <i class="fas fa-truck-moving"></i>
            {{-- <i class="ion ion-exit-outline"></i> --}}
          </div>
          <div class="small-box-footer">
            <div class="row">
              <div class="col-6">
                <a href="#"
                   data-jenis="pending-in-tms"
                   data-judul="Pending In OneTMS" 
                   class="text-white btn-block informasi">
                  IN <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
              <div class="col-6">
                <a href="#" 
                   data-jenis="pending-out-tms"
                   data-judul="Pending Out OneTMS"
                   class="text-white btn-block informasi">
                  OUT <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./col -->
    </div>
     <!-- Info boxes -->
     <div class="row">
      <div class="col col-md-1"></div>
      <div class="col-12 col-md-3">
        <div class="info-box informasi" data-jenis="current-now" data-judul="Current Now">
          <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">Current Now</span>
            <span class="info-box-number">{{ number_format($current, 0, ',','.') }}</span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <div class="col-12 col-md-3">
        <div class="info-box informasi mb-3" data-jenis="abandon" data-judul="Abandoned">
          <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-thumbs-down"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">Abandon</span>
            <span class="info-box-number">{{ number_format($abandon, 0, ',','.') }}</span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <div class="col-12 col-md-3">
        <div class="info-box informasi mb-3" data-jenis="delivered" data-judul="Delivered">
          <span class="info-box-icon bg-success elevation-1"><i class="fas fa-truck-moving"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">Delivered</span>
            <span class="info-box-number">{{ number_format($delivered, 0, ',','.') }}</span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <div class="col col-md-1"></div>
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
      $('#dataAjax').DataTable().destroy();
      $('#info-table').html('<i class="fas fa-sync"></i>');
      $('.informasi').prop('disabled', true);

      $.ajax({
        url: "{{ route('dashboard.shipment') }}",
        type: "GET",
        data: {
          jenis: jenis,
          user: "{{ \Crypt::encrypt(auth()->user()->id) }}",
        },
        success:function(msg){
          // console.log(msg.data);
          $('#dataAjax').DataTable({
            data: msg.data,
            columns:[
              @forelse ($items as $keys => $item)
                @if($keys == 'id')
                  {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false},
                @elseif(in_array($keys, ['ArrivalDate', 'ExitDate', 'SCAN_IN_DATE']))
                {
                  data: {
                    _: "{{ $keys }}.display",
                    sort: "{{ $keys }}.timestamp",
                  }
                },
                @elseif(in_array($keys, ['BRUTO', 'ChargeableWeight']))
                {data:"{{$keys}}", name: "{{$keys}}", className:'text-right', 
                  render: $.fn.dataTable.render.number( ',', '.', 2 )
                },
                @elseif($keys == 'JML_BRG')
                {data: "{{ $keys }}",name: "{{$keys}}", className:'text-center'},
                @elseif($keys == 'actions')
                {data:"actions", name: "actions", searchable: false, className:'text-center'},
                @else
                {data: "{{$keys}}", name: "{{$keys}}"},
                @endif                
              @empty
              @endforelse              
            ],
            buttons: [                
                {
                  extend: 'excelHtml5',
                  exportOptions: { 
                    orthogonal: 'export',
                    columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13]
                  }
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    exportOptions: { 
                      orthogonal: 'export',
                      columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13]
                    },
                },
                {
                  extend: 'print',
                  exportOptions: { orthogonal: 'export' }
                },
            ],
            createdRow: function( row, data, dataIndex ) {
                // Set the data-status attribute, and add a class
                // console.log(data['AL_PENERIMA']);
              $( 'td' , row ).eq(5)
                  .attr('data-toggle', 'tooltip')
                  .attr('title', data['AL_PENERIMA']);                 
            },
            initComplete: function () {
              this.api().columns([1,2,3,4]).every( function () {
                var column = this;
                var select = $('<select class="select2bs4clear" style="width: 100%;"><option value="">Select...</option></select>')
                .appendTo( $(column.footer(3)).empty() )
                .on( 'change', function () {
                  var val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
                    );
                  column
                  .search( val ? '^'+val+'$' : '', true, false )
                  .draw();
                } );

                column.data().unique().sort().each( function ( d ) {
                  if(d !== ''){                    
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                  }              
                } );
              } );

              select2bs4Clear();
            }, 
          }).buttons().container().appendTo('#dataAjax_wrapper .col-md-6:eq(0)');

          $('.informasi').prop('disabled', false);
          $('#info-table').html(judul);
        },
        error:function(jqXHR){
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

          $('.informasi').prop('disabled', false);
        }
      });
    }
    jQuery(document).ready(function(){
      // getDataAjax();
      $(document).on('click', '.informasi', function(){
        var jenis = $(this).attr('data-jenis');
        var judul = $(this).attr('data-judul');

        // $('#info-table').html(judul);
        getDataAjax(jenis, judul);
      });
      $(document).on('click', '.actions', function(){
        var id = $(this).attr('data-id');
        var href = $(this).attr('data-href');
        var method = $(this).attr('data-method');
        var jenis = $(this).attr('data-jenis');
        var parameter = $(this).attr('data-parameter');
        var untuk = $(this).attr('data-untuk');
        var nama = $(this).html();

        if(untuk){
          $('#'+untuk).val(id);
        } else {
          Swal.fire({			
            title: 'Are you sure?',			
            html: "Proceed "+nama+"!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, proceed!'
          }).then((result) => {
            if (result.value) {
              $('.btn').prop('disabled', true);
              $.ajax({
                url: href,
                type: method,
                data:{
                  _token: _token,
                  jenis: parameter,
                },
                success:function(msg){
                  if(msg.status == 'OK'){
                    toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                    getDataAjax(jenis);
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
    });
  </script>
@endsection
