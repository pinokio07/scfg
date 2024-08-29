<div class="row">
  <div class="col-lg-4">
    @if($disabled != 'disabled')
    <button id="btnNewItem" class="btn btn-sm btn-primary btn-block elevation-2"
            data-toggle="modal"
            data-target="#modal-item">
      <i class="fas fa-plus"></i> Add Item
    </button>
    @endif
  </div>
</div>
<div class="table-responsive mt-2">
  <table id="tblHSCodes" class="table table-sm table-striped" style="width: 100%">
    <thead>
      <tr>
        @forelse ($headerDetail as $hd)
          <th>{{ $hd }}</th>
        @empty
          
        @endforelse
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<div class="modal fade" id="modal-item">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Items</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formHSCodes"
              class="form-horizontal" 
              method="post">
          @csrf
          @method('PUT')
          <input type="hidden" name="house_id" id="house_id">
          <!-- HS Code -->
          <div class="form-group row">
            <label for="HS_CODE" 
                   class="col-lg-3 col-form-label">
              HS Code</label>
            <div class="col-lg-9">
              <input type="text" 
                    class="form-control form-control-sm clearable"
                    id="HS_CODE"
                    name="HS_CODE"
                    placeholder="HS Code"
                    required
                    {{ $disabled }}>
            </div>
          </div>
          <!-- Descriptons -->
          <div class="form-group row">
            <label for="UR_BRG" 
                   class="col-lg-3 col-form-label">
              Description</label>
            <div class="col-lg-9">
              <textarea name="UR_BRG" 
                        id="UR_BRG"
                        class="form-control form-control-sm clearable"
                        placeholder="Item Description"
                        rows="3"
                        {{ $disabled }}></textarea>
            </div>
          </div>
          <!-- IMEI1 -->
          <div class="form-group row">
            <label for="IMEI1" 
                   class="col-lg-3 col-form-label">
              IMEI1</label>
            <div class="col-lg-9">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="IMEI1"
                    name="IMEI1"
                    placeholder="IMEI1"
                    {{ $disabled }}>
            </div>
          </div>
          <!-- IMEI2 -->
          <div class="form-group row">
            <label for="IMEI2" 
                   class="col-lg-3 col-form-label">
              IMEI2</label>
            <div class="col-lg-9">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="IMEI2"
                    name="IMEI2"
                    placeholder="IMEI2"
                    {{ $disabled }}>
            </div>
          </div>
          <!-- CIF -->
          <div class="form-group row">
            <label for="CIF" 
                   class="col-lg-3 col-form-label">
              CIF</label>
            <div class="col-lg-3">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="CIF"
                    name="CIF"
                    placeholder="CIF"
                    {{ $disabled }}>
            </div>
          </div>
          <!-- Satuan Harga -->
          <div class="form-group row">
            <label for="JML_SAT_HRG" 
                   class="col-lg-3 col-form-label">
              JML Sat Harga</label>
            <div class="col-lg-3">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="JML_SAT_HRG"
                    name="JML_SAT_HRG"
                    placeholder="Jumlah Satuan Harga"
                    {{ $disabled }}>
            </div>
            <label for="KD_SAT_HRG" 
                   class="col-lg-3 col-form-label">
                   KD Sat Harga</label>
            <div class="col-lg-3">
              <input type="text" 
                    class="form-control form-control-sm clearable"
                    id="KD_SAT_HRG"
                    name="KD_SAT_HRG"
                    placeholder="Kode Satuan Harga"
                    {{ $disabled }}>
            </div>
          </div>
          <!-- Kemasan -->
          <div class="form-group row">
            <label for="JML_KMS" 
                   class="col-lg-3 col-form-label">
              Jumlah Kemasan</label>
            <div class="col-lg-3">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="JML_KMS"
                    name="JML_KMS"
                    placeholder="Jumlah Kemasan"
                    {{ $disabled }}>
            </div>
            <label for="JNS_KMS" 
                   class="col-lg-3 col-form-label">
                   Jenis Kemasan</label>
            <div class="col-lg-3">
              <input type="text" 
                    class="form-control form-control-sm clearable"
                    id="JNS_KMS"
                    name="JNS_KMS"
                    placeholder="Jenis Kemasan"
                    {{ $disabled }}>
            </div>
          </div>
          <!-- Flag Bebas -->
          <div class="form-group row">
            <label for="FL_BEBAS"
                   class="col-lg-3 col-form-label">Flag Bebas</label>
            <div class="col-lg-3">
              <select name="FL_BEBAS" id="FL_BEBAS"
                      class="custom-select custom-select-sm">
                <option value="0">Tidak</option>
                <option value="1">Ya</option>
              </select>
            </div>
          </div>
          <!-- SKEP -->
          <div class="form-group row">
            <label for="NO_SKEP" 
                   class="col-lg-3 col-form-label">
              SKEP</label>
            <div class="col-lg-3">
              <input type="text" 
                    class="form-control form-control-sm clearable"
                    id="NO_SKEP"
                    name="NO_SKEP"
                    placeholder="SKEP Number"
                    {{ $disabled }}>
            </div>
            <label for="TGL_SKEP" 
                   class="col-lg-3 col-form-label">
              Tanggal Skep</label>
            <div class="col-lg-3">
              <div class="input-group input-group-sm date onlydate clearable" 
                  id="dtp_tgl_skep" 
                  data-target-input="nearest">
              <input type="text" 
                      id="tglskep"
                      class="form-control datetimepicker-input tanggal"
                      placeholder="SKEP Date"
                      data-target="#dtp_tgl_skep"
                      data-ganti="TGL_SKEP"
                      {{ $disabled }}>
              <div class="input-group-append" 
                    data-target="#dtp_tgl_skep" 
                    data-toggle="datetimepicker">
                <div class="input-group-text">
                  <i class="fa fa-calendar"></i>
                </div>
              </div>
            </div>
            <input type="hidden" 
                    name="TGL_SKEP" 
                    id="TGL_SKEP" 
                    class="form-control form-control-sm clearable"
                    {{ $disabled }}>
            </div>
          </div>
          <!-- Tariff -->
          <div class="form-group row">
            <label for="BM_TRF" 
                   class="col-lg-3 col-form-label">
              Tarrif</label>
            <div class="col-lg-2 text-right">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="BM_TRF"
                    name="BM_TRF"
                    placeholder="BM"
                    {{ $disabled }}>
              <span>% BM</span>
            </div>
            <div class="col-lg-2 text-right">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="PPN_TRF"
                    name="PPN_TRF"
                    placeholder="PPN"
                    {{ $disabled }}>
              <span>% PPN</span>
            </div>
            <div class="col-lg-2 text-right">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="PPH_TRF"
                    name="PPH_TRF"
                    placeholder="PPH"
                    {{ $disabled }}>
              <span>% PPH</span>
            </div>
            <div class="col-lg-3 text-right">
              <input type="text" 
                    class="form-control form-control-sm desimal clearable"
                    id="BMTP_TRF"
                    name="BMTP_TRF"
                    placeholder="Rupiah BMTP / pcs"
                    {{ $disabled }}>
              <span>Rupiah BMTP / pcs</span>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
        <button type="submit" form="formHSCodes" 
                class="btn btn-lg btn-primary">
          <i class="fas fa-save"></i> Save
        </button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script>
  function getTblHSCodes(id) {
    $('#tblHSCodes').DataTable().destroy();

    $.ajax({
      url:"{{ route('house-details.index') }}",
      type: "GET",
      data:{
        id:id,
      },
      success:function(msg){

        $('#tblHSCodes').DataTable({
          data:msg.data,
          pageLength: parseInt("{{ config('app.page_length') }}"),
          columns:[
            @forelse ($headerDetail as $keys => $value )
              @if($keys == 'id')
              {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false},
              @elseif($keys == 'actions')
              {data:"actions", searchable: false, className:"text-nowrap"},
              @elseif($keys == 'TGL_SKEP')
              {data: "{{$keys}}", name: "{{$keys}}", className:"text-nowrap"},
              @elseif(in_array($keys, ['BM_TRF', 'PPN_TRF', 'PPH_TRF']))
              {data: "{{$keys}}", name: "{{$keys}}",
                // render: $.fn.dataTable.render.number(',', '.', 2, '')
                render: function(data, type, full, meta) {
                  return formatAsMoney(data) + "%"
                }                
              },
              @elseif(in_array($keys, ['BEstimatedBM', 'BEstimatedPPN', 'BEstimatedPPH', 'BEstimatedBMTP', 'BMTP_TRF']))
              {data: "{{$keys}}", name: "{{$keys}}",
                render: $.fn.dataTable.render.number(',', '.', 2, '')
              },
              @else
              {data: "{{$keys}}", name: "{{$keys}}"},
              @endif
            @empty                
            @endforelse
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
        }).buttons().container().appendTo('#tblHSCodes_wrapper .col-md-6:eq(0)');

      }
    });
  }
  jQuery(document).ready(function(){
    $(document).on('click', '.editDetail', function(){
      var id = $(this).attr('data-id');
      var house = $(this).attr('data-house');

      $.ajax({
        url: "/manifest/house-details/"+id,
        type: "GET",
        success: function(msg){
          $('#formHSCodes').attr('action', '/manifest/house-details/'+id);
          $('#formHSCodes input[name="_method"]').val('PUT');
          $('#formHSCodes #house_id').val(house);
          $('#formHSCodes #HS_CODE').val(msg.detail.HS_CODE);
          $('#formHSCodes #UR_BRG').val(msg.detail.UR_BRG);
          $('#formHSCodes #CIF').val(msg.detail.CIF);
          $('#formHSCodes #JML_SAT_HRG').val(msg.detail.JML_SAT_HRG);
          $('#formHSCodes #KD_SAT_HRG').val(msg.detail.KD_SAT_HRG);
          $('#formHSCodes #JML_KMS').val(msg.detail.JML_KMS);
          $('#formHSCodes #JNS_KMS').val(msg.detail.JNS_KMS);
          $('#formHSCodes #BM_TRF').val(msg.detail.BM_TRF);
          $('#formHSCodes #PPN_TRF').val(msg.detail.PPN_TRF);
          $('#formHSCodes #PPH_TRF').val(msg.detail.PPH_TRF);
          $('#formHSCodes #BMTP_TRF').val(msg.detail.BMTP_TRF);
          $('#formHSCodes #FL_BEBAS').val(msg.detail.FL_BEBAS);
          $('#formHSCodes #NO_SKEP').val(msg.detail.NO_SKEP);

          if(msg.detail.TGL_SKEP){
            var tglskep = moment(msg.detail.TGL_SKEP);

            $('#tglskep').val(tglskep.format('DD/MM/YYYY')).trigger('change');
            $('#TGL_SKEP').val(tglskep.format('YYYY-MM-DD')).trigger('change');
          } else {
            $('#tglskep').val('').trigger('change');
            $('#TGL_SKEP').val('').trigger('change');
          }
        },
        error: function (jqXHR, exception) {
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
        }
      });      
    });
    $(document).on('click', '#btnNewItem', function(){
      $('#formHSCodes').attr('action', '/manifest/house-details');
      $('#formHSCodes input[name="_method"]').val('POST');
      $('.clearable').val('');
      $('#formHSCodes #FL_BEBAS').val('0');
      $('#formHSCodes #KD_SAT_HRG').val('PCE');
      $('#formHSCodes #JML_KMS').val('1');
      $('#formHSCodes #JNS_KMS').val('PK');
    });
    $(document).on('change input paste', '#modal-item #HS_CODE', function(){
      var val = $(this).val();
      var house = $('#house_id').val();

      if(val != '' && house != '' && val.length > 4)
      {
        setTimeout(() => {
          $.ajax({
            url: "/manifest/cek-hscode/"+house,
            type: "GET",
            data:{
              val : val,
            },
            success: function(msg) {
              $('#BM_TRF').val(msg.BM_TRF);
              $('#PPN_TRF').val(msg.PPN_TRF);
              $('#PPH_TRF').val(msg.PPH_TRF);
              $('#BMTP_TRF').val(msg.BMTP_TRF);
            },
            error: function (jqXHR, exception) {
              jsonValue = jQuery.parseJSON( jqXHR.responseText );
              toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
            }
          })
        }, 350);
      }
    });
    $(document).on('submit', '#formHSCodes', function(e){
      e.preventDefault();

      var form = $(this).serialize();
      var action = $(this).attr('action');

      $('.btn').prop('disabled', 'disabled');

      $.ajax({
        url: action,
        type: "POST",
        data: form,
        success:function(msg){
          console.log(msg);
          if(msg.status == 'OK'){
            toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('#modal-item').modal('toggle');
            getTblHSCodes(msg.house);
          } else {
            toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
          }
          
          $('.btn').prop('disabled', false);
        },
        error: function (jqXHR, exception) {
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

          $('.btn').prop('disabled', false);
        }
      })
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
  })
</script>