@extends('layouts.master')
@section('title') Shipments @endsection
@section('page_name') Shipments @endsection

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

      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        success:function(msg){
          // console.log(msg.data);
          $('#dataAjax').DataTable({
            data: msg.data,
            pageLength: parseInt("{{ config('app.page_length') }}"),
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
              this.api().columns([1,2,3,4,6,9,13]).every( function () {
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
      // getDataAjax();
      var table = $('#dataAjax').DataTable({
            processing: true,
            serverSide: true,
            searchDelay: 350,
            pageLength: parseInt("{{ config('app.page_length') }}"),
            ajax: {
              url:"{{ url()->current() }}",
              type: "GET",
              // data: function (d) {
              //   var s = d.search.value;
              //   d.search.value = s.replace('-', '');
                
              //   return d;
              // }
            },
            columns:[
              @forelse ($items as $keys => $item)
                @if($keys == 'id')
                  {data:"DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
                @elseif(in_array($keys, ['TGL_TIBA', 'ExitDate', 'SCAN_IN_DATE']))
                {
                  data: {
                    _: "{{ $keys }}.display",
                    sort: "{{ $keys }}.timestamp",
                  },
                  name: "{{ $keys }}",
                },
                @elseif($keys == 'NO_BARANG')
                {
                  data: "{{ $keys }}",
                  name: "{{ $keys }}",
                  render: function(data, type, row) {
                      if (type === 'display') {
                          return '<a href="' + data.url + '" class="url">' + data.raw + '</a> ';
                      }
                      return data;
                  }
                },
                @elseif($keys == 'NO_MASTER_BLAWB')
                {
                  data:"{{ $keys }}",
                  name: "{{ $keys }}",
                  render: function(data, type, row) {                      
                      return data.display;
                  }
                },
                @elseif($keys == 'AL_PENERIMA')
                {
                  data: "{{ $keys }}",
                  name: "{{ $keys }}",
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
              $( 'td' , row ).eq(5)
                  .attr('data-toggle', 'tooltip')
                  .attr('title', data['AL_PENERIMA']);                 
            },
            initComplete: function () {
              this.api().columns([1,2,3,4,6,9]).every( function () {
                var column = this;
                var select = $('<select class="select2bs4clear" style="width: 100%;"><option value="">Select...</option></select>')
                .appendTo( $(column.footer(3)).empty() )
                .on( 'change', function () {
                  var val = $(this).val();
                  if(!moment(val).isValid())
                  {
                    val = $.fn.dataTable.util.escapeRegex(
                            val.replace('-', '')
                          );
                  }
                  column
                  .search( val ? '^'+val+'$' : '', true, false )
                  .draw();
                } );

                column.data().unique().sort().each( function ( d ) {
                  if(d !== '' && d != null){
                    var s = '';
                    var v = '';
                    if(d.hasOwnProperty('raw'))
                    {
                      s = d.raw;
                      if(d.hasOwnProperty('display'))
                      {
                        v = d.display;
                      } else {
                        v = s;
                      }                      
                    } else if(moment(d, 'DD/MM/YYYY', true).isValid()){
                      s = moment(d, 'DD/MM/YYYY', true).format('YYYY-MM-DD');
                      v = d;
                    } else {
                      s = d;
                      v = s;
                    }
                    select.append( '<option value="'+s+'">'+v+'</option>' )
                  }               
                } );
              } );

              select2bs4Clear();
            }, 
          });
      
    });
  </script>
@endsection
