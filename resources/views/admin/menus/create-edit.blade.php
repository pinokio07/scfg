@extends('layouts.master')
@section('title') Menu @endsection
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
            @if(!$menu->name == '')
              <form action="/administrator/menus/{{$menu->id}}" method="post">
                @method('PUT')
            @else
              <form action="/administrator/menus" method="post">
            @endif
                @csrf
            <div class="card-body">
              <div class="form-group form-group-sm">
                <label for="name">Name</label>
                <input type="text" class="form-control" name="name" id="name" required value="{{ old('name') ?? $menu->name }}">
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-sm btn-success elevation-2">Save</button>
            </div>
            </form>
          </div>          
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection