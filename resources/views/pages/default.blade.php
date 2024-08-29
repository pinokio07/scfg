@extends('layouts.master')
@section('title') {{Str::title(Request::segment(1))}} @endsection
@section('page_name') {{Str::title(Request::segment(1))}} @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">{{Str::title(Request::segment(1))}} Page</h3>
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