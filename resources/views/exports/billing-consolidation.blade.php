<table>
  <thead>
    <tr>
      <th>NO MASTER BLAWB</th>
      <th>NO BARANG</th>
      <th>NM PENERIMA</th>
      <th>Estimasi BM</th>
      <th>Estimasi PPN</th>
      <th>Estimasi PPH</th>
      <th>Actual BM</th>
      <th>Actual PPN</th>
      <th>Actual PPH</th>
      <th>BMTP</th>
      <th>BMAD</th>
      <th>DENDA</th>
      <th>TOTAL TAGIHAN</th>
      <th>KODE BILLING</th>
      <th>TGL BILLING</th>
      <th>TGL JT TEMPO</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($billing->sppbmcp as $b)
      @php
        $tglBilling = ($billing->TGL_BILLING)
                        ? \Carbon\Carbon::parse($billing->TGL_BILLING)->format('d/m/Y')
                        : "";
        $tglJtTempo = ($billing->TGL_JT_TEMPO)
                       ? \Carbon\Carbon::parse($billing->TGL_JT_TEMPO)->format('d/m/Y')
                       : "";
      @endphp   
      <tr>
        <td>{{ $b->house->mawb_parse }}</td>
        <td>{{ $b->NO_BARANG }}</td>
        <td>{{ $b->house->NM_PENERIMA }}</td>
        <td data-format="0,0.00">{{ $b->house->HEstimatedBM }}</td>
        <td data-format="0,0.00">{{ $b->house->HEstimatedPPN }}</td>
        <td data-format="0,0.00">{{ $b->house->HEstimatedPPH }}</td>
        <td data-format="0,0.00">{{ $b->house->HActualBM }}</td>
        <td data-format="0,0.00">{{ $b->house->HActualPPN }}</td>
        <td data-format="0,0.00">{{ $b->house->HActualPPH }}</td>
        <td data-format="0,0.00">{{ $b->house->HActualBMTP }}</td>
        <td data-format="0,0.00">{{ 0 }}</td>
        <td data-format="0,0.00">{{ $b->house->HActualDenda }}</td>
        <td data-format="0,0.00">{{ $b->TOTAL_TAGIHAN }}</td>
        <td data-format="0">{{ $billing->KODE_BILLING }}</td>
        <td>{{ $tglBilling }}</td>
        <td>{{ $tglJtTempo }}</td>
      </tr>
    @empty
      
    @endforelse
    
  </tbody>
</table>