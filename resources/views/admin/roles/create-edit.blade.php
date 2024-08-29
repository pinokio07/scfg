@extends('layouts.master')
@section('title') Role @endsection
@section('page_name') Role @endsection

@section('header')
  <style>
    /* Remove default bullets */
    ul, #treeUl {
      list-style-type: none;
    }

    /* Remove margins and padding from the parent ul */
    #treeUl {
      margin: 0;
      padding: 0;
    }

    /* Style the caret/arrow */
    .caret {
      cursor: pointer;
      user-select: none; /* Prevent text selection */
    }

    /* Create the caret/arrow with a unicode, and style it */
    .caret::before {
      content: "\229E";
      color: black;
      display: inline-block;
      margin-right: 6px;
    }

    /* Rotate the caret/arrow icon when clicked on (using JavaScript) */
    .caret-down::before {
      content: "\25A2";
      /* transform: rotate(90deg); */
    }

    /* Hide the nested list */
    .nested {
      display: none;
    }

    /* Show the nested list when the user clicks on the caret/arrow (with JavaScript) */
    .active {
      display: block;
    }
  </style>
@endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      @if(!$role->name == '')
        <form id="formRole" action="/administrator/roles/{{$role->id}}" method="post">
          @method('PUT')
      @else
        <form id="formRole" action="/administrator/roles" method="post">
      @endif
      @csrf
        <div class="row">
          <div class="col-md-12">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title">Role</h3>
              </div>              
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
                  <input type="text" 
                         class="form-control form-control-sm" 
                         name="name" 
                         id="name" 
                         required 
                         value="{{ old('name') ?? $role->name ?? "" }}">
                </div>
              </div>                         
            </div>          
          </div>
          <div class="col-lg-6">
            <div class="card card-success card-outline">
              <div class="card-header">
                <h3 class="card-title">Permissions List</h3>
              </div>
              <div class="card-body overflow-auto" style="height: 50vh;">
                <?php $rolePermissions = $role->permissions->pluck('name')->toArray(); ?>
                <div class="row">
                  @forelse ($permissions->groupBy('group') as $key => $permission)
                  <div class="col-12">
                    <ul id="treeUl">
                      <li>
                        <span class="caret">          
                          <label>{{Str::title($key)}}</label>          
                        </span>
                        <input type="checkbox" class="check-all all_{{$key}}" data-key="{{$key}}">
                        <ul class="nested">          
                          @foreach ($permission->sortBy('name') as $p)
                            <li>
                              <div class="form-check">
                                <input class="form-check-input child_{{$key}} child" type="checkbox" name="permission[]" value="{{$p->name}}" @if(in_array($p->name, $rolePermissions)) checked @endif data-key="{{$key}}">
                                <label class="form-check-label card-text">{{$p->name}}</label>
                              </div>
                            </li>
                          @endforeach
                        </ul>
                      </li>
                    </ul>
                  </div>
                  @empty
                    Permissions Empty
                  @endforelse 
                </div>                               
              </div>              
            </div>
          </div>
          @if($role->id)
          <div class="col-lg-6">
            <div class="card card-warning card-outline">
              <div class="card-header">
                <h3 class="card-title">
                  User List
                </h3>
              </div>
              <div class="card-body overflow-auto" style="height: 50vh;">
                <table id="tblUsers" class="table table-striped table-sm" style="width: 100%;">
                  <thead>
                    <tr>
                      <th>User Name</th>
                      <th>Email</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($users as $user)
                      <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                          <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox"
                                   name="users[]"
                                   id="user_{{ $user->id }}" 
                                   value="{{ $user->id }}"
                                   @if($user->hasRole($role->name)) checked @endif>
                          </div>
                        </td>
                      </tr>
                    @empty
                      
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          @endif
          <div class="col-12">
            <div class="card">
              <div class="card-footer">
                <div class="row">
                  <div class="col-lg-4">
                    <button type="submit" 
                            class="btn btn-sm btn-block btn-success elevation-2">Save</button>
                  </div>
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

@section('footer')
<script>
  var toggler = document.getElementsByClassName("caret");
  var i;

  for (i = 0; i < toggler.length; i++) {
    toggler[i].addEventListener("click", function() {
      this.parentElement.querySelector(".nested").classList.toggle("active");
      this.classList.toggle("caret-down");
    });
  }

  function checkedAll()
  {      
    $('.check-all').each(function(){
      var key = $(this).data('key');
      var child = $('.child_'+key);

      if(child.filter(':checked').length == child.length){
        $('.all_'+key).prop('checked', true);
      }
    })
  }

  jQuery(document).ready(function(){

    $('#tblUsers').DataTable({
      paging:false,
      stateSave:true,
    });

    checkedAll();

    $(document).on('click', '.check-all', function() {
      var key = $(this).data('key');
      if($(this).is(':checked')){
        $('.child_'+key).each(function(){
          $(this).prop('checked', true);                 
        });
        $(this).prev('.caret').addClass('caret-down');
        $(this).next('.nested').addClass('active');
      } else {
        $('.child_'+key).each(function(){
          $(this).prop('checked', false);
        });
      }
    });
    $(document).on('click', '.child', function(){
      var key = $(this).data('key');
      var child = $('.child_'+key);

      if(child.filter(':checked').length != child.length){
        $('.all_'+key).prop('checked', false);
      }
    })
  });

</script>
@endsection