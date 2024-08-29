@extends('layouts.master')
@section('title') Permissions @endsection
@section('page_name') Permission Detail @endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Detail</h3>
              <div class="card-tools">
                <a href="{{url()->current()}}/edit" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">      
              <p>Name : {{$permission->name}}</p>
              <p>Guard : {{$permission->guard_name}}</p>
              <p>Group : {{$permission->group}}</p>
              <p>Roles with Permission: </p>
              <ul>
                @forelse ($permission->roles as $role)
                  <li>{{$role->name}}</li>
                @empty
                  <li>Empty</li>
                @endforelse
              </ul>
              <p>Menu with permissions: </p>
              <ul>
                @forelse ($itemMenus as $item)
                  <li><b>{{$item->title}}</b> ({{$item->url}})</li>
                @empty
                  <li>Empty</li>
                @endforelse
              </ul>
            </div>
          </div>          
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection