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
    jQuery(document).ready(function(){
      var table = $('#dataAjax').DataTable({
        responsive:true,
        processing: true,
        serverSide: true,
        ajax: "{{ url()->current() }}",
        columns:[
          @forelse ($items as $keys => $item)
          {data: "{{$keys}}", name: "{{$keys}}"},
          @empty
          @endforelse          
        ]
      });
    });
  </script>
@endsection
