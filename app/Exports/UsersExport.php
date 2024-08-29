<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;

class UsersExport implements FromView, WithTitle, ShouldAutoSize
{
    public function view(): View
    {
      
        $items = User::with(['roles'])->get();

        return view('exports.users', compact(['items']));
    }

    public function title(): string
    {
        return "Users";
    }
}
