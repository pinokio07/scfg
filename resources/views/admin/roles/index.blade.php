@extends('layouts.master')
@section('title') Roles @endsection
@section('page_name') User Roles @endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">User Roles</h3>
            </div>
            <div class="card-body">
              @include('buttons.add', ['link' => url()->current().'/create'])
              @include('buttons.download', ['link' => '/administrator/download?model=Roles'])
              @include('table.admin')
            </div>
          </div>          
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection