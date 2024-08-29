@extends('layouts.master')
@section('title') Permission @endsection
@section('page_name') Permission @endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Permission</h3>
            </div>
            @if(!$permission->name == '')
              <form action="/administrator/permissions/{{$permission->id}}" method="post">
                @method('PUT')
            @else
              <form action="/administrator/permissions" method="post">
            @endif
                @csrf
            <div class="card-body">

              @if (count($errors) > 0)
                <div class="row">
                  <div class="col-12">
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                  </div>
                </div>
              @endif
              
              <div class="form-group form-group-sm">
                <label for="name">Name</label>
                <input type="text" class="form-control form-control-sm" name="name" id="name" required value="{{ old('name') ?? $permission->name ?? '' }}">
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group form-group-sm">
                    <label for="group">Create Group Name</label>
                    <input type="text" class="form-control form-control-sm" name="group" id="group" @if($permission->group == '') required @endif value="{{ old('group') ?? $permission->group ?? '' }}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group form-group-sm">
                    <label for="exists_group">Or select from lists</label>
                    <select name="exists_group" id="exists_group" class="select2bs4" style="width: 100%;">
                        <option value="" selected disabled>Choose...</option>
                        @forelse ($groups as $group)
                          <option value="{{$group}}" @if($group == $permission->group) selected @endif>{{$group}}</option>
                        @empty
                          <option disabled>Item Empty</option>
                        @endforelse
                    </select>
                  </div>
                </div>
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

@section('footer')
  <script>
    jQuery(document).ready(function(){
      $(document).on('change', '#exists_group', function(){
        var val = $(this).val();

        $('#group').val(val);
      });
    });
  </script>
@endsection