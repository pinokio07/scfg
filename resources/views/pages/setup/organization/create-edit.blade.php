@extends('layouts.master')
@section('title') Organizations @endsection
@section('page_name') Organizations @endsection
@section('header')
  <style>
    label{
      margin-bottom: 0px !important;
    }
  </style>
@endsection
@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
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
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">@if($organization->id != '') Edit @else New @endif Organizations</h3>
          </div>

            <div class="card-body">
              <ul class="nav nav-tabs" id="custom-content-above-tab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="org-detail" data-toggle="pill" href="#org-detail-content" role="tab" aria-controls="org-detail-content" aria-selected="true">Details</a>
                </li>

                @if($organization->id != '')

                  <li class="nav-item">
                    <a class="nav-link" id="org-address" data-toggle="pill" href="#org-address-content" role="tab" aria-controls="org-address-content" aria-selected="false">Address</a>
                  </li>

                  {{-- <li class="nav-item">
                    <a class="nav-link" id="org-contact" data-toggle="pill" href="#org-contact-content" role="tab" aria-controls="org-contact-content" aria-selected="false">Contact</a>
                  </li> --}}

                @endif

              </ul>
              <div class="tab-content" id="custom-content-above-tabContent">
                <div class="tab-pane fade show active" id="org-detail-content" role="tabpanel" aria-labelledby="org-detail">
                  @if($organization->id != '')
                    <form id="formOrganization" action="/setup/organization/{{$organization->id}}" method="post" autocomplete="off">
                      @method('PUT')
                    @else
                    <form id="formOrganization" action="/setup/organization" method="post" autocomplete="off">
                    @endif
                      @csrf
                  <div class="row mt-2">
                    <!-- Organization Details Form -->
                    <div class="col-md-8">
                      <div class="card card-primary card-outline">
                        <div class="card-header">
                          <h3 class="card-title">Organization Details</h3>
                        </div>
                        <div class="card-body">
                          <div class="row">
                            <!-- Organization Code -->
                            <div class="col-12 col-md-4">
                              <div class="form-group form-group-sm">
                                <label for="OH_Code">Organization Code</label>
                                <input type="text" id="OH_Code" class="form-control form-control-sm" value="{{ old('OH_Code') ?? $organization->OH_Code ?? ''}}" readonly>
                              </div>
                            </div>
                            <!-- Legacy Code -->
                            {{-- <div class="col-12 col-md-4">
                              <div class="form-group form-group-sm">
                                <label for="OH_LegacyCode">Legacy Code</label>
                                <input type="text" 
                                       name="header[OH_LegacyCode]" 
                                       id="OH_LegacyCode" 
                                       class="form-control form-control-sm" 
                                       value="{{ old('OH_LegacyCode') ?? $organization->OH_LegacyCode ?? ''}}">
                              </div>
                            </div> --}}
                            <!-- Screening Status -->
                            {{-- <div class="col-6 col-md-2">
                              <div class="form-group form-group-sm">
                                <label for="OH_ScreeningStatus">Screening Status</label>
                                <select class="custom-select custom-select-sm" name="header[OH_ScreeningStatus]" id="OH_ScreeningStatus">
                                  <option value="0" 
                                    @selected($organization->OH_ScreeningStatus == false)>
                                    No</option>
                                  <option value="1" 
                                    @selected($organization->OH_ScreeningStatus == true)>
                                    Yes</option>
                                </select>
                              </div>
                            </div> --}}
                            <!-- Organization Category -->
                            {{-- <div class="col-6 col-md-2">
                              <div class="form-group form-group-sm">
                                <label for="OH_Category">Org Category</label>
                                <select class="custom-select custom-select-sm"
                                        name="header[OH_Category]" id="OH_Category"
                                        required>                                  
                                  <option value="BUS"
                                          @if($organization->OH_Category == 'BUS') selected @endif>Business</option>
                                  <option value="GOV"
                                          @if($organization->OH_Category == 'GOV') selected @endif>Government</option>
                                  <option value="NAT"
                                          @if($organization->OH_Category == 'NAT') selected @endif>Natural Person/Individual</option>
                                  <option value="NGO"
                                          @if($organization->OH_Category == 'NGO') selected @endif>Non Government Organization</option>
                                </select>
                              </div>
                            </div> --}}
                            <!-- Organization Full Name -->
                            <div class="col-12">
                              <div class="form-group form-group-sm">
                                <label for="OH_FullName">Organization Full Name</label>
                                <input type="text"
                                       name="header[OH_FullName]"
                                       id="OH_FullName"
                                       class="form-control form-control-sm"
                                       placeholder="Organization Name"
                                       minlength="3"
                                       value="{{ old('header[OH_FullName]') ?? $organization->OH_FullName ?? ''}}" required>
                              </div>
                            </div>
                            <!-- Additional Address Info -->
                            {{-- <div class="col-12">
                              <div class="form-group form-group-sm">
                                <label for="OA_AdditionalAddressInformation">Additional Address Info</label>
                                <input type="text"
                                        name="address[OA_AdditionalAddressInformation]"
                                        id="OA_AdditionalAddressInformation"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_AdditionalAddressInformation]') ?? optional($organization->mainAddress)->first()->OA_AdditionalAddressInformation ?? ''}}"
                                        placeholder="Additional Address Information"
                                        >
                              </div>
                            </div> --}}
                            <!-- Address 1 -->
                            <div class="col-12">
                              <div class="form-group form-group-sm">
                                <label for="OA_Address1">Address 1</label>
                                <input type="text"
                                        name="address[OA_Address1]"
                                        id="OA_Address1"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_Address1]') ?? optional($organization->mainAddress)->first()->OA_Address1 ?? ''}}"
                                        placeholder="Address 1"
                                        required>
                              </div>
                            </div>
                            <!-- Address 2 -->
                            {{-- <div class="col-12">
                              <div class="form-group form-group-sm">
                                <label for="OA_Address2">Address 2</label>
                                <input type="text"
                                        name="address[OA_Address2]"
                                        id="OA_Address2"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_Address2]') ?? optional($organization->mainAddress)->first()->OA_Address2 ?? ''}}"
                                        placeholder="Address 2"
                                        >
                              </div>
                            </div> --}}
                            <!-- UNLOCO -->
                            {{-- <div class="col-lg-6">
                              <div class="form-group form-group-sm">
                                <label for="unloco">UNLOCO</label>
                                <select name="header[OH_RL_NKClosestPort]" 
                                        id="unloco" 
                                        class="form-control form-control-sm unloco"
                                        style="width: 100%;"
                                        required>
                                  <option value="{{ $organization->OH_RL_NKClosestPort ?? '' }}"
                                          selected>
                                    {{ $organization->OH_RL_NKClosestPort ?? 'Select...' }}</option>
                                </select>
                              </div>
                            </div> --}}
                            <!-- Country Code -->
                            {{-- <div class="col-6">
                              <div class="form-group form-group-sm">
                                <label for="OA_RN_NKCountryCode">Country Code</label>
                                <select name="address[OA_RN_NKCountryCode]"
                                        id="OA_RN_NKCountryCode"
                                        required
                                        class="form-control form-control-sm country"
                                        style="width: 100%;"
                                        required>
                                    <option value="{{ optional($organization->mainAddress)->first()->OA_RN_NKCountryCode ?? '' }}"
                                            selected>
                                      {{ optional($organization->mainAddress)->first()->OA_RN_NKCountryCode ?? 'Select...' }}</option>
                                </select>
                              </div>
                            </div> --}}
                            <!-- City -->
                            {{-- <div class="col-6 col-md-5">
                              <div class="form-group form-group-sm">
                                <label for="OA_City">City</label>
                                <input type="text"
                                        name="address[OA_City]"
                                        id="OA_City"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_City]') ?? optional($organization->mainAddress)->first()->OA_City ?? ''}}"
                                        placeholder="City"
                                        required
                                        >
                              </div>
                            </div> --}}
                            <!-- Post Code -->
                            {{-- <div class="col-6 col-md-3">
                              <div class="form-group form-group-sm">
                                <label for="OA_PostCode">Post Code</label>
                                <input type="text"
                                        name="address[OA_PostCode]"
                                        id="OA_PostCode"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_PostCode]') ?? optional($organization->mainAddress)->first()->OA_PostCode ?? ''}}"
                                        placeholder="Postal Code"
                                        required
                                        >
                              </div>
                            </div> --}}
                            <!-- State -->
                            {{-- <div class="col-6 col-md-4">
                              <div class="form-group form-group-sm">
                                <label for="OA_State">State</label>
                                <input type="text"
                                        name="address[OA_State]"
                                        id="OA_State"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_State]') ?? optional($organization->mainAddress)->first()->OA_State ?? ''}}"
                                        placeholder="State"
                                        required
                                        >
                              </div>
                            </div> --}}
                          </div>
                          <div class="row">
                            <!-- Phone -->
                            {{-- <div class="col-6 col-md-4">
                              <div class="form-group form-group-sm">
                                <label for="OA_Phone">Phone</label>
                                <input type="text"
                                        name="address[OA_Phone]"
                                        id="OA_Phone"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_Phone]') ?? optional($organization->mainAddress)->first()->OA_Phone ?? ''}}"
                                        placeholder="Phone"
                                        >
                              </div>
                            </div> --}}
                             <!-- Fax -->
                             {{-- <div class="col-6 col-md-4">
                              <div class="form-group form-group-sm">
                                <label for="OA_Fax">Fax</label>
                                <input type="text"
                                        name="address[OA_Fax]"
                                        id="OA_Fax"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_Fax]') ?? optional($organization->mainAddress)->first()->OA_Fax ?? ''}}"
                                        placeholder="Fax"
                                        >
                              </div>
                            </div> --}}
                            <!-- Mobile -->
                            {{-- <div class="col-12 col-md-4">
                              <div class="form-group form-group-sm">
                                <label for="OA_Mobile">Mobile</label>
                                <input type="text"
                                        name="address[OA_Mobile]"
                                        id="OA_Mobile"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_Mobile]') ?? optional($organization->mainAddress)->first()->OA_Mobile ?? ''}}"
                                        placeholder="Mobile"
                                        >
                              </div>
                            </div>                            --}}
                            <!-- Email -->
                            {{-- <div class="col-lg-6">
                              <div class="form-group form-group-sm">
                                <label for="OA_Email">Email</label>
                                <input type="email"
                                        name="address[OA_Email]"
                                        id="OA_Email"
                                        class="form-control form-control-sm"
                                        value="{{ old('address[OA_Email]') ?? optional($organization->mainAddress)->first()->OA_Email ?? ''}}"
                                        placeholder="Email"
                                        >
                              </div>
                            </div> --}}
                            <!-- Website -->
                            {{-- <div class="col-lg-6">
                              <div class="form-group form-group-sm">
                                <label for="Website">Website</label>
                                <input type="text"
                                        name=""
                                        id="Website"
                                        class="form-control form-control-sm"
                                        value=""
                                        placeholder="Website"
                                        >
                              </div>
                            </div> --}}
                            <!-- Tax ID -->
                            <div class="col-lg-4">
                              <div class="form-group form-group-sm">
                                <label for="OA_TaxID">Tax ID</label>
                                <input type="text" 
                                       name="address[OA_TaxID]" 
                                       id="OA_TaxID" 
                                       class="form-control form-control-sm"
                                       placeholder="Organization Tax ID"
                                       value="{{ old('address.OA_TaxID') 
                                                ?? optional($organization->address)->first()->OA_TaxID
                                                ?? '' }}">                                
                              </div>
                            </div>                            
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- Organization Type Form -->
                    <div class="col md-4">
                      <div class="card card-secondary card-outline">
                        <div class="card-header">
                          <h3 class="card-title">Organization Type</h3>
                        </div>
                        <div class="card-body">
                          <!-- Active -->
                          <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsActive]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_active"
                                   name="header[OH_IsActive]"
                                   value="1"
                                   @if($organization->OH_IsActive == true
                                        || $organization->id == '') checked @endif
                                   >
                            <label for="is_active" class="custom-control-label">Active Client</label>
                          </div>                          
                          <hr class="w-100 my-1">                          
                          <!-- Consignee -->
                          <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsConsignee]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_consignee"
                                   name="header[OH_IsConsignee]"
                                   value="1"
                                   @if($organization->OH_IsConsignee == true) checked @endif
                                   >
                            <label for="is_consignee" class="custom-control-label">Consignee</label>
                          </div>
                          <!-- Consignor -->
                          <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsConsignor]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_consignor"
                                   name="header[OH_IsConsignor]"
                                   value="1"
                                   @if($organization->OH_IsConsignor == true) checked @endif
                                   >
                            <label for="is_consignor" class="custom-control-label">Consignor</label>
                          </div>
                          <!-- Transport Client -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsTransportClient]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_transport"
                                   name="header[OH_IsTransportClient]"
                                   value="1"
                                   @if($organization->OH_IsTransportClient == true) checked @endif
                                   >
                            <label for="is_transport" class="custom-control-label">Transport Client</label>
                          </div> --}}
                          <!-- Warehouse -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsWarehouseClient]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_warehouse"
                                   name="header[OH_IsWarehouseClient]"
                                   value="1"
                                   @if($organization->OH_IsWarehouseClient == true) checked @endif
                                   >
                            <label for="is_warehouse" class="custom-control-label">Warehouse</label>
                          </div>
                          <hr class="w-100 my-1"> --}}
                          <!-- Shipper -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsShippingLine]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_shipper"
                                   name="header[OH_IsShippingLine]"
                                   value="1"
                                   @if($organization->OH_IsShippingLine == true) checked @endif
                                   >
                            <label for="is_shipper" class="custom-control-label">Shipping Line</label>
                          </div> --}}
                          <!-- Carrier -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsAirLine]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_carrier"
                                   name="header[OH_IsAirLine]"
                                   value="1"
                                   @if($organization->OH_IsAirLine == true) checked @endif
                                   >
                            <label for="is_carrier" class="custom-control-label">Air Line</label>
                          </div> --}}
                          <!-- Forwarder/Agent -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsForwarder]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_forwarder"
                                   name="header[OH_IsForwarder]"
                                   value="1"
                                   @if($organization->OH_IsForwarder == true) checked @endif
                                   >
                            <label for="is_forwarder" class="custom-control-label">Forwarder/Agent</label>
                          </div> --}}
                          <!-- Brooker -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsBroker]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_brooker"
                                   name="header[OH_IsBroker]"
                                   value="1"
                                   @if($organization->OH_IsBroker == true) checked @endif
                                   >
                            <label for="is_brooker" class="custom-control-label">Customs Brooker</label>
                          </div> --}}
                          <!-- Service -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsMiscFreightServices]" value="0">
                            <input class="custom-control-input"
                                    type="checkbox"
                                    id="is_service"
                                    name="header[OH_IsMiscFreightServices]"
                                    value="1"
                                    @if($organization->OH_IsMiscFreightServices == true) checked @endif
                                    >
                            <label for="is_service" class="custom-control-label">Provider</label>
                          </div> --}}
                          <!-- Competitor -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsCompetitor]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_competitor"
                                   name="header[OH_IsCompetitor]"
                                   value="1"
                                   @if($organization->OH_IsCompetitor == true) checked @endif
                                   >
                            <label for="is_competitor" class="custom-control-label">Competitor</label>
                          </div>
                          <hr class="w-100 my-1"> --}}
                          <!-- Sales -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsSalesLead]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_sales"
                                   name="header[OH_IsSalesLead]"
                                   value="1"
                                   @if($organization->OH_IsSalesLead == true) checked @endif
                                   >
                            <label for="is_sales" class="custom-control-label">Sales</label>
                          </div> --}}
                          <!-- Staff -->
                          {{-- <div class="custom-control custom-checkbox">
                            <input type="hidden" name="header[OH_IsStaff]" value="0">
                            <input class="custom-control-input"
                                   type="checkbox"
                                   id="is_staff"
                                   name="header[OH_IsStaff]"
                                   value="1"
                                   @if($organization->OH_IsStaff == true) checked @endif
                                   >
                            <label for="is_staff" class="custom-control-label">Staff</label>
                          </div> --}}
                        </div>
                      </div>
                    </div>
                  </div>
                  </form>
                </div>
                @if($organization->id != '')

                  <!-- Tab Address -->
                  @include('pages.setup.organization.tab-address')

                  <!-- Tab Contact -->
                  @include('pages.setup.organization.tab-contact')                  

                @endif
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-sm btn-success elevation-2" form="formOrganization">
                <i class="fas fa-save"></i>
                Save
              </button>
              <a href="{{ route('setup.organization') }}" class="btn btn-sm btn-default elevation-2 ml-2">Cancel</a>
              @if($organization->id != '')
              <a href="{{ route('setup.organization.create') }}" class="btn btn-sm btn-info elevation-2 ml-2">
                <i class="fas fa-plus"></i> New
              </a>
              @endif
            </div>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection

@section('footer')
  <script>
    $('input:text').inputmask({
      casing: 'upper',
    });

    $('#OA_TaxID').inputmask({
      mask: "999.999.99-9999999",
    });

    jQuery(document).ready(function(){
      $(document).on('blur', '#OH_FullName', function(){
        if($(this).val() != ''){
          $.ajax({
            url: "{{ route('select2.setup.organization') }}",
            type: "GET",
            dataType: 'json',
            delay: 250,
            data: {
              q: $(this).val(),
              id: "{{ $organization->id }}",
              precise: 1,
              all: 1,
            },
            success:function(msg){
              console.log(msg)
              if(msg != ''){
                toastr.error("Organization already exists", "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            }
          })
        }        
      });      
      $('.country').select2({
        placeholder: 'Select...',
        ajax: {
          url: "{{ route('select2.setup.countries') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.RN_Code + " (" + item.RN_Desc + ")",
                        id: item.RN_Code,
                    }
                })
            };
          },
          cache: true
        }
      });
      $('.unloco').select2({
        placeholder: 'Select...',
        ajax: {
          url: "{{ route('select2.setup.unloco') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.RL_Code + " - "+ item.RL_PortName + " (" + item.RL_RN_NKCountryCode + ")",
                        id: item.RL_Code,
                        code: item.RL_RN_NKCountryCode,
                    }
                })
            };
          },
          cache: true
        }
      });
      $('.unloco').on('select2:select', function (e) {
          var data = e.params.data.code;
          $.ajax({
            url: "{{ route('select2.setup.countries') }}",
            type: "GET",
            data: {
              q: data,
              precise: 1
            },
            success:function(msg){
              console.log(msg);
              var option = new Option(msg.RN_Code + "( " + msg.RN_Desc + " )", msg.RN_Code, true, true);

              $('#OA_RN_NKCountryCode').append(option).trigger('change');
            }
          });
      });     
      $('.currency').select2({
        placeholder: 'Select...',
        ajax: {
          url: "{{ route('select2.setup.currency') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.RX_Code,
                        id: item.RX_Code
                    }
                })
            };
          },
          cache: true
        }
      });       
    });
  </script>
@endsection
