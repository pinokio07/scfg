@extends('layouts.master')
@section('title') Permissions @endsection
@section('page_name') Permissions @endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">User Permissions</h3>
            </div>
            <div class="card-body">
              @include('buttons.add', ['link' => url()->current().'/create'])
              @include('table.admin')
            </div>
          </div>          
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection