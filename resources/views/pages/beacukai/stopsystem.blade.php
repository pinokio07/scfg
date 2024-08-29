@extends('layouts.master')
@section('title') Stop System @endsection
@section('page_name') Stop System @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Stop System</h3>
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
        <h4 id="tegah-title" class="modal-title"></h4>
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
          <input type="hidden" name="house_id" id="house_id" value="">
          <input type="hidden" name="_method" id="method-tegah" value="POST">
          <!-- REASON -->
          <div class="form-group row">
            <label for="AlasanLepasTegah" 
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
                @elseif($keys == 'TanggalTegah')
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
                  action: function ( e, dt, node, config ) {
                    window.open("{{ route('download.bea-cukai.stop-system', ['jenis' => 'xls']) }}");
                  }
                },
                {
                    extend: 'pdfHtml5',
                    action: function ( e, dt, node, config ) {
                    window.open("{{ route('download.bea-cukai.stop-system', ['jenis' => 'pdf']) }}");
                  }
                }
            ]
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
    });
  </script>
@endsection
