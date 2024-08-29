<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;

class RolesUsersExport implements FromView, WithTitle, ShouldAutoSize
{  
  
  public function view(): View
  {
    $items = User::whereHas('roles', function($q){
                      return $q->where('name', '<>', 'super-admin')
                              ->with('permissions');
                  })
                  ->with(['roles' => function($q){
                    $q->where('name', '<>', 'super-admin')
                      ->with('permissions');
                  }])
                  ->orderBy('name')
                  ->get();

      return view('exports.rolesuser', compact(['items']));
  }

  public function title(): string
  {
      return "Users";
  }
}
