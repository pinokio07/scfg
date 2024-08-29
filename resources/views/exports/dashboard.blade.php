<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Master BL/AWB</th>
      <th>House AWB</th>
      <th>Job File</th>
      <th>Nama Penerima</th>
      <th>Package</th>
      <th>Gross</th>
      <th>CW</th>
      <th>No SPPB</th>
      <th>No Plp</th>
      <th>Tanggal Tiba</th>
      <th>Exit Date</th>
      <th>Exit Time</th>
      <th>Gate In Ref</th>
      <th>Gate Out Ref</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($data as $d)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $d->mawb_parse }}</td>
        <td>{{ $d->NO_BARANG }}</td>
        <td>{{ $d->ShipmentNumber }}</td>
        <td>{{ $d->NM_PENERIMA }}</td>
        <td>{{ $d->JML_BRG }}</td>
        <td>{{ $d->BRUTO }}</td>
        <td>{{ $d->ChargeableWeight }}</td>
        <td>{{ $d->SPPBNumber }}</td>
        <td>{{ $d->master->PLPNumber }}</td>
        <td>
          @php
            if($d->master->ArrivalDate){
              $time = \Carbon\Carbon::parse($d->master->ArrivalDate);
              $display = $time->format('d/m/Y');
            } else {
              $display = "-";
            }
          @endphp
          {{ $display }}
        </td>
        <td>
          @php
            if($d->ExitDate){
              $exitDate = \Carbon\Carbon::parse($d->ExitDate);
              $exitDisplay = $exitDate->format('d/m/Y');
            } else {
              $exitDisplay = "-";
            }
          @endphp
          {{ $exitDisplay }}
        </td>
        <td>{{ $d->ExitTime }}</td>
        <td>{{ $d->TPS_GateInREF }}</td>
        <td>{{ $d->TPS_GateOutREF }}</td>
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>