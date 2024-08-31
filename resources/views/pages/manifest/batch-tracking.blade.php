@extends('layouts.master')
@section('title') Batch Tracking @endsection
@section('page_name') Batch Tracking @endsection
@section('header')
  <style>
    .w-bc{
      min-width: 280px !important;
    }
  </style>
@endsection
@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Batch Tracking</h3>
            <div class="card-tools"> 
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-12">
                <form id="formSearch" action="{{ url()->current() }}"
                      method="POST">
                  @csrf
                  <div class="form-group">
                    <label for="houses">C/N</label>
                    <textarea name="houses"
                              id="houses"
                              class="form-control"
                              cols="30"
                              rows="10"
                              required>{{ old('houses') ?? '' }}</textarea>
                  </div>
                  <div class="form-group">
                    <label for="bc_code">BC Code</label>
                    <select name="bc_code[]"
                            id="bc_code"
                            class="select2bs4multiple"
                            multiple
                            style="width: 100%;">
                      {{-- <option value="">All...</option> --}}
                      @forelse (bcCodes() as $code)
                        <option value="{{ $code }}">{{ $code }}</option>
                      @empty
                        
                      @endforelse
                    </select>
                  </div>
                  <button class="btn btn-primary btn-block elevation-2">
                    <i class="fas fa-search"></i> Search
                  </button>
                </form>
              </div>
              <div class="col-12 mt-4">
                <button id="btnPrint" class="btn btn-success btn-block elevation-2 mb-4 d-none">
                  <i class="fas fa-download"></i> Download
                </button>
                <div class="table-responsive">
                  @include('table.ajax')
                </div>
              </div>
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
@endsection

@section('footer')
  <script>
    jQuery(document).ready(function(){
      var table = $('#dataAjax').DataTable();

      $(document).on('submit', '#formSearch', function(e){
        e.preventDefault();
        $('#btnPrint').addClass('d-none');
        $('.btn').prop('disabled', true);
        table = $('#dataAjax').DataTable().destroy();

        $.ajax({
          url: $(this).attr('action'),
          type: "POST",
          data: $(this).serialize(),
          success: function(msg){
            table = $('#dataAjax').DataTable({
                      data:msg.data,
                      pageLength: parseInt("{{ config('app.page_length') }}"),
                      columns:[            
                        @forelse ($items as $keys => $item)
                          @if($keys == 'id')
                            {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false},
                          @elseif($keys == 'cekbox')
                            {data: "{{$keys}}", name: "{{$keys}}", className:"text-center", searchable: false, orderable: false},
                          @elseif($keys == 'STATUS_BC')
                          {data: "{{$keys}}", name: "{{$keys}}", className:"text-wrap w-bc"},
                          @else
                            {data: "{{$keys}}", name: "{{$keys}}"},
                          @endif
                        @empty
                        @endforelse 
                      ],
                      buttons: [
                          'excelHtml5',
                          {
                              extend: 'pdfHtml5',
                              orientation: 'landscape',
                              pageSize: 'LEGAL'
                          },
                          'print',
                      ],
                    });

            $('.btn').prop('disabled', false);
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            showError(jqXHR.status + ' || ' + jsonValue.message);
            $('.btn').prop('disabled', false);
          }
        })
      });
      $(document).on('click', '#selectall', function(){
        var checked = this.checked;
        
        table.column(5).nodes().to$().each(function(index) {    
          $(this).find('.check').prop('checked', checked);
        });
        if(checked)
        {
          $('#btnPrint').removeClass('d-none');
        } else {
          $('#btnPrint').addClass('d-none');
        }
      });
      $(document).on('click', '.check', function(){
        var checked = this.checked;
        var all = 0;
        var ck = 0;
        var tb = table.rows().nodes();

        $('.check', tb).each(function(){
          all += 1;
          if(this.checked){
            ck += 1;
          }
        });

        if(checked === false){
          $('#selectall').prop('checked', false);
        } else {
          if(ck === all){
            $('#selectall').prop('checked', true);
          } else {
            $('#selectall').prop('checked', false);
          }
        }

        if(ck > 0) {
          $('#btnPrint').removeClass('d-none');
        } else {
          $('#btnPrint').addClass('d-none');
        }
      });
      $(document).on('click', '#btnPrint', function(){
        var tb = table.rows().nodes();
        var ids = [];

        $('.check:checked', tb).each(function(){
          ids.push($(this).val());
        });

        Swal.fire({
          title: "Batch Download PDF!",
          html: "<span id='downloadinfo'>Please wait while we completed merge the PDF..</span><br>",
          // timer: 2000,
          timerProgressBar: true,
          allowOutsideClick: false,
          allowEscapeKey: false,
          returnFocus: false,
          didOpen: () => {
            Swal.showLoading();
            // const timer = Swal.getPopup().querySelector("b");
            // timerInterval = setInterval(() => {
            //   timer.textContent = `${Swal.getTimerLeft()}`;
            // }, 100);
            $.ajax({
              url: "{{ route('download.manifest.batch-tracking') }}",
              type: "GET",
              data: {
                ids: ids
              },
              success: function(msg){
                Swal.hideLoading();
                if(msg.status == 'OK') {
                  $('#downloadinfo').removeClass('text-danger')
                                    .html('Merge PDF Completed!<br><a href="'+msg.pdf+'" target="_blank">Merged_Result.pdf</a><br><a href="'+msg.zip+'" target="_blank">Merged_Result.zip</a>');
                } else {
                  $('#downloadinfo').addClass('text-danger').html('ERROR! <br>'+msg.message);
                }
                
              },
              error:function(jqXHR){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                // showError(jqXHR.status + ' || ' + jsonValue.message);
                Swal.hideLoading();
                $('#downloadinfo').addClass('text-danger').html('ERROR! <br>'+jqXHR.status + ' || ' + jsonValue.message);
              }

            })
          },
          // willClose: () => {
          //   clearInterval(timerInterval);
          // }
        }).then((result) => {
          /* Read more about handling dismissals below */
          // if (result.dismiss === Swal.DismissReason.timer) {
          //   console.log("I was closed by the timer");
          // }
        });

        
      })
    });
  </script>
@endsection

