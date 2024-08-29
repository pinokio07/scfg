<?php

namespace App\Exports;

use Spatie\Permission\Models\Role;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;

class RolesListExport implements FromView, WithTitle, ShouldAutoSize
{  
  
  public function view(): View
  {
    
      $items = Role::with(['permissions', 'users'])
                       ->where('name', '<>', 'super-admin')
                       ->orderBy('name')
                       ->get();

      return view('exports.roles', compact(['items']));
  }

  public function title(): string
  {
      return "Roles";
  }
}
