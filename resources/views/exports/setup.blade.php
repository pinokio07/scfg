<table>
  <thead>
    <tr>
      <th>No</th>
      @forelse ($headers as $h)

        @if(!in_array($h, $exc))
          <th @if($h == 'id') style="background-color: red;" @endif>{{ $h }}</th>
        @endif

      @empty
      @endforelse
    </tr>
  </thead>
  <tbody>
    @forelse ($data as $keys => $d)
      <tr>
        <td>{{ $loop->iteration }}</td>
        @forelse ($headers as $head)

          @if(!in_array($head, $exc))
            <td @if($head == 'id') style="background-color: red;" @endif>{{ $d->$head }}</td>
          @endif

        @empty          
        @endforelse        
      </tr>      
    @empty      
    @endforelse
  </tbody>
</table>