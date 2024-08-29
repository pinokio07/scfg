@extends('layouts.master')
@section('title') Abandon @endsection
@section('page_name') Abandon @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Abandon</h3>
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

<div class="modal fade" id="modal-tegah">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Tegah Barang</h4>
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
          <!-- REASON -->
          <div class="form-group row">
            <label for="AlasanTegah" 
                   class="col-sm-3 col-form-label">
              Alasan</label>
            <div class="col-sm-9">
              <textarea name="AlasanTegah" 
                        id="AlasanTegah" 
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
    })
    function getDataAjax() {
      
      $('#dataAjax').DataTable().destroy();

      $('.btn').prop('disabled', true);

      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
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
                @elseif($keys == 'TGL_PLP')
                {
                  data: {
                    _: "{{ $keys }}.display",
                    sort: "{{ $keys }}.timestamp",
                  }
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
                  exportOptions: { 
                    orthogonal: 'export',
                    columns: [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14]
                  },
                  customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];

                    $('c[r=A1] t', sheet).text('ABANDONED ITEMS {{ Str::upper(activeCompany()->GC_Name) }}');
                  }
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    title: 'ABANDONED ITEMS {{ Str::upper(activeCompany()->GC_Name) }}',
                    download: 'open',
                    exportOptions: { 
                      orthogonal: 'export',
                      columns: [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13]
                    },
                    customize: function(doc) {
                        doc.styles.tableHeader.fontSize = 7;
                        doc.defaultStyle.fontSize = 6;
                    },
                    messageTop: "Print at " + moment().format('DD-MM-YYYY hh:mm')
                },
                {
                  extend: 'print',
                  orientation: 'landscape',
                  exportOptions: { 
                    orthogonal: 'export',
                    columns: [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13]
                  }
                },
            ],
            createdRow: function( row, data, dataIndex ) {
                // Set the data-status attribute, and add a class
                // console.log(data['AL_PENERIMA']);
              $( 'td' , row ).eq(11)
                  .attr('data-toggle', 'tooltip')
                  .attr('title', data['AL_PENERIMA']);                 
            }
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

      $(document).on('click', '#btnFilter', function(){
        getDataAjax();
      });
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
    });
  </script>
@endsection
