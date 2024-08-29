<table class="table table-sm table-responsive-sm" id="dataTable" style="width: 100%;">
  @if(!$items->isEmpty())
    <thead>
      <tr>
        <?php $content = $items->first(); $keys = array_keys($content->toArray()); ?>
        @forelse($keys as $k)
          @if($k == 'id')
            <th>
              @if(isset($from) && $from == 'menu')
                No
              @else              
                <input type="checkbox" class="check-all">
              @endif
            </th>
          @else
            <th>{{Str::title($k)}}</th>
          @endif
        @empty
          <th>Empty Data</th>
        @endforelse
        <th class="text-right">Action</th>
      </tr>    
    </thead>
    <tbody>
      @forelse ($items as $key => $item)
        <tr>
          @forelse ($keys as $ky)
            <td>
              @if($ky == 'avatar')
                <img src="/img/users/{{$item->$ky}}" class="img-fluid img-circle elevation-2" style="max-height: 80px; width:auto;" alt="Foto User">
              @elseif($ky == 'roles')
                @forelse ($item->roles as $role)
                  {{$role->name}}
                @empty
                  -
                @endforelse                
              @elseif($ky == 'id')
                @if(isset($from) && $from == 'menu')
                {{$key + 1}}
                @else
                <input type="checkbox" name="id" id="id_{{$item->index}}">
                @endif
              @else
              {{$item->$ky}}
              @endif
            </td>
          @empty
            <td>Empty Items</td>
          @endforelse
          <td class="text-right text-nowrap">
            
            @if(isset($from) && $from == 'menu')
            <a href="{{url()->current()}}/{{$item->id}}/builder" class="btn btn-xs elevation-2 btn-success"><i class="fas fa-list"></i> Builder</a>            
            @elseif(isset($from) && $from == 'companies')
            <a href="{{url()->current()}}/{{$item->id}}/branches" class="btn btn-xs elevation-2 btn-success"><i class="fas fa-warehouse"></i> Branches</a>     
            @else
              @include('buttons.view', ['link' => url()->current().'/'.$item->id])
            @endif
            
            @include('buttons.edit', ['link' => url()->current().'/'.$item->id.'/edit'])            
            @include('buttons.delete', ['link' => url()->current().'/'.$item->id])
          </td>
        </tr>
      @empty
        
      @endforelse
    </tbody>
  @endif
</table>

@section('footer')
  <script>
    $(function(){      
      var indexLastColumn = $('#dataTable').find('tr')[0].cells.length-1;
      $('#dataTable').DataTable({
        responsive:true,        
        columnDefs:[
          {
            orderable: false,
            targets: [
              @if(!isset($from) || $from != 'menu') 0, @endif
              indexLastColumn
              ]
          }
        ]
      });
    });
  </script>
@endsection