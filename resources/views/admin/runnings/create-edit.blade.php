@extends('layouts.master')
@section('title') Running Code @endsection
@section('page_name') Running Code @endsection
@section('header')
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/editable/css/bootstrap-editable.css') }}"> 
  <style type="text/css">
    .kecil{
      max-width: 80px;
    }
  </style> 
@endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Running Code</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            @if($running_code->id != '')
              <form action="/administrator/running-codes/{{$running_code->id}}" method="post">
                @method('PUT')
            @else
              <form action="/administrator/running-codes" method="post">
            @endif
                @csrf                
            <div class="card-body">

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
                <div class="col-12 col-md-6">
                  <div class="row">
                    <div class="col-12">
                      <div class="form-group form-group-sm">
                        <label for="title">Title</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="title" 
                               id="title"
                               placeholder="Running Code Title"
                               required 
                               {{ $disabled }}
                               value="{{ old('title') ?? $running_code->title ?? '' }}">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-group form-group-sm">
                        <label for="module">Module</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="module" 
                               id="module" 
                               placeholder="Running Code Module Ex. AR"
                               {{ $disabled }}
                               value="{{ old('module') ?? $running_code->module ?? '' }}">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-group form-group-sm">
                        <label for="name">Name</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="name" 
                               id="name"
                               placeholder="Running Code Name Ex. INV"
                               {{ $disabled }}
                               value="{{ old('name') ?? $running_code->name ?? '' }}">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="row">
                    <div class="col-12">
                      <div class="form-group form-group-sm">
                        <label for="pattern">Pattern <small class="text-danger">ends with N</small></label>
                        <input type="text" 
                              class="form-control form-control-sm" 
                              name="pattern" 
                              id="pattern" 
                              placeholder="Ex. INV%m%Y%N"
                              {{ $disabled }}
                              value="{{ old('pattern') ?? $running_code->pattern ?? '' }}">
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="form-group form-group-sm">
                        <label for="reset">Reset Every</label>
                        <select name="reset" 
                                id="reset" 
                                class="custom-select custom-select-sm"
                                {{ $disabled }}>
                          <option value="daily"
                            @selected($running_code->reset == 'daily')>Daily</option>
                          <option value="month"
                            @selected($running_code->reset == 'month')>Monthly</option>
                          <option value="year"
                            @selected($running_code->reset == 'year')>Yearly</option>
                          <option value="never"
                            @selected($running_code->reset == 'never')>Never</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="form-group form-group-sm">
                        <label for="leading_zero">Leading Zero</label>
                        <input type="number" 
                              class="form-control form-control-sm" 
                              name="leading_zero" 
                              id="leading_zero"
                              {{ $disabled }}
                              value="{{ old('leading_zero') ?? $running_code->leading_zero ?? '1' }}">
                      </div>
                    </div>                    
                    <div class="col-12">
                      <small>
                        <b>Pattern usage:</b><br>                        
                        Use % as separator; m = Month; Rm = Roman Month; y = Year; d = Day; N = Number of Leading Zero; <br><b>Ex. INV%m%Y%N</b>
                      </small>
                    </div>
                  </div>                  
                </div>                
              </div>              
            </div>
            <div class="card-footer">
              @if($disabled == 'disabled')
              <a href="{{ url()->current() }}/edit" class="btn btn-sm btn-warning elevation-2">Edit</a>
              @else
              <button type="submit" class="btn btn-sm btn-success elevation-2">Save</button>
              @endif
              <a href="/administrator/running-codes" class="btn btn-default btn-sm elevation-2">Cancel</a>
            </div>
            </form>
          </div>          
        </div>
        @if($running_code->details->count() > 0)
        <div class="col-md-12">
          <div class="card card-success card-outline">
            <div class="card-header">
              <h3 class="card-title">Current Running Codes</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-sm text-nowrap" style="width: 100%;">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Date</th>
                      <th>Month</th>
                      <th>Year</th>
                      <th>Sequence</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($running_code->details as $detail)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detail->date }}</td>
                        <td>{{ $detail->month }}</td>
                        <td>{{ $detail->year }}</td>
                        <td>
                          <a href="#" class="sequence" 
                             data-type="text" 
                             data-pk="{{$detail->id}}" 
                             data-url="/api/editsequence" 
                             data-title="Edit Sequence"
                             value="{{ $detail->sequence ?? 0 }}">{{ $detail->sequence }}</a>
                        </td>
                      </tr>
                    @empty
                      
                    @endforelse
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

@section('footer')
  <script src="{{ asset('adminlte/plugins/editable/js/bootstrap-editable.min.js') }}"></script>
  <script>
    jQuery(document).ready(function(){
      $('.sequence').editable({
        mode: 'inline',
        onblur: 'submit', 		
        savenochange : false,
        showbuttons: false,
        inputclass: 'kecil form-control form-control-sm',
        validate: function(value) {
          if(!$.isNumeric(value)) {
              return " Please input numeric.";
          } else if(value == 0){
            return " Minimum value is 1.";
          }
        },
        success:function(msg){
          if(msg == "OK"){
            toastr.success("Edit Sequence Success", "Success!", {timeOut: 6000, closeButton: true})
          }
        },
      });
    });
  </script>
@endsection
