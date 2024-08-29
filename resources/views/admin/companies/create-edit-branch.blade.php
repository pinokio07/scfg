@extends('layouts.master')

@section('title') Branch @endsection
@section('page_name')
 <i class="fas fa-warehouse"></i> @if($branch->id != '') Edit @else New @endif Branch 
@endsection
@section('header')
  <style>
    label{
      margin-bottom: 0px !important;
    }
  </style>
@endsection
@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      @if($branch->id != '')
        <form action="/administrator/companies/{{ $company->id }}/branches/{{ $branch->id }}" 
              method="post" enctype="multipart/form-data">        
          @method('PUT')
      @else
        <form action="/administrator/companies/{{ $company->id }}/branches" 
              method="post" enctype="multipart/form-data">   
      @endif      
        @csrf
        <div class="row">
          <div class="col-12 col-md-8">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  {{ $company->GC_Name }} Branch
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-6 col-md-3">
                    <div class="form-group form-group-sm">
                      <label for="CB_Code">Branch Code</label>
                      <input type="text" name="CB_Code" id="CB_Code" 
                             class="form-control form-control-sm"
                             value="{{ old('CB_Code') ?? $branch->CB_Code ?? '' }}"
                             required
                             {{ $disabled }}>
                    </div>
                  </div>
                  <div class="col-12 col-md-9">
                    <div class="form-group form-group-sm">
                      <label for="CB_FullName">Full Name</label>
                      <input type="text" name="CB_FullName" id="CB_FullName" 
                             class="form-control form-control-sm"
                             value="{{ old('CB_FullName') ?? $branch->CB_FullName ?? '' }}"
                             required
                             {{ $disabled }}>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-group form-group-sm">
                      <label for="CB_Address">Address</label>
                      <textarea name="CB_Address" id="CB_Address" rows="3"
                                class="form-control form-control-sm"
                                {{ $disabled }}
                                required>{{ old('CB_Address') ?? $branch->CB_Address ?? '' }}</textarea>                      
                    </div>
                  </div>
                  <div class="col-6 col-md-4">
                    <div class="form-group form-group-sm">
                      <label for="CB_Phone">Phone</label>
                      <input type="text" name="CB_Phone" id="CB_Phone" 
                             class="form-control form-control-sm"
                             value="{{ old('CB_Phone') ?? $branch->CB_Phone ?? '' }}"
                             required
                             {{ $disabled }}>
                    </div>
                  </div>
                  <div class="col-6 col-md-4">
                    <div class="form-group form-group-sm">
                      <label for="CB_City">City</label>
                      <input type="text" name="CB_City" id="CB_City" 
                             class="form-control form-control-sm"
                             value="{{ old('CB_City') ?? $branch->CB_City ?? '' }}"
                             required
                             {{ $disabled }}>
                    </div>
                  </div>
                  <div class="col-6 col-md-4">
                    <div class="form-group form-group-sm">
                      <label for="CB_WhCode">Warehouse Code</label>
                      <input type="text" name="CB_WhCode" id="CB_WhCode" 
                             class="form-control form-control-sm"
                             value="{{ old('CB_WhCode') ?? $branch->CB_WhCode ?? '' }}"
                             required
                             {{ $disabled }}>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 col-md-4">
                    @if($disabled == 'false')
                      <button type="submit" class="btn btn-success btn-block elevation-2">
                        <i class="fas fa-save"></i> Save
                      </button>
                    @else
                      <a href="/administrator/companies/{{ $company->id }}/branches/{{ $branch->id }}/edit" class="btn btn-warning btn-block elevation-2">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                    @endif
                  </div>
                  <div class="col-6 col-md-4">
                    <a href="/administrator/companies/{{ $company->id }}/branches"
                       class="btn btn-default btn-block elevation-2">Cancel</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection
