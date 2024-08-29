@extends('layouts.master')
@section('title') Exchange Rate @endsection
@section('page_name') Exchange Rate @endsection
@section('header')
  <style>
    label{
      margin-bottom: 0px !important;
    }
  </style>
@endsection

@section('content')
<!-- Main contents -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">@if($exchange_rate->id != '') Edit @else New @endif Exchange Rate</h3>
          </div>
          @if($exchange_rate->id != '')
          <form action="/setup/exchange-rate/{{$exchange_rate->id}}" method="post" autocomplete="off">
            @method('PUT')
          @else
          <form action="/setup/exchange-rate" method="post" autocomplete="off">
          @endif
            @csrf
            <div class="card-body">
              <div class="row">
                <div class="col-2 col-md-2">
                  <div class="form-group form-group-sm">
                    <label for="RE_ExRateType">Ex Rate Type</label>
                    <select name="RE_ExRateType" id="RE_ExRateType" 
                            class="custom-select custom-select-sm"
                            style="width: 100%;" 
                            required 
                            {{$disabled}}>
                        <option value="BUY" @if (old('RE_ExRateType') == "BUY" || $exchange_rate->RE_ExRateType == "BUY") selected @endif>BUY</option>
                        <option value="SELL" @if (old('RE_ExRateType') == "SELL" || $exchange_rate->RE_ExRateType == "SELL") selected @endif>SELL</option>
                        <option value="CUS" @if (old('RE_ExRateType') == "CUS" || $exchange_rate->RE_ExRateType == "CUS") selected @endif>CUS</option>
                        <option value="TAX" @if (old('RE_ExRateType') == "TAX" || $exchange_rate->RE_ExRateType == "TAX") selected @endif>TAX</option>
                    </select>
                  </div>
                </div>
                <div class="col-5 col-md-4">
                  <div class="form-group form-group-sm">
                    <label for="RE_RX_NKExCurrency">Currency</label>                    
                    <select name="RE_RX_NKExCurrency" id="RE_RX_NKExCurrency" 
                            class="select2"
                            style="width: 100%;"
                            required
                            {{ $disabled }}>
                      @if($exchange_rate->id != '')
                      <option value="{{ $exchange_rate->RE_RX_NKExCurrency }}">
                        {{ $exchange_rate->RE_RX_NKExCurrency }}
                      </option>
                      @else
                      <option selected disabled value="">Choose..</option>
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-5 col-md-4">
                  <div class="form-group form-group-sm">
                    <label for="RE_SellRate">Exchange Rate</label>
                    <input type="text" name="RE_SellRate" id="RE_SellRate"
                           class="form-control form-control-sm"
                           placeholder="Exchange Rate"
                           value="{{ old('RE_SellRate') ?? $exchange_rate->RE_SellRate ?? '' }}"
                           {{ $disabled }}>
                  </div>
                </div>
              </div>
              <div id="taxRef" class="row @if($exchange_rate->RE_ExRateType != 'TAX') d-none @endif">
                <div class="col-12 col-md-6">
                  <div class="form-group form-group-sm">
                    <label for="RE_Reference">Reference</label>
                    <input type="text" name="RE_Reference" id="RE_Reference"
                           class="form-control form-control-sm"
                           placeholder="Reference"
                           value="{{ old('RE_Reference') ?? $exchange_rate->RE_Reference ?? '' }}"
                           {{ $disabled }}>
                  </div>
                </div>
                <div class="col-6 col-md-2">
                  <div class="form-group form-group-sm">
                    <label for="RE_ReferenceDate">Reference Date</label>
                    <input type="date" name="RE_ReferenceDate" id="RE_ReferenceDate"
                           class="form-control form-control-sm"
                           value="{{ old('RE_ReferenceDate') ?? optional($exchange_rate->RE_ReferenceDate)->format('Y-m-d') ?? today()->format('Y-m-d') }}"
                           {{ $disabled }}>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-6 col-md-2">
                  <div class="form-group form-group-sm">
                    <label for="RE_StartDate">Start Date</label>
                    <input type="date" name="RE_StartDate" id="RE_StartDate"
                           class="form-control form-control-sm"
                           placeholder="Start Date"
                           value="{{ old('RE_StartDate') ?? $exchange_rate->RE_StartDate ?? '' }}"
                           {{ $disabled }}>
                  </div>
                </div>
                <div class="col-6 col-md-2">
                  <div class="form-group form-group-sm">
                    <label for="RE_ExpiryDate">Expiry Date</label>
                    <input type="date" name="RE_ExpiryDate" id="RE_ExpiryDate"
                           class="form-control form-control-sm"
                           placeholder="Expiry Date"
                           value="{{ old('RE_ExpiryDate') ?? $exchange_rate->RE_ExpiryDate ?? '' }}"
                           {{ $disabled }}>
                  </div>
                </div>
              </div>              
            </div>
            <div class="card-footer">
              @if($disabled != 'disabled')
              <button type="submit" class="btn btn-sm btn-success elevation-2">
                <i class="fas fa-save"></i> Save</button>
              @else
              <a href="{{ url()->current() }}/edit" class="btn btn-sm btn-warning elevation-2">
                <i class="fas fa-edit"></i> Edit</a>
              @endif
              <a href="/setup/exchange-rate" class="btn btn-sm btn-default elevation-2 ml-2">Cancel</a>
            </div>
          </form>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection

@section('footer')
  <script>
    jQuery(document).ready(function(){
      $('#RE_RX_NKExCurrency').select2({
        placeholder: 'Select an item',
        ajax: {
          url: "{{ route('select2.setup.currency') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                  return {
                      text: item.RX_Code,
                      id: item.RX_Code
                  }
              })
            };
          },
          cache: true
        }        
      });
      $(document).on('change', '#RE_ExRateType', function(){
        var val = $(this).val();
        if(val == 'TAX'){
          $('#taxRef').removeClass('d-none');
          $('#RE_Reference').prop('required', 'required');
          $('#RE_ReferenceDate').prop('required', 'required');
        } else{
          $('#taxRef').addClass('d-none');
          $('#RE_Reference').prop('required', false);
          $('#RE_ReferenceDate').prop('required', false);
        }
      })

      $("#RE_SellRate").inputmask({
        alias: "currency",
        digits: 2,
        groupSeparator: ',',
        rightAlign: 1,
        reverse: true,
        removeMaskOnSubmit: true
      });
    });
  </script>
@endsection
