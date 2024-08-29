<div class="col-lg-3">
  <button data-id="{{ $item->id }}" data-ceisa="0" class="btn btn-success btn-block elevation-2 mb-4 paksarespon">
    <i class="fas fa-sync"></i> Get Responses
  </button>  
</div>
@can('get_respon_ceisa')
<div class="col-lg-3">  
  <button data-id="{{ $item->id }}" data-ceisa="1" class="btn btn-primary btn-block elevation-2 mb-4 paksarespon">
    <i class="fas fa-sync"></i> Get Responses Ceisa 4.0
  </button>
</div>
@endcan
<div class="table-responsive mt-2">
  <table id="tblLogsBc" class="table table-sm table-striped" style="width: 100%">
    <thead>
      <tr>
        <th>No</th>
        <th>Kode Respon</th>
        <th>Wk Respon</th>
        <th>PDF</th>
        <th>Keterangan Respon</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  function getTblLogsBc(id){
    $('#tblLogsBc').DataTable().destroy();

    $.ajax({
      url: "{{ route('logs.bc') }}",
      type: "GET",
      data:{
        house: id,
      },
      success: function(msg){
        $('#tblLogsBc').DataTable({
          data:msg.data,
          pageLength: parseInt("{{ config('app.page_length') }}"),
          columns:[
            {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
            {data:"BC_CODE", name: "BC_CODE"},
            {data:"BC_DATE", name: "BC_DATE"},
            {data:"PDF", name: "PDF"},
            {data:"BC_TEXT", name: "BC_TEXT"},
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

    });
  }
  jQuery(document).ready(function(){
    $(document).on('click', '.paksarespon', function(){
      var ids = [];
      ids[0] = $(this).attr('data-id');
      var ceisa = $(this).attr('data-ceisa');

      $(this).attr('disabled', true);

      loadingStart();

      $.ajax({
        url: "{{ route('manifest.shipments.store') }}",
        type: "POST",
        data:{
          ids:ids,
          ceisa: ceisa
        },
        success: function(msg){
          loadingStop();
          if(msg.status == 'OK')
          {
            showSuccess(msg.message);
            getTblLogsBc(ids[0]);

            if($('#tblHouses').length > 0)
            {
              getTblHouse();
            }
          } else {  
            showError(msg.message);
          }
          
          $('.paksarespon').attr('disabled', false);
        },
        error: function (jqXHR, exception) {
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          loadingStop();
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
          $('.paksarespon').attr('disabled', false);
        }
      })
    });
  });
</script>
