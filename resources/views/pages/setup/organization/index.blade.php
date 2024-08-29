@extends('layouts.master')
@section('header')
  <style>
    .add{cursor: pointer;}
    .remove{cursor: pointer;}
  </style>
@endsection
@section('title') Organizations @endsection
@section('page_name') Organizations @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">      
      <div class="col-12">
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">Organizations</h3>
            <div class="card-tools">
              <a href="{{url()->current()}}/create" class="btn btn-success elevation-2">
                <i class="fas fa-plus-circle"></i>
                Add                
              </a>
              <div class="btn-group">
                <button type="button"
                        class="btn btn-info elevation-2 dropdown-toggle dropdown-icon"
                        data-toggle="dropdown">
                  <i class="fas fa-download"></i> Download
                </button>
                <div class="dropdown-menu">
                  <a href="/download/{{ Request::path() }}" 
                     class="dropdown-item"
                     target="_blank">
                    Organization                
                  </a>
                  <a href="/download/companydata" 
                     class="dropdown-item"
                     target="_blank">
                    Company Data                
                  </a>
                </div>
              </div>
              
              <div class="btn-group">
                <button type="button"
                        class="btn btn-warning elevation-2 dropdown-toggle dropdown-icon"
                        data-toggle="dropdown">
                  <i class="fas fa-download"></i> Upload
                </button>
                <div class="dropdown-menu">
                  <button class="dropdown-item upload"
                          data-toggle="modal"
                          data-target="#modal-upload"
                          data-action="{{ route('upload.setup.organization') }}">
                    Organization                
                  </button>
                  <button class="dropdown-item upload"
                          data-toggle="modal"
                          data-target="#modal-upload"
                          data-action="{{ route('upload.companydata') }}">
                    Company Data                
                  </button>
                </div>
              </div>
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>          
            <div class="card-body">
              <form id="formSearch" action="{{ url()->current() }}" method="get">
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group">
                    <div class="form-check">
                      <input type="hidden" name="OH_IsActive" value="0">
                      <input type="checkbox" 
                              name="OH_IsActive" 
                              id="OH_IsActive" 
                              class="form-check-input"
                              value="1"
                              checked>
                      <label class="form-check-label" for="OH_IsActive">Active</label>
                    </div>
                  </div>
                </div>
              </div>
              <div id="form">
                <div class="row kriteria" id="kriteria_1">
                  <div class="col-md-3">                    
                      <select id="type_1" 
                              class="custom-select custom-select-sm tipe"
                              data-baris="1">
                        <option value="" selected disabled>Choose...</option>
                        <option value="org_type">Organization Type</option>
                        <option value="org_name">Name</option>
                        <option value="org_unloco">Main UNLOCO</option>
                        <option value="org_code">Code</option>
                      </select>
                  </div>
                  <div class="col-md-8 mt-2 mt-md-0" id="hasil_1"></div>                        
                </div>
              </div>              
              <div class="row">                
                <div class="col-6 col-md-4">
                  <div class="row">
                    <div class="col text-primary mt-2 add">
                      <i class="fas fa-plus-circle"></i> Add
                    </div>
                    <div class="col text-danger mt-2 remove">
                      <i class="fas fa-minus-circle"></i> Remove
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button id="btnCari" type="submit" class="btn btn-sm btn-success elevation-2">
                <i class="fas fa-search"></i> Search</button>
            </div>
          </form>
        </div>
      </div>
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              @include('table.ajax')  
            </div>
          </div>
        </div>        
      </div>
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@include('forms.upload', ['action' => '#'])

@endsection

@section('footer')
  <script>
    function unloco(){
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
                        text: item.RL_Code,
                        id: item.RL_Code,
                    }
                })
            };
          },
          cache: true
        }
      });
    }
    var tipeForm = '<div class="row"><div class="col-4 col-md-3"><div class="form-group"><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsConsignor" id="is_consignor" value="1"><label class="form-check-label" for="is_consignor">Consignor</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsConsignee" id="is_consignee" value="1"><label class="form-check-label" for="is_consignee">Consignee</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsTransportClient" id="is_transport" value="1"><label class="form-check-label" for="is_transport">Transport Client</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsWarehouseClient" id="is_warehouse" value="1"><label class="form-check-label" for="is_warehouse">Warehouse</label></div></div></div><div class="col-4 col-md-3"><div class="form-group"><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsAirLine" id="is_carrier" value="1"><label class="form-check-label" for="is_carrier">Airline</label></div><div class="form-group"><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsShippingLine" id="is_shipper" value="1"><label class="form-check-label" for="is_shipper">Shipping Line</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsForwarder" id="is_forwarder" value="1"><label class="form-check-label" for="is_forwarder">Forwarder/Agent</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsBroker" id="is_broker" value="1"><label class="form-check-label" for="is_broker">Brooker</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsMiscFreightServices" id="is_service" value="1"><label class="form-check-label" for="is_service">Service</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="OH_IsCompetitor" id="is_competitor" value="1"><label class="form-check-label" for="is_competitor">Competitor</label></div></div></div></div>';
    var nameForm = '<div class="row"><div class="col-12 col-md-6"><input type="text" name="OH_FullName" class="form-control form-control-sm" placeholder="Organization Full Name"></div></div>';
    var unlocoForm = '<div class="row"><div class="col-12 col-md-6"><select name="OH_RL_NKClosestPort" class="form-control form-control-sm unloco w-100"></select></div></div>';
    var codeForm = '<div class="row"><div class="col-12 col-md-6"><input type="text" name="OH_Code" class="form-control form-control-sm" placeholder="Organization Code"></div></div>';
    jQuery(document).ready(function(){
      $('#dataAjax tbody').on('mouseover', 'tr', function () {
          $('[data-toggle="tooltip"]').tooltip({
              trigger: 'hover',
              html: true
          });
      });
      $(document).on('click', '.add', function(){
        var last = $('.kriteria').length + 1;

        $('#form').append('<div class="row mt-2 kriteria" id="kriteria_'+last+'"><hr class="w-100 d-block d-md-none"><div class="col-md-3"><select id="type_'+last+'" class="custom-select custom-select-sm tipe" data-baris="'+last+'"><option value="" selected disabled>Choose...</option><option value="org_type">Organization Type</option><option value="org_name">Name</option><option value="org_unloco">Main UNLOCO</option><option value="org_code">Code</option></select></div><div class="col-md-8 mt-2 mt-md-0" id="hasil_'+last+'"></div>');
      });
      $(document).on('click', '.remove', function(){
        var last = $('.kriteria').length;
        if(last > 1){
          $('#kriteria_'+last).remove();
        }        
      });
      $(document).on('change', '.tipe', function() {
        var baris = $(this).data('baris');
        var tipe = $(this).find(':selected').val();
        if(tipe == 'org_type'){
          $('#hasil_'+baris).html(tipeForm);          
        } else if(tipe == 'org_name'){
          $('#hasil_'+baris).html(nameForm);
        } else if(tipe == 'org_unloco'){
          $('#hasil_'+baris).html(unlocoForm);
          unloco();
        } else if(tipe == 'org_code'){
          $('#hasil_'+baris).html(codeForm);
        }
      })
      $(document).on('submit', '#formSearch', function(e){
        e.preventDefault();
        var form = $(this).serialize();
        $('#dataAjax').DataTable().destroy();
        $('#btnCari').prop('disabled', true);
        $.ajax({
          url: "{{ url()->current() }}",
          type: "GET",
          data: form,
          success:function(msg){
            $('#dataAjax').DataTable({
              data: msg.data,
              columns:[
                @forelse ($items as $keys => $item)
                  @if($keys == 'id')
                  {data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},                  
                  @else
                  {data: "{{$keys}}", name: "{{$keys}}"},
                  @endif
                @empty            
                @endforelse          
                // {data: 'actions', name: 'actions', orderable: false, searchable: false}
              ]
            });             
            $('#btnCari').prop('disabled', false);
          }
        })
        
      });
      $(document).on('click', '.upload', function(e){
        var action = $(this).data('action');

        $('#formUpload').attr('action', action);
      });
    });
  </script>
@endsection