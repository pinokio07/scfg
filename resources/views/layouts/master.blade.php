<?php $subDomain = subDomain(); $activeCompany = activeCompany(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'TPS')}} | @yield('title')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Icon -->
  <link rel="icon" href="{{ asset('/img/default-logo-dark.png')}}">  
  
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/dist/css/adminlte.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/toastr/toastr.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
  <!-- daterange picker -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/daterangepicker/daterangepicker.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">

  <!-- jQuery -->
  <script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="{{ asset('adminlte') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  {{-- <!-- Popper -->
  <script src="{{ asset('adminlte') }}/plugins/popper/popper.min.js"></script> --}}
  <!-- AdminLTE App -->
  <script src="{{ asset('adminlte') }}/dist/js/adminlte.min.js"></script>
  <!-- Dirty Form -->
  <script src="{{ asset('adminlte') }}/dist/js/jquery.dirty.js"></script>
  <!-- SweetAlert2 -->
  <script src="{{ asset('adminlte') }}/plugins/sweetalert2/sweetalert2.min.js"></script>
  <!-- Toastr -->
  <script src="{{ asset('adminlte') }}/plugins/toastr/toastr.min.js"></script>
  <!-- Select2 -->
  <script src="{{ asset('adminlte') }}/plugins/select2/js/select2.full.min.js"></script>
  <!-- DataTables -->
  <script src="{{asset('adminlte')}}/plugins/datatables/jquery.dataTables.js"></script>
  <script src="{{asset('adminlte')}}/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
  <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
  <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
  <script src="{{ asset('adminlte') }}/plugins/jszip/jszip.min.js"></script>
  <script src="{{ asset('adminlte') }}/plugins/pdfmake/pdfmake.min.js"></script>
  <script src="{{ asset('adminlte') }}/plugins/pdfmake/vfs_fonts.js"></script>
  <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
  <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/buttons.print.min.js"></script>
  <script src="{{ asset('adminlte') }}/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
  <!-- InputMask -->
  <script src="{{asset('adminlte')}}/plugins/moment/moment.min.js"></script>
  <script src="{{asset('adminlte')}}/plugins/inputmask/jquery.inputmask.min.js"></script>
  <!-- date-range-picker -->
  <script src="{{asset('adminlte')}}/plugins/daterangepicker/daterangepicker.js"></script>
  <!-- Tempusdominus Bootstrap 4 -->
  <script src="{{asset('adminlte')}}/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
  <!-- PDF Js -->
  {{-- <script src="{{ asset('adminlte/plugins/pdfjs/build/pdf.js') }}"></script> --}}
  <style>
    label{
      margin-bottom: 0px !important;
    }
    .modal-xls {
      max-width: 90vmax;
    }
    .bg-senator{
      background-color: #1f4e78;
      color: #fff;
    }    
  </style>
  @if($subDomain == 'fms'
      && request()->segment(1) != 'administrator')
  <style>
    .navbar-dark .navbar-nav .nav-link{
      color: #FFFFFF !important;
    }

    .navbar-dark .navbar-nav .nav-link:hover{
      color: rgb(171, 170, 170) !important;
    }

    [class*=sidebar-dark-]{
      color: #1f4e78 !important;
    }

    [class*=sidebar-dark-] .nav-header{
      color: #FFFFFF !important;
    }

    [class*=sidebar-dark-] .sidebar a{
      color: #FFFFFF !important;
    }

    [class*=sidebar-dark] .form-control-sidebar{
      background-color: #FFFFFF !important;
      border: 1px solid #FFFFFF !important;
      color:  #1f4e78 !important;
    }

    [class*=sidebar-dark] .btn-sidebar{
      background-color: #1f4e78 !important;
      border: 1px solid #FFFFFF !important;
      color: #FFFFFF !important;
    }

    [class*=sidebar-dark] .user-panel{
      border-bottom: 1px solid white;
    }

    .layout-navbar-fixed .wrapper .sidebar-dark-primary .brand-link:not([class*=navbar]){
      background-color: #1f4e78 !important;
      border-bottom: 1px solid white;
    }    
  </style>
  @endif
  @yield('header')
  
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed sidebar-collapse text-sm">
  
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <?php $navClass = (request()->segment(1) == 'administrator') ? 'navbar-primary navbar-dark border-bottom-0' : (($subDomain == 'fms') ? 'bg-senator navbar-dark text-bold border-bottom-0' : 'navbar-white navbar-light'); ?>
  <nav class="main-header navbar navbar-expand  {{ $navClass }}">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>            
      @if(request()->segment(1) != 'administrator')
        @include('layouts.mainmenu')
      @endif
    </ul>
    
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      @if(request()->segment(1) != 'administrator')
        <div class="d-block d-sm-none">
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <i class="fas fa-th-large"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">            
                @include('layouts.mainmenu_sm')
            </div>
          </li>
        </div>
      @endif
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">                
        <a class="nav-link" data-toggle="dropdown" href="#">  
          @if(!Auth::user()->hasRole('super-admin') && $activeCompany != '')
            {{ $activeCompany->GC_Name ?? '' }}
          @endif
          <i class="far fa-user ml-1"></i>          
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">@if(Auth::check()) {{auth()->user()->name}} @else Guest @endif</span>          
          <div class="dropdown-divider"></div>
          <a href="{{(Auth::user()->hasRole('super-admin')) ? '/administrator/profile' : '/profile'}}" class="dropdown-item">
            Profile
          </a>
          <div class="dropdown-divider"></div>
          <a href="/logout" class="dropdown-item dropdown-footer">Logout</a>
        </div>
      </li>      
    </ul>
  </nav>
  <!-- /.navbar -->
  <?php $sideClass = (request()->segment(1) == 'administrator') ? 'sidebar-light-primary' : 'sidebar-dark-primary'; ?>
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar {{ $sideClass }} @if($subDomain == 'fms' && request()->segment(1) != 'administrator') bg-senator @endif elevation-4">
    <!-- Brand Logo -->    
    <a href="/dashboard" class="brand-link">
      @php
          if(request()->segment(1) == 'administrator'){
            $src = asset('/img/default-logo-dark.png');
          } else {
            $src = asset('/img/default-logo-light.png');
          }
      @endphp
      <img src="{{ $src }}" alt="Logo Icon" class="brand-image elevation-3" style="opacity: .8">
      <span class="brand-text @if($subDomain == 'fms') text-bold text-white @else font-weight-light @endif">{{ config('app.name', 'TPS')}}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{ Auth::user()->getAvatar() }}" class="img-circle elevation-2" alt="{{ Auth::user()->name }}">
        </div>
        <div class="info">
          <a href="{{(request()->segment(1) == 'administrator') ? '/administrator/profile' : '/profile'}}" class="d-block">{{ Str::title( Auth::user()->name ) }}</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar search-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2 mb-4">
        @if(request()->segment(1) == 'administrator')
          @include('layouts.sidebar_admin')
        @else
          @include('layouts.sidebar')
        @endif
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">    
    <!-- Main content -->
    <section class="content-header">
      <div class="container-fluid">
        @if(!in_array(Request::segment(2), ['scan-in', 'scan-out']))
        <div class="row mb-2">
          <div class="col-sm-6 d-none d-sm-block">
            <h1>               
              @yield('page_name', Str::title(request()->path()))
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <?php $link = "" ?>
              @for($i = 1; $i <= count(Request::segments()); $i++)
                @php
                  if(strlen(Request::segment($i)) > 20){
                    $linkText = "Item";
                  } else {
                    $linkText = Request::segment($i);
                  }
                @endphp
                @if($i < count(Request::segments()) & $i > 0)
                  <?php $link .= "/" . Request::segment($i); ?>                    
                  <li class="breadcrumb-item"><a href="<?= $link ?>">{{ ucwords(str_replace('-',' ',$linkText))}}</a></li>
                @else 
                  <li class="breadcrumb-item">{{ucwords(str_replace('-',' ',$linkText))}}</li>
                @endif
              @endfor              
            </ol>
          </div>
        </div>
        @endif
      </div><!-- /.container-fluid -->
    </section>
    @yield('content')
    <!-- /.content -->
    
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; 2022 <a href="{{ config('app.url') }}">{{ config('app.name') }}</a>.</strong> All rights reserved.
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'X-Requested-With' : 'XMLHttpRequest'
    }
  });
  (function() {
    'use strict';
    window.addEventListener('load', function() {
      // Fetch all the forms we want to apply custom Bootstrap validation styles to
      var forms = document.getElementsByClassName('needs-validation');
      // Loop over them and prevent submission
      var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {          
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            $('.collapse').collapse('show');
            toastr.error("Please complete the required fields", "Failed!", {timeOut: 6000, closeButton: true,progressBar: true});
          }
          form.classList.add('was-validated');          
        }, false);
      });
    }, false);
  })();
  @if(Session::has('sukses'))
    toastr.success("{!!Session::get('sukses')!!}", "Success!", {timeOut: 3000, closeButton: true,progressBar: true})   
  @elseif(Session::has('gagal'))
    toastr.error("{!!Session::get('gagal')!!}", "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
  @endif
  
  function resetForm($form) {
      $form.find('input:text, input:password, input:file, select, textarea').val('');
      $form.find('input:radio, input:checkbox')
          .removeAttr('checked').removeAttr('selected');
  }

  function pdfjs(url, cid) {

    // The workerSrc property shall be specified.
    pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('adminlte/plugins/pdfjs/build/pdf.worker.js' )}}";

    // Asynchronous download of PDF
    var loadingTask = pdfjsLib.getDocument(url);
    loadingTask.promise.then(function(pdf) {
      console.log('PDF loaded');
      
      // Fetch the first page
      var pageNumber = 1;
      pdf.getPage(pageNumber).then(function(page) {
        console.log('Page loaded');
        
        var scale = 1.5;
        var viewport = page.getViewport({scale: scale});

        // Prepare canvas using PDF page dimensions
        var canvas = document.getElementById('canvas-'+cid);
        var context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        // Render PDF page into canvas context
        var renderContext = {
          canvasContext: context,
          background: 'rgba(0,0,0,0)',
          viewport: viewport
        };
        var renderTask = page.render(renderContext);
        renderTask.promise.then(function () {
          console.log('Page rendered');
        });
      });
    }, function (reason) {
      // PDF loading error
      console.error(reason);
    });
  }

  function pdfjsPages(url, cid) {
    // The workerSrc property shall be specified.
    pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('adminlte/plugins/pdfjs/build/pdf.worker.js' )}}";

    var pdfDoc = null,
        pageNum = 1,
        pageRendering = false,
        pageNumPending = null,
        scale = 1.5,
        canvas = document.getElementById('canvas-'+cid),
        ctx = canvas.getContext('2d');

    /**
     * Get page info from document, resize canvas accordingly, and render page.
     * @param num Page number.
     */
    function renderPage(num) {
      pageRendering = true;
      // Using promise to fetch the page
      pdfDoc.getPage(num).then(function(page) {
        var viewport = page.getViewport({scale: scale});
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        // Render PDF page into canvas context
        var renderContext = {
          canvasContext: ctx,
          viewport: viewport
        };
        var renderTask = page.render(renderContext);

        // Wait for rendering to finish
        renderTask.promise.then(function() {
          pageRendering = false;
          if (pageNumPending !== null) {
            // New page rendering is pending
            renderPage(pageNumPending);
            pageNumPending = null;
          }
        });
      });

      // Update page counters
      document.getElementById('page_num_'+cid).textContent = num;
    }

    /**
     * If another page rendering in progress, waits until the rendering is
     * finised. Otherwise, executes rendering immediately.
     */
    function queueRenderPage(num) {
      if (pageRendering) {
        pageNumPending = num;
      } else {
        renderPage(num);
      }
    }

    /**
     * Displays previous page.
     */
    function onPrevPage() {
      if (pageNum <= 1) {
        return;
      }
      pageNum--;
      queueRenderPage(pageNum);
    }
    document.getElementById('prev_'+cid).addEventListener('click', onPrevPage);

    /**
     * Displays next page.
     */
    function onNextPage() {
      if (pageNum >= pdfDoc.numPages) {
        return;
      }
      pageNum++;
      queueRenderPage(pageNum);
    }
    document.getElementById('next_'+cid).addEventListener('click', onNextPage);

    /**
     * Asynchronously downloads PDF.
     */
    pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
      pdfDoc = pdfDoc_;
      document.getElementById('page_count_'+cid).textContent = pdfDoc.numPages;

      // Initial/first page rendering
      renderPage(pageNum);
    });
  }

  function formatAsMoney(n) {
    var absValue = Math.abs(n);    
    var string = (Number(absValue).toFixed(2) + '').split('.');
    var returnString = string[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ',' + (string[1] || '00');

    return n < 0 ? '(' + returnString + ')' : returnString;
  }

  function loadingStart() {
    $("#loading-image").show();
  }

  function loadingStop() {
    $("#loading-image").hide();
  }

  function select2bs4() {
    $('.select2bs4').select2({
      placeholder: 'Select...',
      theme: 'bootstrap4'
    });
  }

  function select2bs4Clear() {
    $('.select2bs4clear').select2({
      placeholder: 'Select...',
      theme: 'bootstrap4',
      allowClear: true,
    });
  }

  // function showTab(){
  //   if(window.location.hash){
  //     var tujuan = window.location.hash        
  //   } else {
  //     var tujuan = "#main-data-content";
  //   }

  //   $('a[href="' + tujuan + '"]').trigger('click');
  // }

  $(function(){
    var hash = window.location.hash;
    // hash && $('ul.nav a[href="' + hash + '"]').tab('show');
    hash && $('ul.nav a[href="' + hash + '"]').trigger('click');

    $('.nav-tabs a').click(function (e) {
      $(this).tab('show');
      var scrollmem = $('body').scrollTop() || $('html').scrollTop();
      window.location.hash = this.hash;
      $('html,body').scrollTop(scrollmem);
    });
  });

  function cryptThis(id) {
    var encrypted = null;
    
    $.ajax({
      url: "{{ route('crypt.this') }}",
      type: "POST",
      data: {
        id: id,
      },
      success: function (msg) {
        if(msg.status == 'OK'){
          encrypted = msg.encrypted;
        } else {
          toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
        }
      },
      error:function(jqXHR){
        jsonValue = jQuery.parseJSON( jqXHR.responseText );
        toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
      },
      async: false
    });

    return encrypted;
  }

  function gotoView(viewId){
      document.getElementById(viewId).scrollIntoView();
      window.location.hash = viewId;
  }
  
  $(function(){
    
    //Bootstrap Tooltip
    $('[data-toggle="tooltip"]').tooltip();

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      placeholder: 'Select...',
      theme: 'bootstrap4'
    });

    $('.select2bs4clear').select2({
      placeholder: 'Select...',
      theme: 'bootstrap4',
      allowClear: true,
    });

    //Initialize Select2 Elements
    $('.select2bs4multiple').select2({
      placeholder: 'Select...',
      theme: 'bootstrap4',
      allowClear: true
    });
    
    //Input Mask
    $('[data-mask]').inputmask();

    $(".money").inputmask({
      alias: "currency",
      digits: 2,
      groupSeparator: ',',
      rightAlign: 1,
      reverse: true,
      autoUnmask: true,
      removeMaskOnSubmit: true
    });

    $('.numeric').inputmask('numeric', {
      groupSeparator: ',',
      rightAlign: false,
      allowMinus: false,
      autoUnmask: true,
      removeMaskOnSubmit: true
    });
    $('.desimal').inputmask('decimal', {
      groupSeparator: ',',
      digits: 2,
      digitsOptional: false,
      placeholder: "0",
      allowMinus: false,
      autoUnmask: true,
      removeMaskOnSubmit: true
    });    
    $('.berat').inputmask({
      alias: "decimal",
      groupSeparator: ',',
      rightAlign: false,
      integerDigits: 5,
      digits: 4,
      digitsOptional: false,
      placeholder: "0",
      allowMinus: false,
      autoUnmask: true,
      removeMaskOnSubmit: true
    });
    
    //Date range picker
    $('.daterange').daterangepicker({
      autoApply: true,
      autoUpdateInput: false,
      minYear: 2020,
      locale: {
          format: 'YYYY-MM-DD'
      }
    }).on("apply.daterangepicker", function (e, picker) {
        picker.element.val(picker.startDate.format(picker.locale.format) + ' - ' + picker.endDate.format(picker.locale.format));
    });    

  });
  jQuery(document).ready(function(){
    $(document).on('click', '.delete', function(){
      var href = $(this).data('href');		

			Swal.fire({			
				title: 'Are you sure?',			
				html: 
          '<form id="hapus" action="'+href+'" method="POST">'+
          '{{ csrf_field() }}'+
          '<input type="hidden" name="_method" value="DELETE">'+
          '</form>'+
          "You won't be able to revert this!",
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cancel',
				confirmButtonText: 'Yes, delete!'
			}).then((result) => {
				if (result.value) {
          $('#hapus').submit();
				}
			});
    });
    $(document).on('click', '.restore', function(){
      var href = $(this).attr('data-href');
      var ajx = $(this).attr('data-ajx');

			Swal.fire({			
				title: 'Are you sure?',			
				html:
          "Restore this Item?",
        icon: "question",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cancel',
				confirmButtonText: 'Yes, restore!'
			}).then((result) => {
				if (result.value) {
          if(ajx != undefined){
            $.ajax({
              url: href,
              type: "GET",
              success: function(msg){
                if(msg.status == 'OK'){
                  toastr.success(msg.message, "Success!", {timeOut: 3000, closeButton: true,progressBar: true});
                } else {
                  toastr.error(msg.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
                }
              },
              error: function (jqXHR, exception) {
                jsonValue = jQuery.parseJSON( jqXHR.responseText );
                toastr.error(jqXHR.status + ' || ' + jsonValue.message, "Failed!", {timeOut: 3000, closeButton: true,progressBar: true});
              }
            })
          } else {
            location.replace(href);
          }          
				}
			});
    });
    $(document).on('select2:open', (e) => {
      const selectId = e.target.id

      $(".select2-search__field[aria-controls='select2-" + selectId + "-results']").each(function (
          key,
          value,
      ) {
          value.focus()
      })
    });

    $('[data-widget="sidebar-search"]').SidebarSearch({
      @if(Request::segment(1) == 'administrator')
      highlightClass: 'text-dark',
      @else
      highlightClass: 'text-light',
      @endif
    });

    $(document).on('change', '.onlyone', function(){
      var temen = $(this).attr('data-temen');

      if(temen != undefined){
        if($(this).is(':checked')){
          $('#'+temen).prop('checked', false);
        }        
      } else {
        $('.onlyone').not($(this)).prop('checked', false);
      }      
    });

    @if(isset($diff) && $diff > 76)

     Swal.fire({			
				title: 'Please update your password!',				
        @if($diff >= 90)
        allowOutsideClick: false,
        html: 'Your password is already expired, please change it!',
        @else
        html: 'Your password will expired in {{ (90 - $diff) }} days!',
        @endif
        confirmButtonText: 'Yes, change!'
			}).then((result) => {
				if (result.value) {
          location.replace("{{ route('profile') }}");
				}
			});
    @endif
			
  });
</script>
@yield('footer')
</body>
</html>
