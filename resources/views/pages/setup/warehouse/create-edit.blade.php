@extends('layouts.master')
@section('title') Warehouse @endsection
@section('page_name') Warehouse @endsection

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
              <h3 class="card-title">Warehouse</h3>
            </div>
            @php
                if($item->id){
                  $url = route('setup.bonded-warehouses.update', ['bonded_warehouse' => $item->id]);
                } else {
                  $url = route('setup.bonded-warehouses.store');
                }
            @endphp
            <form action="{{ $url }}"
                  method="post"
                  class="needs-validation"
                  novalidate>              
              @csrf
              @if($item->id)
                @method('PUT')
              @endif
            <div class="card-body">
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group form-group-sm">
                    <label for="company_name">Company Name</label>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="company_name" 
                           id="company_name" 
                           required 
                           value="{{ old('company_name') ?? $item->company_name ?? '' }}">
                  </div>
                </div>
                <div class="col-lg-1">
                  <div class="form-group form-group-sm">
                    <label for="tps_code">TPS Code</label>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="tps_code" 
                           id="tps_code" 
                           required 
                           value="{{ old('tps_code') ?? $item->tps_code ?? '' }}">
                  </div>
                </div>
                <div class="col-lg-1">
                  <div class="form-group form-group-sm">
                    <label for="warehouse_code">Warehouse Code</label>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="warehouse_code" 
                           id="warehouse_code" 
                           required 
                           value="{{ old('warehouse_code') ?? $item->warehouse_code ?? '' }}">
                  </div>
                </div>
                <div class="col-lg-1">
                  <div class="form-group form-group-sm">
                    <label for="tariff">Storage Tariff</label>
                    <input type="text" 
                           class="form-control form-control-sm desimal" 
                           name="tariff" 
                           id="tariff" 
                           required 
                           value="{{ old('tariff') ?? $item->tariff ?? 0 }}">
                  </div>
                </div>               
              </div>
              <div class="row">
                <div class="col-lg-7">
                  <div class="form-group form-group-sm">
                    <label for="address">Warehouse Address</label>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="address" 
                           id="address"
                           value="{{ old('address') ?? $item->address ?? '' }}">
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-sm btn-success elevation-2">Save</button>
            </div>
            </form>
          </div>          
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection