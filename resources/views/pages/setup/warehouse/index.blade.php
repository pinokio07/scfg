@extends('layouts.master')
@section('title') {{ Str::title(Request::segment(2)) }} @endsection
@section('page_name') {{ Str::title(Request::segment(2)) }} @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">{{ Str::title(Request::segment(2)) }}</h3>
            <div class="card-tools">
              <a href="{{url()->current()}}/create" class="btn btn-success elevation-2">
                <i class="fas fa-plus-circle"></i>
                Add                
              </a>              
              <a href="/download/{{ Request::path() }}" class="btn btn-info elevation-2">
                <i class="fas fa-download"></i>
                Download                
              </a>
              <button type="button"
                      class="btn btn-warning elevation-2"
                      data-toggle="modal"
                      data-target="#modal-upload">
                <i class="fas fa-upload"></i> Upload
              </button>              
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              @include('table.ajax')
            </div>            
          </div>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

@include('forms.upload', ['action' => '/upload/'.Request::path()])
@endsection

@section('footer')
<script src="{{asset('/adminlte/plugins/editable/js/bootstrap-editable.js')}}"></script>
  <script>
    jQuery(document).ready(function(){
      var table = $('#dataAjax').DataTable({
        responsive:true,
        processing: true,
        serverSide: true,
        ajax: "{{ url()->current() }}",
        columns:[
          @forelse ($items as $keys => $item)
          {data: "{{$keys}}", name: "{{$keys}}"},
          @empty
          @endforelse          
        ],
        fnDrawCallback: function( oSettings ){
          $('.tariff').editable({
            mode: 'inline',
            onblur: 'submit', 		
            savenochange : false,
            showbuttons: false,
            inputclass: 'form-control form-control-sm',
            ajaxOptions: {
                type: 'post'
            },
            validate: function(value) {
              if(!$.isNumeric(value)) {
                return ' Please input numeric value';
              }
            },
            params: function(params) {
                params._token = $('meta[name="csrf-token"]').attr('content');
                params._method = 'PUT';
                params.nama = $(this).attr('data-nama');
                params.id = $(this).attr('data-pk');
                params.val = params.value;
                return params;
            },
            success:function(msg){
              if(msg.status == "OK"){
                toastr.success("Edit Success", "Success!", {timeOut: 6000, closeButton: true})
              } else {
                toastr.error(msg.message, "Failed!", {timeOut: 6000, closeButton: true})
              }
            },
            error:function(jqXHR){
              jsonValue = jQuery.parseJSON( jqXHR.responseText );
              toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
            }
          });
        }
      });
    });
  </script>
@endsection
