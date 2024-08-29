@extends('layouts.master')
@section('title') SPPB On Demand @endsection
@section('page_name') SPPB On Demand @endsection

@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-4">
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">SPPB</h3>
            <div class="card-tools">  
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <form id="formSPPB" method="POST">
              @csrf
              <div class="form-group form-group-sm">
                <label for="jenis">Jenis Aju</label>
                <select name="jenis" id="jenis"
                        class="form-control form-control-sm"
                        required>
                  <option value="sppbpib">PIB</option>
                  <option value="sppbbc23">BC23</option>
                  <option value="sppbbc16">BC16</option>
                  <option value="manual">Manual</option>
                </select>
              </div>
              <div id="kode_doc" 
                   class="form-group form-group-sm d-none">
                <label for="kd_doc">Kode Dokumen</label>
                <select name="kd_doc" id="kd_doc"
                        class="select2bs4clear"
                        style="width:100%;">
                  <option value=""></option>
                  @forelse ($kodeDocs as $kd)
                    <option value="{{ $kd->kode }}">{{ $kd->kode }} - {{ $kd->uraian }}</option>
                  @empty                    
                  @endforelse
                </select>
              </div>
              <div class="form-group form-group-sm">
                <label for="no_sppb">Nomor SPPB</label>
                <input type="text" 
                       name="no_sppb" 
                       id="no_sppb" 
                       class="form-control form-control-sm"
                       required>
              </div>
              <div class="form-group form-group-sm">
                <label for="tgl_sppb">Tanggal SPPB</label>
                <input type="date" 
                       name="tgl_sppb" 
                       id="tgl_sppb" 
                       class="form-control form-control-sm"
                       required>
              </div>
              <div id="npwp" class="form-group form-group-sm">
                <label for="npwp_imp">NPWP IMP</label>
                <input type="text" 
                       name="npwp_imp" 
                       id="npwp_imp" 
                       class="form-control form-control-sm"
                       required>
              </div>
            </form>
          </div>
          <div class="card-footer">
            <button type="submit"
                    form="formSPPB"
                    class="btn btn-sm btn-primary btn-block elevation-2">
              <i class="fas fa-save"></i> Submit
            </button>
          </div>
        </div>
      </div>
      <!-- /.col -->
      <div class="col-md-4">
        <div class="card card-success card-outline">
          <div class="card-header">
            <h3 class="card-title">SPPB Result</h3>
            <div class="card-tools">  
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <tr>
                  <td style="width:120px;">CAR</td>
                  <td style="width:1px;">:</td>
                  <td id="hasil_car"></td>
                </tr>
                <tr>
                  <td>No SPPB</td>
                  <td>:</td>
                  <td id="hasil_no_sppb"></td>
                </tr>
                <tr>
                  <td>Tanggal SPPB</td>
                  <td>:</td>
                  <td id="hasil_tgl_sppb"></td>
                </tr>
                <tr>
                  <td>PIB</td>
                  <td>:</td>
                  <td id="hasil_pib"></td>
                </tr>
                <tr>
                  <td>Tanggal PIB</td>
                  <td>:</td>
                  <td id="hasil_tgl_pib"></td>
                </tr>
              </table>
            </div>
          </div>
          <div class="card-footer">
            
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
      $(document).on('change', '#jenis', function(){
        if($(this).find(':selected').val() == 'manual'){
          $('#kode_doc').removeClass('d-none');
          $('#kd_doc').prop('required', true);
          $('#npwp_imp').prop('required', false);
          $('#npwp').addClass('d-none')
        } else {
          $('#kode_doc').addClass('d-none');
          $('#kd_doc').val('').trigger('change');
          $('#kd_doc').prop('required', false);
          $('#npwp').removeClass('d-none');
          $('#npwp_imp').prop('required', true);
        }
      });
      $(document).on('submit', '#formSPPB', function(e){
        e.preventDefault();
        var jenis = $('#jenis').val();
        var data = $(this).serialize();

        $('.btn').prop('disabled', true);

        $.ajax({
          url: "{{ route('scheduler') }}",
          type: "GET",
          data: data,
          success: function(msg){
            if(msg.status == 'OK'){

              toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});

              $('#hasil_car').text(msg.sppb[0].CAR);
              $('#hasil_no_sppb').text(msg.sppb[0].NO_SPPB);
              $('#hasil_pib').text(msg.sppb[0].NO_PIB);

              if(msg.sppb[0].TGL_SPPB){
                var sppbDate = moment(msg.sppb[0].TGL_SPPB).format('DD/MM/YYYY');
              } else {
                var sppbDate = '-';
              }
              if(msg.sppb[0].TGL_PIB){
                var pibDate = moment(msg.sppb[0].TGL_PIB).format('DD/MM/YYYY');
              } else {
                var pibDate = '-';
              }

              $('#hasil_tgl_sppb').text(sppbDate);
              $('#hasil_tgl_pib').text(pibDate);

            } else {

              toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            }

            $('.btn').prop('disabled', false);
          },
          error:function(jqXHR){
            jsonValue = jQuery.parseJSON( jqXHR.responseText );
            toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});

            $('.btn').prop('disabled', false);
          }
        })
      });
    });
  </script>
@endsection
