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
      
      getDataAjax();
      $(document).on('submit', '#formBeaCukai', function(e){
        e.preventDefault();
        getDataAjax();
      });
    });
  </script>
@endsection
