<div class="col-12">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">Billing Report</h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tblBillReport" class="table table-sm table-striped" style="width: 100%;">
          <thead>
            <tr>
              <th>Response Code</th>
              <th>Billing Code</th>
              <th>Billing Date</th>
              <th>Billing Exp. Date</th>
              <th>MAWB No</th>
              <th>Consignee</th>
              <th>CC Agency</th>
              <th>ID Number</th>
              <th>Carton No</th>
              <th>Currency</th>
              <th>Declare Value</th>
              <th>HS Code</th>
              <th>BM</th>
              <th>PPN</th>
              <th>PPH</th>
              <th>BMTP</th>
              <th>BMAD</th>
              <th>Denda</th>
              <th>CC Tax Amount</th>
              <th>Destination</th>
            </tr>
          </thead>
          <tbody>
            @php
              $agency = $item->agency();
            @endphp
            @forelse ($item->houses->whereIn('BC_CODE', ['401','408','403']) as $hbill)
              <tr>
                <td>#{{ ($hbill->BillFetchStatus == 1) ? '401' : '403' }}</td>
                <td class="text-nowrap">{{ $hbill->sppbmcp?->billing?->KODE_BILLING ?? '' }}</td>
                <td class="text-nowrap">
                  @if($hbill->sppbmcp?->billing?->TGL_BILLING)
                  {{ \Carbon\Carbon::parse($hbill->sppbmcp?->billing?->TGL_BILLING)->format('d-M-Y H:i:s')}}
                  @else
                  -
                  @endif
                </td>
                <td class="text-nowrap">
                  @if($hbill->sppbmcp?->billing?->TGL_JT_TEMPO)
                  {{ \Carbon\Carbon::parse($hbill->sppbmcp?->billing?->TGL_JT_TEMPO)->format('d-M-Y H:i:s')}}
                  @else
                  -
                  @endif
                </td>
                <td>{{ $hbill->mawb_parse }}</td>
                <td>{{ $hbill->NM_PENERIMA }}</td>
                <td>{{ $agency }}</td>
                <td>{{ $hbill->NO_BARANG }}</td>
                <td>{{ $hbill->Marking }}</td>
                <td>{{ $hbill->KD_VAL }}</td>
                <td>{{ $hbill->CIF }}</td>
                <td>{{ $hbill->details?->first()?->HS_CODE ?? "" }}</td>
                <td>{{ $hbill->HActualBM }}</td>
                <td>{{ $hbill->HActualPPN }}</td>
                <td>{{ $hbill->HActualPPH }}</td>
                <td>{{ $hbill->HActualBMTP }}</td>
                <td>{{ $hbill->HActualBMAD }}</td>
                <td>{{ $hbill->HActualDenda }}</td>
                <td>{{ $hbill->BillingFinal }}</td>
                <td>{{ $hbill->KD_PEL_AKHIR }}</td>
              </tr>
            @empty
              
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  jQuery(document).ready(function(){
    $('#tblBillReport').DataTable({
      pageLength: parseInt("{{ config('app.page_length') }}"),
      ordering: false,
      buttons: [                
          'excelHtml5',
          {
              extend: 'pdfHtml5',
              orientation: 'landscape',
              pageSize: 'LEGAL'
          },
          'print',
      ],
    }).buttons().container().appendTo('#tblBillReport_wrapper .col-md-6:eq(0)');
  });
</script>
