<div class="col-12">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">PLP Online</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <?php $latestPlp = $item->latestPlp->first(); ?>
        @if($disabled != 'disabled')
          <div class="col-6" id="btn-request">
            @if((!$latestPlp 
                  || ($latestPlp->pengajuan == true
                      && $latestPlp->STATUS != 'Pending')
                  || ($latestPlp->pembatalan == true
                      && $latestPlp->STATUS != 'Pending'))
                && $item->PUNumber != '')
              @can('request_aju_plp')
                <button id="sendRequestPlp"
                        data-jenis="plp-request"
                        data-judul="Request PLP"
                        class="btn btn-sm btn-success btn-block elevation-2 plp">
                  <i class="fas fa-paper-plane"></i> Request PLP
                </button>
              @endcan
            @endif
          </div>        
          <div class="col-6" id="btn-response">
            @if($latestPlp
                && $latestPlp->pengajuan == true
                && $latestPlp->STATUS == 'Pending')
              @can('request_aju_plp')
                <button id="sendResponsePlp"
                        data-jenis="plp-response"
                        data-judul="Response PLP"
                        class="btn btn-sm btn-info btn-block elevation-2 plp">
                  <i class="fas fa-sync-alt"></i> Get Response
                </button>
              @endcan
            @endif
          </div>        
          <div class="col-6 mt-2" id="btn-batal">
            @if($latestPlp
                && $latestPlp->pengajuan == true
                && $latestPlp->FL_SETUJU == 'Y')
              @can('request_batal_plp')
                <button id="sendRequestBatalPlp"
                        data-jenis="plp-batal"
                        data-judul="Request Batal PLP"
                        class="btn btn-sm btn-danger btn-block elevation-2 plp">
                  <i class="fas fa-paper-plane"></i> Request Batal PLP
                </button>
              @endcan
            @endif
          </div>        
          <div class="col-6 mt-2" id="btn-batal-response">
            @if($latestPlp
                && $latestPlp->pembatalan == true
                && $latestPlp->STATUS == 'Pending')
              @can('request_batal_plp')
                <button id="sendResponseBatalPlp"
                        data-jenis="plp-resbatal"
                        data-judul="Response Batal"
                        class="btn btn-sm btn-warning btn-block elevation-2 plp">
                  <i class="fas fa-paper-plane"></i> Get Response Batal
                </button>
              @endcan
            @endif
          </div>
        @endif
      </div>
      <div class="row mt-4">
        <div class="col-12">
          <div class="table-responsive">
            <table id="tblPlp" class="table table-sm" style="width:100%;">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Jenis</th>
                  <th>Reference Number</th>
                  <th>No Surat</th>
                  <th>No PLP</th>
                  <th>Status</th>
                  <th>Info</th>
                  <th>Actions</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>  