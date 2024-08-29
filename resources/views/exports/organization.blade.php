<table>
  <thead>
    <tr>
      @forelse ($headers as $h)
        @if($loop->first)
          <th>No</th>
        @elseif(!in_array($h, $exc))
          <th @if($h == 'OH_Code') style="background-color: red;" @endif>{{ $h }}</th>
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
          @elseif(!in_array($head, $exc))
            <td @if($head == 'OH_Code') style="background-color: red;" @endif>{{ $item->$head }}</td>         
          @endif
        @empty          
        @endforelse        
      </tr>      
    @empty      
    @endforelse
  </tbody>
</table>