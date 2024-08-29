@extends('layouts.master')
@section('title') Exchange Rate @endsection
@section('page_name') Exchange Rates @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Exchange Rate</h3>
            <div class="card-tools">
              <a href="{{url()->current()}}/create" class="btn btn-success elevation-2">
                <i class="fas fa-plus-circle"></i>
                Add
              </a>
              <a href="/download/{{ Request::path() }}" class="btn btn-info elevation-2">
                <i class="fas fa-download"></i>
                Download                
              </a>
              <button type="button"
                      class="btn btn-warning elevation-2"
                      data-toggle="modal"
                      data-target="#modal-upload">
                <i class="fas fa-upload"></i> Upload
              </button>
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
    $(function(){
      var table = $('#dataAjax').DataTable({
        responsive:true,
        processing: true,
        serverSide: true,
        ajax: "{{ url()->current() }}",
        columns:[
          @forelse ($items as $keys => $item)
            @if($keys == 'RE_SellRate')
              {
                data: "{{$keys}}",
                name: "{{$keys}}",
                className: 'text-right',
                render: DataTable.render.number(',', '.', 2)
              },
            @else
              {data: "{{$keys}}", name: "{{$keys}}"},
            @endif
          @empty
          @endforelse
        ]
      });      
    });
    jQuery(document).ready(function(){
      $('.select2bs4').select2({
        theme: 'bootstrap4'
      });
    });
  </script>
@endsection
