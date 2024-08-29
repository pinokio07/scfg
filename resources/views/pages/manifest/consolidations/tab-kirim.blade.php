<div class="col-12">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">Kirim Data</h3>
    </div>
    <div class="card-body">
      @if($item->PUNumber && $item->PUDate)
      {{-- <div class="row">
        <div class="col-lg-4">
          <button id="kirimData" class="btn btn-sm btn-block btn-primary elevation-2 mb-4">
            Kirim Data
          </button>
        </div>
        <div class="col-lg-4">
          <button id="kirimCN" class="btn btn-sm btn-block btn-warning elevation-2 mb-4"
                  data-toggle="modal"
                  data-target="#modal-kirim">
            Kirim Per CN
          </button>
        </div>        
      </div> --}}
      <div class="row">
        <div class="col-lg-2">
          <button class="btn btn-sm btn-block btn-info elevation-2 mb-4"
                  onclick="location.reload();">
            <i class="fas fa-sync"></i> Refresh
          </button>
        </div>
      </div>
      @else
      <div class="row">
        <div class="col-lg-4">
          <p class="text-danger">Please Input BC Number before send!</p>
        </div>
      </div>
      @endif
      <div class="row">
        <div class="col-lg-12">
          <div class="table-responsive">
            <table id="tblKirim" class="table table-sm" style="width: 100%;">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Batch</th>
                  <th>Progress</th>
                  <th>Pesan</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $hCount = $item->houses->count();
                  $skbc = ["901","902","903","912","915","906","908","ERR"];
                  $skip = 0;
                  $no = 1;
                  if($hCount > 10) {
                    $take = 10;
                  } else {
                    $take = $hCount;
                  }
                @endphp

                @while ($skip < $hCount)
                  @php
                    $houses = $item->houses->skip($skip)->take($take);
                    $hCanSend = $houses->filter(function($h) use ($skbc){
                      return $h->BC_201 === NULL || in_array($h->BC_CODE, $skbc);
                    });
                    $ids = $houses->pluck('id');
                    $houseCount = $houses->count();
                    $canSend = $hCanSend->count();
                    $batch = $houses->whereNotNull('batch')->count();
                    $percentage = ($batch > 0) ? (100 / $houseCount) * ($batch - $canSend) : 0;
                    $pending = 0;
                    // $completedBatch = 0;
                  @endphp
                  <tr>
                    <td @class(['jmlRow' => $canSend > 0])
                        id="row_{{ $no }}"
                        data-row="{{ $no }}"
                        data-ids="{{ $ids }}">
                        {{ $no }}
                    </td>
                    <td> {{ $skip + 1 }} to {{ $take + $skip }}</td>
                    <td>
                      <div class="progress">
                        <div id="progress-{{$no}}" class="progress-bar" style="width: {{ $percentage }}%">{{ $percentage }} %</div>
                      </div>
                      {{ ($batch>0) ? ($batch - $canSend) : 0 }}/{{ $houseCount }}
                    </td>
                    <td id="pesan_{{ $no }}">
                      @forelse ($houses as $house)
                        @if($house->batch)
                          @php
                            $batch = $house->batch;
                          @endphp                        
                          {{-- @if($batch->Status == 'Completed')
                            @php
                              $completedBatch++;
                            @endphp --}}
                            {{-- <a href="{{ $house->batch->getFile() }}" target="_blank">{{ $house->batch->xml }}</a>@if(!$loop->last)<br>@endif --}}
                          {{-- @elseif($batch->Status == 'Pending')
                            @php
                              $pending++;
                            @endphp
                          @endif --}}
                          <a href="{{ route('manifest.shipments.edit', ['shipment' => \Crypt::encrypt($house->id)]) }}" target="_blank">{{ $house->NO_BARANG }}</a>: {{ $batch->Status }} @if($batch->Info) - {{ $batch->Info }} @endif 
                          @if(!$loop->last)<br>@endif
                        @endif
                      @empty
                        
                      @endforelse
                      {{-- @if($pending > 0) {{ $pending }} PENDING @endif --}}
                    </td>
                    <td>
                      @if($canSend > 0
                          && $item->PUNumber
                          && $item->PUDate)
                      <button class="btn btn-xs btn-primary elevation-2 sendbatch"
                              data-row="{{ $no }}">
                        <i class="fas fa-send"></i> Send This Batch
                      </button>
                      @endif
                    </td>
                  </tr>
                  @php
                    $skip += $take;
                    $no++;
                  @endphp
                @endwhile                
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  jQuery(document).ready(function(){
    $(document).on('submit', '#formKirimSatuan', function(e){
      e.preventDefault();
      var action = $(this).attr('action');
      var data = $(this).serialize();

      $('#btnFormKirimSatuan').prop('disabled', true);

      $.ajax({
        url : action,
        type: "POST",
        data: data,
        success: function(msg){
          if(msg.status == 'OK') {
            showSuccess(msg.message);
            location.reload();
          } else {
            showError(msg.message);
          }

          $('#btnFormKirimSatuan').prop('disabled', false);
        },
        error: function (jqXHR, exception) {
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

          $('#btnFormKirimSatuan').prop('disabled', false);
        }
      })
    });
    $(document).on('click', '.sendbatch', function(e){
      var row = $(this).attr('data-row');

      Swal.fire({			
        title: 'Send this batch CN?',			
        html:
          "This will send all CN in this batch?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Yes, send!'
      }).then((result) => {
        if (result.value) {
          var rows = [];

          rows.push(row);

          $('.sendbatch').prop('disabled', true);

          getAjax(rows, rows[0], row);
        }
      });
    });
  });
</script>