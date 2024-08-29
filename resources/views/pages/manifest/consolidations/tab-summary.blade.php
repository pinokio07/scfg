<div class="col-12">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">
        Response Summary
        <button id="btnRefresh" class="btn btn-sm btn-success elevation-2">
          <i class="fas fa-sync"></i> Refresh
        </button>
      </h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tblResSummary" class="table table-sm table-striped" style="width: 100%;">
          <thead>
            <tr>
              <th>KD RESPON</th>
              <th>CN</th>
              <th>Keterangan</th>
            </tr>
          </thead>          
        </table>        
      </div>
      <div class="row">
        <div class="col-lg-1">
          <p>&nbsp;</p>
          <table id="tblResStat" class="table table-sm table-striped">
            <tbody>
              <tr>
                <td>Un Sent</td>
                <td id="un-sent"></td>
              </tr>
              <tr>
                <td>Sent</td>
                <td id="sent-cn"></td>
              </tr>
              <tr>
                <td><b>Total CN</b></td>
                <td id="total-cn">{{ $item->houses->count() }}</td>
              </tr>
              <tr class="infocn"
                  data-toggle="modal"
                  data-target="#modal-cn"
                  data-info="SKIP CN"
                  data-d="skcn"
                  style="cursor:pointer;">
                <td>Skip CN</td>
                <td class="text-primary">{{ $item->houses->where('SKIP', 'Y')->count() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-lg-2">
          <p><b>Type Data of Submission</b></p>
          <table id="tblSubType" class="table table-sm table-striped" style="width: 50%;">
            <tbody>
              <tr class="infocn"
                  data-toggle="modal"
                  data-target="#modal-cn"
                  data-info="CN"
                  data-d="iscn"
                  style="cursor:pointer;">
                <td>CN</td>
                <td class="text-primary">{{ $item->houses->where('JNS_AJU', 1)->count() }}</td>
              </tr>
              <tr class="infocn"
                  data-toggle="modal"
                  data-target="#modal-cn"
                  data-info="PIB"
                  data-d="ispib"
                  style="cursor:pointer;">
                <td>PIB</td>
                <td class="text-primary">{{ $item->houses->whereNotIn('JNS_AJU', [1,2])->count() }}</td>
              </tr>
              <tr class="infocn"
                  data-toggle="modal"
                  data-target="#modal-cn"
                  data-info="PIBK"
                  data-d="ispibk"
                  style="cursor:pointer;">
                <td>PIBK</td>
                <td class="text-primary">{{ $item->houses->where('JNS_AJU', 2)->count() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="col-12">
  <div class="card card-success card-outline">
    <div class="card-header">
      <h3 class="card-title">
        Response Logs
        <button id="btnRefreshLog" class="btn btn-sm btn-success elevation-2">
          <i class="fas fa-sync"></i> Refresh
        </button>
      </h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tblResLogs" class="table table-sm table-striped" style="width: 100%;">
          <thead>
            <tr>
              <th>No</th>
              <th>Kode Respon</th>
              <th>CN</th>
              <th>Keterangan Respon</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-cn">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Data Houses <span id="info-cn"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table id="tblCnDetail" class="table table-sm" style="width: 100%;">
          <thead>
            <tr>
              <th>No</th>
              <th>NO BARANG</th>
            </tr>
          </thead>
          <tbody id="tbody-tblCnDetail"></tbody>
        </table>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script>
  var skcn = '';
  var iscn = '';
  var ispib = '';
  var ispibk = '';

  @forelse ($item->houses->where('SKIP', 'Y') as $sk)
    skcn += '<tr><td>{{ $loop->iteration }}</td><td><a href="{{ route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($sk->id)]) }}" target="_blank">{{ $sk->NO_BARANG }}</a></td></tr>';
  @empty
    
  @endforelse

  
  @forelse ($item->houses->where('JNS_AJU', 1) as $icn)
    iscn += '<tr><td>{{ $loop->iteration }}</td><td><a href="{{ route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($icn->id)]) }}" target="_blank">{{ $icn->NO_BARANG }}</a></td></tr>';
  @empty
    
  @endforelse

  @forelse ($item->houses->whereNotIn('JNS_AJU', [1, 2]) as $spib)
    ispib += '<tr><td>{{ $loop->iteration }}</td><td><a href="{{ route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($spib->id)]) }}" target="_blank">{{ $spib->NO_BARANG }}</a></td></tr>';
  @empty
    
  @endforelse

  @forelse ($item->houses->where('JNS_AJU', 2) as $spibk)
    ispibk += '<tr><td>{{ $loop->iteration }}</td><td><a href="{{ route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($spibk->id)]) }}" target="_blank">{{ $spibk->NO_BARANG }}</a></td></tr>';
  @empty
    
  @endforelse

  function getTblResSummary() {
    if ( $.fn.dataTable.isDataTable( '#tblResSummary' ) ) {        
      $('#tblResSummary').DataTable().clear().destroy();
    }
    $.ajax({
      url: "{{ route('logs.bc') }}",
      type: "GET",
      data:{
        master: "{{ $item->id }}",
      },
      success: function(msg){
        if ( $.fn.dataTable.isDataTable( '#tblResSummary' ) ) {        
          $('#tblResSummary').DataTable().clear().destroy();
        }
        $('#tblResSummary').DataTable({
          data:msg.table.original.data,
          pageLength: parseInt("{{ config('app.page_length') }}"),
          columns:[            
            {data:"BC_CODE", name: "BC_CODE"},
            {data:"CN_COUNT", name: "CN_COUNT"},
            {data:"BC_STATUS", name: "BC_STATUS"},
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
        }).buttons().container().appendTo('#tblResSummary_wrapper .col-md-6:eq(0)');
        var jmlCN = "{{ $item->houses->count() }}";
        var jmlSent = msg.count;
        var sisa = jmlCN - jmlSent;

        $('#sent-cn').html(jmlSent);
        $('#un-sent').html(sisa);
        
        $('#btnRefresh').attr('disabled', false);
      }

    });
  }
  function getTblResLogs() {
    if ( $.fn.dataTable.isDataTable( '#tblResLogs' ) ) {        
      $('#tblResLogs').DataTable().clear().destroy();
    }
    $.ajax({
      url: "{{ route('logs.bc') }}",
      type: "GET",
      data:{
        mawb: "{{ $item->id }}",
        group: 1,
      },
      success: function(msg){
        if ( $.fn.dataTable.isDataTable( '#tblResLogs' ) ) {        
          $('#tblResLogs').DataTable().clear().destroy();
        }
        $('#tblResLogs').DataTable({
          data:msg.data,
          pageLength: parseInt("{{ config('app.page_length') }}"),
          columns:[            
            {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
            {data:"BC_CODE", name: "BC_CODE"},
            {data:"CN_COUNT", name: "CN_COUNT"},
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
        }).buttons().container().appendTo('#tblResLogs_wrapper .col-md-6:eq(0)');
        
        $('#btnRefreshLog').attr('disabled', false);
      }

    });
  }  
  function getTblResLogModal(code, mawb) {
    $('#tblResponModal').DataTable().destroy();

    $.ajax({
      url: "{{ route('logs.bc') }}",
      type: "GET",
      data:{
        group: 1,
        detail: code,
        mawb: mawb,
      },
      success: function(msg){
        $('#tblResponModal').DataTable({
          data:msg.data,
          pageLength: parseInt("{{ config('app.page_length') }}"),
          columns:[
            {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
            {data:"NO_BARANG", name: "NO_BARANG"},
            {data:"NM_PENERIMA", name: "NM_PENERIMA"},
            {data:"ChargeableWeight", name: "ChargeableWeight"},
            {data:"BRUTO", name: "BRUTO"},
            {data:"BC_CODE", name: "BC_CODE"},
            {data:"BC_DATE", name: "BC_DATE"},
            {data:"PDF", name: "PDF"},
            {data:"BC_TEXT", name: "BC_TEXT"},
            {data:"actions", searchable: false,sortable:false},
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
        }).buttons().container().appendTo('#tblResponModal_wrapper .col-md-6:eq(0)');
      }

    })
  }
  function getTblResModal(code, mawb) {
    $('#tblResponModal').DataTable().destroy();

    $.ajax({
      url: "{{ route('logs.bc') }}",
      type: "GET",
      data:{
        code: code,
        mawb: mawb
      },
      success: function(msg){
        $('#tblResponModal').DataTable({
          data:msg.data,
          pageLength: parseInt("{{ config('app.page_length') }}"),
          columns:[
            {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
            {data:"NO_BARANG", name: "NO_BARANG"},
            {data:"NM_PENERIMA", name: "NM_PENERIMA"},
            {data:"ChargeableWeight", name: "ChargeableWeight", 
              render: $.fn.dataTable.render.number( '.', ',', 2 ),
            },
            {data:"BRUTO", name: "BRUTO", 
              render: $.fn.dataTable.render.number( '.', ',', 2 ),
            },
            {data:"BC_CODE", name: "BC_CODE"},
            {data:"BC_DATE", name: "BC_DATE"},
            {data:"PDF", name: "PDF"},
            {data:"BC_STATUS", name: "BC_STATUS"},
            {data:"actions", searchable: false,sortable:false},
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
        }).buttons().container().appendTo('#tblResponModal_wrapper .col-md-6:eq(0)');
      }

    })
  }
  function getTblHouseInfoCn(d)
  {
    var dtinfo = '';

    switch (d) {
      case 'skcn':
        dtinfo = skcn;
        break;
      case 'iscn':
        dtinfo = iscn;
        break;
      case 'ispib':
        dtinfo = ispib;
        break;
      case 'ispibk':
        dtinfo = ispibk;
        break;
    }

    console.log(dtinfo);

    if($.fn.DataTable.isDataTable('#tblCnDetail'))
    {
      $('#tblCnDetail').DataTable().clear().destroy();
    }    
    $('#tbody-tblCnDetail').html(dtinfo);
    $('#tblCnDetail').DataTable({
      pageLength: parseInt("{{ config('app.page_length') }}"),
    });
    
  }
  jQuery(document).ready(function(){
    $(document).on('click', '.cncount', function(){
      var code = $(this).attr('data-code');
      var mawb = $(this).attr('data-mawb');

      getTblResModal(code, mawb);
    });
    $(document).on('click', '.cndetail', function(){
      var code = $(this).attr('data-code');
      var mawb = $(this).attr('data-mawb');

      getTblResLogModal(code, mawb);
    });
    $(document).on('click', '.infocn', function(){
      var info = $(this).attr('data-info');
      var d =$(this).attr('data-d');

      $('#modal-cn #info-cn').html(info);

      getTblHouseInfoCn(d);
    });
    $(document).on('click', '#btnRefresh', function(){
      $(this).attr('disabled', true);
      getTblResSummary();
    });
    $(document).on('click', '#btnRefreshLog', function(){
      $(this).attr('disabled', true);
      getTblResLogs();
    });
    $(document).on('click', '.fcdownload', function(){
      var ids = [];
      var th = $(this);
      var mawb = th.attr('data-mawb');
      var code = th.attr('data-code');
      ids[0] = th.attr('data-id');

      th.attr('disabled', true);

      loadingStart();

      $.ajax({
        url: "{{ route('manifest.shipments.store') }}",
        type: "POST",
        data:{
          ids:ids
        },
        success: function(msg){
          loadingStop();
          if(msg.status == 'OK')
          {
            showSuccess(msg.message);
            getTblResSummary();
            getTblResLogs();
            getTblResModal(code, mawb)
          } else {  
            showError(msg.message);
          }
          
          th.attr('disabled', false);
        },
        error: function (jqXHR, exception) {
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          loadingStop();
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
          th.attr('disabled', false);
        }
      })
    });
  });
</script>