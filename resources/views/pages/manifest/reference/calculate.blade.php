<div class="row">
  <div class="col-lg-3">
    <div class="form-group form-group-sm">
      <label for="cal_arrival">Arrivals</label>      
      {{-- <input type="date" 
             name="cal_arrival" 
             id="cal_arrival" 
             class="form-control form-control-sm"> --}}
      <div class="input-group input-group-sm date onlydate" 
           id="datetimepickercalarv" 
           data-target-input="nearest">      
        <input type="text" 
                id="cal_arrival"
                name="cal_arrival"
                class="form-control datetimepicker-input tanggal"
                placeholder="Estimated Exit Date"
                data-target="#datetimepickercalarv"
                {{ $disabled }}>
        <div class="input-group-append" 
              data-target="#datetimepickercalarv" 
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
      <label for="cal_out">Estimated Exit Date</label>
      <div class="input-group input-group-sm date onlydate" 
           id="datetimepicker8" 
           data-target-input="nearest">      
        <input type="text" 
                id="cal_out"
                name="cal_out"
                class="form-control datetimepicker-input tanggal"
                placeholder="Estimated Exit Date"
                data-target="#datetimepicker8"
                form="formCalculate"
                {{ $disabled }}>
        <div class="input-group-append" 
              data-target="#datetimepicker8" 
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
      <label for="cal_days">Estimated Days</label>
      <input type="text" 
             class="form-control form-control-sm" 
             id="cal_days"
             name="cal_days"
             form="formCalculate"
             readonly
             required             
             {{ $disabled }}>
    </div>
  </div> 
</div>
<div class="row">
  <div class="col-lg-3">
    <div class="form-group form-group-sm">
      <label for="cal_tariff">Tariff Schema</label>
      <select name="cal_tariff"
              id="cal_tariff"
              class="select2bs4clear"
              style="width: 100%"
              form="formCalculate"
              required
              {{ $disabled }}>
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
      <label for="cal_chargable">Chargable Weight</label>
      <input type="text" 
             name="cal_chargable" 
             id="cal_chargable" 
             class="form-control form-control-sm"
             readonly
             {{ $disabled }}>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="form-group form-group-sm">
      <label for="cal_gross">Gross Weight</label>
      <input type="text" 
             name="cal_gross" 
             id="cal_gross" 
             class="form-control form-control-sm"
             readonly
             {{ $disabled }}>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <form id="formCalculate" method="post">
      @csrf
      <input type="hidden" name="show_estimate" id="show_estimate" value="0">
      <input type="hidden" name="show_actual" id="show_actual" value="0">
    </form>
    <button type="button" 
            id="btnCalculate"
            form="formCalculate" 
            class="btn btn-block btn-warning btn-sm elevation-2">
      <i class="fas fa-calculator"></i> Calculate
    </button>
  </div>
  <div class="col-lg-6 mt-2">
    <button id="btnShowEstimated"
            class="btn btn-sm btn-info btn-block elevation-2 @if(!$item->tariff || !$item->estimatedTariff) d-none @endif">
      View Estimated
    </button>
  </div>
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
      <form id="formStoreCalculate" method="POST">
        @csrf
        <input type="hidden" name="is_estimate" id="is_estimate" value="1">
        <input type="hidden" name="cal_date" id="cal_date" value="">
        <input type="hidden" name="cal_tariff_id" id="cal_tariff_id" value="">
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
          <tbody id="tblIsiCalculate"></tbody>          
        </table>
      </form>
    </div>
  </div>
  @if($disabled != 'disabled')
  <div class="col-lg-6">
    <button type="button"
            data-estimate="1"
            class="btn btn-xm btn-primary btn-block elevation-2 d-none saveCalculation">
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
    <a id="btnEstimateH"
       target="_blank"
       @if($item->estimatedTariff)
        href="/manifest/download-calculated/{{ Crypt::encrypt($item->id) }}?header=1"
       @endif
       class="btn btn-xm bg-fuchsia btn-block elevation-2">
      With Header
    </a>
  </div>
  <div class="col-lg-3">
    <a id="btnEstimateWH"
       target="_blank"
       class="btn btn-xm bg-lime btn-block elevation-2">
      No Header
    </a>
  </div>
  @endif
</div>