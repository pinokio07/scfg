<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Username</th>
      <th>Email</th>
      <th>Roles</th>
    </tr>
  </thead>
  <tbody>
   @forelse ($items as $item)
     @php
        $roleCount = $item->roles->count();
     @endphp
     <tr>
      <td rowspan="{{ $roleCount }}"      
          style="vertical-align: top;">{{ Str::title($item->name) }}</td>
      <td rowspan="{{ $roleCount }}"      
          style="vertical-align: top;">{{ $item->username }}</td>
      <td rowspan="{{ $roleCount }}"      
          style="vertical-align: top;">{{ $item->email }}</td>
      @forelse ($item->roles->sortBy('name') as $role)
        <td>{{ $role->name }}</td>

        @if(!$loop->last)
          </tr>
          <tr>
        @endif
      @empty
        
      @endforelse
      </tr>
   @empty
     
   @endforelse
  </tbody>
</table>