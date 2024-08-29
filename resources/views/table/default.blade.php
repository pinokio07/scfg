<table class="table table-sm table-responsive text-nowrap" id="dataTable" style="width: 100%;">
  @if(!$items->isEmpty())
    <thead>
      <tr>
        <?php $content = $items->first(); $keys = array_keys($content->toArray()); ?>
        @forelse($keys as $k)
          @if($k == 'id')
            <th>              
              No
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
              @if($ky == 'id')
               {{$loop->parent->iteration}}
              @else
              {{$item->$ky}}
              @endif
            </td>
          @empty
            <td>Empty Items</td>
          @endforelse
            <?php (Request::has('page')) 
                  ? $linkDelete = url()->current().'/'.$item->getKey().'?page='.Request::get('page') 
                  : $linkDelete= url()->current().'/'.$item->getKey(); ?>
          <td class="text-right text-nowrap">                        
            @include('buttons.view', ['link' => url()->current().'/'.$item->getKey()])
            @include('buttons.edit', ['link' => url()->current().'/'.$item->getKey().'/edit'])
            @include('buttons.delete', ['link' => $linkDelete])
          </td>
        </tr>
      @empty        
      @endforelse
    </tbody>
    <tfoot>
      <tr>        
        @forelse($keys as $kf)
          @if($kf == 'id')
            <th>              
              No
            </th>
          @else
            <th>{{Str::title($kf)}}</th>
          @endif
        @empty
          <th>Empty Data</th>
        @endforelse
        <th class="text-right">Action</th>
      </tr> 
    </tfoot>
  @endif
</table>