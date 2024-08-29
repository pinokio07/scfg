@extends('layouts.master')
@section('title') Consolidations @endsection
@section('page_name') Consolidations @endsection

@section('header')
  <style>
    ul, #myUL {
      list-style-type: none;
    }

    #myUL {
      margin: 0;
      padding: 0;
    }

    .caret {
      cursor: pointer;
      -webkit-user-select: none; /* Safari 3.1+ */
      -moz-user-select: none; /* Firefox 2+ */
      -ms-user-select: none; /* IE 10+ */
      user-select: none;
    }

    .caret::before {
      content: "\25B6";
      color: black;
      display: inline-block;
      margin-right: 6px;
    }

    .caret-down::before {
      -ms-transform: rotate(90deg); /* IE 9 */
      -webkit-transform: rotate(90deg); /* Safari */'
      transform: rotate(90deg);  
    }

    .nested {
      display: none;
    }

    .active {
      display: block;
    }
    .keterangan{
      min-width: 400px !important;
    }    
    .reason{
      /* width: 20px !important; */
      max-width: 200px !important;
    }
  </style>
@endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    @if (count($errors) > 0)
      <div class="row">
        <div class="col-12">
          <div class="alert alert-danger">
              <ul>
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
        </div>
      </div>
    @endif
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Consolidations @if($item->id) || {{ $item->mawb_parse ?? "" }} ({{ $item->branch->CB_Code ?? "-" }}) @endif</h3>
            @if($item->PUNumber && $item->POSNumber && $item->PUDate)
            <button id="kirimData" class="btn btn-xs btn-primary elevation-2 ml-5">
              <i class="fas fa-paper-plane"></i>
              Kirim Data
            </button>
            <button id="kirimCN" class="btn btn-xs btn-warning elevation-2"
                    data-toggle="modal"
                    data-target="#modal-kirim">
              <i class="fas fa-paper-plane"></i>
              Kirim Per CN
            </button>
            @can('send_ceisa40')
            <button id="kirimCeisa" class="btn btn-xs btn-primary elevation-2 ml-5">
              <i class="fas fa-paper-plane"></i>
              Kirim Data CEISA 4.0
            </button>
            <button id="responCeisa" class="btn btn-xs btn-primary elevation-2 ml-5">
              <i class="fas fa-sync"></i>
              Tarik Respon CEISA 4.0
            </button>
            @endcan
            @endif
            <div class="card-tools">
              @can('tarik_respon')
              <button id="tarik-respon" class="btn btn-xs btn-primary elevation-2">
                <i class="fas fa-sync-alt"></i> Tarik Response
              </button>
              @endcan
              @can('update_bc11')
              <button id="updateNoBC"
                      class="btn btn-xs btn-danger elevation-2"
                      data-href="{{ route('update.bc11') }}?m={{ $item->id }}">
                <i class="fas fa-send"></i>
                Update No BC
              </button>
              @endcan
              @can('export_manifest')
                @if($item->houses->whereNull('PartialID')->count() == 0)
                  <button class="btn btn-xs btn-success elevation-2"
                          data-toggle="modal"
                          data-target="#modal-manifest">
                    <i class="fas fa-file-excel"></i> Generate Manifest
                  </button>
                @endif
              <div class="btn-group">
                <button type="button"
                        class="btn btn-xs btn-info elevation-2 dropdown-toggle dropdown-icon"
                        data-toggle="dropdown">
                  <i class="fas fa-download"></i> Print Label
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <button class="dropdown-item printlabel"
                          data-mt="legacy"> Legacy Label
                  </button>
                  <button class="dropdown-item printlabel"
                          data-mt="barcode"> Barcode Label
                  </button>
                  <button class="dropdown-item printlabel"
                          data-mt="barcode307"> Barcode Label 307
                  </button>
                  <button class="dropdown-item printlabel"
                          data-mt="list"> Lists Label
                  </button>
                </div>                
              </div>
              @endcan
            </div>
          </div>
          <form id="formPrintLabel" action="{{ route('manifest.consolidations.update', ['consolidation' => \Crypt::encrypt($item->id)]) }}" method="post" target="_blank">
            @csrf
            @method('PUT')
            <input type="hidden" name="jenis" value="label">
            <input type="hidden" name="mt" id="mt" value="legacy">
          </form>
          {{-- <input type="hidden" id="JR_GE" name="JR_GE" value="96"/> --}}
            <div class="card-body">
              <!-- Tab Lists -->
              <ul class="nav nav-tabs" id="custom-content-above-tab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link" id="main-data" data-toggle="pill" href="#main-data-content" role="tab" aria-controls="main-data-content" aria-selected="true">Main Data</a>
                </li>
                @can('open_tab_houses')
                <li class="nav-item">
                  <a class="nav-link" id="tab-houses" data-toggle="pill" href="#tab-houses-content" role="tab" aria-controls="tab-houses-content" aria-selected="false">Houses</a>
                </li>
                @endcan
                @can('open_tab_plp')
                <li class="nav-item">
                  <a class="nav-link" id="tab-plp" data-toggle="pill" href="#tab-plp-content" role="tab" aria-controls="tab-plp-content" aria-selected="false">PLP</a>
                </li>
                @endcan
                @can('open_tab_billreport')
                <li class="nav-item">
                  <a class="nav-link" id="tab-billreport" data-toggle="pill" href="#tab-billreport-content" role="tab" aria-controls="tab-billreport-content" aria-selected="false">Billing Report</a>
                </li>
                @endcan
                @can('open_tab_summary')
                <li class="nav-item">
                  <a class="nav-link" id="tab-summary" data-toggle="pill" href="#tab-summary-content" role="tab" aria-controls="tab-summary-content" aria-selected="false">Summary</a>
                </li>
                @endcan
                @can('open_tab_kirim')
                <li class="nav-item">
                  <a class="nav-link" id="tab-kirim" data-toggle="pill" href="#tab-kirim-content" role="tab" aria-controls="tab-kirim-content" aria-selected="false">Kirim Data</a>
                </li>
                @endcan
                @can('open_tab_calculate')
                <li class="nav-item">
                  <a class="nav-link" id="tab-calc" data-toggle="pill" href="#tab-calc-content" role="tab" aria-controls="tab-calc-content" aria-selected="false">Sewa Gudang</a>
                </li>
                @endcan
                @can('open_tab_partial')
                <li class="nav-item">
                  <a class="nav-link" id="tab-part" data-toggle="pill" href="#tab-part-content" role="tab" aria-controls="tab-part-content" aria-selected="false">Partial</a>
                </li>
                @endcan
                @can('open_tab_estimasi')
                <li class="nav-item">
                  <a class="nav-link" id="tab-estimasi" data-toggle="pill" href="#tab-estimasi-content" role="tab" aria-controls="tab-estimasi-content" aria-selected="false">Estimasi Billing</a>
                </li>
                @endcan                
                @can('open_tab_log')
                <li class="nav-item">
                  <a class="nav-link" id="tab-log" data-toggle="pill" href="#tab-log-content" role="tab" aria-controls="tab-log-content" aria-selected="false">Logs</a>
                </li>
                @endcan
              </ul>
              <!-- Tab Contents -->
              <div class="tab-content" id="custom-content-above-tabContent">
                <div class="tab-pane fade show active" id="main-data-content" role="tabpanel" aria-labelledby="main-data">
                  
                  <div class="row mt-2">
                    <!-- Organization Details Form -->
                    <div class="col-12">
                      <div class="card card-primary card-outline">
                        {{-- <div class="card-header">
                          <h3 class="card-title">Details</h3>
                        </div> --}}
                        <form id="formDetails"
                              @if($item->id)
                              action="{{ route('manifest.consolidations.update', ['consolidation' => \Crypt::encrypt($item->id)]) }}"
                              @else
                              action="{{ route('manifest.consolidations.store') }}" 
                              @endif
                              method="POST"
                              class="form-horizontal needs-validation"
                              autocomplete="off"
                              novalidate>

                          @csrf

                          @if($item->id)
                            @method('PUT')
                          @endif

                          <div class="card-body">
                            <div class="form-group row">
                              <!-- KPBC -->
                              <label for="KPBC" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     KPBC @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-3">
                                <select name="KPBC" 
                                        id="KPBC" 
                                        style="width: 100%;"
                                        class="select2kpbc"
                                        required
                                        {{ $disabled }}>
                                  @if($item->KPBC)
                                  <option value="{{ $item->KPBC }}"
                                          selected>
                                    {{ $item->KPBC }} - {{ $item->customs?->UrKdkpbc }}
                                  </option>
                                  @endif
                                </select>                                
                              </div>
                              <!-- KPBC -->
                              <label for="mBRANCH" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Company @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-3">
                                <select name="mBRANCH" 
                                        id="mBRANCH" 
                                        style="width: 100%;"
                                        class="select2bs4"
                                        required
                                        {{ $disabled }}>
                                 @forelse (auth()->user()->branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        @selected($item->mBRANCH == $branch->id)
                                        data-npwp="{{ $branch->company->GC_TaxID }}">
                                      {{ $branch->company->GC_Name }} | {{ $branch->CB_Code }}
                                    </option>
                                 @empty
                                   
                                 @endforelse
                                </select>
                              </div>
                              <!-- NPWP -->
                              <label for="NPWP" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     NPWP @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-3">
                                <input type="text" 
                                       name="NPWP" 
                                       id="NPWP" 
                                       class="form-control form-control-sm"
                                       placeholder="NPWP"
                                       readonly
                                       {{ $disabled }}>
                              </div>
                            </div>
                            <div class="form-group row">
                              <!-- AirlineCode -->
                              <label for="AirlineCode" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Airline @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-3">
                                <select name="AirlineCode" 
                                        id="AirlineCode" 
                                        style="width: 100%;"
                                        class="select2airline"
                                        required
                                        {{ $disabled }}>
                                  @if($item->AirlineCode)
                                  <option value="{{ $item->AirlineCode }}" 
                                          data-name="{{ $item->NM_SARANA_ANGKUT }}"
                                          data-code="{{ substr($item->MAWBNumber, 0, 3) }}"
                                          selected>
                                    {{ $item->AirlineCode }} - {{ $item->NM_SARANA_ANGKUT }}
                                  </option>
                                  @endif
                                </select>
                                <!-- NM_SARANA_ANGKUT -->
                                <input type="hidden" 
                                       name="NM_SARANA_ANGKUT"
                                       id="NM_SARANA_ANGKUT"
                                       value="{{ old('NM_SARANA_ANGKUT') 
                                                 ?? $item->NM_SARANA_ANGKUT
                                                 ?? '' }}"
                                       {{ $disabled }}>
                              </div>
                              <!-- FlightNo -->
                              <label class="col-sm-3 col-lg-1 col-form-label" for="FlightNo">
                                Flight No @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-3">
                                <input type="text" 
                                       name="FlightNo" 
                                       id="FlightNo"
                                       class="form-control form-control-sm"
                                       placeholder="Flight No"
                                       value="{{ old('FlightNo')
                                                  ?? $item->FlightNo
                                                  ?? '' }}"
                                       required
                                       {{ $disabled }}>
                              </div>

                              <!-- Arrivals -->
                              <label class="col-sm-3 col-lg-1 col-form-label" for="arrivals">
                                Arrivals @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-3">
                                <div class="input-group input-group-sm date datetimemin" 
                                    id="datetimepicker1" 
                                    data-target-input="nearest">
                                  <input type="text" 
                                        id="arrivals"
                                        name="arrivals"
                                        class="form-control datetimepicker-input" 
                                        placeholder="Arrival Date"
                                        data-target="#datetimepicker1"
                                        required
                                        value="{{ old('arrivals')
                                                  ?? $item->arrivals
                                                  ?? '' }}"
                                        {{ $disabled }}>
                                  <div class="input-group-append" 
                                      data-target="#datetimepicker1" 
                                      data-toggle="datetimepicker">
                                    <div class="input-group-text">
                                      <i class="fa fa-calendar"></i>
                                    </div>
                                  </div>
                                </div>
                                <!-- ArrivalDate -->
                                <input type="hidden" 
                                      name="ArrivalDate" 
                                      id="ArrivalDate"
                                      value="{{ old('ArrivalDate')
                                                ?? $item->ArrivalDate
                                                ?? '' }}"
                                      {{ $disabled }}>
                                <!-- ArrivalTime -->
                                <input type="hidden" 
                                      name="ArrivalTime" 
                                      id="ArrivalTime"
                                      value="{{ old('ArrivalTime')
                                                ?? $item->ArrivalTime
                                                ?? '' }}"
                                      {{ $disabled }}>
                              </div>
                              
                            </div>
                            <div class="form-group row">
                              <!-- Origin -->
                              <label for="Origin" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Origin @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-3">
                                <select name="Origin" 
                                        id="Origin" 
                                        style="width: 100%;"
                                        class="select2unloco"
                                        required
                                        {{ $disabled }}>
                                  @if($item->Origin)
                                  <option value="{{ $item->Origin }}"
                                          selected>
                                    {{ ($item->unlocoOrigin?->RL_Code ?? "")
                                        . " - " 
                                        . ($item->unlocoOrigin?->RL_PortName ?? "")
                                        . " ( "
                                        . ($item->unlocoOrigin?->RL_RN_NKCountryCode ?? "")
                                        . " )" }}
                                  </option>
                                  @endif
                                </select>                                
                              </div>
                              <!-- Transit -->
                              <label for="Transit" 
                                     class="col-sm-3 col-lg-1 col-form-label">Transit</label>
                              <div class="col-12 col-lg-3">
                                <select name="Transit" 
                                        id="Transit" 
                                        style="width: 100%;"
                                        class="select2unloco"
                                        {{ $disabled }}>
                                  @if($item->Transit)
                                  <option value="{{ $item->Transit }}"
                                          selected>
                                    {{ ($item->unlocoTransit?->RL_Code ?? "")
                                        . " - " 
                                        . ($item->unlocoTransit?->RL_PortName ?? "")
                                        . " ( "
                                        . ($item->unlocoTransit?->RL_RN_NKCountryCode ?? "")
                                        . " )" }}
                                  </option>
                                  @endif
                                </select>                                
                              </div>
                              <!-- Destination -->
                              <label for="Destination" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Destination @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-3">
                                <select name="Destination" 
                                        id="Destination" 
                                        style="width: 100%;"
                                        class="select2unloco"
                                        required
                                        {{ $disabled }}>
                                  @if($item->Destination)
                                  <option value="{{ $item->Destination }}"
                                          selected>
                                    {{ ($item->unlocoDestination?->RL_Code ?? "")
                                        . " - " 
                                        . ($item->unlocoDestination?->RL_PortName ?? "")
                                        . " ( "
                                        . ($item->unlocoDestination?->RL_RN_NKCountryCode ?? "")
                                        . " )" }}
                                  </option>
                                  @endif
                                </select>                                
                              </div>
                            </div>
                            <div class="form-group row">
                              <!-- ConsolNumber -->
                              <label for="ConsolNumber" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Consolidation Number</label>
                              <div class="col-12 col-lg-3">
                                <input type="text" 
                                       name="ConsolNumber" 
                                       id="ConsolNumber" 
                                       class="form-control form-control-sm"
                                       placeholder="Shipment Number"
                                       value="{{ old('ConsolNumber')
                                                 ?? $item->ConsolNumber
                                                 ?? ''}}"
                                       {{ $disabled }}>
                              </div>
                              <!-- MAWBNumber -->
                              <label for="MAWBNumber" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     MAWB No @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-2">
                                <input type="text" 
                                       name="MAWBNumber" 
                                       id="MAWBNumber" 
                                       class="form-control form-control-sm mawb-mask"
                                       placeholder="MAWB Number"
                                       required
                                       value="{{ old('MAWBNumber')
                                                 ?? $item->MAWBNumber
                                                 ?? ''}}"
                                       {{ $disabled }}>
                              </div>
                              <!-- MAWBDate -->
                              <label for="MAWBDate" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     MAWB Date @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-2">
                                <div class="input-group input-group-sm date onlydate" 
                                     id="datetimepicker2" 
                                     data-target-input="nearest">
                                  <input type="text" 
                                         id="tglmawb"
                                         name="tglmawb"
                                         class="form-control datetimepicker-input tanggal"
                                         placeholder="MAWB Date"
                                         data-target="#datetimepicker2"
                                         data-ganti="MAWBDate"
                                         required
                                         value="{{ old('tglmawb')
                                                   ?? $item->date_mawb
                                                   ?? '' }}"
                                         {{ $disabled }}>
                                  <div class="input-group-append" 
                                       data-target="#datetimepicker2" 
                                       data-toggle="datetimepicker">
                                    <div class="input-group-text">
                                      <i class="fa fa-calendar"></i>
                                    </div>
                                  </div>
                                </div>
                                <input type="hidden" 
                                       name="MAWBDate" 
                                       id="MAWBDate" 
                                       class="form-control form-control-sm"
                                       placeholder="MAWB Date"
                                       value="{{ old('MAWBDate')
                                                 ?? $item->MAWBDate
                                                 ?? ''}}"
                                       {{ $disabled }}>
                              </div>
                              <!-- HAWBCount -->
                              <label for="HAWBCount" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     HAWB Count @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-1">
                                <input type="text" 
                                       name="HAWBCount" 
                                       id="HAWBCount" 
                                       class="form-control form-control-sm numeric"
                                       placeholder="HAWB Count"
                                       required
                                       value="{{ old('HAWBCount')
                                                 ?? $item->HAWBCount
                                                 ?? ''}}"
                                       {{ $disabled }}>
                              </div>
                            </div>
                            <div class="form-group row">
                              <!-- mNoOfPackages -->
                              <label for="mNoOfPackages" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Total Collie</label>
                              <div class="col-12 col-lg-2">
                                <input type="text" 
                                       name="mNoOfPackages" 
                                       id="mNoOfPackages" 
                                       class="form-control form-control-sm numeric"
                                       placeholder="Total Collie"
                                       value="{{ old('mNoOfPackages')
                                                 ?? $item->mNoOfPackages
                                                 ?? 0}}"
                                       {{ $disabled }}>
                              </div>
                              <!-- mGrossWeight -->
                              <label for="mGrossWeight" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     GW</label>
                              <div class="col-12 col-lg-2">
                                <input type="text" 
                                       name="mGrossWeight" 
                                       id="mGrossWeight" 
                                       class="form-control form-control-sm desimal"
                                       placeholder="Gross Weight"
                                       value="{{ old('mGrossWeight')
                                                 ?? $item->mGrossWeight
                                                 ?? 0}}"
                                       {{ $disabled }}>
                              </div>
                              <!-- mChargeableWeight -->
                              <label for="mChargeableWeight" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     CW</label>
                              <div class="col-12 col-lg-2">
                                <input type="text" 
                                       name="mChargeableWeight" 
                                       id="mChargeableWeight" 
                                       class="form-control form-control-sm desimal"
                                       placeholder="Chargable Weight"
                                       value="{{ old('mChargeableWeight')
                                                 ?? $item->mChargeableWeight
                                                 ?? 0}}"
                                       {{ $disabled }}>
                              </div>
                              <!-- Partial -->
                              <label for="Partial" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Partial</label>
                              <div class="col-12 col-lg-2">
                                <select name="Partial" 
                                        id="Partial" 
                                        class="custom-select custom-select-sm"
                                        {{ $disabled }}>
                                  <option value="0" 
                                    @selected($item->Partial == false)>No</option>
                                  <option value="1" 
                                    @selected($item->Partial == true)>Yes</option>
                                </select>
                              </div>
                            </div>
                            <div class="form-group row">
                              <!-- BC 1.1 -->
                              <label for="PUNumber" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     BC 1.1</label>
                              <div class="col-12 col-lg-3">
                                <input type="text" 
                                       name="PUNumber" 
                                       id="PUNumber" 
                                       class="form-control form-control-sm"
                                       placeholder="BC 1.1 Number"
                                       maxlength="6"
                                       value="{{ old('PUNumber')
                                                 ?? $item->PUNumber
                                                 ?? ''}}"
                                       {{ $disabled }}>
                              </div>
                              <!-- POS BC 1.1 -->
                              <label for="POSNumber" 
                                      class="col-sm-3 col-lg-1 col-form-label">
                                      POS BC 1.1</label>
                              <div class="col-12 col-lg-3">
                                <input type="text" 
                                      name="POSNumber" 
                                      id="POSNumber" 
                                      class="form-control form-control-sm"
                                      placeholder="POS BC 1.1"
                                      maxlength="4"
                                      value="{{ old('POSNumber')
                                                ?? $item->POSNumber
                                                ?? ''}}"
                                      {{ $disabled }}>
                              </div>
                              <!-- BC 1.1 Date -->
                              <label for="tglbc" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     BC 1.1 Date</label>
                              <div class="col-12 col-lg-2">
                                <div class="input-group input-group-sm date onlydate" 
                                     id="datetimepicker3" 
                                     data-target-input="nearest">
                                  <input type="text" 
                                         id="tglbc"
                                         name="tglbc"
                                         class="form-control datetimepicker-input tanggal"
                                         placeholder="BC 1.1 Date"
                                         data-target="#datetimepicker3"
                                         data-ganti="PUDate"
                                         value="{{ old('tglbc')
                                                   ?? $item->date_pu
                                                   ?? '' }}"
                                         {{ $disabled }}>
                                  <div class="input-group-append" 
                                       data-target="#datetimepicker3" 
                                       data-toggle="datetimepicker">
                                    <div class="input-group-text">
                                      <i class="fa fa-calendar"></i>
                                    </div>
                                  </div>
                                </div>
                                <input type="hidden" 
                                       name="PUDate" 
                                       id="PUDate" 
                                       class="form-control form-control-sm"
                                       value="{{ old('PUDate')
                                                 ?? $item->PUDate
                                                 ?? ''}}"
                                       {{ $disabled }}>
                              </div>
                            </div>
                            <div class="form-group row">
                              <!-- OriginWarehouse -->
                              <label for="OriginWarehouse" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Line 1 Warehouse </label>
                              <div class="col-12 col-lg-3">
                                <select name="OriginWarehouse" 
                                        id="OriginWarehouse" 
                                        style="width: 100%;"
                                        {{ $disabled }}>
                                  @if($item->OriginWarehouse)
                                    <option value="{{ $item->OriginWarehouse }}" selected>
                                    {{ $item->OriginWarehouse }} - {{ $item->warehouseLine1->company_name }}
                                    </option>
                                  @endif
                                </select>
                              </div>
                              <!-- Tanggal Masuk Gudang -->
                              <label for="OriginWarehouse" 
                                     class="col-sm-3 col-lg-1 col-form-label">
                                     Tgl Masuk Gudang </label>
                              <div class="col-12 col-lg-3">
                                <div class="input-group input-group-sm date withtime" 
                                     id="datetimegudang" 
                                     data-target-input="nearest">
                                  <input type="text" 
                                         id="tglmg"
                                         name="tglmg"
                                         class="form-control datetimepicker-input tgltime"
                                         placeholder="Masuk Gudang"
                                         data-target="#datetimegudang"
                                         data-ganti="MasukGudang"
                                         value="{{ old('tglmg')
                                                   ?? $item->date_mg
                                                   ?? '' }}"
                                         {{ $disabled }}>
                                  <div class="input-group-append" 
                                       data-target="#datetimegudang" 
                                       data-toggle="datetimepicker">
                                    <div class="input-group-text">
                                      <i class="fa fa-calendar"></i>
                                    </div>
                                  </div>
                                </div>
                                <input type="hidden" 
                                       name="MasukGudang" 
                                       id="MasukGudang" 
                                       class="form-control form-control-sm"
                                       value="{{ old('MasukGudang')
                                                 ?? $item->MasukGudang
                                                 ?? ''}}"
                                       {{ $disabled }}>
                                {{-- <input type="text" 
                                       name="MasukGudang" 
                                       id="MasukGudang" 
                                       class="form-control form-control-sm"
                                       readonly
                                       value="{{ $item->MasukGudang }}"> --}}
                              </div>
                              <!-- No Segel PLP BC -->
                              <label for="NO_SEGEL" 
                                     class="col-sm-3 col-lg-2 col-form-label">
                                     No Segel PLP BC @include('buttons.mandatory')</label>
                              <div class="col-12 col-lg-2">
                                <input type="text" 
                                       name="NO_SEGEL" 
                                       id="NO_SEGEL" 
                                       class="form-control form-control-sm"
                                       placeholder="No Segel PLP Bea Cukai"
                                       value="{{ old('NO_SEGEL')
                                                 ?? $item->NO_SEGEL
                                                 ?? '' }}"
                                       {{ $disabled }}>
                              </div>
                            </div>
                          </div>
                          <!-- /.card-body -->
                        </form>
                        <div class="card-footer">
                          @if($disabled != 'disabled')
                          <button type="submit" 
                                  class="btn btn-sm btn-success elevation-2"
                                  form="formDetails">
                            <i class="fas fa-save"></i>
                            Save
                          </button>
                          @endif
                          <a href="{{ route('manifest.consolidations') }}" 
                             class="btn btn-sm btn-default elevation-2 ml-2">Cancel</a>
                          @if($item->id
                              && $disabled != 'disabled')
                          <a href="{{ route('manifest.consolidations.create') }}" class="btn btn-sm btn-info elevation-2 ml-2">
                            <i class="fas fa-plus"></i> New
                          </a>
                          @endif
                        </div>
                        <!-- /.card-footer -->
                      </div>
                    </div>                   
                  </div>

                </div>
                <div class="tab-pane fade" id="tab-houses-content" role="tabpanel" aria-labelledby="tab-houses">
                  <div class="row mt-2">
                   @include('pages.manifest.consolidations.tab-house')
                  </div>
                </div>

                <div class="tab-pane fade" id="tab-plp-content" role="tabpanel" aria-labelledby="tab-plp">
                  <div class="row mt-2">
                   @include('pages.manifest.consolidations.tab-plp')
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-billreport-content" role="tabpanel" aria-labelledby="tab-billreport">
                  <div class="row mt-2">
                    @include('pages.manifest.consolidations.tab-billreport')
                  </div>
                </div>

                <div class="tab-pane fade" id="tab-summary-content" role="tabpanel" aria-labelledby="tab-summary">
                  <div class="row mt-2">
                    @include('pages.manifest.consolidations.tab-summary') 
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-kirim-content" role="tabpanel" aria-labelledby="tab-kirim">
                  <div class="row mt-2">
                    @include('pages.manifest.consolidations.tab-kirim')
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-calc-content" role="tabpanel" aria-labelledby="tab-calc">
                  <div class="row mt-2">
                    @include('pages.manifest.consolidations.tab-calculate')
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-part-content" role="tabpanel" aria-labelledby="tab-part">
                  <div class="row mt-2">
                    @include('pages.manifest.consolidations.tab-partial')
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-estimasi-content" role="tabpanel" aria-labelledby="tab-estimasi">
                  <div class="row mt-2">
                    <div class="col-12">
                      <div class="card card-primary card-outline">
                        <div class="card-header">
                          <h3 class="card-title">Estimasi Billing</h3>
                        </div>
                        <div class="card-body">
                          <div class="row">
                            <div class="col-lg-12">
                              <button id="hitungBilling" class="btn btn-sm btn-info btn-block elevation-2">
                                Hitung Estimasi Billing
                              </button>
                            </div>
                            <div class="col-lg-4 mt-4">
                              <p class="mb-0 pb-1 border">PPN 
                                <span id="jml-ppn" class="float-right text-right">
                                  {{ number_format(($item->EstimatedPPN ?? 0), 0, ',','.') }}
                                </span>
                              </p>
                              <p class="mb-0 pb-1 border">Bea Masuk 
                                <span id="jml-bm" class="float-right text-right">
                                  {{ number_format(($item->EstimatedBM ?? 0), 0, ',','.') }}
                                </span>
                              </p>
                              <p class="mb-0 pb-1 border">BMTP 
                                <span id="jml-bmtp" class="float-right text-right">
                                  {{ number_format(($item->EstimatedBMTP ?? 0), 0, ',','.') }}
                                </span>
                              </p>
                              <p class="mb-0 pb-1 border">PPH 
                                <span id="jml-pph" class="float-right text-right">
                                  {{ number_format(($item->EstimatedPPH ?? 0), 0, ',','.') }}
                                </span>
                              </p>
                              <p class="mb-0 pb-1 border">Total Billing
                                @php
                                  $totalBilling = $item->EstimatedPPN + $item->EstimatedBM + $item->EstimatedBMTP + $item->EstimatedPPH;
                                @endphp
                                <span id="jml-total" style="cursor: pointer;" title="Click to Copy" class="float-right text-right text-primary copy"
                                      data-val="{{ $totalBilling }}">
                                  {{ number_format(($totalBilling), 0, ',','.') }}
                                </span>
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>  
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-log-content" role="tabpanel" aria-labelledby="tab-log">
                  <div class="row mt-2">
                    <div class="col-3 col-sm-1">
                      <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="vert-tabs-home-tab" data-toggle="pill" href="#vert-tabs-home" role="tab" aria-controls="vert-tabs-home" aria-selected="true">Shipment</a>
                        <a class="nav-link" id="vert-tabs-plp-tab" data-toggle="pill" href="#vert-tabs-plp" role="tab" aria-controls="vert-tabs-plp" aria-selected="false">PLP</a>
                        <a class="nav-link" id="vert-tabs-sch-tab" data-toggle="pill" href="#vert-tabs-sch" role="tab" aria-controls="vert-tabs-sch" aria-selected="false">Scheduler</a>
                      </div>
                    </div>
                    <div class="col-9 col-sm-11">
                      <div class="tab-content" id="vert-tabs-tabContent">
                        <div class="tab-pane text-left fade show active" id="vert-tabs-home" role="tabpanel" aria-labelledby="vert-tabs-home-tab">
                          @include('pages.manifest.reference.logs')
                        </div>
                        <div class="tab-pane fade" id="vert-tabs-plp" role="tabpanel" aria-labelledby="vert-tabs-plp-tab">
                          <div class="col-12">
                            <div class="table-responsive">
                              <table id="tblPlpLog" class="table table-sm" style="width:100%;">
                                <thead>
                                  <tr>
                                    @forelse ($headerPlp as $hplp)
                                      <th>
                                        @if($hplp == 'id')
                                        No
                                        @else
                                        {{ $hplp }}
                                        @endif
                                      </th>
                                    @empty                    
                                    @endforelse
                                  </tr>
                                </thead>
                              </table>
                            </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="vert-tabs-sch" role="tabpanel" aria-labelledby="vert-tabs-sch-tab">
                          <div class="col-12">
                            <div class="table-responsive">
                              <table id="tblSchLog" class="table table-sm" style="width:100%;">
                                <thead>
                                  <tr>
                                    <th>No</th>
                                    <th>Time</th>
                                    <th>Process</th>
                                    <th>Request</th>
                                    <th>Response</th>
                                    <th>Info</th>
                                  </tr>
                                </thead>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<div class="modal fade" id="modal-manifest">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Generate Manifest Online</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formGenerateManifest"
              action="{{ route('download.manifest.consolidations') }}"
              method="get"
              target="_blank">
          <input type="hidden" name="id" value="{{ \Crypt::encrypt($item->id) }}">
          <input type="hidden" name="manifest" value="1">
          @if($item->partials->isNotEmpty())
            <div class="form-group form-group-sm">
              <label for="PartialID">Partial</label>
              <select name="PartialID"
                      id="PartialID-manifest"
                      class="custom-select custom-select-sm">
                {{-- <option value="">All..</option> --}}
                @forelse ($item->partials as $part)
                  @if($loop->first)
                  <option value="{{ $part->PartialID }}">All...</option>
                  @else
                  <option value="{{ $part->PartialID }}">
                    {{ $part->NO_FLIGHT }} - Tgl {{ $part->TGL_TIBA->format('d-m-Y') }} jam {{ $part->JAM_TIBA }}
                  </option>
                  @endif
                @empty
                  
                @endforelse
              </select>
            </div>
          @endif
          <div class="form-group form-group-sm">
            <label for="AWBFormat">AWB Format</label>
            <select name="AWBFormat"
                    id="AWBFormat"
                    class="custom-select custom-select-sm">
              <option value="{{ $item->mawb_parse }}">{{ $item->mawb_parse }}</option>
              <option value="{{ $item->MAWBNumber }}">{{ $item->MAWBNumber }}</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
        <button type="submit" form="formGenerateManifest"
                class="btn btn-lg btn-primary">
          <i class="fas fa-file-export"></i> Download
        </button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="modal-respon">
  <div class="modal-dialog modal-xls">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Respon <span id="info-respon"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table id="tblResponModal" class="table table-sm table-striped" style="width: 100%;">
            <thead>
              <tr>
                <th>No</th>
                <th>No Barang</th>
                <th>Nama Penerima</th>
                <th>CW</th>
                <th>GW</th>
                <th>Kode Respon</th>
                <th>Wk Respon</th>
                <th>PDF</th>
                <th>Keterangan Respon</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="modal-kirim">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Kirim CN</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formKirimSatuan"
              action="{{ route('barkir.post-data') }}"
              class="form-horizontal" 
              method="post">
          @csrf
          <input type="hidden" name="ms" value="{{ $item->id }}">
          <input type="hidden" name="bps" value="1">
          <!-- PILIH_CN -->
          <div id="hs_kirim_div" class="form-group row">
            <label for="hs_kirim" 
                   class="col-lg-2 col-form-label">
              Select House(s)</label>
            <div class="col-lg-10">
              <select name="hs[]" id="hs_kirim"
                      style="width: 100%;"
                      required
                      multiple>
                @php
                  $skbc = ["901","902","903","912","915","906","908","ERR"];
                  $hfk = $item->houses->filter(function($h) use ($skbc){
                    return $h->BC_201 === NULL || in_array($h->BC_CODE, $skbc);
                  });
                @endphp     
                @forelse ($hfk as $hsk)
                  <option value="{{ $hsk->id }}">{{ $hsk->NO_BARANG }}</option>
                @empty                  
                @endforelse
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label for="hs_text_kirim" 
                    class="col-lg-2 col-form-label">
              Paste/Type House(s)</label>
            <div class="col-lg-10">
              <textarea name="hst"
                        id="hs_text_kirim"
                        class="form-control"
                        rows="10"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
        <button type="submit" id="btnFormKirimSatuan" form="formKirimSatuan" 
                class="btn btn-lg btn-primary">
          <i class="fas fa-save"></i> Send
        </button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="modal-edit-plp">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit PLP</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formEditPlp"
              class="form-horizontal" 
              action="{{ route('plp.edit') }}"
              method="post">
          @csrf
          <input type="hidden" name="pk" id="pk">
          <input type="hidden" name="uid" id="uid" value="{{ \Auth::id() }}">
          <!-- NO_SURAT -->
          <div class="form-group row">
            <label for="PLP_JENIS" 
                   class="col-sm-3 col-form-label">
              Jenis Pengajuan</label>
            <div class="col-sm-9">
              <select class="custom-select custom-select-sm"
                      name="PLP_JENIS"
                      id="PLP_JENIS"
                      required>
                <option value="pengajuan">Pengajuan</option>
                <option value="pembatalan">Pembatalan</option>
              </select>
            </div>
          </div>
          <!-- REF_NUMBER -->
          <div class="form-group row">
            <label for="REF_NUMBER" 
                   class="col-sm-3 col-form-label">
              Ref Number</label>
            <div class="col-sm-9">
              <input type="text" 
                    class="form-control form-control-sm"
                    id="REF_NUMBER"
                    name="REF_NUMBER"
                    placeholder="Ref Number"
                    required>
            </div>
          </div>
          <!-- NO_SURAT -->
          <div class="form-group row">
            <label for="NO_SURAT" 
                   class="col-sm-3 col-form-label">
              No Surat</label>
            <div class="col-sm-9">
              <input type="text" 
                    class="form-control form-control-sm"
                    id="NO_SURAT"
                    name="NO_SURAT"
                    placeholder="No Surat"
                    required>
            </div>
          </div>
          <!-- NO_PLP -->
          <div class="form-group row">
            <label for="NO_PLP" 
                   class="col-sm-3 col-form-label">
              No Plp</label>
            <div class="col-sm-9">
              <input type="text" 
                    class="form-control form-control-sm"
                    id="NO_PLP"
                    name="NO_PLP"
                    placeholder="No Plp"
                    required>
            </div>
          </div>
          <!-- TGL_PLP -->
          <div class="form-group row">
            <label for="TGL_PLP" 
                   class="col-sm-3 col-form-label">
              Tgl Plp</label>
            <div class="col-sm-9">
              <div class="input-group input-group-sm date onlydate"
                   id="dtpplp"
                   data-target-input="nearest">
                <input type="text"
                       id="tglplp"
                       class="form-control datetimepicker-input tanggal"
                       placeholder="PLP Date"
                       data-target="#dtpplp"
                       data-ganti="TGL_PLP"
                       value=""
                       required>
                <div class="input-group-append"
                     data-target="#dtpplp"
                     data-toggle="datetimepicker">
                  <div class="input-group-text">
                    <i class="fa fa-calendar"></i>
                  </div>
                </div>
              </div>
              <input type="hidden"
                    id="TGL_PLP"
                    name="TGL_PLP"
                    required>
            </div>
          </div>
          <!-- NO_SURAT -->
          <div class="form-group row">
            <label for="FL_SETUJU" 
                   class="col-sm-3 col-form-label">
              Setuju</label>
            <div class="col-sm-9">
              <select class="custom-select custom-select-sm"
                      name="FL_SETUJU"
                      id="FL_SETUJU"
                      required>
                <option value="" disabled>Pending</option>
                <option value="Y">Disetujui</option>
                <option value="N">Ditolak</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-lg btn-default" data-dismiss="modal">Close</button>
        <button type="submit" form="formEditPlp" 
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

