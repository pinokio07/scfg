<div class="col-12">
  <div class="card card-info card-outline">
    <div class="card-header">
      <h3 class="card-title">Calculate</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-lg-3">
          <div class="form-group form-group-sm">
            <label for="cal_arrival_csl">Arrivals</label>
            <div class="input-group input-group-sm date onlydate" 
                 id="datetimepickercalarvcsl" 
                 data-target-input="nearest">      
              <input type="text" 
                      id="cal_arrival_csl"
                      name="cal_arrival"
                      class="form-control datetimepicker-input tanggal"
                      placeholder="Scan In Date"
                      data-target="#datetimepickercalarvcsl">
              <div class="input-group-append" 
                    data-target="#datetimepickercalarvcsl" 
                    data-toggle="datetimepicker">
                <div class="input-group-text">
                  <i class="fa fa-calendar"></i>
                </div>
              </div>      
            </div>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="form-group form-group-sm">
            <label for="cal_out_csl">Estimated Exit Date</label>
            <select name="cal_out" id="cal_out_csl"
                    class="custom-select custom-select-sm"
                    form="formCalculateCsl">
              <option value="" selected disabled>Select...</option>
              @if($item->houses)
                @forelse ($item->houses->where('ExitDate', '<>', '')->sortBy('ExitDate')->groupBy('ExitDate') as $key => $h )
                  <option value="{{ $h->first()->ExitDate->format('d-m-Y') }}">
                    {{ $h->first()->ExitDate->format('d-m-Y') }}
                  </option>
                @empty
                  
                @endforelse
              @endif
            </select>
          </div>
        </div> 
        <div class="col-lg-3">
          <div class="form-group form-group-sm">
            <label for="cal_days_csl">Estimated Days</label>
            <input type="text" 
                   class="form-control form-control-sm" 
                   id="cal_days_csl"
                   name="cal_days"
                   form="formCalculateCsl"
                   readonly
                   required>
          </div>
        </div> 
      </div>
      <div class="row">
        <div class="col-lg-3">
          <div class="form-group form-group-sm">
            <label for="cal_tariff_csl">Tariff Schema</label>
            <select name="cal_tariff"
                    id="cal_tariff_csl"
                    class="select2bs4clear"
                    style="width: 100%"
                    form="formCalculateCsl"
                    required>
              <option value=""></option>
              @forelse ($tariff as $t)
                <option value="{{ $t->id }}"
                  @selected($t->id == $item->tariff_id)>{{ $t->name }}</option>
              @empty                  
              @endforelse
            </select>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="form-group form-group-sm">
            <label for="cal_chargable_csl">Chargable Weight</label>
            <input type="text" 
                   name="cal_chargable" 
                   id="cal_chargable_csl" 
                   class="form-control form-control-sm"
                   form="formCalculateCsl"
                   readonly>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="form-group form-group-sm">
            <label for="cal_gross_csl">Gross Weight</label>
            <input type="text" 
                   name="cal_gross" 
                   id="cal_gross_csl" 
                   class="form-control form-control-sm"
                   form="formCalculateCsl"
                   readonly>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <form id="formCalculateCsl" method="post" 
                action="{{ route('calculate.master', ['consolidation' => \Crypt::encrypt($item->id)]) }}">
            @csrf
            <input type="hidden" name="show_estimate" id="show_estimate_csl" value="0">
            <input type="hidden" name="show_actual" id="show_actual_csl" value="0">
          </form>
          <button type="button" 
                  id="btnCalculateCsl"
                  form="formCalculateCsl" 
                  class="btn btn-block btn-warning btn-sm elevation-2">
            <i class="fas fa-calculator"></i> Calculate
          </button>
        </div>
        {{-- <div class="col-lg-6 mt-2">
          <button id="btnShowEstimatedCsl"
                  class="btn btn-sm btn-info btn-block elevation-2 @if(!$item->tariff || !$item->estimatedTariff) d-none @endif">
            View Estimated
          </button>
        </div> --}}
        {{-- <div class="col-lg-6 mt-2">
          <button id="btnShowActual"
                  class="btn btn-sm btn-success btn-block elevation-2">
            View Actual
          </button>
        </div> --}}
      </div>
      <div class="row  mt-5">
        <div class="col-12">
          <div class="table-responsive">
            <form id="formStoreCalculateCsl" method="POST"
                  action="{{ route('save.calculate.master', ['consolidation' => \Crypt::encrypt($item->id)]) }}">
              @csrf
              <input type="hidden" name="is_estimate" id="is_estimate_csl" value="1">
              <input type="hidden" name="cal_date" id="cal_date_csl" value="">
              <input type="hidden" name="cal_tariff_id" id="cal_tariff_id_csl" value="{{ $item->tariff_id }}">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Items</th>
                    <th>Days</th>
                    <th>Vol/Weight</th>
                    <th class="text-right">Rate</th>            
                    <th class="text-right">Total</th>
                  </tr>
                </thead>          
                <tbody id="tblIsiCalculateCsl"></tbody>          
              </table>
            </form>
          </div>
        </div>
        @if($disabled != 'disabled')
        <div class="col-lg-6">
          <button type="button"
                  data-estimate="1"
                  class="btn btn-xm btn-primary btn-block elevation-2 d-none saveCalculationCsl">
            Save as Estimated
          </button>
        </div>
        {{-- <div class="col-lg-6 mt-2 mt-md-0">
          <button type="button"
                  data-estimate="0"
                  class="btn btn-xm bg-lime btn-block elevation-2 saveCalculation">
            Save as Actual
          </button>
        </div> --}}
        <div class="col-lg-3">
          <a id="btnEstimateHCsl"
             target="_blank"
             @if($item->estimatedTariff)
              href="/manifest/download-sewagudang/{{ Crypt::encrypt($item->id) }}?header=1"
             @endif
             class="btn btn-xm bg-fuchsia btn-block elevation-2">
            With Header
          </a>
        </div>
        <div class="col-lg-3">
          <a id="btnEstimateWHCsl"
             target="_blank"
             class="btn btn-xm bg-lime btn-block elevation-2">
            No Header
          </a>
        </div>
        @endif
      </div>                          
    </div>
  </div>
</div>