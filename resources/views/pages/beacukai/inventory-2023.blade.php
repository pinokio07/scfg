@extends('layouts.master')
@section('title') 
  {{ Str::title(Str::replace('-', ' ', Request::segment(2))) }}
@endsection
@section('page_name') 
  {{ Str::title(Str::replace('-', ' ', Request::segment(2))) }}
@endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              {{ Str::title(Str::replace('-', ' ', Request::segment(2))) }}
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">

            @if($form)
              <form id="formBeaCukai" 
              action="{{ url()->current() }}" 
              method="get">
            
              @if(in_array($form, ['current-now', 'current-now-bc']))
                <div class="row">
                  <div class="col-10 col-lg-4">
                    <div class="form-group">
                      <div class="input-group input-group-sm date onlydate" 
                            id="datetimepicker1" 
                            data-target-input="nearest">
                        <input type="text" 
                                id="tanggal"
                                name="tanggal"
                                class="form-control datetimepicker-input tanggal"
                                placeholder="As Of Date"
                                data-target="#datetimepicker1"
                                value="{{ Request::get('tanggal') ?? today()->format('d-m-Y') }}">
                        <div class="input-group-append" 
                              data-target="#datetimepicker1" 
                              data-toggle="datetimepicker">
                          <div class="input-group-text">
                            <i class="fa fa-calendar"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-2 col-lg-1">
                    <button type="submit" 
                            class="btn btn-sm btn-primary btn-block elevation-2"
                            id="btnFilter">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>                
              @elseif($form == 'inventory')
                <div class="row">
                  <div class="col-lg-3">
                    <div class="form-group">
                      <div class="input-group input-group-sm date onlydate" 
                            id="datetimepicker1" 
                            data-target-input="nearest">
                        <input type="text" 
                                id="from"
                                name="from"
                                class="form-control datetimepicker-input tanggal"
                                placeholder="From Date"
                                data-target="#datetimepicker1"
                                value="{{ Request::get('from') 
                                          ?? today()->startOfMonth()->format('d-m-Y') }}"
                                required>
                        <div class="input-group-append" 
                              data-target="#datetimepicker1" 
                              data-toggle="datetimepicker">
                          <div class="input-group-text">
                            <i class="fa fa-calendar"></i>
                          </div>
                        </div>
                      </div>
                    </div>                  
                  </div>
                  <div class="col-lg-3">
                    <div class="form-group">
                      <div class="input-group input-group-sm date onlydate" 
                            id="datetimepicker2" 
                            data-target-input="nearest">
                        <input type="text" 
                                id="to"
                                name="to"
                                class="form-control datetimepicker-input tanggal"
                                placeholder="To Date"
                                data-target="#datetimepicker2"
                                value="{{ Request::get('to') 
                                          ?? today()->format('d-m-Y') }}"
                                required>
                        <div class="input-group-append" 
                              data-target="#datetimepicker2" 
                              data-toggle="datetimepicker">
                          <div class="input-group-text">
                            <i class="fa fa-calendar"></i>
                          </div>
                        </div>
                      </div>
                    </div>                  
                  </div>                
                  <div class="col-2 col-lg-1">
                    <button type="submit" 
                            class="btn btn-sm btn-primary btn-block elevation-2"
                            id="btnFilter">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>                
              @endif

              </form>
            @endif

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

@if($form == 'tegah')
<div class="modal fade" id="modal-tegah">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Lepas Tegah Barang</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formTegah"
              class="form-horizontal needs-validation" 
              method="post"
              novalidate>
          @csrf
          @method('PUT')
          {{-- <input type="hidden" name="house_id" id="house_id" value=""> --}}
          <!-- REASON -->
          <div class="form-group row">
            <label for="AlasanLepasTegah" 
                   class="col-sm-3 col-form-label">
              Alasan</label>
            <div class="col-sm-9">
              <textarea name="AlasanLepasTegah" 
                        id="AlasanLepasTegah" 
                        class="form-control form-control-sm"
                        rows="5"
                        required></textarea>              
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
        <button type="submit" form="formTegah" 
                class="btn btn-lg btn-primary">
          <i class="fas fa-save"></i> Save
        </button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endif

@if($form == 'current-now-bc')
<div class="modal fade" id="modal-tegah">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 id="tegah-title" class="modal-title">Tegah Barang</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formTegah"
              class="form-horizontal" 
              method="post">
          @csrf
          <input type="hidden" name="house_id" id="house_id" value="">
          <input type="hidden" name="_method" id="method-tegah" value="POST">
          <!-- REASON -->
          <div class="form-group row">
            <label for="AlasanTegah" 
                   class="col-sm-3 col-form-label">
              Alasan</label>
            <div class="col-sm-9">
              <textarea name="AlasanLepasTegah" 
                        id="AlasanLepasTegah" 
                        class="form-control form-control-sm"
                        rows="5"></textarea>
              <textarea name="AlasanTegah" 
                        id="AlasanTegah" 
                        class="form-control form-control-sm"
                        rows="5"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
        <button type="submit" form="formTegah" 
                class="btn btn-lg btn-primary">
          <i class="fas fa-save"></i> Save
        </button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endif

@endsection

