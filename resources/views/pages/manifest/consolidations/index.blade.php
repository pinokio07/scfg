@extends('layouts.master')
@section('title') Consolidations @endsection
@section('page_name') Consolidations @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Consolidations</h3>
            <div class="card-tools">              
              <a href="{{ route('manifest.consolidations.create') }}" 
                 class="btn btn-sm btn-primary elevation-2">
                 <i class="fas fa-plus"></i>
              </a>
              @can('upload.master')
              <div class="btn-group">
                <button type="button"
                        class="btn btn-warning elevation-2 dropdown-toggle dropdown-icon"
                        data-toggle="dropdown">
                  <i class="fas fa-download"></i> Upload
                </button>
                <div class="dropdown-menu">
                  <button class="dropdown-item upload"
                          data-toggle="modal"
                          data-target="#modal-upload"
                          data-action="{{ route('upload.data') }}?jenis=master">
                    <i class="fas fa-upload"></i> From Manifest                
                  </button>
                  <button class="dropdown-item upload"
                          data-toggle="modal"
                          data-target="#modal-upload"
                          data-action="{{ route('upload.data') }}?jenis=barkir">
                    <i class="fas fa-upload"></i> From Barkir
                  </button>
                </div>
              </div>
              @endcan
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

@include('forms.upload', ['action' => '#'])

@endsection

@section('footer')
  <script>
    function getDataAjax() {
      $('#dataAjax').DataTable().destroy();

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
                @elseif($keys == 'ArrivalDate')
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
                'excelHtml5',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                },
                'print',
            ],
            initComplete: function () {
              this.api().columns([2,3,4,5,6,7,8]).every( function () {
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
          data: function (d) {
            var s = d.search.value;
            d.search.value = s.replace('-', '');
            d.branch_id = $('#branch_id').find(':selected').val();
            return d;
          }
        },
        columns:[
          @forelse ($items as $keys => $item)
            @if($keys == 'id')
              {data:"DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
            @elseif($keys == 'ArrivalDate')
            {
              data: {
                _: "{{ $keys }}.display",
                sort: "{{ $keys }}.timestamp", 
              },
              name: "{{ $keys }}",
            },
            @elseif($keys == 'MAWBNumber')
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
            @elseif(in_array($keys, ['pending','pendingXRAY','released','UploadStatus']))
                {data: "{{$keys}}", name: "{{$keys}}", searchable: false},
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
        initComplete: function () {
          this.api().columns([1,2,3,5]).every( function () {
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
              // console.log(val);
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
                  v = s;
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

      $(document).on('click', '.upload', function(e){
        var action = $(this).data('action');

        $('#formUpload').attr('action', action);
      });
    });
  </script>
@endsection
