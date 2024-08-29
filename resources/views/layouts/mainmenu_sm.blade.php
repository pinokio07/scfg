<?php $items = getMenu('main_menu'); ?>

@if($items != '')
  @foreach ($items->parent_items as $item)
    @can($item->permission)
      <a href="{{ $item->link() }}" class="dropdown-item">
        {{ $item->title }}
      </a>
    @endcan    
  @endforeach
@endif

@if(Auth::user()->hasRole('super-admin'))

  <div class="dropdown-divider"></div>
  <a href="/administrator" class="dropdown-item dropdown-footer">Admin</a>  

@endif