<!-- Main Houses -->
<div class="col-12">
  <div class="card card-success card-outline">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tblHouses" class="table table-sm table-striped" style="width: 100%;">
          <thead>
            <tr>
              @forelse ($headerHouse as $ky => $hs)
              <th 
                @if(in_array($ky, ['X_RAYDATE'])) class="text-nowrap" @endif>{{ $hs }}</th>
              @empty
                
              @endforelse
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- Edit Houses -->
<div class="col-12">
  <div id="collapseHouse" class="collapse">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">House <span id="detailHouse"></span></h3>
        <div class="card-tools">
          <button type="button" class="btn btn-xs btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown">
            <i class="fa fa-print"></i>
            Print DO
          </button>
          <div class="dropdown-menu">                
            <a class="dropdown-item" id="printWithHeader" target="_blank">With Header</a>
            <a class="dropdown-item" id="printNoHeader" target="_blank">Without Header</a>
          </div>
          <button id="hideHouse" type="button" class="btn btn-tool">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>    

      <div class="card-body">

        <form id="formHouse" 
              method="post" 
              autocomplete="off">

          @csrf
          @method('PUT')

          @include('pages.manifest.reference.house')

          <div class="row">
            <div class="col-12">
              @if($disabled != 'disabled')
                <button type="submit" 
                        class="btn btn-sm btn-primary btn-block elevation-2">
                  <i class="fas fa-save"></i> Save
                </button>
              @endif
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- HS Codes -->
<div class="col-12">
  <div id="collapseHSCodes" class="collapse">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">HS Codes <span id="detailCodes"></span></h3>
        <div class="card-tools">
          <button id="hideHSCodes" type="button" class="btn btn-tool">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>      
      <div class="card-body">
        @include('pages.manifest.reference.items')
      </div>
    </div>
  </div>
</div>
<!-- Responses -->
<div class="col-12">
  <div id="collapseResponse" class="collapse">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Responses <span id="detailResponse"></span></h3>
        <div class="card-tools">
          <button id="hideResponse" type="button" class="btn btn-tool">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>      
      <div class="card-body">
        @include('pages.manifest.reference.response')
      </div>
    </div>
  </div>
</div>
<!-- Calculate -->
<div class="col-12">
  <div id="collapseCalculate" class="collapse">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Calculate <span id="detailCalculate"></span></h3>
        <div class="card-tools">
          <button id="hideCalculate" type="button" class="btn btn-tool">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>      
      <div class="card-body">
        @include('pages.manifest.reference.calculate')
      </div>
    </div>
  </div>
</div>

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