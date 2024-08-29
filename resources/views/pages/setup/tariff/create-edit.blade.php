@extends('layouts.master')
@section('title') Tariff @endsection
@section('page_name') Tariff @endsection

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
              <h3 class="card-title">Tariff</h3>
            </div>
            @php
                if($item->id){
                  $url = route('setup.tariff-schema.update', ['tariff_schema' => $item->id]);
                } else {
                  $url = route('setup.tariff-schema.store');
                }
            @endphp
            <form action="{{ $url }}" method="post">              
              @csrf
              @if($item->id)
                @method('PUT')
              @endif
            <div class="card-body">
              <div class="row">
                <div class="col-lg-4">
                  <div class="form-group form-group-sm">
                    <label for="name">Name</label>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="name" 
                           id="tariff_name" 
                           required 
                           value="{{ old('name') ?? $item->name ?? '' }}">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group form-group-sm">
                    <label for="name">Minimum Tariff</label>
                    <input type="text" 
                           class="form-control form-control-sm desimal" 
                           name="minimum" 
                           id="tariff_minimum" 
                           required 
                           value="{{ old('minimum') ?? $item->minimum ?? 0 }}">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="form-group form-group-sm">
                    <label for="vat">VAT</label>
                    <select name="vat" 
                            id="vat" 
                            class="custom-select custom-select-sm">
                      <option value=""
                        @selected($item->vat == null)>No VAT</option>
                      <option value="11"
                        @selected($item->vat == 11)>11 %</option>
                      <option value="1.1"
                        @selected($item->vat == 1.1)>1.1 %</option>
                    </select>
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

        @if($item->id)

        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Tariff Items</h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-sm text-nowrap" style="width: 100%;">
                  <thead>
                    <tr>
                      <th>Urut</th>
                      <th>Name</th>
                      <th>Rate</th>                      
                      <th>Column Calculated</th>
                      <th>Days</th>
                      <th>Options</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($item->schema->sortBy('urut') as $schema)
                      <tr id="kolom_{{ $loop->iteration }}" class="kolom">
                        <form action="{{ route('schema.update', ['schema' => $schema->id]) }}"
                              method="POST">
                          @csrf
                          @method('PUT')  
                        <td>
                          <select name="urut" 
                                  id="urut_{{ $loop->iteration }}" 
                                  class="custom-select custom-select-sm"
                                  >
                            @for ($i = 1; $i <= $item->schema->count(); $i++)
                              <option value="{{ $i }}"
                                @selected($i == $schema->urut)>{{ $i }}</option>
                            @endfor
                          </select>
                        </td>
                        <td>
                          <input type="text" 
                                  name="name" 
                                  id="name_{{ $loop->iteration }}" 
                                  class="form-control form-control-sm"
                                  required
                                  value="{{ $schema->name ?? '' }}">
                        </td>
                        <td>
                          <input type="text" 
                                  name="rate" 
                                  id="rate_{{ $loop->iteration }}" 
                                  class="form-control form-control-sm desimal"
                                  required
                                  value="{{ $schema->rate ?? '' }}">
                        </td>                        
                        <td>
                          <select name="column" 
                                  id="column_{{ $loop->iteration }}" 
                                  class="custom-select custom-select-sm">
                            <option value="ChargeableWeight"
                              @selected($schema->column == 'ChargeableWeight')>Chargable</option>
                            <option value="BRUTO"
                              @selected($schema->column == 'BRUTO')>Gross</option>
                            <option value="NETTO"
                              @selected($schema->column == 'NETTO')>Netto</option>
                            <option value="CHARGE"
                              @selected($schema->column == 'CHARGE')>Total Storage</option>
                            <option value="HAWB"
                              @selected($schema->column == 'HAWB')>Jumlah HAWB</option>
                            <option value="CDC"
                              @selected($schema->column == 'CDC')>CDC</option>
                          </select>
                        </td>
                        <td>
                          <input type="text" 
                                  name="days" 
                                  id="days_{{ $loop->iteration }}" 
                                  class="form-control form-control-sm numeric"
                                  required
                                  value="{{ $schema->days ?? 0 }}">
                        </td>
                        <td>
                          <div class="form-group form-group-sm mb-0">
                            <div class="form-check">
                              <input class="form-check-input onlyone" 
                                     type="checkbox"
                                     id="as_one_{{ $loop->iteration }}"
                                     name="as_one"
                                     data-temen="is_fixed_{{ $loop->iteration }}"
                                     value="1"
                                     @checked($schema->as_one == true)>
                              <label class="form-check-label"
                                     for="as_one_{{ $loop->iteration }}">Count as 1 Day</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input onlyone"
                                     type="checkbox"
                                     id="is_fixed_{{ $loop->iteration }}"
                                     name="is_fixed"
                                     data-temen="as_one_{{ $loop->iteration }}"
                                     value="1"
                                     @checked($schema->is_fixed == true)>
                              <label class="form-check-label"
                                     for="is_fixed_{{ $loop->iteration }}">Fixed Rate</label>
                            </div>                          
                          </div>
                        </td>
                        <td>
                          <button type="submit"
                                  class="btn btn-primary btn-xs elevation-2">
                            <i class="fas fa-save"></i>
                          </button>
                          <button type="button"
                                  class="btn btn-danger btn-xs elevation-2 delete"
                                  data-row="{{ $loop->iteration }}"
                                  data-id="{{ $schema->id }}"
                                  data-href="{{ route('schema.destroy', ['schema' => $schema->id]) }}">
                            <i class="fas fa-trash"></i>
                          </button>              
                        </td>
                        </form>
                      </tr>
                    @empty                          
                    @endforelse
                    <tr>
                      <td colspan="100%"></td>
                    </tr>
                    <tr>
                      <td colspan="100%" 
                          style="border-top: solid 1px black;background-color:white;">Create New Item</td>
                    </tr>
                    <tr>
                      <form action="{{ route('schema.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tariff_id" value="{{ $item->id }}">
                      <td>
                        <select name="urut" 
                                id="urut_{{ $item->schema->count() + 1 }}" 
                                class="custom-select custom-select-sm"
                                >
                          @for ($i = 1; $i <= ($item->schema->count() + 1); $i++)
                            <option value="{{ $i }}"
                                    @selected($i == ($item->schema->count() + 1))>{{ $i }}</option>
                          @endfor
                        </select>
                        </td>
                      <td>
                        <input type="text" 
                                name="name" 
                                id="name_{{ $item->schema->count() + 1 }}" 
                                class="form-control form-control-sm"
                                required
                                value="">
                      </td>
                      <td>
                        <input type="text" 
                                name="rate" 
                                id="rate_{{ $item->schema->count() + 1 }}" 
                                class="form-control form-control-sm desimal"
                                required
                                value="">
                      </td>                      
                      <td>
                        <select name="column" 
                                  id="column_{{ $item->schema->count() + 1 }}" 
                                  class="custom-select custom-select-sm">
                          <option value="ChargeableWeight">Chargable</option>
                          <option value="BRUTO">Gross</option>
                          <option value="NETTO">Netto</option>
                          <option value="CHARGE">Total Storage</option>
                          <option value="HAWB">Jumlah HAWB</option>
                          <option value="CDC">CDC</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" 
                                name="days" 
                                id="days_{{ $item->schema->count() + 1 }}" 
                                class="form-control form-control-sm numeric"
                                required
                                value="0">
                      </td>
                      <td>
                        <div class="form-group form-group-sm mb-0">
                          <div class="form-check">
                            <input class="form-check-input onlyone" 
                                   type="checkbox"
                                   id="as_one_{{ $item->schema->count() + 1 }}"
                                   name="as_one"
                                   data-temen="is_fixed_{{ $item->schema->count() + 1 }}"
                                   value="1">
                            <label class="form-check-label"
                                   for="as_one_{{ $item->schema->count() + 1 }}">Count as 1 Day</label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input onlyone"
                                   type="checkbox"
                                   id="is_fixed_{{ $item->schema->count() + 1 }}"
                                   name="is_fixed"
                                   data-temen="as_one_{{ $item->schema->count() + 1 }}"
                                   value="1">
                            <label class="form-check-label"
                                   for="is_fixed_{{ $item->schema->count() + 1 }}">Fixed Rate</label>
                          </div>                          
                        </div>
                      </td>
                      <td>
                        <button type="submit"
                                class="btn btn-primary btn-xs elevation-2">
                          <i class="fas fa-save"></i>
                        </button>                                         
                      </td>
                      </form>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>          
        </div>

        @endif

      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection