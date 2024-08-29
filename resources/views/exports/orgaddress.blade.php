<table>
  <thead>
    <tr>
      @forelse ($headers as $h)
        @if($loop->first)
          <th>No</th>
        @elseif(!in_array($h, $exc))
          <th>{{ $h }}</th>
        @endif
      @empty        
      @endforelse
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $keys => $item)
      <tr>
        @forelse ($headers as $head)
          @if($loop->first)
            <td>{{ $loop->parent->iteration }}</td>
          @elseif(!in_array($head, $exc) && !in_array($head, $ref))
            <td>{{ $item->$head }}</td>
          @elseif($head == 'OA_OH')
            <td>{{ optional($item->header)->OH_Code }}</td>          
          @endif
        @empty          
        @endforelse        
      </tr>      
    @empty      
    @endforelse
  </tbody>
</table>