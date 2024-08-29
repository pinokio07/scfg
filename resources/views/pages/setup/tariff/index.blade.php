@extends('layouts.master')
@section('title') {{ Str::title(Request::segment(2)) }} @endsection
@section('page_name') {{ Str::title(Request::segment(2)) }} @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">{{ Str::title(Request::segment(2)) }}</h3>
            <div class="card-tools">
              <a href="{{url()->current()}}/create" class="btn btn-success elevation-2">
                <i class="fas fa-plus-circle"></i>
                Add                
              </a>
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

@include('forms.upload', ['action' => '/upload/'.Request::path()])
@endsection

@section('footer')
  <script>
    function refreshTbl() {
      $('#dataAjax').DataTable().destroy();

      $.ajax({
        url: "{{ url()->current() }}",
        type: "GET",
        success:function(msg){
          $('#dataAjax').DataTable({
            data:msg.data,
            columns:[
              @forelse ($items as $keys => $item)
                @if($keys == 'id')
                {data: "DT_RowIndex", name: "DT_RowIndex", searchable: false},
                @elseif($keys == 'minimum')
                {
                  data: "{{$keys}}",
                  name: "{{$keys}}",
                  className: 'text-right',
                  render: $.fn.dataTable.render.number( ',', '.', 2 ),
                },
                @else
                  {data: "{{$keys}}", name: "{{$keys}}"},
                @endif
              @empty
              @endforelse          
            ]
          })
        }
      })
    }
    jQuery(document).ready(function(){
      refreshTbl();

      $(document).on('click', '.duplicate', function(){
        var href = $(this).attr('data-href');

        Swal.fire({			
          title: 'Duplicate current Data?',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, duplicate!'
        }).then((result) => {
          if (result.value) {
            window.location.replace(href);
          }
        });
      })
    });
  </script>
@endsection
