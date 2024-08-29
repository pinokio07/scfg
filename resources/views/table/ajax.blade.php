<table class="table table-sm text-nowrap" id="dataAjax" style="width: 100%;">
  @if(!$items->isEmpty())
    <thead>
      <tr>
        @forelse($items as $header)
          @if($header == 'id')
            <th>              
              No              
            </th>
          @elseif($header == 'cekbox')
            <th>              
              <input type="checkbox"
                     id="selectall">
            </th>
          @else
            <th>{{$header}}</th>
          @endif
        @empty
          <th>Empty Data</th>
        @endforelse        
      </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
      <tr>
        @forelse($items as $foot)
          @if($foot == 'id')
            <th>              
              No              
            </th>
          @elseif($header == 'cekbox')
            <th>              
              <input type="checkbox"
                      id="selectall">
            </th>
          @else
            <th>{{$foot}}</th>
          @endif
        @empty
          <th>Empty Data</th>
        @endforelse        
      </tr>
    </tfoot>
  @endif
</table>

