<div class="col-12">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">Partial</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-lg-4">
          <button id="addPartial" class="btn btn-sm btn-block btn-primary elevation-2 mb-4"
                  data-toggle="modal"
                  data-target="#modal-partial">
            Add Partial
          </button>
        </div>
        @if($item->partials->isNotEmpty())
          @can('allocate_partial')
            <div class="col-lg-4">
              <button id="addAlocate" class="btn btn-sm btn-block btn-danger elevation-2 mb-4">
                Alokasi
              </button>
            </div>
          @endcan
        @endif
        <div class="col-lg-12">
          <div class="table-responsive">
            <table id="tblPartial" class="table table-sm" style="width: 100%;">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Partial ID</th>
                  <th>Nama Angkut</th>
                  <th>No Flight</th>
                  <th>Tgl Tiba</th>
                  <th>Jam Tiba</th>
                  <th>No BC11</th>
                  <th>Tgl BC11</th>
                  <th>No Pos BC11</th>
                  <th>BRUTO</th>
                  <th>Allocated</th>
                  <th>Allocated C/N</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $allocated = 0;
                @endphp
                @forelse ($item->partials->sortByDesc('TGL_TIBA')->sortByDesc('JAM_TIBA') as $partial)
                  @php
                    $alokasi = $partial->houses->sum('BRUTO');
                    $allocated += $alokasi;
                  @endphp
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $partial->PartialID }}</td>
                    <td>{{ $partial->NM_ANGKUT }}</td>
                    <td>{{ $partial->NO_FLIGHT }}</td>
                    <td>{{ ($partial->TGL_TIBA && $partial->TGL_TIBA->year > 1) ? $partial->TGL_TIBA->format('d-M-Y') : "" }}</td>
                    <td>{{ $partial->JAM_TIBA }}</td>
                    <td>{{ $partial->NO_BC11 }}</td>
                    <td>{{ ($partial->TGL_BC11 && $partial->TGL_BC11->year > 1) ? $partial->TGL_BC11->format('d-M-Y') : ""}}</td>
                    <td>{{ $partial->NO_POS_BC11 }}</td>
                    <td>{{ number_format($partial->TOTAL_BRUTO, 4, ',','.') }}</td>
                    <td>{{ number_format($alokasi, 4, ',','.') }}</td>
                    <td>{{ $partial->houses_count }}</td>
                    <td>
                      <button class="btn btn-warning btn-xs elevation-2 editpartial"
                              data-toggle="modal"
                              data-target="#modal-partial"
                              data-id="{{ $partial->PartialID }}"
                              data-angkut="{{ $partial->NM_ANGKUT }}"
                              data-flight="{{ $partial->NO_FLIGHT }}"
                              data-tgltiba="{{ $partial->TGL_TIBA }}"
                              data-jamtiba="{{ $partial->JAM_TIBA }}"
                              data-nobc="{{ $partial->NO_BC11 }}"
                              data-tglbc="{{ $partial->TGL_BC11 }}"
                              data-nopos="{{ $partial->NO_POS_BC11 }}"
                              data-bruto="{{ $partial->TOTAL_BRUTO }}">
                        <i class="fas fa-edit"></i>
                      </button>
                    </td>
                  </tr>
                @empty
                  
                @endforelse
              </tbody>
              <tfoot>
                <tr>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th>{{ $item->partials->sum('TOTAL_BRUTO') }}</th>
                  <th>{{ $allocated }}</th>
                  <th>{{ $item->partials->sum('houses_count') }}</th>
                  <th>Action</th>
                </tr>
                <tr>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th>Unallocated</th>
                  <th>{{ $item->houses->whereNull('PartialID')->count() }}</th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-partial">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><span id="info-partial"></span> Kedatangan Partial</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formPartial"
              action="{{ route('manifest.consolidations.update', ['consolidation' => \Crypt::encrypt($item->id)]) }}"
              class="form-horizontal" 
              method="post">
          @csrf
          @method('PUT')
          <input type="hidden" name="jenis" id="jenis" value="partial">
          <input type="hidden" name="partial_id" id="partial_id">
          <!-- NM_ANGKUT -->
          <div class="form-group row">
            <label for="NM_ANGKUT_PARTIAL" 
                   class="col-lg-2 col-form-label">
              Nama Angkut</label>
            <div class="col-lg-10">
              <input type="text" 
                    class="form-control form-control-sm clearable"
                    id="NM_ANGKUT_PARTIAL"
                    name="NM_ANGKUT"
                    placeholder="Nama Angkut"
                    required>
            </div>
          </div>
          <!-- Flight -->
          <div class="form-group row">
            <label for="NO_FLIGHT_PARTIAL" 
                   class="col-lg-2 col-form-label">
              No Flight</label>
            <div class="col-lg-3">
              <input type="text" 
                    class="form-control form-control-sm clearable"
                    id="NO_FLIGHT_PARTIAL"
                    name="NO_FLIGHT"
                    placeholder="No Flight">
            </div>
            <div class="col-lg-7">
              <div class="row">
                <label for="TGL_TIBA_PARTIAL" 
                        class="col-lg-2 col-form-label">
                        Arrivals</label>
                <div class="col-lg-6 pl-sm-0 pl-lg-3">
                  <div class="input-group input-group-sm date datetimemin" 
                        id="dtp_tiba_partial" 
                        data-target-input="nearest">
                    <input type="text" 
                            id="tgltibapartial"
                            name="tgltibapartial"
                            class="form-control datetimepicker-input clearable"
                            placeholder="Waktu Tiba"
                            data-target="#dtp_tiba_partial"
                            required
                            >
                    <div class="input-group-append" 
                          data-target="#dtp_tiba_partial" 
                          data-toggle="datetimepicker">
                      <div class="input-group-text">
                        <i class="fa fa-calendar"></i>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" 
                        class="form-control form-control-sm clearable"
                        id="TGL_TIBA_PARTIAL"
                        name="TGL_TIBA">
                  <input type="hidden" 
                        class="form-control form-control-sm clearable"
                        id="JAM_TIBA_PARTIAL"
                        name="JAM_TIBA"
                        placeholder="Jam Tiba">
                </div>
              </div>              
            </div>
          </div>
          <!-- BC 1.1 -->
          <div class="form-group row">
            <label for="NO_BC11" 
                   class="col-lg-2 col-form-label">
              No BC11</label>
            <div class="col-lg-3">
              <input type="text" 
                    class="form-control form-control-sm clearable"
                    id="NO_BC11_PARTIAL"
                    name="NO_BC11"
                    placeholder="No BC 1.1">
            </div>
            <div class="col-lg-4">
              <div class="row">
                <label for="TGL_BC11_PARTIAL" 
                        class="col-lg-4 col-form-label">
                        Tgl BC11</label>
                <div class="col-lg-8">
                  <div class="input-group input-group-sm date onlydate" 
                        id="dtp_tgl_bc11" 
                        data-target-input="nearest">
                    <input type="text" 
                            id="tglbc11partial"
                            name="tglbc11partial"
                            class="form-control datetimepicker-input clearable tanggal"
                            placeholder="Tgl Tiba"
                            data-target="#dtp_tgl_bc11"
                            data-ganti="TGL_BC11_PARTIAL"
                            required
                            >
                    <div class="input-group-append" 
                          data-target="#dtp_tgl_bc11" 
                          data-toggle="datetimepicker">
                      <div class="input-group-text">
                        <i class="fa fa-calendar"></i>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" 
                        class="form-control form-control-sm clearable"
                        id="TGL_BC11_PARTIAL"
                        name="TGL_BC11"
                        placeholder="Tgl BC 1.1">
                </div>
              </div>              
            </div>
            <div class="col-lg-3">
              <div class="row">
                <label for="NO_POS_BC11" 
                        class="col-lg-6 col-form-label">
                        No Pos BC11</label>
                <div class="col-lg-6">
                  <input type="text" 
                        class="form-control form-control-sm clearable"
                        id="NO_POS_BC11_PARTIAL"
                        name="NO_POS_BC11"
                        placeholder="No Pos BC11"
                        required>
                </div>
              </div>              
            </div>
          </div>
          <!-- TOTAL_BRUTO -->
          <div class="form-group row">
            <label for="TOTAL_BRUTO" 
                   class="col-lg-2 col-form-label">
              Berat Tiba</label>
            <div class="col-lg-10">
              <input type="text" 
                    class="form-control form-control-sm berat clearable"
                    id="TOTAL_BRUTO"
                    name="TOTAL_BRUTO"
                    placeholder="Berat Tiba"
                    required>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
        <button type="submit" form="formPartial" 
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
  jQuery(document).ready(function(){
    $(document).on('click', '#addPartial', function(){
      $('.clearable').val('').trigger('change');

      $('#info-partial').text('').text('Tambah');
      $('#formPartial #partial_id').val('');
    });
    $(document).on('click', '#addAlocate', function(){
      Swal.fire({			
          title: 'Allocate Partial?',			
          html:
            "This will allocate houses to partial",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, allocate!'
        }).then((result) => {
          if (result.value) {
            $('.btn').prop('disabled', true);
            loadingStart();
            $.ajax({
              url: "{{ route('manifest.consolidations.update', ['consolidation' => \Crypt::encrypt($item->id)]) }}",
              type: "POST",
              data:{
                _method: "PUT",
                jenis: "alokasi"
              },
              success: function(msg){
                if(msg.status == 'OK'){
                  showSuccess(msg.message);

                  setTimeout(() => {
                    location.reload();
                  }, 3000);            

                } else {
                  showError(msg.message);
                  $('.btn').prop('disabled', false);
                }
                loadingStop();
              },
              error:function(jqXHR){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                showError(jqXHR.status + ' || ' + jsonValue.message);
                $('.btn').prop('disabled', false);
                loadingStop();
              }
            });
          }
        });
    });
    $(document).on('click', '.editpartial', function(){
      var id = $(this).attr('data-id');
      var angkut = $(this).attr('data-angkut');
      var flight = $(this).attr('data-flight');
      var tgltiba = $(this).attr('data-tgltiba');
      var jamtiba = $(this).attr('data-jamtiba');
      var nobc = $(this).attr('data-nobc');
      var tglbc = $(this).attr('data-tglbc');
      var nopos = $(this).attr('data-nopos');
      var bruto = $(this).attr('data-bruto');

      $('#formPartial #partial_id').val(id);
      $('#formPartial #NM_ANGKUT_PARTIAL').val(angkut);
      $('#formPartial #NO_FLIGHT_PARTIAL').val(flight);
      $('#formPartial #NO_BC11_PARTIAL').val(nobc);
      $('#formPartial #NO_POS_BC11_PARTIAL').val(nopos);
      $('#formPartial #TOTAL_BRUTO').val(bruto);
      if(tglbc)
      {
        $('#formPartial #tglbc11partial').val(moment(tglbc).format('DD-MM-YYYY')).trigger('change');
      }
      if(tgltiba)
      {
        $('#formPartial #tgltibapartial').val(moment(tgltiba).format('DD-MM-YYYY')+' '+jamtiba).trigger('change');
      }
    })
  });
</script>