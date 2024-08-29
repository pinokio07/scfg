@extends('layouts.master')
@section('header')
<link rel="stylesheet" href="{{ asset('adminlte/plugins/nestable/nestable.min.css') }}">
<style>  
  .pull-right{
    float: right !important;
  }
  .item_actions{
    z-index: 9;
    position: relative;
    top: 4px;
    right: 10px;
  }
</style>
@endsection
@section('title') Menu Builder @endsection
@section('page_name') Menu Builder ({{$menu->name}})@endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        @if (count($errors) > 0)
          <div class="col-12">
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
          </div>
        @endif
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Menu Item Lists <small class="text-info">Drag and Drop to Re-arange Items</small></h3>
            </div>            
            <div class="card-body">
              <button class="btn btn-sm btn-success elevation-2 new" 
                      data-toggle="modal" 
                      data-target="#modalMenu"
                      data-ket="New"
              >
                <i class="fas fa-plus-circle"></i> New Menu Item
              </button>                            
            </div>
            <hr class="w-100">
            <div class="card-body">
              <div class="dd dd-2">
                <ol class="dd-list">
                  @forelse ($menu->parent_items->sortBy('order') as $list)
                    @if($list->children->isEmpty())
                      <li class="dd-item" data-id="{{$list->id}}">
                        <div class="pull-right item_actions">
                            <button class="btn btn-warning btn-sm elevation-2 edit" 
                                    data-toggle="modal" 
                                    data-target="#modalMenu"
                                    data-ket="Edit"
                                    data-id="{{$list->id}}"
                                    data-title="{{$list->title}}"
                                    data-url="{{$list->url}}"
                                    data-varName="{{$list->var_name}}"
                                    data-controller="{{$list->controller}}"
                                    data-permission="{{$list->permission}}"
                                    data-tab="{{$list->target}}"
                                    data-icon_class="{{$list->icon_class}}"
                                    data-active="{{$list->active}}"
                            >
                            <i class="fas fa-edit"></i> Edit
                            </button>
                            @include('buttons.delete', ['link' => '/administrator/menus/'.$menu->id.'/builder/'.$list->id])
                        </div>
                        <div class="dd-handle">
                          <span class="text-bold">{{$list->title}}</span>
                          <small>{{$list->url}}</small>
                        </div>
                      </li>
                    @else
                      <li class="dd-item" data-id="{{$list->id}}">
                        <div class="pull-right item_actions">
                            <button class="btn btn-warning btn-sm elevation-2 edit" 
                                    data-toggle="modal" 
                                    data-target="#modalMenu"
                                    data-ket="Edit"
                                    data-id="{{$list->id}}"
                                    data-title="{{$list->title}}"
                                    data-url="{{$list->url}}"
                                    data-varName="{{$list->var_name}}"
                                    data-controller="{{$list->controller}}"
                                    data-permission="{{$list->permission}}"
                                    data-tab="{{$list->target}}"
                                    data-icon_class="{{$list->icon_class}}"
                                    data-active="{{$list->active}}"
                            >
                            <i class="fas fa-edit"></i> Edit
                            </button>
                            @include('buttons.delete', ['link' => '/administrator/menus/'.$menu->id.'/builder/'.$list->id])
                        </div>
                        <div class="dd-handle">
                          <span class="text-bold">{{$list->title}}</span>
                          <small>{{$list->url}}</small>
                        </div>
                        <ol>
                          @foreach($list->children->sortBy('order') as $child)
                            @if($child->children->isEmpty())
                              <li class="dd-item" data-id="{{$child->id}}">
                                <div class="pull-right item_actions">
                                    <button class="btn btn-warning btn-sm elevation-2 edit" 
                                            data-toggle="modal" 
                                            data-target="#modalMenu"
                                            data-ket="Edit"
                                            data-id="{{$child->id}}"
                                            data-title="{{$child->title}}"
                                            data-url="{{$child->url}}"
                                            data-varName="{{$child->var_name}}"
                                            data-controller="{{$child->controller}}"
                                            data-permission="{{$child->permission}}"
                                            data-tab="{{$child->target}}"
                                            data-icon_class="{{$child->icon_class}}"
                                            data-active="{{$list->active}}"
                                    >
                                    <i class="fas fa-edit"></i> Edit
                                    </button>
                                    @include('buttons.delete', ['link' => '/administrator/menus/'.$menu->id.'/builder/'.$child->id])
                                </div>
                                <div class="dd-handle">
                                  <span class="text-bold">{{$child->title}}</span>
                                  <small>{{$child->url}}</small>
                                </div>
                              </li>
                            @else
                            <li class="dd-item" data-id="{{$child->id}}">
                              <div class="pull-right item_actions">
                                  <button class="btn btn-warning btn-sm elevation-2 edit" 
                                          data-toggle="modal" 
                                          data-target="#modalMenu"
                                          data-ket="Edit"
                                          data-id="{{$child->id}}"
                                          data-title="{{$child->title}}"
                                          data-url="{{$child->url}}"
                                          data-varName="{{$child->var_name}}"
                                          data-controller="{{$child->controller}}"
                                          data-permission="{{$child->permission}}"
                                          data-tab="{{$child->target}}"
                                          data-icon_class="{{$child->icon_class}}"
                                          data-active="{{$child->active}}"
                                  >
                                  <i class="fas fa-edit"></i> Edit
                                  </button>
                                  @include('buttons.delete', ['link' => '/administrator/menus/'.$menu->id.'/builder/'.$child->id]);                          
                              </div>
                              <div class="dd-handle">
                                <span class="text-bold">{{$child->title}}</span>
                                <small>{{$child->url}}</small>
                              </div>
                              <ol>
                                @foreach($child->children->sortBy('order') as $grandChild)
                                  <li class="dd-item" data-id="{{$grandChild->id}}">
                                    <div class="pull-right item_actions">
                                        <button class="btn btn-warning btn-sm elevation-2 edit" 
                                                data-toggle="modal" 
                                                data-target="#modalMenu"
                                                data-ket="Edit"
                                                data-id="{{$grandChild->id}}"
                                                data-title="{{$grandChild->title}}"
                                                data-url="{{$grandChild->url}}"
                                                data-varName="{{$grandChild->var_name}}"
                                                data-controller="{{$grandChild->controller}}"
                                                data-permission="{{$grandChild->permission}}"
                                                data-tab="{{$grandChild->target}}"
                                                data-icon_class="{{$grandChild->icon_class}}"
                                                data-active="{{$grandChild->active}}"
                                        >
                                        <i class="fas fa-edit"></i> Edit
                                        </button>
                                        @include('buttons.delete', ['link' => '/administrator/menus/'.$menu->id.'/builder/'.$grandChild->id])
                                    </div>
                                    <div class="dd-handle">
                                      <span class="text-bold">{{$grandChild->title}}</span>
                                      <small>{{$grandChild->url}}</small>
                                    </div>
                                  </li>
                                @endforeach
                              </ol>
                            @endif
                          @endforeach
                        </ol>
                      </li>
                    @endif
                  @empty
                    <li><h5>List Empty</h5></li>
                  @endforelse
                </ol>
              </div>
            </div>
          </div>          
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->

  <div class="modal fade" id="modalMenu">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span id="ket"></span> Menu Item</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formMenu" action="" method="POST">
          <input type="hidden" name="_method" value="POST">
          @csrf
          <div class="modal-body">
            <div class="form-group form-group-sm">
              <label for="title">Title for Menu Item</label>
              <input type="text" name="title" id="title" class="form-control" required>
            </div>            
            <div class="form-group form-group-sm">
              <label for="url">Url for Menu Item</label>
              <input type="text" name="url" id="url" class="form-control">
            </div>
            <div class="form-group form-group-sm">
              <label for="icon_class">Icon Class (Use <a href="https://fontawesome.com/" target="_blank">Font Awesome</a>)</label>
              <input type="text" name="icon_class" id="icon_class" class="form-control">
            </div>
            <div class="form-group form-group-sm">
              <label for="controller_name">Controller Name</label>
              <input type="text" name="controller_name" id="controller_name" class="form-control" placeholder="If not specified, it will auto generated">
            </div>
            <div class="form-group form-group-sm">
              <label for="var_name">Variable Name</label>
              <input type="text" name="var_name" id="var_name" class="form-control" placeholder="If not specified, it will auto generated">
            </div>
            <div class="form-group form-group-sm">
              <label for="target">Link Type</label>
              <select name="target" id="target" class="custom-select">
                <option value="_self" selected>Same Tab/Window</option>
                <option value="_blank">New Tab/Window</option>
              </select>
            </div>
            <hr class="w-100">
            <p>== Optional ==</p>
            <div class="form-group form-group-sm">
              <label for="active">Active</label>              
              <div>
                <input type="checkbox" id="active" name="active"  data-bootstrap-switch>                
              </div>              
            </div>
            <div class="form-group form-group-sm">
              <label for="controller">Create BREAD Controller</label>
              <div>
                <input type="checkbox" id="controller" name="controller"  data-bootstrap-switch>                
              </div>
              <div>
                <small class="text-danger" id="controllerName"></small>
              </div>
            </div>
            <div class="form-group form-group-sm">
              <label for="permission">Create Permission to open</label>
              <div>
                <input type="checkbox" id="permission" name="permission" data-bootstrap-switch>
              </div>
            </div>
          </div>            
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
@endsection

