@extends('layouts.master')
@section('title') Role @endsection
@section('page_name') Role @endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">      
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Role - {{$role->name}}</h3>
                <div class="card-tools">
                  <a href="{{url()->current()}}/edit" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>              
              <div class="card-body">
                <div class="row">
                  @forelse ($role->permissions->groupBy('group') as $key => $permission)
                    <div class="card-group">
                      <div class="card">
                        <div class="card-header">
                          <h3 class="card-title"><b>{{Str::title($key)}}</b></h3>
                        </div>
                        <div class="card-body">
                          <ol>
                            @foreach ($permission->sortBy('name') as $p)
                              <li>{{$p->name}}</li>                
                            @endforeach
                          </ol>             
                        </div>
                      </div>
                    </div>                    
                  @empty
                    Permissions Empty
                  @endforelse
                </div>                 
              </div>                        
            </div>          
          </div>          
        </div>
      </form>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection