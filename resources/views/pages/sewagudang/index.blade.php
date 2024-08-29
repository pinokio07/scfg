@extends('layouts.master')
@section('title') Sewa Gudang @endsection
@section('page_name') Sewa Gudang @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Shipments</h3>
            <div class="card-tools">              
              {{-- <a href="{{ route('manifest.shipments.create') }}" 
                 class="btn btn-sm btn-primary elevation-2">
                 <i class="fas fa-plus"></i>
              </a> --}}
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <form id="formSearch" action="{{ url()->current() }}" method="get">
              <div class="row">
                <div class="col-lg-2">
                  <div class="form-group">
                    <div class="input-group input-group-sm date onlydate" 
                          id="datetimepicker1" 
                          data-target-input="nearest">
                      <input type="search" 
                              id="tanggal"
                              name="tanggal"
                              class="form-control datetimepicker-input tanggal"
                              placeholder="Date"
                              data-target="#datetimepicker1"
                              value="{{ Request::get('tanggal') ?? today()->format('d-m-Y') }}"
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
              </div>
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

@endsection

@section('footer')
  <script>
    function getDataAjax() {
      $('#dataAjax').DataTable().destroy();
      var data = $('#formSearch').serialize();

      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        data:data,
        success:function(msg){
          console.log(msg.data);
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
            ],
            createdRow: function( row, data, dataIndex ) {
                // Set the data-status attribute, and add a class
                // console.log(data['AL_PENERIMA']);
              $( 'td' , row ).eq(5)
                  .attr('data-toggle', 'tooltip')
                  .attr('title', data['AL_PENERIMA']);                 
            },
            initComplete: function () {
              this.api().columns([1,2,3,4,5,6,8]).every( function () {
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
          
          
        }
      })
    }
    jQuery(document).ready(function(){
      getDataAjax();

      $('.onlydate').datetimepicker({
        icons: { time: 'far fa-clock' },
        format: 'DD-MM-YYYY',
        sideBySide: true,
        // allowInputToggle: true
      });

      $(document).on('change.datetimepicker', '#datetimepicker1', function(){
        getDataAjax();
      })
      
    });
  </script>
@endsection
