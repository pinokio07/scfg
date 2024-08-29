@extends('layouts.master')
@section('title') {{Str::title(Request::segment(1))}} @endsection
@section('page_name') Reports @endsection

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
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">Reports</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <form id="formReport" 
                  action="{{ url()->current() }}" 
                  method="get">
              <div class="row">                
                <div class="col-lg-3">
                  <div class="form-group">
                    <label for="jenis">Jenis</label>
                    <select name="jenis" 
                            id="jenis" 
                            class="custom-select"
                            required>
                      <option selected disabled value="">Select...</option>
                      <option value="barang-keluar"
                              @selected(Request::get('jenis') == 'barang-keluar')>
                        Barang Keluar</option>
                      <option value="barang-masuk"
                              @selected(Request::get('jenis') == 'barang-masuk')>
                        Barang Masuk</option>
                      <option value="tidak-dikuasai"
                              @selected(Request::get('jenis') == 'tidak-dikuasai')>
                        Barang Tidak Dikuasai</option>
                      <option value="tidak-dikuasai2"
                              @selected(Request::get('jenis') == 'tidak-dikuasai2')>
                        Barang Tidak Dikuasai Legacy</option>
                      <option value="monev"
                              @selected(Request::get('jenis') == 'monev')>
                        Monev</option>
                      <option value="rekap-plp"
                              @selected(Request::get('jenis') == 'rekap-plp')>
                        Rekapitulasi PLP</option>
                      <option value="status-plp"
                              @selected(Request::get('jenis') == 'status-plp')>
                        Status PLP</option>
                      <option value="timbun"
                              @selected(Request::get('jenis') == 'timbun')>
                        Timbun</option>
                    </select>
                  </div>                  
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label for="period">Periode</label>
                    <input type="text" 
                           name="period" 
                           id="period" 
                           class="form-control daterange"
                           autocomplete="off"
                           value="{{ Request::get('period') ?? '' }}"
                           required>
                  </div>                  
                </div>
                <input type="hidden" name="download" id="download" value="0">
                <div class="col-lg-3">
                  <div class="row">
                    <div class="col-6">
                      <button id="btnPreview" type="button" 
                              class="btn btn-info btn-block elevation-2 mt-0 mt-md-4">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                    <div class="col-6">
                      <button id="btnDownload"
                              type="button" 
                              class="btn btn-primary btn-block elevation-2 mt-0 mt-md-4">
                        <i class="fas fa-download"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </form>   
          </div>
        </div>
      </div>
      <!-- /.col -->
      <div class="col-md-12">
        <div class="card card-info card-outline">
          <div class="card-header">
            <h3 class="card-title">Preview</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tblReport"
                     class="table table-bordered table-striped table-sm" style="width:100%;">
                <thead>
                  @if($thead)
                    {!! $thead !!}
                  @endif
                </thead>
                <tbody>
                  @forelse ($items as $item)
                    <tr>
                      @forelse ($tbody as $body)
                        <td @if(!in_array($body, ['NM_PENERIMA', 'NM_PENGIRIM', 'AL_PENERIMA', 'AL_PENGIRIM'])) class="text-nowrap" @endif>

                        @if($body == 'id')
                        {{ $loop->parent->iteration }}                        
                        @elseif($body == 'UR_BRG')
                        {{ $item->details->first()->UR_BRG ?? "-" }}
                        @elseif($body == 'KODE_TPS')
                        SDVL
                        @elseif($body == 'NO_SPPB')
                        {{ $item->SPPBNumber ?? $item->NO_SPPB ?? "-" }}
                        @elseif($body == 'KODE_GUDANG')
                        TE11
                        @elseif($body == 'OUT_STATUS')
                          @if($item->SCAN_OUT_DATE)
                            @if($item->SCAN_OUT_DATE > $end)
                            BELUM
                            @else
                            SUDAH
                            @endif
                          @else
                          BELUM
                          @endif
                          KELUAR
                        @elseif($body == 'warehouseLine1')
                        {{ optional($item->warehouseLine1)->company_name ?? "-" }}
                        @elseif($body == 'PLPNumber')
                        {{ $item->master->PLPNumber ?? $item->PLPNumber ?? "-"}}
                        @elseif($body == 'ConsolNumber')
                        {{ $item->master->ConsolNumber ?? "-"}}
                        @elseif($body == 'shipment_parse')
                          @forelse ($item->houses as $house)
                            {{ $house->ShipmentNumber }}
                            @if(!$loop->last)
                            <br>
                            @endif
                          @empty                          
                          @endforelse
                        @elseif($body == 'PLPDate')
                          @php
                            $plpDate = $item->master->PLPDate ?? $item->PLPDate;
                            if($plpDate){
                              $plpParse = \Carbon\Carbon::parse($plpDate)->format('d/m/Y');
                            } else {
                              $plpParse = '-';
                            }
                          @endphp
                        {{ $plpParse }}
                        @elseif($jenis == 'status-plp'
                                 && $body == 'SCAN_OUT_DATE')
                          @php
                            if($item->SCAN_OUT_DATE){
                              if($item->SCAN_OUT_DATE > $end){
                                $scanOutParse = '-';
                              } else {
                                $scanOutParse = \Carbon\Carbon::parse($item->SCAN_OUT_DATE)->translatedFormat('d/m/Y H:i');
                              }              
                            } else {
                              $scanOutParse = '-';
                            }
                          @endphp
                          {{ $scanOutParse }}
                        @elseif(\DateTime::createFromFormat('Y-m-d', $item->$body) !== false)
                        {{ \Carbon\Carbon::parse($item->$body)->format('d/m/Y') }}
                        @elseif(\DateTime::createFromFormat('Y-m-d H:i:s', $item->$body) !== false)
                        {{ \Carbon\Carbon::parse($item->$body)->format('d/m/Y H:i') }}
                        @else
                        {{ $item->$body }}
                        @endif

                        </td>
                      @empty                      
                      @endforelse
                    </tr>
                  @empty                    
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /.row -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

@endsection

@section('footer')
  <script>    
    jQuery(document).ready(function(){
      $(document).on('click', '#btnPreview', function(){
        $('#download').val("0");

        $('#formReport').submit();
      });
      $(document).on('click', '#btnDownload', function(){
        $('#download').val("1");

        $('#formReport').submit();
      });
      $('#tblReport').DataTable();
    })
  </script>
@endsection
