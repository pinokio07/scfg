@extends('layouts.master')
@section('title') Menus @endsection
@section('page_name') Menu Lists @endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Menu</h3>
            </div>
            <div class="card-body">
              @include('buttons.add', ['link' => url()->current().'/create'])
              @include('table.admin', ['from' => 'menu'])
            </div>
          </div>          
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection