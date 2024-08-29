@extends('layouts.master')
@section('title') Shipments @endsection
@section('page_name') Shipments @endsection

@section('content')
<!-- Main content -->


<!-- Form Print Surat Tugas Pembatalan PEB-->
<div class="modal fade" id="modal-PrintCargoDeliveryReceipt">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content bg-info">
            <div class="modal-header">
                <h4 class="modal-title">Print Cargo Delivery Receipt</h4>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <form id='frmPrintSuratKuasa' target='_BLANK' action="/manifest/shipment/PrintCargoDeliveryReceipt" method='GET'>
                        <input type="hidden" name="JobShipmentPK" id="JobShipmentPK" value="{{ $item->id ?? '' }}">
                        <input type="hidden" name="FileName" id="FileName" value="DeliveryReceipt_JGE_TPS">
                        {{-- <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group form-group-sm">
                                    <label for="NO_SURAT">NO SURAT</label>
                                    <input type="text" name="NO_SURAT" id="NO_SURAT"
                                                class="form-control form-control-sm form-control-border border-width-2">
                                </div>
                            </div>
                        </div> --}}
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group form-group-sm">
                                    <label for="NM_CONSIGNEE">Deliver To</label>
                                    <input type="text" name="NM_CONSIGNEE" id="NM_CONSIGNEE"
                                                class="form-control form-control-sm form-control-border border-width-2">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group form-group-sm">
                                    <label for="AL_CONSIGNEE">Deliver Address To</label>
                                    <textarea name="AL_CONSIGNEE"
                                        id="AL_CONSIGNEE"
                                        class="form-control form-control-sm"
                                        rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group form-group-sm">
                                    <label for="USERRECEIPT">Nama Penerima</label>
                                    <input type="text" name="USERRECEIPT" id="USERRECEIPT"
                                                class="form-control form-control-sm form-control-border border-width-2">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group form-group-sm">
                                    <label for="NO_TLP">No. Tlp.</label>
                                    <input type="text" name="NO_TLP" id="NO_TLP"
                                                class="form-control form-control-sm form-control-border border-width-2">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group form-group-sm">
                                    <label for="NO_POL">Plat No. Kendaraan</label>
                                    <input type="text" name="NO_POL" id="NO_POL"
                                                class="form-control form-control-sm form-control-border border-width-2">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group form-group-sm">
                                    <label for="USERBY">User By</label>
                                    <input type="text" name="USERBY" id="USERBY" value="{{ auth()->user()->name }}"
                                                class="form-control form-control-sm form-control-border border-width-2">
                                </div>
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group form-group-sm">
                                    <label for="TANGGALTUGAS">TANGGAL</label>
                                    <input type="date" name="TANGGALTUGAS" id="TANGGALTUGAS"
                                                class="form-control form-control-sm form-control-border border-width-2">
                                </div>
                            </div>
                        </div> --}}
                        <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btnClosedBillCostRev">Close</button>
                        <button type="submit" class="btn btn-success elevation-2 float-right">
                            <i class="fas fa-save"></i> Print
                        </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
            <h3 class="card-title">Shipment || 
              @if($item->id)
                {{ $item->NO_BARANG }} ({{ $item->branch->CB_Code ?? "-" }}) - 
                @if($item->SCAN_OUT_DATE)
                Gate Out
                @elseif($item->SCAN_IN_DATE)
                Gate In
                @else
                Not Scanned
                @endif
                #{{ $item->BC_CODE ?? "" }}
              @endif
            </h3>            
            <div class="card-tools">
              @can('update_bc11')
              <button class="btn btn-xs btn-danger elevation-2 updateNoBC"
                      data-href="{{ route('update.bc11') }}?m={{ $item->MasterID }}&h={{ $item->id }}">
                <i class="fas fa-send"></i>
                Update No BC
              </button>              
              @endcan
              @can('update_bc11_ceisa')
              <button class="btn btn-xs btn-danger elevation-2 updateNoBC"
                      data-href="{{ route('update.bc11') }}?m={{ $item->MasterID }}&h={{ $item->id }}&ceisa=1">
                <i class="fas fa-send"></i>
                Update No BC Ceisa 4.0
              </button>
              @endcan
              <button type="button" class="btn btn-xs btn-success elevation-2 dropdown-toggle dropdown-icon" data-toggle="dropdown">
                <i class="fa fa-print"></i>
                Print
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('download.manifest.shipments', ['shipment' => $item->id, 'header' => 1]) }}" target="_blank">DO With Header</a>
                <a class="dropdown-item" href="{{ route('download.manifest.shipments', ['shipment' => $item->id, 'header' => 0]) }}" target="_blank">DO Without Header</a>
                <a class="dropdown-item" href="#" id="btnPrintCargoDeliveryReceipt" data-toggle="modal" data-target="#modal-PrintCargoDeliveryReceipt">Cargo Delivery Receipt</a>
                <a class="dropdown-item" href="{{ route('download.manifest.label', ['house' => \Crypt::encrypt($item->id)]) }}?format=xml">XML</a>
              </div>
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
            <div class="col-4 float-right">
              <div class="form-group form-group-sm row">
                <label for="GO_TO" class="col-sm-2 col-form-label text-right">Go To</label>
                <div class="col-sm-10">
                  <select name="GO_TO" id="GO_TO"
                          class="select2bs4"
                          style="width: 100%;"
                          data-placeholder="Go To"
                          onchange="if (this.value) window.location.href=this.value">
                    <option value="" selected disabled>Select...</option>
                    <option value="{{ route('manifest.consolidations.edit', ['consolidation' => \Crypt::encrypt($item->MasterID)]) }}">MAWB - {{ $item->mawb_parse }}</option>
                    @forelse ($item->master?->houses as $hawb)
                    <option value="{{ route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($hawb->id)]) }}" @selected($item->id == $hawb->id)>
                      {{ $loop->iteration }} - {{ $hawb->NO_BARANG }} => {{ $hawb->NM_PENERIMA }}
                    </option>
                    @empty                      
                    @endforelse
                  </select>
                </div>
              </div>
            </div>
          </div>
          {{-- <input type="hidden" id="JR_GE" name="JR_GE" value="96"/> --}}
          <div class="card-body">
            <!-- Tab Lists -->
            <ul class="nav nav-tabs" id="custom-content-above-tab" role="tablist">
              <li class="nav-item">
                <a class="nav-link" id="main-data" data-toggle="pill" href="#main-data-content" role="tab" aria-controls="main-data-content" aria-selected="true">Main Data</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="tab-houses" data-toggle="pill" href="#tab-houses-content" role="tab" aria-controls="tab-houses-content" aria-selected="false">HS Codes</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="tab-response" data-toggle="pill" href="#tab-response-content" role="tab" aria-controls="tab-response-content" aria-selected="false">Response</a>
              </li>
              {{-- <li class="nav-item">
                <a class="nav-link" id="tab-partial" data-toggle="pill" href="#tab-partial-content" role="tab" aria-controls="tab-partial-content" aria-selected="false">Partial</a>
              </li> --}}
              <li class="nav-item">
                <a class="nav-link" id="tab-estimasi" data-toggle="pill" href="#tab-estimasi-content" role="tab" aria-controls="tab-estimasi-content" aria-selected="false">Estimasi Billing</a>
              </li>
              
              <li class="nav-item">
                <a class="nav-link" id="tab-log" data-toggle="pill" href="#tab-log-content" role="tab" aria-controls="tab-log-content" aria-selected="false">Logs</a>
              </li>
            </ul>
            <!-- Tab Contents -->
            <div class="tab-content" id="custom-content-above-tabContent">
              <div class="tab-pane fade show active" id="main-data-content" role="tabpanel" aria-labelledby="main-data">

                <div class="row mt-2">
                  <!-- Organization Details Form -->
                  <div class="col-12">
                    <div class="card card-primary card-outline">
                      {{-- <div class="card-header">
                        <h3 class="card-title">Details</h3>
                      </div> --}}
                      <form id="formDetails"
                            @if($item->id)
                            action="{{ route('houses.update', ['house' => \Crypt::encrypt($item->id)]) }}"
                            @else
                            action="{{ route('houses.store') }}"
                            @endif
                            method="POST"
                            class="form-horizontal needs-validation"
                            autocomplete="off"
                            novalidate>

                        @csrf

                        @if($item->id)
                          @method('PUT')
                        @endif

                        <div class="card-body">

                          @include('pages.manifest.reference.house')

                        </div>
                        <!-- /.card-body -->
                      </form>
                      <div class="card-footer">
                        @if($disabled != 'disabled')
                          <button type="submit"
                                  class="btn btn-sm btn-success elevation-2"
                                  form="formDetails">
                            <i class="fas fa-save"></i>
                            Save
                          </button>
                        @endif
                        <a href="{{ route('manifest.shipments') }}"
                            class="btn btn-sm btn-default elevation-2 ml-2">Cancel</a>
                      </div>
                      <!-- /.card-footer -->
                    </div>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="tab-houses-content" role="tabpanel" aria-labelledby="tab-houses">
                <div class="row mt-2">
                  <div class="col-12">
                    <div class="card card-primary card-outline">
                      <div class="card-header">
                        <h3 class="card-title">HS Codes</h3>
                        <div class="card-tools">
                          <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                          </button>
                        </div>
                      </div>
                      <div class="card-body">
                        @include('pages.manifest.reference.items')
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade" id="tab-response-content" role="tabpanel" aria-labelledby="tab-response">
                <div class="row mt-2">
                  @include('pages.manifest.reference.logs-bc')
                </div>
              </div>

              <div class="tab-pane fade" id="tab-partial-content" role="tabpanel" aria-labelledby="tab-partial">
                <div class="row mt-2">
                  PARTIAL
                </div>
              </div>

              <div class="tab-pane fade" id="tab-estimasi-content" role="tabpanel" aria-labelledby="tab-estimasi">
                <div class="row mt-2">
                  <div class="col-12">
                    <div class="card card-info card-outline">
                      <div class="card-header">
                        <h3 class="card-title">Calculate</h3>
                        <div class="card-tools">
                          <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                          </button>
                        </div>
                      </div>
                      <div class="card-body">
                        @include('pages.manifest.reference.calculate')
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="tab-pane fade" id="tab-log-content" role="tabpanel" aria-labelledby="tab-log">
                <div class="row mt-2">
                  @include('pages.manifest.reference.logs')
                </div>
              </div>

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

@endsection

@section('footer')
  <script>
    var btnRespons = '<button id="sendResponsePlp" data-jenis="plp-response" data-judul="Request PLP" class="btn btn-sm btn-info btn-block elevation-2 plp"> <i class="fas fa-sync-alt"></i> Get Response </button>';
    var btnBatal = '<button id="sendRequestBatalPlp" data-jenis="plp-batal" data-judul="Request Batal PLP" class="btn btn-sm btn-danger btn-block elevation-2 plp"> <i class="fas fa-paper-plane"></i> Request Batal PLP </button>';
    var btnBatalResponse = '<button id="sendResponseBatalPlp" data-jenis="plp-resbatal" data-judul="Response Batal" class="btn btn-sm btn-warning btn-block elevation-2 plp"> <i class="fas fa-paper-plane"></i> Get Response Batal </button>';
      $(function () {
          $('#datetimepicker1').datetimepicker({
            icons: { time: 'far fa-clock' },
            format: 'DD-MM-YYYY HH:mm:ss',
            sideBySide: true,
            allowInputToggle: true,
          });

          $('.withtime').datetimepicker({
            icons: { time: 'far fa-clock' },
            format: 'DD-MM-YYYY HH:mm',
            sideBySide: true,
            allowInputToggle: true,
          });

          $('.onlydate').datetimepicker({
            icons: { time: 'far fa-clock' },
            format: 'DD-MM-YYYY',
            allowInputToggle: true,
          });

          $('.mawb-mask').inputmask({
            mask: "999-99999999",
            removeMaskOnSubmit: true
          });

          // @if($disabled == 'disabled')
          //   $('input, select, textarea, button[type=submit]').not('#input-search, #PostDateBilling, #btnPostingBilling, #btnPostingCost').prop('disabled', true);
          // @endif

      });
    function findNpwp() {
      var npwp = $('#mBRANCH').find(':selected').attr('data-npwp');

      $('#NPWP').val(npwp);
      @if(!$item->id)
        $('#KPBC').append('<option value="050100" selected>050100 - KPPBC Soekarno-Hatta</option>').trigger('change');
      @endif
    }
    function getTblLogs(){
      $('#tblLogs').DataTable().destroy();

      $.ajax({
        url: "{{ route('logs.show') }}",
        type: "GET",
        data:{
          type: 'house',
          id: "{{ $item->id }}",
        },
        success: function(msg){
          $('#tblLogs').DataTable({
            data:msg.data,
            pageLength: parseInt("{{ config('app.page_length') }}"),
            columns:[
              {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
              {data:"created_at", name: "created_at"},
              {data:"user", name: "user"},
              {data:"keterangan", name: "keterangan", searchable: false},
            ],
            buttons: [
                'excelHtml5',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                },
                'print',
            ],
          }).buttons().container().appendTo('#tblLogs_wrapper .col-md-6:eq(0)');
        }

      })
    }
    function setDate() {
      var arrival = "{{ ($item->SCAN_IN_DATE) ? $item->SCAN_IN_DATE : $item->TGL_TIBA }}";
      var exit = "{{ ( $item->SCAN_OUT_DATE) ? $item->SCAN_OUT_DATE : $item->estimatedExitDate }}";
      var chargable = "{{ $item->ChargeableWeight }}";
      var gross = "{{ $item->BRUTO }}";

      if(arrival != ''){
        var parseArrival = moment(arrival).format('DD-MM-YYYY');
        if(parseArrival != NaN)
        {
          $('#cal_arrival').val(parseArrival);
        }
      }

      if(exit !== ''){
        var parseExit = moment(exit).format('DD-MM-YYYY');
        if(parseExit != NaN)
        {
          $('#cal_out').val(parseExit);
        }

      }

      if(arrival != '' && exit != ''){
        calDays();
      }

      $('#cal_chargable').val(chargable);
      $('#cal_gross').val(gross);

      $('#formCalculate').attr('action', "/manifest/calculate/{{ \Crypt::encrypt($item->id) }}");
      $('#formStoreCalculate').attr('action', "/manifest/save-calculate/{{ \Crypt::encrypt($item->id) }}");
    }
    function calDays() {
      var one = $('#cal_arrival').val();
      var two = $('#cal_out').val();

      if(one && two){
        var dayOne = moment(one, "DD-MM-YYYY", true);
        var dayTwo = moment(two, "DD-MM-YYYY", true);
        var diff = dayTwo.diff(dayOne, 'days');
        if(diff != NaN){
          $('#cal_days').val(diff + 1);
        }

        $('#cal_date').val(two);
      }
    }

    jQuery(document).ready(function(){
      // showTab();

      $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        switch (e.target.id){
          case "tab-log":{
            getTblLogs();
            break;
          }
          case "tab-houses":{
            getTblHSCodes("{{ $item->id }}");
            $('#formHSCodes #house_id').val("{{ \Crypt::encrypt($item->id) }}");
            break;
          }
          case "tab-estimasi":{
            setDate();
            break;
          }
          case "tab-response":{
            getTblLogsBc("{{ $item->id }}");
            break;
          }
          case "tab-bilcost":{
            tableShipmentsBilling($('#JR_JH').val());
            tableShipmentsCost($('#JR_JH').val());
            break;
          }
        }
      });
      $('.select2unloco').select2({
        placeholder: 'Select...',
        allowClear: true,
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
      $('.select2kpbc').select2({
        placeholder: 'Select...',
        allowClear: true,
        ajax: {
          url: "{{ route('select2.setup.customs-offices') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.Kdkpbc + " - "+ item.UrKdkpbc,
                        id: item.Kdkpbc,
                    }
                })
            };
          },
          cache: true
        }
      });
      $('.select2organization').select2({
        placeholder: 'Select...',
        ajax: {
          url: "{{ route('select2.setup.organization') }}",
          dataType: 'json',
          delay: 250,
          data: function (params) {
            var query = {
              q: params.term,
              type: $(this).attr('data-type'),
              // country: $(this).attr('data-country'),
              create: true,
              address: 1
            }

            return query;
          },
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: (item.OH_LegacyCode ?? item.OH_Code)+" - "+item.OH_FullName + " || " + item.OA_Address1,
                        id: item.OH_FullName,
                        name: item.OH_FullName,
                        address: item.OA_Address1,
                        tax: item.OA_TaxID,
                        phone: item.OA_Phone,
                    }
                })
            };
          },
          cache: true
        },
        templateSelection: function(container) {
            $(container.element).attr("data-address", container.address)
                                .attr("data-tax", container.tax)
                                .attr("data-phone", container.phone);
            return container.text;
        }
      });
      $(document).on('change', '.select2organization', function(){
        var target = $(this).attr('data-target');
        var npwp = $(this).attr('data-npwp');
        var phone = $(this).attr('data-phone');
        var address = $(this).find(':selected').attr('data-address');
        var idpenerima = $(this).find(':selected').attr('data-tax');
        var phonepenerima = $(this).find(':selected').attr('data-phone');

        if(address != undefined){
          $('#'+target).val(address.toUpperCase());
        }
        if(npwp != ''){
          $('#'+npwp).val(idpenerima);
        }
        if(phone != ''){
          $('#'+phone).val(phonepenerima);
        }

        if(idpenerima != '' && idpenerima != undefined){
          var count = idpenerima.replace(/[^0-9]/g,'');
          if(count.length > 12){
            var value = 5;
          } else if (count.lenght > 10){
            var value = 0;
          } else if(count.length == 10){
            var value = 1;
          } else {
            var value = 4;
          }
          console.log(count.lenght);
          $('#JNS_ID_PENERIMA').val(value).trigger('change');
        }
      });
      $(document).on('change', '#cal_tariff', function(){
        var val = $(this).find(':selected').val();

        $('#cal_tariff_id').val(val);
      });
      $(document).on('input paste', '.tanggal', function(){
        var tgl = $(this).val();
        var ganti = $(this).attr('data-ganti');
        if(tgl != ''){
          var tanggal = moment(tgl, 'DD-MM-YYYY').format('YYYY-MM-DD');
        } else {
          var tanggal = '';
        }

        $('#'+ganti).val(tanggal);

      });
      $(document).on('change', '#JNS_AJU', function(){
        var ref = $(this).find(':selected').attr('data-ref');
        $('#KD_DOC').val(ref).trigger('change');
      });
      $(document).on('click', '.hapusHouse', function(){
        var href = $(this).data('href');

        Swal.fire({
          title: 'Are you sure?',
          html: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, delete!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: href,
              type: "POST",
              data:{
                _token: "{{ csrf_token() }}",
                _method: "DELETE"
              },
              success:function(msg){
                if(msg.status == 'OK'){
                  toastr.success("Delete House Success", "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

                  getTblHouse();

                  $('#collapseHouse').removeClass('show');
                  $('#collapseHSCodes').removeClass('show');
                  $('#collapseResponse').removeClass('show');

                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error:function(jqXHR, exception){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            })
          }
        });
      });
      $(document).on('click', '.hapusDetail', function(){
        var href = $(this).data('href');

        Swal.fire({
          title: 'Are you sure?',
          html: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, delete!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: href,
              type: "POST",
              data:{
                _token: "{{ csrf_token() }}",
                _method: "DELETE"
              },
              success:function(msg){
                if(msg.status == 'OK'){
                  toastr.success("Delete House Item Success", "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

                  getTblHSCodes(msg.house);
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error:function(jqXHR, exception){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            })
          }
        });
      });
      $(document).on("change.datetimepicker", '.onlydate', function (e) {
          calDays();
      });
      $(document).on('click', '#btnCalculate', function(){
        $('#show_estimate').val(0);
        $('#show_actual').val(0);

        $('#formCalculate').submit();

        $('.saveCalculation').removeClass('d-none');
      });
      $(document).on('submit', '#formCalculate', function(e){
        e.preventDefault();
        var action = $(this).attr('action');
        var data = $(this).serialize();

        $('.btn').prop('disabled', 'disabled');

        $.ajax({
          url: action,
          type: "GET",
          data: data,
          success:function(msg){
            console.log(msg);
            $('#tblIsiCalculate').html(msg);

            $('.btn').prop('disabled', false);
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        })
      });
      $(document).on('click', '.saveCalculation', function(){
        var estimate = $(this).attr('data-estimate');
        var info = 'Estimated';

        if(estimate < 1){
          info = 'Actual';
        }

        Swal.fire({
          title: 'Save '+info+'?',
          html: "This will replace current data if exists!",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, calculate!'
        }).then((result) => {
          if (result.value) {
            $('#formStoreCalculate #is_estimate').val(estimate);

            var action = $('#formStoreCalculate').attr('action');
            var data = $('#formStoreCalculate').serialize();

            $.ajax({
              url: action,
              type: "POST",
              data:data,
              success:function(msg){
                if(msg.status == 'OK'){
                  toastr.success("Store "+info+" Success", "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                  if(msg.estimate > 0){
                    $('#btnShowActual').removeClass('d-none');
                  } else {
                    $('#btnShowEstimated').removeClass('d-none');
                  }
                  $('#btnEstimateH').attr('href', "/manifest/download-calculated/"+msg.id+"?header=1");
                  $('#btnEstimateWH').attr('href', "/manifest/download-calculated/"+msg.id+"?header=0");
                  $('#btnEstimateH').removeClass('d-none');
                  $('#btnEstimateWH').removeClass('d-none');
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error:function(jqXHR, exception){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            })
          }
        });
      });
      $(document).on('click', '#btnShowEstimated', function(){
        $('#show_estimate').val(1);

        $('#formCalculate').submit();

        $('.saveCalculation').addClass('d-none');
      });
      $(document).on('click', '#btnCreateJobheader', function(){

        Swal.fire({			
          title: 'Create Job Billing/Cost?',			
          html:
            "Create Job Billing/Cost for this shipment?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, create!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: "{{ route('manifest.shipments.update', ['shipment' => \Crypt::encrypt($item->id)]) }}",
              type: "POST",
              data:{
                _method: "PUT"
              },
              success: function(msg){
                if(msg.status == 'OK'){
                  showSuccess(msg.message);
                  location.reload();
                } else {
                  showError(msg.message);
                }
              },
              error:function(jqXHR){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                showError(jqXHR.status + ' || ' + jsonValue.message);
              }
            })
          }
        });
      });
      $(document).on('click', '.updateNoBC', function(){

        var url = $(this).attr('data-href');

        Swal.fire({			
          title: 'Update No BC?',			
          html:
            "This will send Update No BC to Customs?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, update!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: url,
              type: "POST",
              success: function(msg){
                if(msg.status == 'OK'){
                  showSuccess(msg.message);
                } else {
                  showError(msg.message);
                }
              },
              error:function(jqXHR){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                showError(jqXHR.status + ' || ' + jsonValue.message);
              }
            })
          }
        });
      });
      $('#formDetails').dirty({
        preventLeaving: true,
      });
    });
  </script>
@endsection
