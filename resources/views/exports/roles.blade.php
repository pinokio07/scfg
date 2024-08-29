<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Permissions</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($items as $item)      
      <tr>
        <td rowspan="{{ $item->permissions->count() }}"
            style="vertical-align: top;">{{ Str::title($item->name) }}</td>
        @forelse ($item->permissions as $permission)
          <td>{{ Str::title(Str::replace('_', ' ', $permission->name)) }}</td>
        </tr>
        <tr>
        @empty
        @endforelse
        
      </tr>
    @empty
      
    @endforelse
  </tbody>
</table>