@section('footer')
  <script>
    $(function(){
      $('.onlydate').datetimepicker({
        icons: { time: 'far fa-clock' },
        format: 'DD-MM-YYYY',
        sideBySide: true,
        allowInputToggle: true
      });
    });

    function getDataAjax() {
      var data = $('#formBeaCukai').serialize();
      
      $('#dataAjax').DataTable().destroy();

      $('.btn').prop('disabled', true);

      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        data: data,
        success:function(msg){
          $('#dataAjax').DataTable({
            data: msg.data,
            columns:[
              @forelse ($items as $keys => $item)
                @if($keys == 'id')
                  {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false},
                @elseif($keys == 'AL_PENERIMA')
                {
                  data: "{{ $keys }}",
                  defaultContent: '-',
                  render:function(data, type, row){
                    if( type === 'display'){
                      return (data != null && data.length > 30) ?
                              data.substr( 0, 30 ) +'…' :
                              data;
                    } else if ( type === 'export') {
                      return data;
                    }
                  }
                },
                @elseif(in_array($keys, ['TGL_PLP', 'TGL_BC11', 'TGL_SPPB', 'SCAN_IN_DATE','SCAN_OUT_DATE', 'TGL_TIBA', 'TGL_HOUSE_BLAWB', 'TGL_MASTER_BLAWB', 'SPPBDate', 'WK_DOK_INOUT', 'TGL_DOK_INOUT','TGL_SEGEL_BC', 'TGL_DAFTAR_PABEAN']))
                {
                  data: {
                    _: "{{ $keys }}.display",
                    sort: "{{ $keys }}.timestamp",
                  },
                  className:"text-center",
                },
                @else
                {data: "{{$keys}}", name: "{{$keys}}"},
                @endif
              @empty
              @endforelse          
            ],
            buttons: [                
                {
                  extend: 'excelHtml5',                  
                },
                {
                    extend: 'pdfHtml5',
                    download: 'open',
                    orientation : 'landscape',
                    pageSize: { width: 1280, height: 800 },
                    pageMargins: [ 1, 3, 1, 1 ],
                    customize: function (doc) {
                      doc.styles.tableHeader.fontSize = 6;
                      doc.defaultStyle.fontSize = 6;

                      var rowCount = doc.content[1].table.body.length;
                      for (i = 1; i < rowCount; i++) {
                        doc.content[1].table.body[i][0].alignment = 'center';
                        doc.content[1].table.body[i][2].alignment = 'center';
                        doc.content[1].table.body[i][3].alignment = 'center';
                        doc.content[1].table.body[i][4].alignment = 'center';
                        doc.content[1].table.body[i][5].alignment = 'center';
                        doc.content[1].table.body[i][8].alignment = 'center';
                        doc.content[1].table.body[i][10].alignment = 'center';
                      }
                    },                    
                }
            ],           
          })
          .buttons()
          .container()
          .appendTo('#dataAjax_wrapper .col-md-6:eq(0)');

          $('.btn').prop('disabled', false);
        },
        error:function(jqXHR){
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

          $('.btn').prop('disabled', false);
        }
      });
    }
    
    jQuery(document).ready(function(){      
      getDataAjax();
      $(document).on('submit', '#formBeaCukai', function(e){
        e.preventDefault();
        getDataAjax();
      });
      
      @if($form == 'tegah')
      $(document).on('click', '.tegah', function(){
        var id = $(this).attr('data-id');
        var info = $(this).attr('data-info');

        if(info === 'Lepas'){
          $('#tegah-title').html('Lepas Tegah Barang');
          $('#formTegah #house_id').val('');
          $('#formTegah #AlasanLepasTegah').val('')
                                           .attr('required', true)
                                           .removeClass('d-none');
          $('#formTegah #AlasanTegah').val('')
                                      .attr('required', false)
                                      .addClass('d-none');
          var url = "{{ url()->current() }}/"+id;
          $('#method-tegah').val('PUT');
        } else {
          $('#tegah-title').html('Tegah Barang');
          $('#formTegah #house_id').val(id);
          $('#formTegah #AlasanTegah').val('')
                                           .attr('required', true)
                                           .removeClass('d-none');
          $('#formTegah #AlasanLepasTegah').val('')
                                           .attr('required', false)
                                           .addClass('d-none');
          var url = "{{ url()->current() }}";
          $('#method-tegah').val('POST');
        }

        $('#formTegah').attr('action', url);
      });
      $(document).on('submit', '#formTegah', function(e){
        e.preventDefault();
        var action = $(this).attr('action');
        var data = $(this).serialize();

        $('.btn').prop('disabled', 'disabled');

        $.ajax({
          url: action,
          type: "POST",
          data: data,
          success:function(msg){
            toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('#modal-tegah').modal('toggle');

            getDataAjax();
            
            $('.btn').prop('disabled', false);
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        })

      });
      @endif

      @if($form == 'current-now-bc')
      $(document).on('click', '.tegah', function(){
        var id = $(this).attr('data-id');
        $('#formTegah #house_id').val(id);
        $('#formTegah #AlasanTegah').val('');
        $('#formTegah').attr('action', '{{ url()->current() }}');
      });
      $(document).on('submit', '#formTegah', function(e){
        e.preventDefault();
        var action = $(this).attr('action');
        var data = $(this).serialize();

        $('.btn').prop('disabled', 'disabled');

        $.ajax({
          url: action,
          type: "POST",
          data: data,
          success:function(msg){
            toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('#modal-tegah').modal('toggle');

            getDataAjax();
            
            $('.btn').prop('disabled', false);
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        })

      });
      @endif
    });
  </script>
@endsection
