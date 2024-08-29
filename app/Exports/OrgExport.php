<?php

namespace App\Exports;

use App\Models\OrgHeader;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrgExport implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): view
    {
      $headers = collect(OrgHeader::first())->keys();
      $exc = [
        'OH_SystemLastEditTimeUtc',
        'OH_SystemLastEditUser',
        'OH_SystemCreateTimeUtc',
        'OH_SystemCreateUser',
        'created_at',
        'updated_at',
        'deleted_at'
      ];

      $items = OrgHeader::all();

      return view('exports.organization', compact(['headers', 'exc', 'items']));

    }
}
