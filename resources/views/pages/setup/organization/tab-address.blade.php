<div class="tab-pane fade" id="org-address-content" role="tabpanel" aria-labelledby="org-address">
  <div class="row mt-2">
    <div class="col-12">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">Address Lists</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool"
                    id="btnAddressNew" 
                    data-toggle="modal" 
                    data-target="#modal-address">
              <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm text-nowrap" id="tableAddress" style="width: 100%;">
              <thead>
                <tr>
                  <th>Address Type</th>
                  <th>Address 1</th>
                  <th>Address 2</th>
                  <th>Active</th>
                  <th>Additional Address Info</th>
                  <th>Action</th>
                </tr>
              </thead>
            </table>
          </div>          
        </div>
      </div>
    </div>
    
  </div>
</div>

<div class="modal fade" id="modal-address">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><span id="state">New</span> Address</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formAddress" action="{{ route('setup.organization.newaddress') }}">
        @csrf
        <input type="hidden" name="_method" id="method" value="POST">
        <input type="hidden" name="organization_id" value="{{ $organization->id }}">
        <input type="hidden" name="duplicate" id="duplicate">
      <div class="modal-body">
        <div class="form-group form-group-sm row">
          <label for="OA_CompanyNameOverride" 
                 class="col-sm-4 col-form-label text-sm-right">Company Name</label>
          <div class="col-sm-8">
            <input type="text" class="form-control form-control-sm"
                   name="OA_CompanyNameOverride"id="OA_CompanyNameOverride" 
                   placeholder="Company Name"
                   value="{{ $organization->OH_FullName }}"
                   required>
          </div>
        </div>
        <div class="form-group form-group-sm row">
          <label for="OA_Type" class="col-sm-4 col-form-label text-sm-right">Address Type</label>
          <div class="col-sm-8">
            <select name="OA_Type" 
                    id="OA_Type" 
                    class="custom-select">
              @forelse ($addressType as $key => $type)
                <option value="{{ $key }}">{{ $type }}</option>
              @empty                
              @endforelse
            </select>
          </div>          
        </div>
        <div class="form-group form-group-sm row">
          <label for="OA_AdditionalAddressInformation" 
                 class="col-sm-4 col-form-label text-sm-right">Additional Address Information</label>
          <div class="col-sm-8">
            <input type="text" class="form-control form-control-sm"
                   name="OA_AdditionalAddressInformation"id="OA_AdditionalAddressInformation" 
                   placeholder="Additional Address Information">
          </div>
        </div>
        <div class="form-group form-group-sm row">
          <label for="OA_Address1" 
                 class="col-sm-4 col-form-label text-sm-right">Address 1</label>
          <div class="col-sm-8">
            <input type="text" class="form-control form-control-sm"
                   name="OA_Address1"id="OA_Address1" 
                   placeholder="Address 1"
                   required>
          </div>
        </div>
        <div class="form-group form-group-sm row">
          <label for="OA_Address2" 
                 class="col-sm-4 col-form-label text-sm-right">Address 2</label>
          <div class="col-sm-8">
            <input type="text" class="form-control form-control-sm"
                   name="OA_Address2"id="OA_Address2" 
                   placeholder="Address 2">
          </div>
        </div>
        <!-- Country & City -->
        <div class="form-group form-group-sm row">
          <label for="OA_RN_NKCountryCode" 
                 class="col-sm-4 col-form-label text-sm-right">Country</label>
          <div class="col-sm-3">
            <select name="OA_RN_NKCountryCode" id="OA_RN_NKCountryCode"
                    class="form-control country-address"
                    required>
              <option value="{{ optional($organization->address())->first()->OA_RN_NKCountryCode ?? '' }}"
                      selected>
                {{ optional($organization->address())->first()->OA_RN_NKCountryCode ?? 'Select...' }}</option>
            </select>                
          </div>
          <label for="OA_City" 
                 class="col-sm-1 col-form-label text-sm-right">City</label>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm"
                    name="OA_City"id="OA_City" 
                    placeholder="City"
                    required>
          </div>
        </div>
        <!-- PostCode & State -->
        <div class="form-group form-group-sm row">
          <label for="OA_PostCode" 
                 class="col-sm-4 col-form-label text-sm-right">Post Code</label>
          <div class="col-sm-3">
            <input type="text" class="form-control form-control-sm"
                   name="OA_PostCode"id="OA_PostCode" 
                   placeholder="Post Code"
                   required>
          </div>
          <label for="OA_State" 
                 class="col-sm-1 col-form-label text-sm-right">State</label>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm"
                    name="OA_State"id="OA_State" 
                    placeholder="State"
                    required>
          </div>
        </div>
        <!-- UNLOCO -->
        <div class="form-group form-group-sm row">
          <label for="OA_RL_NKRelatedPortCode" 
                 class="col-sm-4 col-form-label text-sm-right">Related City/Port</label>
          <div class="col-sm-4">
            <select name="OA_RL_NKRelatedPortCode" id="OA_RL_NKRelatedPortCode" 
                    class="form-control unloco-address w-100" required>
              <option value="{{ $organization->OH_RL_NKClosestPort ?? '' }}" 
                      selected>
                {{ $organization->OH_RL_NKClosestPort ?? 'Select...' }}</option>
            </select>
          </div>
        </div>
        <!-- Phone -->
        <div class="form-group form-group-sm row">
          <label for="OA_Phone" 
                 class="col-sm-4 col-form-label text-sm-right">Phone</label>
          <div class="col-sm-6">
            <input type="text" class="form-control form-control-sm"
                   name="OA_Phone"id="OA_Phone" 
                   placeholder="Phone">
          </div>
        </div>
        <!-- Phone -->
        <div class="form-group form-group-sm row">
          <label for="OA_Mobile" 
                 class="col-sm-4 col-form-label text-sm-right">Mobile</label>
          <div class="col-sm-6">
            <input type="text" class="form-control form-control-sm"
                   name="OA_Mobile"id="OA_Mobile" 
                   placeholder="Phone">
          </div>
        </div>
        <!-- Fax -->
        <div class="form-group form-group-sm row">
          <label for="OA_Fax" 
                 class="col-sm-4 col-form-label text-sm-right">Fax</label>
          <div class="col-sm-6">
            <input type="text" class="form-control form-control-sm"
                   name="OA_Fax"id="OA_Fax" 
                   placeholder="Fax">
          </div>
        </div>
        <!-- Email -->
        <div class="form-group form-group-sm row">
          <label for="OA_Email" 
                 class="col-sm-4 col-form-label text-sm-right">Email</label>
          <div class="col-sm-8">
            <input type="email" class="form-control form-control-sm"
                   name="OA_Email"id="OA_Email" 
                   placeholder="Email">
          </div>
        </div>
        <!-- Language -->
        <div class="form-group form-group-sm row">
          <label for="OA_Language" 
                 class="col-sm-4 col-form-label text-sm-right">Language</label>
          <div class="col-sm-8">
            <input type="text" class="form-control form-control-sm"
                   name="OA_Language"id="OA_Language" 
                   placeholder="Language">
          </div>
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
  jQuery( document ).ready( function( $ ) {
    var tabel = $('#tableAddress').DataTable( {
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('setup.organization.address', $organization->id) }}",
        columns:[
          {data: "type"},
          {data: "OA_Address1"},
          {data: "OA_Address2"},          
          {data: "status", className: "text-center"},
          {data: "OA_AdditionalAddressInformation"},
          {data: "action", className: "text-center"}
        ],
    });

    $('#modal-address').on('shown.bs.modal', function (e) {
      $('.country-address').select2({
        placeholder: 'Select...',
        dropdownParent: $('#modal-address .modal-content'),
        ajax: {
          url: "{{ route('select2.setup.countries') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.RN_Code,
                        id: item.RN_Code,
                    }
                })
            };
          },          
          cache: true
        }
      });

      $('.unloco-address').select2({
        placeholder: 'Select...',
        dropdownParent: $('#modal-address .modal-content'),
        ajax: {
          url: "{{ route('select2.setup.unloco') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.RL_Code,
                        id: item.RL_Code,
                    }
                })
            };
          },          
          cache: true
        }
      });
    });
    
    $(document).on('click', '.editAddress, .copyAddress', function(){
      var id = $(this).data('id');
      var duplicate = $(this).data('duplicate');
      var type = $(this).data('type');
      var address1 = $(this).data('address1');
      var address2 = $(this).data('address2');
      var status = $(this).data('status');
      var additional = $(this).data('additional');
      var company = ($(this).data('company')) ? $(this).data('company') : "{{ $organization->OH_FullName }}";
      var country = $(this).data('country');
      var city = $(this).data('city');
      var state = $(this).data('state');
      var postcode = $(this).data('postcode');
      var relatedport = ($(this).data('relatedport')) ? $(this).data('relatedport') : "{{ $organization->OH_RL_NKClosestPort }}";
      var phone = $(this).data('phone');
      var mobile = $(this).data('mobile');
      var fax = $(this).data('fax');
      var email = $(this).data('email');
      var language = $(this).data('language');
      
      if(duplicate > 0){
        $('#state').html("Copy");
      } else {
        $('#state').html("Edit");
      }
      
      $('#formAddress').attr('action', "/setup/organization/address/"+id);
      $('#formAddress #method').val('PUT');
      $('#formAddress #duplicate').val(duplicate);
      $('#formAddress #OA_Type').val(type);
      $('#formAddress #OA_AdditionalAddressInformation').val(additional);
      $('#formAddress #OA_Address1').val(address1);
      $('#formAddress #OA_Address2').val(address2);
      $('#formAddress #OA_RN_NKCountryCode').val(country);
      $('#formAddress #OA_City').val(city);
      $('#formAddress #OA_PostCode').val(postcode);
      $('#formAddress #OA_State').val(state);
      $('#formAddress #OA_RL_NKRelatedPortCode').val(relatedport);
      $('#formAddress #OA_Phone').val(phone);
      $('#formAddress #OA_Mobile').val(mobile);
      $('#formAddress #OA_Fax').val(fax);
      $('#formAddress #OA_Email').val(email);
      $('#formAddress #OA_Language').val(language);

      $('#btnAddressNew').removeClass('d-none');
    });

    $(document).on('click', '#btnAddressNew', function(){
      $("#formAddress")[0].reset();
      $('#formAddress').attr('action', "{{ route('setup.organization.newaddress') }}");
      $('#formAddress #method').val('POST');
    });

    $(document).on('submit', '#formAddress', function(e){
      e.preventDefault();
      var action = $(this).attr('action');
      var state = $('#state').html();
      $.ajax({
        url: action,
        type: "POST",
        data: $(this).serialize(),
        success:function(msg){
          tabel.draw();
          $("#formAddress")[0].reset();
          $('#modal-address').modal('hide');
          toastr.success(state+" Address Success", "Sukses!", {timeOut: 6000, closeButton: true})
          console.log(msg);
        }
      });
    });

    $(document).on('click', '.hapusAddress', function(){
      var id = $(this).data('id');

      Swal.fire({			
				title: 'Are you sure?',			
				html: "This will be permanently delete address!",
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cancel',
				confirmButtonText: 'Yes, delete!'
			}).then((result) => {
				if (result.value) {
          $.ajax({
            url: "/setup/organization/deladdress/"+id,
            type: "GET",
            data: {
              org: "{{$organization->id}}"
            },
            success:function(msg){
              tabel.draw();
              toastr.success("Remove Address Success", "Sukses!", {timeOut: 6000, closeButton: true})
              console.log(msg);
            }
          });
				}
			});
    });

    $(document).on('click', '.aktif', function(){
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
        url: "/setup/organization/address/changestate",
        type: "POST",
        data: {
          _token: _token,
          id: id,
          val: val
        },
        success: function(msg){
          tabel.draw();
          toastr.success("Change State Success", "Sukses!", {timeOut: 6000, closeButton: true})
          console.log(msg);
        }
      });
    });
  });
</script>