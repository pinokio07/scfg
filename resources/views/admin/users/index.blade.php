@extends('layouts.master')
@section('title') Users @endsection
@section('page_name') User Lists @endsection

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Users</h3>
            </div>
            <div class="card-body">      
              @include('buttons.add', ['link' => url()->current().'/create'])
              @include('buttons.download', ['link' => '/administrator/download?model=User'])
              @include('table.ajax')
            </div>
          </div>          
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
@endsection

@section('footer')
  <script>
    jQuery(document).ready(function(){
      $('#dataAjax').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url()->current() }}',
        columns:[
          @forelse ($items as $keys => $item)            
            {data: "{{$keys}}", name: "{{$keys}}"},
          @empty
          @endforelse          
        ]
      });
    })
  </script>
@endsection