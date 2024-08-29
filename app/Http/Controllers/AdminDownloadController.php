<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Exports\RolesExport;
use App\Exports\UsersExport;
use Excel;

class AdminDownloadController extends Controller
{
    public function download(Request $request)
    {
      $model = $request->model;
      switch ($model) {
        case 'Roles':
          return Excel::download( new RolesExport(), $model.'.xlsx');
          break;
        case 'User':
          return Excel::download( new UsersExport(), $model.'.xlsx');
          break;
        default:
          # code...
          break;
      }
    }
}