@section('footer')
  <script src="{{ asset('adminlte/plugins/nestable/nestable.min.js') }}"></script>
  <!-- Bootstrap Switch -->
  <script src="{{ asset('adminlte') }}/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
  <script>
    function resetThis($form) {
        $form.find('input:text, input:password, input:file, textarea').val('');
        $form.find('input:radio, input:checkbox')
            .removeAttr('checked').removeAttr('selected');
    }
    $(function(){
      $('.dd').nestable({
        maxDepth: 3,
        expandBtnHTML: '',
        collapseBtnHTML: '',
      });

      $("input[data-bootstrap-switch]").each(function(){
        $(this).bootstrapSwitch();
      })
    });
    jQuery(document).ready(function(){
      $('.dd').on('change', function(e){
        $.post('{{ route("admin.menus.order")}}',
          {
            order: JSON.stringify($('.dd').nestable('serialize')),
            _token: '{{ csrf_token() }}'
          }, function(data){
            toastr.success("Success reorder items", "Sukses!", {timeOut: 4000, closeButton: true})
          }
        );   
      });
      $(document).on('click', '.new', function(){
        var ket = $(this).data('ket');
        var action = "{{url()->current()}}";

        resetThis($('#formMenu'));

        $('#modalMenu #ket').html(ket);
        $('#modalMenu #formMenu').attr('action', action);
        $('#formMenu input[name="_method"]').val("POST");
        $('#formMenu #controllerName').html('');
        $('input[name="active"]').bootstrapSwitch('state', false, false);
        $('input[name="controller"]').bootstrapSwitch('state', false, false);
        $('input[name="permission"]').bootstrapSwitch('state', false, false);
      });
      $(document).on('click', '.edit', function(){        
        var ket = $(this).attr('data-ket');
        var id = $(this).attr('data-id');
        var title = $(this).attr('data-title');
        var url = $(this).attr('data-url');
        var var_name = $(this).attr('data-varName');
        var route = $(this).attr('data-route');
        var controller = $(this).attr('data-controller');
        var permission = $(this).attr('data-permission');
        var tab = $(this).attr('data-tab');
        var icon_class = $(this).attr('data-icon_class');
        var active = $(this).attr('data-active');
        
        $('#modalMenu #ket').html(ket);
        $('#formMenu input[name="_method"]').val("PUT");
        $('#formMenu').attr('action', "{{url()->current()}}/"+id)
        $('#formMenu #title').val(title);
        $('#formMenu #url').val(url);
        $('#formMenu #var_name').val(var_name);
        $('#formMenu #controller_name').val(controller);
        $('#formMenu #icon_class').val(icon_class);
        $('#formMenu #target').val(tab);
        $('#formMenu #title').val(title);
        if(active > 0){
          $('input[name="active"]').bootstrapSwitch('state', true, true);
        } else {
          $('input[name="active"]').bootstrapSwitch('state', false, false);
        }
        if(permission != ''){
          $('input[name="permission"]').bootstrapSwitch('state', true, true);
        } else {
          $('input[name="permission"]').bootstrapSwitch('state', false, false);
        }
        if(controller != ''){
          $('#formMenu #controllerName').html('Controller Name : ' + controller + 'Controller');
          $('input[name="controller"]').bootstrapSwitch('state', true, true);
        } else {
          $('#formMenu #controllerName').html('');
          $('input[name="controller"]').bootstrapSwitch('state', false, false);
        }

      });
    });
  </script>
@endsection