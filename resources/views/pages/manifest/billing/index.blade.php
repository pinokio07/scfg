@extends('layouts.master')
@section('title') Billing Consolidations @endsection
@section('page_name') Billing Consolidations @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Billing Consolidations</h3>
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

<div class="modal fade" id="modal-jobheader">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Job Header Created</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-sm" style="width: 100%;">
          <thead>
            <tr>
              <th>Master</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody id="body-jobheader"></tbody>
        </table>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

@endsection

@section('footer')
  {{-- <script src="https://cdn.datatables.net/plug-ins/2.0.8/dataRender/datetime.js"></script> --}}
  <script>
    
    jQuery(document).ready(function(){
      
      var table = $('#dataAjax').DataTable({
            processing: true,
            serverSide: true,
            searchDelay: 350,
            pageLength: parseInt("{{ config('app.page_length') }}"),
            ajax: {
              url:"{{ url()->current() }}",
              type: "GET",
            },
            columns:[
              @forelse ($items as $keys => $item)
                @if($keys == 'id')
                  {data:"DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                @elseif($keys == 'TOTAL_BILLING')
                {
                  data: "{{ $keys }}",
                  name: "{{ $keys }}",
                  render: DataTable.render.number('.',',',2),
                  className: "text-right pr-3"
                },
                @elseif(in_array($keys, ['WK_REKAM', 'TGL_BILLING', 'TGL_JT_TEMPO']))
                {
                  data: "{{ $keys }}",
                  name: "{{ $keys }}",
                  render: function (data, type, row) {
                    if (type === 'display') {
                      if(isNaN(data) && moment(data, 'YYYY-MM-DD HH:mm:ss', true).isValid())
                      {
                          return moment(data, 'YYYY-MM-DD HH:mm:ss').format('DD-MM-YYYY HH:mm:ss');
                      }
                    }
                    return data;
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
                  exportOptions: { orthogonal: 'export' }
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    exportOptions: { orthogonal: 'export' }
                },
                {
                  extend: 'print',
                  exportOptions: { orthogonal: 'export' }
                },
            ]
          });
      $(document).on('click', '.jobcost', function(){
        var id = $(this).attr('data-id');

        Swal.fire({			
          title: 'Create Job Cost?',			
          html:
            "Create cost from this Consolidation Billing?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, create!'
        }).then((result) => {
          if (result.value) {
            // $('#hapus').submit();
            $('#body-jobheader').html('<tr><td colspan="2">Loading...<td></tr>');

            $('#modal-jobheader').modal({backdrop: 'static', keyboard: false}, 'show');
            $.ajax({
              url: "{{ route('manifest.billing-consolidation.store') }}",
              type: "POST",
              data: {id:id},
              success: function(msg){
                console.log(msg);
                if(msg.status == 'OK'){
                  var master = msg.master;
                  if(master.length > 0)
                  {
                    var tr = '';
                    $.each(master, function(k, m){
                      console.log(m.mid);
                      if(m.mid != '' && m.mid != null)
                      {
                        tr += '<tr>';
                        tr += '<td><a href="/manifest/consolidations/'+m.mEncrypted+'/edit" target="_blank">'+m.data.NO_MASTER_BLAWB+'</a></td>';
                        tr += '<td class="text-right">'+formatAsMoney(m.total)+'</td>';
                        tr += '</tr>';
                      }
                    });

                    $('#body-jobheader').html(tr);
                  }
                  
                } else {
                  showError(msg.message);
                }
              },
              error:function(jqXHR){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                showError(jqXHR.status + ' || ' + jsonValue.message);
                // toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            });
          }
        });

      });
      
    });
  </script>
@endsection