@endsection

@section('footer')  
  <script>
  var btnRequest = '<button id="sendRequestPlp" data-jenis="plp-request" data-judul="Request PLP" class="btn btn-sm btn-success btn-block elevation-2 plp"> <i class="fas fa-paper-plane"></i> Request PLP </button>';
  var btnRespons = '<button id="sendResponsePlp" data-jenis="plp-response" data-judul="Request PLP" class="btn btn-sm btn-info btn-block elevation-2 plp"> <i class="fas fa-sync-alt"></i> Get Response </button>';
  var btnBatal = '<button id="sendRequestBatalPlp" data-jenis="plp-batal" data-judul="Request Batal PLP" class="btn btn-sm btn-danger btn-block elevation-2 plp"> <i class="fas fa-paper-plane"></i> Request Batal PLP </button>';
  var btnBatalResponse = '<button id="sendResponseBatalPlp" data-jenis="plp-resbatal" data-judul="Response Batal" class="btn btn-sm btn-warning btn-block elevation-2 plp"> <i class="fas fa-paper-plane"></i> Get Response Batal </button>';
    $(function () {
        $('.datetimemin').datetimepicker({
          icons: { time: 'far fa-clock' },
          format: 'DD-MM-YYYY HH:mm',
          sideBySide: true,
          allowInputToggle: true,
        });

        $('.withtime').datetimepicker({
          icons: { time: 'far fa-clock' },
          format: 'DD-MM-YYYY HH:mm',
          sideBySide: true,
          allowInputToggle: true,
        });

        $('.onlydate').datetimepicker({
          icons: { time: 'far fa-clock' },
          format: 'DD-MM-YYYY',
          allowInputToggle: true,
        });       

        $('.mawb-mask').inputmask({
          mask: "999-99999999",
          removeMaskOnSubmit: true
        });

        // @if($disabled == 'disabled')
        //   $('input, select, textarea, button[type=submit]').not('#input-search, #PostDateBilling, #btnPostingBilling, #btnPostingCost').prop('disabled', true);
        // @endif
        
    });
    
    function findNpwp() {
      var npwp = $('#mBRANCH').find(':selected').attr('data-npwp');

      $('#NPWP').val(npwp);
      @if(!$item->id)
      $('#KPBC').append('<option value="050100" selected>050100 - KPPBC Soekarno-Hatta</option>').trigger('change');
      @endif
    }
    function getTblHouse() {
      $('#tblHouses').DataTable().destroy();

      $('#tblHouses').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 350,
        pageLength: parseInt("{{ config('app.page_length') }}"),
        ajax: {
          url:"{{ route('houses.index') }}",
          type: "GET",
          data: function (d) {
            var s = d.search.value;
            d.search.value = s.replace('-', '');
            d.id = "{{ $item->id }}";

            return d;
          }
        },
        columns:[
          @forelse ($headerHouse as $keys => $value )
            @if($keys == 'id')
            {data:"DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false},
            @elseif($keys == 'actions')
            {data:"actions", orderable: false, searchable: false, className:"text-nowrap"},
            @elseif(in_array($keys, ['SCAN_IN_DATE', 'SCAN_OUT_DATE']))
            {
              data: "{{ $keys }}",
              render: function (data, type, row) {
                if (type === 'display') {
                  if(isNaN(data) && moment(data, 'YYYY-MM-DD HH:MM:SS', true).isValid())
                  {
                      return moment(data).format('DD-MM-YYYY HH:MM:SS');
                  }
                }
                return data;
              },
              className: 'text-nowrap'
            },
            @elseif($keys == 'BC_STATUS')
            {data: "{{$keys}}", name: "{{$keys}}", className: 'keterangan'},
            @else
            {data: "{{$keys}}", name: "{{$keys}}"},
            @endif
          @empty                
          @endforelse
        ],
        dom: 'Blfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            action: function ( e, dt, node, config ) {                    
              window.open("{{ route('houses.index') }}?id={{ $item->id }}&print=1");
            }
          },
        ],
      });
      // $.ajax({
      //   url: "{{ route('houses.index') }}",
      //   type: "GET",
      //   data:{
      //     id: "{{ $item->id }}",
      //   },
      //   success: function(msg){
      //     $('#tblHouses').DataTable({
      //       data:msg.data,
      //       pageLength: parseInt("{{ config('app.page_length') }}"),
      //       columns:[
      //         @forelse ($headerHouse as $keys => $value )
      //           @if($keys == 'id')
      //           {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false},
      //           @elseif($keys == 'actions')
      //           {data:"actions", searchable: false, className:"text-nowrap"},
      //           @elseif(in_array($keys, ['SCAN_IN_DATE', 'SCAN_OUT_DATE']))
      //           {
      //             data: "{{ $keys }}",
      //             render: function (data, type, row) {
      //               if (type === 'display') {
      //                 if(isNaN(data) && moment(data, 'YYYY-MM-DD HH:MM:SS', true).isValid())
      //                 {
      //                     return moment(data).format('DD-MM-YYYY HH:MM:SS');
      //                 }
      //               }
      //               return data;
      //             },
      //             className: 'text-nowrap'
      //           },
      //           @elseif($keys == 'BC_STATUS')
      //           {data: "{{$keys}}", name: "{{$keys}}", className: 'keterangan'},
      //           @else
      //           {data: "{{$keys}}", name: "{{$keys}}"},
      //           @endif
      //         @empty                
      //         @endforelse
      //       ],
      //       buttons: [                
      //           'excelHtml5',
      //           {
      //               extend: 'pdfHtml5',
      //               orientation: 'landscape',
      //               pageSize: 'LEGAL'
      //           },
      //           'print',
      //       ],
      //     }).buttons().container().appendTo('#tblHouses_wrapper .col-md-6:eq(0)');
      //   }

      // })
    }    
    function getTblLogs(){
      $('#tblLogs').DataTable().destroy();

      $.ajax({
        url: "{{ route('logs.show') }}",
        type: "GET",
        data:{
          type: 'master',
          id: "{{ $item->id }}",
        },
        success: function(msg){
          $('#tblLogs').DataTable({
            data:msg.data,
            pageLength: parseInt("{{ config('app.page_length') }}"),
            columns:[
              {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
              {data:"created_at", name: "created_at"},
              {data:"user", name: "user"},
              {data:"keterangan", name: "keterangan", searchable: false},
            ],
            buttons: [                
                'excelHtml5',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                },
                'print',
            ],
          }).buttons().container().appendTo('#tblLogs_wrapper .col-md-6:eq(0)');
        }

      })
    }
    function getTblPlp(){
      $('#tblPlp').DataTable().destroy();

      $.ajax({
        url: "{{ route('plp.table', ['master' => Crypt::encrypt($item->id)]) }}",
        type: "GET",
        success: function(msg){
          $('#tblPlp').DataTable({
            data:msg.data,
            pageLength: parseInt("{{ config('app.page_length') }}"),
            columns:[
              {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
              {data:"jenis", name: "jenis"},
              {data:"REF_NUMBER", name: "REF_NUMBER"},
              {data:"NO_SURAT", name: "NO_SURAT"},
              {data:"NO_PLP", name: "NO_PLP"},
              {data:"status", name: "status"},
              {data:"ALASAN_REJECT", name: "ALASAN_REJECT"},
              {data:"actions", name: "actions", searchable: false, className:"text-center"},
            ],
            buttons: [                
                'excelHtml5',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                },
                'print',
            ],
          }).buttons().container().appendTo('#tblPlp_wrapper .col-md-6:eq(0)');
        }

      })
    }
    function getTblPlpLog(){
      $('#tblPlpLog').DataTable().destroy();

      $.ajax({
        url: "{{ route('logs.plp') }}",
        type: "GET",
        data:{
          id: "{{ $item->id }}",
        },
        success: function(msg){
          $('#tblPlpLog').DataTable({
            data:msg.data,
            pageLength: parseInt("{{ config('app.page_length') }}"),
            columns:[
              @forelse ($headerPlp as $key => $value)
                @if($key == 'id')
                {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
                @elseif($key == 'reason')
                {data:"{{ $key }}", name: "{{ $key }}", className:'reason'},
                @else
                {data:"{{ $key }}", name: "{{ $key }}"},
                @endif
              @empty                
              @endforelse
            ],
            buttons: [                
                'excelHtml5',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                },
                'print',
            ],
          }).buttons().container().appendTo('#tblPlpLog_wrapper .col-md-6:eq(0)');
        }

      })
    }
    function getTblSchLog(){
      $('#tblSchLog').DataTable().destroy();

      $.ajax({
        url: "{{ route('logs.sch') }}",
        type: "GET",
        data:{
          id: "{{ $item->id }}",
        },
        success: function(msg){
          $('#tblSchLog').DataTable({
            data:msg.data,
            pageLength: parseInt("{{ config('app.page_length') }}"),
            columns:[
              {data:"DT_RowIndex", name: "DT_RowIndex", searchable: false, className:"h-10"},
              {data:"created_at", name: "created_at"},
              {data:"process", name: "process"},
              {data:"request", name: "request", className:'reason'},
              {data:"response", name: "response", className:'reason'},
              {data:"info", name: "info"},
            ],
            buttons: [                
                'excelHtml5',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                },
                'print',
            ],
          }).buttons().container().appendTo('#tblSchLog_wrapper .col-md-6:eq(0)');
        }

      })
    }
    function calDays() {
      var one = $('#cal_arrival').val();
      var two = $('#cal_out').val();      
      
      if(one && two){
        var dayOne = moment(one, "DD-MM-YYYY", true);
        var dayTwo = moment(two, "DD-MM-YYYY", true);
        var diff = dayTwo.diff(dayOne, 'days');
        if(diff != NaN){
          $('#cal_days').val(diff + 1);
        }

        $('#cal_date').val(two);
      }
    }
    function calConsolDays() {
      var one = $('#tab-calc-content #cal_arrival_csl').val();
      var two = $('#tab-calc-content #cal_out_csl').val();      
      console.log(one+';'+two);
      if(one && two){        
        var dayOne = moment(one, "DD-MM-YYYY", true);
        var dayTwo = moment(two, "DD-MM-YYYY", true);
        var diff = dayTwo.diff(dayOne, 'days');
        if(diff != NaN){
          $('#tab-calc-content #cal_days_csl').val(diff + 1);
        }

        $('#tab-calc-content #cal_date_csl').val(two);
      }
    }
    function numericEditable() {
      $('.editable').editable({
        mode: 'inline',
        onblur: 'submit', 		
        savenochange : false,
        showbuttons: false,
        inputclass: 'kecil form-control form-control-sm',
        display: function(value, response) {
                var k = formatAsMoney(value,2);
                $(this).text(k);
              },
        validate: function(value) {
          if(!$.isNumeric(value)) {
              return " Please input numeric.";
          } else if(value == 0){
            return " Minimum value is 1.";
          }
        },
        success:function(msg){
          if(msg.status == "OK"){
            toastr.success(msg.message, "Success!", {timeOut: 6000, closeButton: true});
            $(this).text('').text(msg.value);
          } else {
            toastr.error(msg.message, "Success!", {timeOut: 6000, closeButton: true})
          }
        },
        error:function(jqXHR){
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
        }
      });
    }
    function getAjax(rows, no, akhir) {
      if($('#row_'+no).hasClass('jmlRow')){
        $('#progress-'+no).text('').text('POSTING');
      }
      var ids = $('#row_'+no).attr('data-ids');
      
      $.ajax({
        url: "/manifest/post-data",
        type: "POST",
        // async:false,
        data:{          
          mt: "{{ $item->id }}",
          hs: ids,
        },
        success(msg){
          console.log(no);
          var baru = rows.splice(1);

          if(msg.status == 'OK'){
            $('#progress-'+no).css('width', '100%').text('').text('100%');
            
            $('#row_'+no).removeClass('jmlRow');
            $('#no_'+baru[0]).html('POSTING');
            // var link = '';
            // if(msg.xml.length > 0)
            // {              
            //   $.each(msg.xml, function(k, v){
            //     var asset = "{{ asset('/storage/file/xml') }}";
            //     link += '<a href="'+asset+'/'+v+'" target="_blank">'+v+'</a><br>';
            //   });
            // }

            // $('#pesan_'+no).html(link);

          } else {
            $('#progress-'+no).css('width', '0%');
            $('#pesan_'+no).text('').text(msg.message);
          } 

          console.log(baru);
          console.log(akhir);

          if(baru.length === 0){
            $('#kirimData').prop('disabled', false);
            $('.sendbatch').prop('disabled', false);

            showSuccess('Send Batch Completed');
          } else {
            getAjax(baru, baru[0], akhir);
          }          
        },
        error: function (jqXHR, exception) {
          jsonValue = jQuery.parseJSON( jqXHR.responseText );
          toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

          $('#kirimData').prop('disabled', false);
        }
      });
    }
    jQuery(document).ready(function(){ 
      findNpwp();
      var timeout = null;

      $('.select2kpbc').select2({
        placeholder: 'Select...',
        allowClear: true,        
        ajax: {
          url: "{{ route('select2.setup.customs-offices') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.Kdkpbc + " - "+ item.UrKdkpbc,
                        id: item.Kdkpbc,
                    }
                })
            };
          },
          cache: true
        }
      });
      $('.select2unloco').select2({
        placeholder: 'Select...',
        allowClear: true,
        ajax: {
          url: "{{ route('select2.setup.unloco') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.RL_Code + " - "+ item.RL_PortName + " (" + item.RL_RN_NKCountryCode + ")",
                        id: item.RL_Code,
                        code: item.RL_RN_NKCountryCode,
                    }
                })
            };
          },
          cache: true
        }
      });
      $('.select2airline').select2({
        placeholder: 'Select...',
        allowClear: true,
        ajax: {
          url: "{{ route('select2.setup.airlines') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.RM_TwoCharacterCode + " - "+ item.RM_AirlineName1.toUpperCase(),
                        id: item.RM_TwoCharacterCode,
                        name: item.RM_AirlineName1,
                        code: item.RM_AccountingCode
                    }
                })
            };
          },
          cache: true
        },
        templateSelection: function(container) {
            $(container.element).attr("data-name", container.name)
                                .attr("data-code", container.code);
            return container.text;
        }
      });
      $('#OriginWarehouse').select2({
        placeholder: 'Select...',
        allowClear: true,
        ajax: {
          url: "{{ route('select2.setup.bonded-warehouses') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.warehouse_code + " - "+ item.company_name,
                        id: item.warehouse_code
                    }
                })
            };
          },
          cache: true
        },
        templateSelection: function(container) {
            $(container.element).attr("data-name", container.name);
            return container.text;
        }
      });
      $('.select2organization').select2({
        placeholder: 'Select...',
        ajax: {
          url: "{{ route('select2.setup.organization') }}",          
          dataType: 'json',
          delay: 250,
          data: function (params) {
            var query = {
              q: params.term,
              type: $(this).attr('data-type'),
              create: true,
              address: 1
            }

            return query;
          },
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: (item.OH_LegacyCode ?? item.OH_Code)+" - "+item.OH_FullName + " || " + item.OA_Address1,
                        id: item.OH_FullName,
                        name: item.OH_FullName,
                        address: item.OA_Address1,
                        tax: item.OA_TaxID,
                        phone: item.OA_Phone,
                    }
                })
            };
          },
          cache: true
        },
        templateSelection: function(container) {
            $(container.element).attr("data-address", container.address)
                                .attr("data-tax", container.tax)
                                .attr("data-phone", container.phone);
            return container.text;
        }
      });
      $('.select2country').select2({
        placeholder: 'Select...',
        ajax: {
          url: "{{ route('select2.setup.countries') }}",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                    return {
                        text: item.RN_Code + " (" + item.RN_Desc + ")",
                        id: item.RN_Code,
                    }
                })
            };
          },
          cache: true
        }
      });
      $('.selectForwarder').select2({
          placeholder: 'Select...',
          allowClear: true,
          ajax: {
          url: "/select2/setup/organization/forwarder",
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results:  $.map(data, function (item) {
                return {
                    text: item.OH_Code+" - "+item.OH_FullName+" - "+item.Address,
                    id: item.id,
                }
              })
            };
          },
          cache: true
          }
      });
      $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        var masuk = "{{ ($item->houses->isNotEmpty()) ? $item->houses->sortBy('SCAN_IN_DATE')->first()->SCAN_IN_DATE : '' }}";
        clearTimeout(timeout);
        switch (e.target.id){
            case "tab-houses":{
                timeout = setTimeout(() => {
                  getTblHouse();
                }, 300);                 
                break;
            }
            case "tab-plp":{
                timeout = setTimeout(() => {
                  getTblPlp();
                }, 300);
                
                break;
            }
            case "tab-calc":{
              $('#tab-calc-content #cal_arrival_csl').val('');
                if(masuk !== '' && masuk != null)
                {
                  var masukParse = moment(masuk).format('DD-MM-YYYY');
                  $('#tab-calc-content #cal_arrival_csl').val(masukParse);
                }
                break;
            }
            case "tab-log":{
                timeout = setTimeout(() => {
                  getTblLogs();
                  getTblPlpLog();
                }, 300);                
                break;
            }
            case "vert-tabs-home-tab":{
                timeout = setTimeout(() => {
                  getTblLogs();
                }, 300);
                
                break;
            }
            case "vert-tabs-plp-tab":{
                timeout = setTimeout(() => {
                  getTblPlpLog();
                }, 300);
                
                break;
            }
            case "vert-tabs-sch-tab":{
                timeout = setTimeout(() => {
                  getTblSchLog();
                }, 300);
                
                break;
            }
            case "tab-bilcost":{
              timeout = setTimeout(() => {
                tableShipmentsBilling($('#JR_JH').val());
                tableShipmentsCost($('#JR_JH').val());
                getTblCharges();
              }, 300);
              
              break;
            }
            case "tab-summary":{
              timeout = setTimeout(() => {
                getTblResSummary();
                getTblResLogs();
              }, 300);
              
              break;
            }
            case "tab-kirim":{
              // var tableKirim = $('#tblKirim').DataTable({
              //               paging: false,
              //               ordering: false,
              //               scrollY: '50vh',
              //               scrollCollapse: true,
              //             });
              break;
            }
        }
      });
      $(document).on('click', '.editplp', function(){
        var id = $(this).attr('data-id');
        var ref = $(this).attr('data-ref');
        var surat = $(this).attr('data-surat');
        var plp = $(this).attr('data-plp');
        var tgl = $(this).attr('data-tgl');
        var fl = $(this).attr('data-fl');
        var pengajuan = $(this).attr('data-pengajuan');
        var pembatalan = $(this).attr('data-pembatalan');
        var tglplp = moment(tgl);

        $('#formEditPlp #pk').val(id).trigger('change');
        $('#formEditPlp #REF_NUMBER').val(ref).trigger('change');
        $('#formEditPlp #NO_SURAT').val(surat).trigger('change');
        $('#formEditPlp #NO_PLP').val(plp).trigger('change');
        $('#formEditPlp #tglplp').val(tglplp.format('DD-MM-YYYY')).trigger('change');
        $('#formEditPlp #TGL_PLP').val(tglplp.format('YYYY-MM-DD')).trigger('change');
        $('#formEditPlp #FL_SETUJU').val(fl).trigger('change');

        if(pengajuan === '1')
        {
          // console.log('pengajuan');
          $('#formEditPlp #PLP_JENIS').val('pengajuan').trigger('change');
        } else if(pembatalan === '1')
        {
          // console.log('pembatalan');
          $('#formEditPlp #PLP_JENIS').val('pembatalan').trigger('change');
        }
      });
      $(document).on('submit', '#formEditPlp', function(e){
        e.preventDefault();
        var url = $(this).attr('action');

        $('.btn').prop('disabled', true);

        $.ajax({
          url: url,
          type: "POST",
          data: $(this).serialize(),
          success: function(msg){

            if(msg.status == 'OK'){
              toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

              $('#modal-edit-plp').modal('hide');
              getTblPlp();
            } else {
              toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
            }

            $('.btn').prop('disabled', false);
          },
          error: function (jqXHR, exception) {
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        })
      });
      $(document).on('click', '#btnCreateJobheader', function(){

        Swal.fire({			
          title: 'Create Job Billing/Cost?',			
          html:
            "Create Job Billing/Cost for this consolidation?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, create!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: "{{ route('manifest.consolidations.update', ['consolidation' => \Crypt::encrypt($item->id)]) }}",
              type: "POST",
              data:{
                _method: "PUT",
                jenis: "jobheader"
              },
              success: function(msg){
                if(msg.status == 'OK'){
                  showSuccess(msg.message);
                  location.reload();
                } else {
                  showError(msg.message);
                }
              },
              error:function(jqXHR){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                showError(jqXHR.status + ' || ' + jsonValue.message);
              }
            })
          }
        });
      });
      $(document).on('click', '.editcw', function(){
        var ini = $(this);
        var pk = $(this).attr('data-pk');
        var url = $(this).attr('data-url');
        var name = $(this).attr('data-name');
        var title = $(this).attr('data-title');
        var placeholder = $(this).attr('data-placeholder');
        var val = $(this).attr('value');

        // alert(title);
        Swal.fire({
          title: title,
          input: "text",
          inputPlaceholder: placeholder,
          inputValue: val,
          inputAttributes: {
              id: 'myInput'
          },
          didOpen: function(el) {
              var container = $(el);
              container.find('#myInput').inputmask('numeric', {
                                          groupSeparator: '.',
                                          rightAlign: false,
                                          allowMinus: false,
                                          autoUnmask: true,
                                          removeMaskOnSubmit: true
                                        });
          },
          inputValidator: (value) => {
            if(!$.isNumeric(value)) {
                return " Please input numeric.";
            } else if(value == 0){
              return " Minimum value is 1.";
            }
          }
        }).then((result) => {
          if(result.value){
            $.ajax({
              url : url,
              type: "POST",
              data:{
                pk: pk,
                value:result.value,
                name:name
              },
              success: function(msg){
                if(msg.status == 'OK'){
                  toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                  ini.text('').text(formatAsMoney(result.value, 2, '.'));
                  ini.attr('value', formatAsMoney(result.value, 2, '.'));
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error: function (jqXHR, exception) {
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            })
          }
        });
      });
      $(document).on('change', '.select2airline', function(){
        var name = $(this).find(':selected').attr('data-name');
        var code = $(this).find(':selected').attr('data-code');
        // var mawb = $('#MAWBNumber').val();

        if(code != '' && code != undefined)
        {
          $('#MAWBNumber').val(code);
        }

        $('#NM_SARANA_ANGKUT').val(name.toUpperCase());
      });
      $(document).on('change', '.select2organization', function(){
        var target = $(this).attr('data-target');
        var npwp = $(this).attr('data-npwp');
        var phone = $(this).attr('data-phone');
        var address = $(this).find(':selected').attr('data-address');
        var idpenerima = $(this).find(':selected').attr('data-tax');
        var phonepenerima = $(this).find(':selected').attr('data-phone');

        if(address != undefined){
          $('#'+target).val(address.toUpperCase());
        }        
        if(npwp != ''){
          $('#'+npwp).val(idpenerima);
        }
        if(phone != ''){
          $('#'+phone).val(phonepenerima);
        }       

        if(idpenerima != '' && idpenerima != undefined){          
          var count = idpenerima.replace(/[^0-9]/g,'');
          if(count.length > 12){
            var value = 5;
          } else if (count.lenght > 10){
            var value = 0;
          } else if(count.length == 10){
            var value = 1;
          } else {
            var value = 4;
          }
          console.log(count.lenght);
          $('#JNS_ID_PENERIMA').val(value).trigger('change');
        }
      });
      $(document).on('change', '#cal_tariff', function(){
        var val = $(this).find(':selected').val();

        $('#cal_tariff_id').val(val);
      });
      $(document).on('change', '#cal_tariff_csl', function(){
        var val = $(this).find(':selected').val();

        $('#cal_tariff_id_csl').val(val);
      });
      $(document).on('input paste', '#departure', function(){
        var dept = moment($(this).val(), 'DD-MM-YYYY HH:mm');
        var tgl = $(this).val().split(' ');

        $('#DepartureDate').val(moment(tgl[0], 'DD-MM-YYYY').format('YYYY-MM-DD'));
        $('#DepartureTime').val(tgl[1]);

      });
      $(document).on('input paste', '#arrivals', function(){
        var tgl = $(this).val().split(' ');

        $('#ArrivalDate').val(moment(tgl[0], 'DD-MM-YYYY').format('YYYY-MM-DD'));
        $('#ArrivalTime').val(tgl[1]);
      });
      $(document).on('input paste', '#tgltibapartial', function(){
        var tgl = $(this).val().split(' ');

        $('#TGL_TIBA_PARTIAL').val(moment(tgl[0], 'DD-MM-YYYY').format('YYYY-MM-DD'));
        $('#JAM_TIBA_PARTIAL').val(tgl[1]);
      }); 
      $(document).on('input paste', '.tanggal', function(){
        var tgl = $(this).val();
        var ganti = $(this).attr('data-ganti');
        if(tgl != ''){
          var tanggal = moment(tgl, 'DD-MM-YYYY').format('YYYY-MM-DD');          
        } else {
          var tanggal = '';
        }

        $('#'+ganti).val(tanggal);
        
      });
      $(document).on('input paste', '.tgltime', function(){
        var tgl = $(this).val();
        var ganti = $(this).attr('data-ganti');
        if(tgl != ''){
          var tanggal = moment(tgl, 'DD-MM-YYYY HH:mm', true).format('YYYY-MM-DD HH:mm:ss');          
        } else {
          var tanggal = '';
        }

        $('#'+ganti).val(tanggal).trigger('change');
        
      });
      $(document).on('change', '#MAWBNumber', function(){
        var val = $(this).val().replace(/[^0-9]/gi, '');
        
        if(val.length == 11){
          var end = val.substr(10,1);
          var code = val.substr(3,7);
          var divseven = code / 7;
          var substr = divseven.toString().split('.');
          console.log('substr: '+substr[1]);
          var nbr = (0+'.'+substr[1]) * 7;
          console.log('nbr:' + nbr);
          var checkNum = Math.round(nbr);
          console.log('check:' + checkNum);
          
          if(end != checkNum){
            alert('Please provide a valid MAWB Number!');
          }
        }
      });
      $(document).on('click', '.edit', function(){
        var target = $(this).attr('data-target');
        var id = $(this).attr('data-id');

        $('#collapseHSCodes').removeClass('show');
        $('#collapseResponse').removeClass('show');
        $('#collapseCalculate').removeClass('show');
        $('#'+target).removeClass('show');

        $.ajax({
          url:"/manifest/houses/"+id,
          type: "GET",
          success:function(msg){

            $('#detailHouse').text('').text(msg.NO_HOUSE_BLAWB);

            $('#SKIP').val((msg.SKIP ?? "N")).trigger('change');
            $('#JNS_AJU').val((msg.JNS_AJU ?? 1)).trigger('change');
            $('#KD_DOC').val((msg.KD_DOC ?? 1)).trigger('change');
            $('#KD_JNS_PIBK').val((msg.KD_JNS_PIBK ?? 6)).trigger('change');
            $('#SPPBNumber').val(msg.SPPBNumber).trigger('change');

            if(msg.SPPBDate){
              var sppbDate = moment(msg.SPPBDate);

              $('#tglsppb').val(sppbDate.format('DD/MM/YYYY')).trigger('change');
              $('#SPPBDate').val(sppbDate.format('YYYY-MM-DD')).trigger('change');
            } else {
              $('#tglsppb').val('').trigger('change');
              $('#SPPBDate').val('').trigger('change');
            }

            if(msg.TGL_BC11){
              var bcDate = moment(msg.TGL_BC11);              
              $('#TGL_BC11').val(bcDate.format('DD-MM-YYYY'));
            } else {              
              $('#TGL_BC11').val('');
            }

            $('#NO_BC11').val(msg.NO_BC11);
            $('#NO_POS_BC11').val(msg.NO_POS_BC11);
            $('#NO_SUBPOS_BC11').val(msg.NO_SUBPOS_BC11);
            $('#NO_SUBSUBPOS_BC11').val(msg.NO_SUBSUBPOS_BC11);

            $('#BCF15_Status').val((msg.BCF15_Status ?? 'N')).trigger('change');
            $('#BCF15_Number').val(msg.BCF15_Number).trigger('change');

            if(msg.BCF15_Date){
              var bcfDate = moment(msg.BCF15_Date);

              $('#tglbcf').val(bcfDate.format('DD/MM/YYYY')).trigger('change');
              $('#BCF15_Date').val(bcfDate.format('YYYY-MM-DD')).trigger('change');
            } else {
              $('#tglbcf').val('').trigger('change');
              $('#BCF15_Date').val('').trigger('change');
            }

            $('#NO_DAFTAR_PABEAN').val(msg.NO_DAFTAR_PABEAN).trigger('change');

            if(msg.TGL_DAFTAR_PABEAN){
              var tglpib = moment(msg.TGL_DAFTAR_PABEAN);

              $('#tglpib').val(tglpib.format('DD/MM/YYYY')).trigger('change');
              $('#TGL_DAFTAR_PABEAN').val(tglpib.format('YYYY-MM-DD')).trigger('change');
            } else {
              $('#tglpib').val('').trigger('change');
              $('#TGL_DAFTAR_PABEAN').val('').trigger('change');
            }

            $('#SEAL_NO').val(msg.SEAL_NO).trigger('change');

            if(msg.SEAL_DATE){
              var sealdate = moment(msg.SEAL_DATE);

              $('#tglseal').val(sealdate.format('DD/MM/YYYY')).trigger('change');
              $('#SEAL_DATE').val(sealdate.format('YYYY-MM-DD')).trigger('change');
            } else {
              $('#tglseal').val('').trigger('change');
              $('#SEAL_DATE').val('').trigger('change');
            }

            $('#TOTAL_PARTIAL').val(msg.TOTAL_PARTIAL).trigger('change');

            $('#ShipmentNumber').val(msg.ShipmentNumber).trigger('change');
            $('#CUS_PO').val(msg.CUS_PO).trigger('change');
            $('#NO_HOUSE_BLAWB').val(msg.NO_HOUSE_BLAWB).trigger('change');

            if(msg.TGL_HOUSE_BLAWB){
              var houseDate = moment(msg.TGL_HOUSE_BLAWB);

              $('#tglhouse').val(houseDate.format('DD/MM/YYYY')).trigger('change');
              $('#TGL_HOUSE_BLAWB').val(houseDate.format('YYYY-MM-DD')).trigger('change');
            } else {
              $('#tglhouse').val('').trigger('change');
              $('#TGL_HOUSE_BLAWB').val('').trigger('change');
            }

            

            if(msg.KD_PEL_MUAT){
              var optmuat = '<option value="'+ msg.KD_PEL_MUAT +'">'
                                + msg.KD_PEL_MUAT
                                + ' - ' + msg.unloco_origin.RL_PortName
                                + ' - ' + msg.unloco_origin.RL_RN_NKCountryCode
                                + '</option>';
              $('#KD_PEL_MUAT').empty().append(optmuat);
            } else {
              $('#KD_PEL_MUAT').empty();
            }

            if(msg.KD_PEL_TRANSIT){
              var optmuat = '<option value="'+ msg.KD_PEL_TRANSIT +'">'
                                + msg.KD_PEL_TRANSIT
                                + ' - ' + msg.unloco_transit.RL_PortName
                                + ' - ' + msg.unloco_transit.RL_RN_NKCountryCode
                                + '</option>';
              $('#KD_PEL_TRANSIT').empty().append(optmuat);
            } else {
              $('#KD_PEL_TRANSIT').empty();
            }

            if(msg.KD_PEL_AKHIR){
              var optmuat = '<option value="'+ msg.KD_PEL_AKHIR +'">'
                                + msg.KD_PEL_AKHIR
                                + ' - ' + msg.unloco_destination.RL_PortName
                                + ' - ' + msg.unloco_destination.RL_RN_NKCountryCode
                                + '</option>';
              $('#KD_PEL_AKHIR').empty().append(optmuat);
            } else {
              $('#KD_PEL_AKHIR').empty();
            }

            if(msg.KD_PEL_BONGKAR){
              var optmuat = '<option value="'+ msg.KD_PEL_BONGKAR +'">'
                                + msg.KD_PEL_BONGKAR
                                + ' - ' + msg.unloco_bongkar.RL_PortName
                                + ' - ' + msg.unloco_bongkar.RL_RN_NKCountryCode
                                + '</option>';
              $('#KD_PEL_BONGKAR').empty().append(optmuat);
            } else {
              $('#KD_PEL_BONGKAR').empty();
            }

            if(msg.SCAN_IN_DATE){
              $('#SCAN_IN_DATE').val(msg.SCAN_IN_DATE);
            }
            if(msg.SCAN_OUT_DATE){
              $('#SCAN_OUT_DATE').val(msg.SCAN_OUT_DATE);
            }
            if(msg.TPS_GateInStatus){
              $('#TPS_GateInStatus').val(msg.TPS_GateInStatus);
            }
            if(msg.TPS_GateOutStatus){
              $('#TPS_GateOutStatus').val(msg.TPS_GateOutStatus);
            }

            if(msg.NM_PENGIRIM){
              var optPengirim = '<option value="'+ msg.NM_PENGIRIM +'"'
                                +'data-address="'+ msg.AL_PENGIRIM +'"'
                                +'data-tax="" data-phone="">'
                                + msg.NM_PENGIRIM + ' || ' + msg.AL_PENGIRIM +'</option>';
              $('#NM_PENGIRIM').empty().append(optPengirim);
            } else {
              $('#NM_PENGIRIM').empty();
            }
            
            $('#AL_PENGIRIM').val(msg.AL_PENGIRIM).trigger('change');
            $('#KD_NEG_PENGIRIM').val(msg.KD_NEG_PENGIRIM).trigger('change');

            if(msg.NM_PENERIMA){
              var optPengirim = '<option value="'+ msg.NM_PENERIMA +'"'
                                +'data-address="'+ msg.AL_PENERIMA +'"'
                                +'data-tax="'+ msg.NO_ID_PENERIMA +'"'
                                +'data-phone="'+ msg.TELP_PENERIMA +'">'
                                + msg.NM_PENERIMA + ' || ' + msg.AL_PENERIMA +'</option>';
              $('#NM_PENERIMA').empty().append(optPengirim);
            } else {
              $('#NM_PENERIMA').empty()
            }
            
            $('#AL_PENERIMA').val(msg.AL_PENERIMA).trigger('change');
            $('#NO_ID_PENERIMA').val(msg.NO_ID_PENERIMA).trigger('change');
            $('#JNS_ID_PENERIMA').val((msg.JNS_ID_PENERIMA ?? 0)).trigger('change');
            $('#TELP_PENERIMA').val(msg.TELP_PENERIMA).trigger('change');

            $('#NM_PEMBERITAHU').val(msg.NM_PEMBERITAHU);
            $('#NO_ID_PEMBERITAHU').val(msg.NO_ID_PEMBERITAHU);
            $('#AL_PEMBERITAHU').val(msg.AL_PEMBERITAHU);

            $('#NETTO').val(msg.NETTO).trigger('change');
            $('#INCO').val(msg.INCO).trigger('change');
            $('#BRUTO').val(msg.BRUTO).trigger('change');
            $('#ChargeableWeight').val(msg.ChargeableWeight).trigger('change');
            $('#CIF').val(msg.CIF);
            $('#FOB').val(msg.FOB).trigger('change');
            $('#FREIGHT').val(msg.FREIGHT).trigger('change');
            $('#VOLUME').val(msg.VOLUME).trigger('change');

            if(msg.details.length > 0){
              $('#UR_BRG').val(msg.details[0].UR_BRG).trigger('change');
            } else {
              $('#UR_BRG').val('').trigger('change');
            }
            
            $('#ASURANSI').val(msg.ASURANSI).trigger('change');
            $('#JML_BRG').val(msg.JML_BRG).trigger('change');
            $('#JNS_KMS').val(msg.JNS_KMS).trigger('change');
            $('#MARKING').val(msg.MARKING).trigger('change');

            $('#tariff_id').val(msg.tariff_id).trigger('change');
            $('#NPWP_BILLING').val(msg.NPWP_BILLING).trigger('change');
            $('#NAMA_BILLING').val(msg.NAMA_BILLING).trigger('change');
            $('#NO_INVOICE').val(msg.NO_INVOICE).trigger('change');
            
            if(msg.TGL_INVOICE){
              var invDate = moment(msg.TGL_INVOICE);

              $('#tglinv').val(invDate.format('DD/MM/YYYY')).trigger('change');
              $('#TGL_INVOICE').val(invDate.format('YYYY-MM-DD')).trigger('change');
            } else {
              $('#tglinv').val('').trigger('change');
              $('#TGL_INVOICE').val('').trigger('change');
            }

            $('#TOT_DIBAYAR').val(msg.TOT_DIBAYAR).trigger('change');
            $('#NDPBM').val(msg.NDPBM).trigger('change');

            $('#printWithHeader').attr('href', "{{ route('download.manifest.shipments') }}?shipment="+msg.id+"&header=1");
            $('#printNoHeader').attr('href', "{{ route('download.manifest.shipments') }}?shipment="+msg.id+"&header=0");

            $('#'+target).addClass('show');  
            gotoView(target);          
            console.log(msg);
          }
        });

        $('#formHouse').attr('action', '/manifest/houses/'+id);

      });
      $(document).on('change', '#JNS_AJU', function(){
        var ref = $(this).find(':selected').attr('data-ref');
        // var val = $(this).find(':selected').val();
        // var kddoc = 0;
        // switch (val) {
        //   case "1":
        //     kddoc = "43";
        //     break;
        //   case "2":
        //     kddoc = "43";
        //     break;
        //   case "3":
        //     kddoc = "43";
        //     break;
        //   case "4":
        //     kddoc = "1";
        //     break;
        //   case "5":
        //     kddoc = "2";
        //     break;
        //   default:
        //     kddoc = val;
        //     break;
        // }
        $('#KD_DOC').val(ref).trigger('change');
      });
      $(document).on('click', '.codes', function(){
        var target = $(this).attr('data-target');
        var id = $(this).attr('data-id');
        var house = $(this).attr('data-house');
        var code = $(this).attr('data-code');

        $('#collapseHouse').removeClass('show');
        $('#collapseResponse').removeClass('show');
        $('#collapseCalculate').removeClass('show');
        $('#'+target).removeClass('show');

        getTblHSCodes(id);
        $('#detailCodes').text('').text(code);
        $('#formHSCodes #house_id').val(house);
        $('#'+target).addClass('show');
        gotoView(target);
      });
      $(document).on('click', '.response', function(){
        var target = $(this).attr('data-target');
        var id = $(this).attr('data-id');
        var code = $(this).attr('data-code');

        $('#collapseHouse').removeClass('show');
        $('#collapseHSCodes').removeClass('show');
        $('#collapseCalculate').removeClass('show');
        $('#'+target).removeClass('show');

        $('#detailResponse').text('').text(code);

        $('#'+target).addClass('show');
        $('#'+target+' #paksarespon').attr('data-id', id);
        gotoView(target);

        getTblLogsBc(id);

      });
      $(document).on('click', '.calculate', function(){
        var target = $(this).attr('data-target');
        var id = $(this).attr('data-id');
        var code = $(this).attr('data-code');

        $('#collapseHouse').removeClass('show');
        $('#collapseHSCodes').removeClass('show');
        $('#collapseResponse').removeClass('show');
        $('#'+target).removeClass('show');

        $('#detailCalculate').text('').text(code);
        $('#tblIsiCalculate').html('');

        $.ajax({
          url:"/manifest/houses/"+id,
          type: "GET",
          success:function(msg){

            if(msg.SCAN_IN_DATE !== null)
            {
              var arrival = msg.SCAN_IN_DATE;
            } else {
              var arrival = "{{ $item->ArrivalDate }}";
            }

            if(arrival != ''){
              var parseArrival = moment(arrival).format('DD-MM-YYYY');
              $('#cal_arrival').val(parseArrival).trigger('change');
            }
            
            if(msg.SCAN_OUT_DATE){
              var parseOut = moment(msg.SCAN_OUT_DATE).format('DD-MM-YYYY');

              $('#cal_out').val(parseOut);

              calDays();
            } else {
              $('#cal_out').val('');
            }

            if(msg.tariff_id){
              $('#cal_tariff').val(msg.tariff_id).trigger('change');
            }

            $('#cal_chargable').val(msg.ChargeableWeight).trigger('change');
            $('#cal_gross').val(msg.BRUTO).trigger('change');

            if(msg.estimated_tariff.length > 0){
              $('#btnShowEstimated').removeClass('d-none');
              $('#btnEstimateH').attr('href', "/manifest/download-calculated/"+id+"?header=1");
              $('#btnEstimateWH').attr('href', "/manifest/download-calculated/"+id+"?header=0");
            } else {
              $('#btnShowEstimated').addClass('d-none');
              $('#btnEstimateH').addClass('d-none');
              $('#btnEstimateWH').addClass('d-none');
            }

            $('#formCalculate').attr('action', "/manifest/calculate/"+id);
            $('#formStoreCalculate').attr('action', "/manifest/save-calculate/"+id);
            
            $('#'+target).addClass('show');
            gotoView(target);
          }
        });

      });
      $(document).on('click', '#hideHouse', function(){
        $('#collapseHouse').removeClass('show');
      });
      $(document).on('click', '#hideHSCodes', function(){
        $('#collapseHSCodes').removeClass('show');
      });
      $(document).on('click', '#hideResponse', function(){
        $('#collapseResponse').removeClass('show');
      });
      $(document).on('click', '#hideCalculate', function(){
        $('#collapseCalculate').removeClass('show');
      });
      $(document).on('submit', '#formHouse', function(e){
        e.preventDefault();

        var form = $(this).serialize();
        var action = $(this).attr('action');

        $('.btn').prop('disabled', 'disabled');

        $.ajax({
          url: action,
          type: "POST",
          data: form,
          success:function(msg){
            console.log(msg);
            if(msg.status == 'OK'){
              toastr.success("Update House Success", "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

              getTblHouse();
              $('#detailHouse').text('').text(msg.house);
            } else {
              toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
            }
            
            $('.btn').prop('disabled', false);
          },
          error: function (jqXHR, exception) {
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        })
      });      
      $(document).on('click', '.hapusHouse', function(){
        var href = $(this).data('href');		

        Swal.fire({			
          title: 'Are you sure?',			
          html: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, delete!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: href,
              type: "POST",
              data:{
                _token: "{{ csrf_token() }}",
                _method: "DELETE"
              },
              success:function(msg){
                if(msg.status == 'OK'){
                  toastr.success("Delete House Success", "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

                  getTblHouse();

                  $('#collapseHouse').removeClass('show');
                  $('#collapseHSCodes').removeClass('show');
                  $('#collapseResponse').removeClass('show');
                  
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error:function(jqXHR, exception){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            })
          }
        });
      });      
      $(document).on("change.datetimepicker", '.onlydate', function (e) {          
          calDays();
      });
      $(document).on("change", '#tab-calc-content #cal_out_csl', function () {
        var val = $(this).val();
        calConsolDays();

        $.ajax({
          url: "{{ route('calculate.chargable', ['consolidation' => \Crypt::encrypt($item->id)]) }}",
          type: "GET",
          data:{tanggal:val},
          success:function(msg){
            $('#cal_chargable_csl').val(msg.cw);
            $('#cal_gross_csl').val(msg.gross);
          },
          error:function(jqXHR, exception){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            showError(jqXHR.status + ' || ' + jsonValue.message);
          }
        })
      });
      $('#tab-calc-content #datetimepickercalarvcsl').on("change.datetimepicker", ({
        date,
        oldDate
      }) => {
        // console.log("New date", date);
        // console.log("Old date", oldDate);
        calConsolDays();
      });
      // $(document).on('change', '#tab-calc-content #cal_arrival_csl', function(){
      //   console.log($(this).val());
      //   calConsolDays();
      // });
      $(document).on('click', '#btnCalculate', function(){
        $('#show_estimate').val(0);
        $('#show_actual').val(0);

        $('#formCalculate').submit();

        $('.saveCalculation').removeClass('d-none');
      });
      $(document).on('submit', '#formCalculate', function(e){
        e.preventDefault();
        var action = $(this).attr('action');        
        var data = $(this).serialize();

        $('.btn').prop('disabled', 'disabled');
        
        $.ajax({
          url: action,
          type: "GET",
          data: data,
          success:function(msg){
            $('#tblIsiCalculate').html(msg);
            $('.btn').prop('disabled', false);
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        })
      });
      $(document).on('click', '.saveCalculation', function(){
        var estimate = $(this).attr('data-estimate');
        var info = 'Estimated';
        
        if(estimate < 1){
          info = 'Actual';
        }

        Swal.fire({			
          title: 'Save '+info+'?',			
          html: "This will replace current data if exists!",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, calculate!'
        }).then((result) => {
          if (result.value) {
            $('#formStoreCalculate #is_estimate').val(estimate);

            var action = $('#formStoreCalculate').attr('action');
            var data = $('#formStoreCalculate').serialize();

            $.ajax({
              url: action,
              type: "POST",
              data:data,
              success:function(msg){
                if(msg.status == 'OK'){
                  toastr.success("Store "+info+" Success", "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                  if(msg.estimate > 0){
                    $('#btnShowActual').removeClass('d-none');
                  } else {
                    $('#btnShowEstimated').removeClass('d-none');
                  }
                  $('#btnEstimateH').attr('href', "/manifest/download-calculated/"+msg.id+"?header=1");
                  $('#btnEstimateWH').attr('href', "/manifest/download-calculated/"+msg.id+"?header=0");
                  $('#btnEstimateH').removeClass('d-none');
                  $('#btnEstimateWH').removeClass('d-none');
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error:function(jqXHR, exception){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            })
          }
        });
      });
      $(document).on('click', '#btnShowEstimated', function(){
        $('#show_estimate').val(1);

        $('#formCalculate').submit();

        $('.saveCalculation').addClass('d-none');
      });
      $(document).on('click', '#btnCalculateCsl', function(){
        $('#show_estimate_csl').val(0);
        $('#show_actual_csl').val(0);

        $('#formCalculateCsl').submit();

        $('.saveCalculationCsl').removeClass('d-none');
      });
      $(document).on('submit', '#formCalculateCsl', function(e){
        e.preventDefault();
        var action = $(this).attr('action');        
        var data = $(this).serialize();

        $('.btn').prop('disabled', 'disabled');
        
        $.ajax({
          url: action,
          type: "GET",
          data: data,
          success:function(msg){
            $('#tblIsiCalculateCsl').html(msg);
            $('.btn').prop('disabled', false);
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        })
      });
      $(document).on('click', '.saveCalculationCsl', function(){
        var estimate = $(this).attr('data-estimate');
        var info = 'Estimated';
        
        if(estimate < 1){
          info = 'Actual';
        }

        Swal.fire({			
          title: 'Save '+info+'?',			
          html: "This will replace current data if exists!",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, calculate!'
        }).then((result) => {
          if (result.value) {
            $('#formStoreCalculateCsl #is_estimate_csl').val(estimate);

            var action = $('#formStoreCalculateCsl').attr('action');
            var data = $('#formStoreCalculateCsl').serialize();

            $.ajax({
              url: action,
              type: "POST",
              data:data,
              success:function(msg){
                if(msg.status == 'OK'){
                  toastr.success("Store "+info+" Success", "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                  if(msg.estimate > 0){
                    $('#btnShowActualCsl').removeClass('d-none');
                  } else {
                    $('#btnShowEstimatedCsl').removeClass('d-none');
                  }
                  $('#btnEstimateHCsl').attr('href', "/manifest/download-sewagudang/"+msg.id+"?tanggal="+msg.tanggal+"&header=1");
                  $('#btnEstimateWH').attr('href', "/manifest/download-sewagudang/"+msg.id+"?tanggal="+msg.tanggal+"&header=0");
                  $('#btnEstimateHCsl').removeClass('d-none');
                  $('#btnEstimateWHCsl').removeClass('d-none');
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error:function(jqXHR, exception){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            })
          }
        });
      });
      $(document).on('click', '.plp', function(){
        var jenis = $(this).attr('data-jenis');
        var judul = $(this).attr('data-judul');
        var htm = '';

        if(jenis == 'plp-batal'){
          htm += '<input type="text" class="form-control form-control-sm"' +
                    ' name="ALASAN_BATAL" id="ALASAN_BATAL" placeholder="Alasan Batal">';
        }
        
        if($.inArray(jenis, ['plp-request', 'plp-batal']) !== -1){
          htm += '<input type="text" class="form-control form-control-sm"' +
                    ' name="pemohon" id="pemohon" placeholder="Pemohon" value="{{ Str::upper(Auth::user()->name ?? "") }}">';
        }

        Swal.fire({			
          title: 'Send '+judul+'?',			
          html: htm,
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, send!'
        }).then((result) => {
          if (result.value) {
            if(jenis == 'plp-batal'){
              var alasan = $('#ALASAN_BATAL').val();
              
              if(alasan == ''){
                toastr.error('Please write a Reason!', "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

                return false;
              }
            } else {
              var alasan = '';
            }

            if($.inArray(jenis, ['plp-request', 'plp-batal']) !== -1){
              var pemohon = $('#pemohon').val();
              
              if(pemohon == ''){
                toastr.error('Please write a Name!', "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

                return false;
              }
            } else {
              var pemohon = '';
            }
            
            $('.btn').prop('disabled', 'disabled');

            $.ajax({
              url: "{{ route('manifest.plp', ['master' => \Crypt::encrypt($item->id)]) }}",
              type: "POST",
              data:{
                _token: "{{ csrf_token() }}",
                jenis: jenis,
                alasan: alasan,
                pemohon: pemohon
              },
              success: function(msg){
                if(msg.status == 'OK'){
                  toastr.success("Send "+jenis+" Success", "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                  
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }

                if(jenis == 'plp-request'
                    && msg.status == 'OK'){
                  $('#btn-request').html('');
                  $('#btn-response').append(btnRespons);
                } else if(jenis == 'plp-response'){
                  if(msg.status == 'OK'){
                    $('#btn-response').html('');
                    $('#btn-batal').append(btnBatal);
                  } else if(msg.status == 'REJECT') {
                    $('#btn-response').html('');
                    $('#btn-request').append(btnRequest);
                  }
                } else if(jenis == 'plp-batal'){
                  if(msg.status == 'OK'){
                    $('#btn-batal').html('');
                    $('#btn-batal-response').append(btnBatalResponse);
                  }
                } else if(jenis == 'plp-resbatal'){
                  if( msg.status == 'OK'){
                    $('#btn-batal-response').html('');
                    $('#btn-request').append(btnRequest);
                  } else if (msg.status == 'REJECT') {
                    $('#btn-batal-response').html('');
                    $('#btn-batal').append(btnBatal);
                  }
                }
                $('.btn').prop('disabled', false);
                getTblPlp();
              },
              error:function(jqXHR){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

                $('.btn').prop('disabled', false);
              }
            });
          }
        });

        
      });
      $(document).on('click', '.restorehouse', function(){
        var href = $(this).attr('data-href');

        Swal.fire({			
          title: 'Are you sure?',			
          html:
            "Restore this House?",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, restore!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: href,
              type: "GET",
              success: function(msg){
                if(msg.status == 'OK'){
                  toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                  getTblHouse();
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error: function (jqXHR, exception) {
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            });
          }
        });
      });
      $(document).on('click', '.cdr', function(){
        var id = $(this).attr('data-id');

        $('#frmPrintSuratKuasa #JobShipmentPK').val(id);
      });
      $(document).on('click', '#hitungBilling', function(){
        var mID = "{{ \Crypt::encrypt($item->id) }}";
        $('.btn').prop('disabled', true);
        $.ajax({
          url: "{{ route('manifest.consolidations.update', ['consolidation' => \Crypt::encrypt($item->id)]) }}",
          type: "POST",
          data:{
            _method: "PUT",
            jenis: "calculate"
          },
          success: function(msg){
            console.log(msg);
            if(msg.status == 'OK') {
              $('#jml-ppn').text('').text(formatAsMoney(msg.TotalPPN));
              $('#jml-pph').text('').text(formatAsMoney(msg.TotalPPH));
              $('#jml-bm').text('').text(formatAsMoney(msg.TotalBM));
              $('#jml-bmtp').text('').text(formatAsMoney(msg.TotalBMTP));
              var total = msg.TotalPPN + msg.TotalPPH + msg.TotalBM + msg.TotalBMTP;

              $('#jml-total').text('').text(formatAsMoney(total));
            } else {
              showError(msg.message);
            }
            $('.btn').prop('disabled', false);
          },
          error:function(jqXHR, exception){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
            $('.btn').prop('disabled', false);
          }
        })
      });
      $(document).on('click', '#kirimData', function(){
        var mt = "{{ $item->id }}";

        Swal.fire({			
          title: 'Send All CN?',			
          html:
            "This will send all CN?",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, send!'
        }).then((result) => {
          if (result.value) {
            var jmlRow = $('.jmlRow').length;
            var last = jmlRow;
            var akhir = last;
            var rows = [];

            if(jmlRow > 0){
              $('#kirimData').prop('disabled', true);
              $('.sendbatch').prop('disabled', true);

              $('.jmlRow').each(function(){
                var row = $(this).attr('data-row');
                rows.push(row);
              });
              
              getAjax(rows, rows[0], akhir);
            }
          }
        });
      });
      $(document).on('click', '#kirimCeisa', function(){
        Swal.fire({			
          title: 'Send All CN?',			
          html:
            "This will send all CN?",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, send!'
        }).then((result) => {
          if (result.value) {
            loadingStart();
            $.ajax({
              url: "/manifest/post-data",
              type: "POST",
              data:{
                ceisa: 1,
                hs: "{{ $item->id }}"
              },
              success: function(msg){
                if(msg.status == 'OK'){
                  toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
                loadingStop();
              },
              error: function (jqXHR, exception) {
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                loadingStop();
              }
            })
          }
        });
      });
      $(document).on('click', '#responCeisa', function(){
        Swal.fire({			
          title: 'Tarik Respon BC?',			
          html:
            "Tarik respon terakhir BC?",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, pull!'
        }).then((result) => {
          if (result.value) {
            loadingStart();

            $.ajax({
              url: "{{ route('manifest.consolidations.show', ['consolidation' => \Crypt::encrypt($item->id)]) }}",
              type: "GET",
              data:{
                respon:1,
                ceisa:1
              },
              success:function(msg){
                if(msg.status == 'OK') {
                  showSuccess(msg.message);

                  if($('#tblResSummary').length){
                    getTblResSummary();
                  }
                  if($('#tblResLogs').length){
                    getTblResLogs();
                  }
                } else {
                  showError(msg.message);
                }
                loadingStop();
              },
              error:function(jqXHR, exception){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                loadingStop();
              }
            })
          }
        });
      })
      $('#modal-kirim').on('shown.bs.modal', function (e) {
        $('#hs_kirim').select2({
          placeholder: 'Select...',
          theme: 'bootstrap4',
          multiple: true,
          allowClear: true,
        });
        $('#hs_kirim').val('').trigger('change');
      });
      $(document).on('change paste input', '#hs_text_kirim', function(){
        if($(this).val() != '')
        {
          $('#hs_kirim').prop('required', false);
        } else {
          $('#hs_kirim').prop('required', true);
        }
      })
      $(document).on('click', '#tarik-respon', function(){

        Swal.fire({			
          title: 'Tarik Respon BC?',			
          html:
            "Tarik respon terakhir BC?",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, pull!'
        }).then((result) => {
          if (result.value) {
            $(this).prop('disabled', true);
            loadingStart();

            $.ajax({
              url: "{{ route('manifest.consolidations.show', ['consolidation' => \Crypt::encrypt($item->id)]) }}",
              type: "GET",
              data:{
                respon:1
              },
              success:function(msg){
                if(msg.status == 'OK') {
                  showSuccess(msg.message);

                  if($('#tblResSummary').length){
                    getTblResSummary();
                  }
                  if($('#tblResLogs').length){
                    getTblResLogs();
                  }
                } else {
                  showError(msg.message);
                }
                loadingStop();
                $('#tarik-respon').prop('disabled', false);
              },
              error:function(jqXHR, exception){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                $('#tarik-respon').prop('disabled', false);
                loadingStop();
              }
            })
          }
        });
      });
      $(document).on('click', '.printlabel', function(){
        var mt = $(this).attr('data-mt');

        $('#formPrintLabel #mt').val(mt);

        $('#formPrintLabel').submit();
      });
      $(document).on('click', '#updateNoBC', function(){

        var url = $(this).attr('data-href');

        Swal.fire({			
          title: 'Update No BC?',			
          html:
            "This will send Update No BC to Customs?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Yes, update!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: url,
              type: "POST",
              success: function(msg){
                if(msg.status == 'OK'){
                  showSuccess(msg.message);
                } else {
                  showError(msg.message);
                }
              },
              error:function(jqXHR){
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                showError(jqXHR.status + ' || ' + jsonValue.message);
              }
            })
          }
        });
      });
      $(document).on('click', '.copy', function(){
        var val = $(this).attr('data-val');

        navigator.clipboard.writeText(val).then(function () {
          showSuccess('Copy to Clipboard Success.');
        }, function () {
          showError('Failure to copy. Check permissions for clipboard')
        });
      });
      $(document).on('submit', '#formAgent', function(e){
        e.preventDefault();
        $('#btnSubmitFormAgent').prop('disabled', true);
        $.ajax({
          url: $(this).attr('action'),
          type: "POST",
          data: $(this).serialize(),
          success: function(msg) {
            if(msg.status == 'OK') {
              showSuccess(msg.message);
            } else {
              showError(msg.messag);
            }
            $('#btnSubmitFormAgent').prop('disabled', false);
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            showError(jqXHR.status + ' || ' + jsonValue.message);
            $('#btnSubmitFormAgent').prop('disabled', false);
          }
        })
      });
      $('#formDetails').dirty({
        preventLeaving: true,
      });
    });
  </script>
@endsection
