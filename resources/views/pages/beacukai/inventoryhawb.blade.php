@extends('layouts.master')
@section('title') 
  @if(Request::segment(2) == 'inventory') 
    Inventory Details  
  @else
    Inventory by HAWB
  @endif
@endsection
@section('page_name') 
  @if(Request::segment(2) == 'inventory') 
    Inventory Details  
  @else
    Inventory by HAWB
  @endif
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
              @if(Request::segment(2) == 'inventory') 
                Inventory Details  
              @else
                Inventory by HAWB
              @endif
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            @if(Request::segment(2) == 'inventory-hawb')
              <form id="formBeaCukai" 
                    action="{{ url()->current() }}" 
                    method="get">
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
                                value="{{ Request::get('from') ?? '' }}"
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
                                value="{{ Request::get('to') ?? '' }}"
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


@endsection

@section('footer')
  <script>
    @if(Request::segment(2) == 'inventory-hawb')
      $(function(){
        $('.onlydate').datetimepicker({
          icons: { time: 'far fa-clock' },
          format: 'DD-MM-YYYY',
          sideBySide: true,
          allowInputToggle: true
        });
      });
    @endif

    function getDataAjax() {
      @if(Request::segment(2) == 'inventory-hawb')
        var data = $('#formBeaCukai').serialize();
      @else
        var data = '';
      @endif
      
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
                @elseif(in_array($keys, ['TGL_PLP', 'TGL_BC11', 'TGL_SPPB', 'SCAN_IN_DATE', 'SCAN_OUT_DATE']))
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
                    var from = $('#from').val();
                    var to = $('#to').val();
                    if(from == ''
                        || to == ''){
                      alert('Please Select Dates');

                      return false;
                    }
                    window.open("{{ route('download.bea-cukai.inventory') }}?jenis=xls&from="+from+"&to="+to);
                  }
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    download: 'open',
                    exportOptions: { 
                      orthogonal: 'export',
                      // columns: [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14]
                    },
                    customize: function(doc) {
                        doc.styles.tableHeader.fontSize = 7;
                        doc.defaultStyle.fontSize = 6;
                    } 
                },
                {
                  extend: 'print',
                  orientation: 'landscape',
                  exportOptions: { 
                    orthogonal: 'export',
                    // columns: [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14]
                  }
                },
            ],
            createdRow: function( row, data, dataIndex ) {
                // Set the data-status attribute, and add a class
                // console.log(data['AL_PENERIMA']);
              $( 'td' , row ).eq(13)
                  // .attr('data-toggle', 'tooltip')
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
      @if(Request::segment(2) == 'inventory')
        getDataAjax();
      @else
        $(document).on('submit', '#formBeaCukai', function(e){
          e.preventDefault();
          getDataAjax();
        });
      @endif
    });
  </script>
@endsection
