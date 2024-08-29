@extends('layouts.master')

@section('title') User @endsection
@section('page_name') <i class="fas fa-user"></i> User Data @endsection
@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      @if($user->id != '')
        <form id="formProfile" 
              action="/administrator/users/{{$user->id}}" 
              method="post" 
              enctype="multipart/form-data">        
          @method('PUT')
      @else
        <form id="formProfile" 
              action="/administrator/users"
              method="post"
              enctype="multipart/form-data">   
      @endif      
        @include('forms.user')
      </form>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->

  <!-- Modal Logs -->
  <div class="modal fade" id="modal-logs">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Login Logs</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>	
        <div class="modal-body">
          <div class="table-responsive">
            <table id="tblLogs" class="table table-sm" style="width: 100%;">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Date</th>
                  <th>Type</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
@endsection

@section('footer')
<script>
  function validatePass(pass) {
    var upperCase= new RegExp('[A-Z]');
    var lowerCase= new RegExp('[a-z]');
    var numbers = new RegExp('[0-9]');
    var regSym = /[ `!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/;
    var confirmPass = $('#confirmPassword').val();

    if(pass.length < 12){
      toastr.error("Minimum 12 Characters for password.", "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

      return false;
    }
    if(!upperCase.test(pass)){
      toastr.error("Characters must contain Upper Case Letter.", "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

      return false;
    }
    if(!lowerCase.test(pass)){
      toastr.error("Characters must contain Lower Case Letter.", "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

      return false;
    }
    if(!numbers.test(pass)){
      toastr.error("Characters must contain Number.", "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

      return false;
    }
    if(!regSym.test(pass)){
      toastr.error("Characters must contain Symbol.", "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

      return false;
    }

    if(pass != confirmPass){
      toastr.error("Please input same password in Confirmation Password.", "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

      return false;
    }

    return btoa(pass);
  }
  jQuery(document).ready(function(){
    $(document).on('click', '.eye1', function(){
      $('#inputPassword').get(0).type = 'text';      
      $(this).addClass('d-none');
      $('.slash1').removeClass('d-none');
    });
    $(document).on('click', '.slash1', function(){
      $('#inputPassword').get(0).type = 'password';      
      $(this).addClass('d-none');
      $('.eye1').removeClass('d-none');
    });
    $(document).on('click', '.eye2', function(){
      $('#confirmPassword').get(0).type = 'text';      
      $(this).addClass('d-none');
      $('.slash2').removeClass('d-none');
    });
    $(document).on('click', '.slash2', function(){
      $('#confirmPassword').get(0).type = 'password';      
      $(this).addClass('d-none');
      $('.eye2').removeClass('d-none');
    });
    $(document).on('click', '#btnSubmit', function(e){
      e.preventDefault();
      var pass = $('#inputPassword').val();
      if(pass != ''){
        var validatedPass = validatePass(pass);
        if(!validatedPass){
          return false;
        }
        $('#formProfile').submit();
      }

      $('#formProfile').submit();

    });
    $(document).on('click', '#viewLogs', function(){
      $('#tblLogs').DataTable().destroy();

      $.ajax({
        url: "{{ route('get.login.logs') }}",
        type: "GET",
        success: function(msg){
          $('#tblLogs').DataTable({
            data: msg.data,
            columns:[
              {data:'DT_RowIndex', searchable: false, className:"text-center"},
              {
                data: {
                  _: "created_at.display",
                  sort: "created_at.timestamp",
                }
              },
              {data:'type', name: 'type', className:"text-center"},
            ]
          });
        },
        error: function (jqXHR, exception) {
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
        }
      })
    });
  });
</script>
@endsection