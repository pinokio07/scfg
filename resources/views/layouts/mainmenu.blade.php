<?php $items = getMenu('main_menu'); ?>

@if($items != '')
  @foreach ($items->parent_items as $item)    
    @can($item->permission)
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ $item->link() }}" class="nav-link">{{ $item->title }}</a>
      </li>
    @endcan
  @endforeach
@endif

@if(Auth::user()->hasRole('super-admin'))

  <li class="nav-item d-none d-sm-inline-block">
    <a href="/administrator" class="nav-link text-primary">Admin</a>
  </li>

@endif
