<div class="tab-pane fade" id="org-contact-content" role="tabpanel" aria-labelledby="org-contact">
  <div class="row mt-2">
    <div class="col-12 col-md-8">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">Contact Lists</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool"
                    id="btnContactNew" 
                    data-toggle="modal" 
                    data-target="#modal-contact">
              <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm text-nowrap" id="tableContact" style="width: 100%;">
              <thead>
                <tr>
                  <th>Contact Name</th>
                  <th>Job Title</th>
                  <th>Active</th>
                  <th>Email</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                
              </tbody>
            </table>
          </div>          
        </div>
      </div>
    </div>    
  </div>
</div>

<div class="modal fade" id="modal-contact">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Contact Form</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formContact" action="{{ route('setup.organization.newcontact') }}" method="post">
        @csrf
        <input type="hidden" name="_method" id="method" value="POST">
        <input type="hidden" name="organization_id" value="{{ $organization->id }}">
      <div class="modal-body">
        <div class="form-group form-group-sm">
          <label for="OC_ContactName">Full Name</label>
          <input type="text" name="OC_ContactName" id="OC_ContactName" 
                 class="form-control form-control-sm"
                 placeholder="Full Name"
                 required>
        </div>
        <div class="form-group form-group-sm">
          <label for="OC_Salutation">Job Title</label>
          <input type="text" name="OC_Salutation" id="OC_Salutation" 
                 class="form-control form-control-sm"
                 placeholder="Job Title"
                 required>
        </div>
        <div class="form-group form-group-sm">
          <label for="OC_JobCategory">Job Category</label>
          <input type="text" name="OC_JobCategory" id="OC_JobCategory" 
                 class="form-control form-control-sm"
                 placeholder="Job Category">
        </div>
        <div class="form-group form-group-sm">
          <label for="OC_Language">Language</label>
          <input type="text" name="OC_Language" id="OC_Language" 
                 class="form-control form-control-sm"
                 placeholder="Language">
        </div>
        <div class="form-group form-group-sm">
          <label for="OC_Email">Email</label>
          <input type="email" name="OC_Email" id="OC_Email" 
                 class="form-control form-control-sm"
                 placeholder="Email Address"
                 required>
        </div>
        <div class="form-group form-group-sm">
          <label for="OC_Phone">Work Phone</label>
          <input type="text" name="OC_Phone" id="OC_Phone" 
                 class="form-control form-control-sm"
                 placeholder="Work Phone">
        </div>  
        <div class="form-group form-group-sm">
          <label for="OC_PhoneExtension">Extension</label>
          <input type="text" name="OC_PhoneExtension" id="OC_PhoneExtension" 
                 class="form-control form-control-sm"
                 placeholder="Work Phone Extension">
        </div>         
        <div class="form-group form-group-sm">
          <label for="OC_Mobile">Mobile Phone</label>
          <input type="text" name="OC_Mobile" id="OC_Mobile" 
                 class="form-control form-control-sm"
                 placeholder="Mobile Phone">
        </div>
        <div class="form-group form-group-sm">
          <label for="OC_HomePhone">Home Phone</label>
          <input type="text" name="OC_HomePhone" id="OC_HomePhone" 
                 class="form-control form-control-sm"
                 placeholder="Home Phone">
        </div>
        <div class="form-group form-group-sm">
          <label for="OC_OH_AddressOverride">Address Override</label>
          <input type="text" name="OC_OH_AddressOverride" id="OC_OH_AddressOverride" 
                 class="form-control form-control-sm"
                 placeholder="Address Override">
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success elevation-2 float-right">
          <i class="fas fa-save"></i> Save
        </button>
      </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script>
  jQuery(document).ready(function(){
    var tabelContact = $('#tableContact').DataTable( {
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('setup.organization.ajaxcontact', $organization->id) }}",
        columns:[
          {data: "OC_ContactName"},
          {data: "OC_Salutation"},          
          {data: "status", className: "text-center"},
          {data: "OC_Email"},
          {data: "action", className: "text-center"}
        ]
    });

    $(document).on('click', '.editContact', function(){
      var id = $(this).data('id');
      var name = $(this).data('name');
      var title = $(this).data('title');
      var category = $(this).data('category');
      var email = $(this).data('email');      
      var country = $(this).data('country');
      var language = $(this).data('language');
      var workphone = $(this).data('workphone');
      var extension = $(this).data('extension');
      var mobile = $(this).data('mobile');
      var home = $(this).data('home');
      var workaddress = $(this).data('workaddress');
      
      $('#formContact').attr('action', "/setup/organization/contact/"+id);
      $('#formContact #method').val('PUT');     
      $('#formContact #OC_ContactName').val(name);
      $('#formContact #OC_Salutation').val(title);
      $('#formContact #OC_JobCategory').val(category);
      $('#formContact #OC_Email').val(email);
      $('#formContact #OC_Language').val(language);
      $('#formContact #OC_Phone').val(workphone);
      $('#formContact #OC_PhoneExtension').val(extension);
      $('#formContact #OC_Mobile').val(mobile);
      $('#formContact #OC_HomePhone').val(home);
      $('#formContact #OC_OH_AddressOverride').val(workaddress);
      
      $('#btnContactNew').removeClass('d-none');
    });

    $(document).on('click', '#btnContactNew', function(){
      $("#formContact")[0].reset();
      $('#formContact').attr('action', "{{ route('setup.organization.newcontact') }}");
      $('#formContact #method').val('POST');
    });

    $(document).on('submit', '#formContact', function(e){
      e.preventDefault();
      var action = $(this).attr('action');
      $.ajax({
        url: action,
        type: "POST",
        data: $(this).serialize(),
        success:function(msg){
          tabelContact.draw();
          $("#formContact")[0].reset();
          $('#modal-contact').modal('hide');
          toastr.success("Update Contact Success", "Sukses!", {timeOut: 6000, closeButton: true})
          console.log(msg);
        }
      });
    });

    $(document).on('click', '.hapusContact', function(){
      var id = $(this).data('id');

      Swal.fire({			
				title: 'Are you sure?',			
				html: "This will be permanently delete contact!",
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cancel',
				confirmButtonText: 'Yes, delete!'
			}).then((result) => {
				if (result.value) {
          $.ajax({
            url: "/setup/organization/delcontact/"+id,
            type: "GET",
            data: {
              org: "{{$organization->id}}"
            },
            success:function(msg){
              tabelContact.draw();
              toastr.success("Remove Contact Success", "Sukses!", {timeOut: 6000, closeButton: true})
              console.log(msg);
            }
          });
				}
			});
    });

    $(document).on('click', '.statecontact', function(){
      var act = $(this);
      var id = $(this).data('id');
      var _token = "{{ csrf_token() }}";

      if(act.is(':checked')){
        var val = 1;
        console.log("Checked");        
      } else {
        var val = 0;
        console.log("Uncheck");        
      }
      $.ajax({
        url: "/setup/organization/contact/changestate",
        type: "POST",
        data: {
          _token: _token,
          id: id,
          val: val
        },
        success: function(msg){
          tabelContact.draw();
          toastr.success("Change State Success", "Sukses!", {timeOut: 6000, closeButton: true})
          console.log(msg);
        }
      });
    });
  });
</script>