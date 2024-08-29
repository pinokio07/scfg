@php
$base = Request::segment(1);
$pat = '/'.request()->path();
$menu = getMenu('sidebar_'.$base);
@endphp

<ul class="nav nav-pills nav-sidebar flex-column nav-compact nav-child-indent nav-collapse-hide-child" data-widget="treeview" role="menu" data-accordion="false">
  @if($base == 'dashboard')
    <li class="nav-item">
      <a href="/dashboard" class="nav-link @if($base == 'dashboard') active @endif">
        <i class="fas fa-tachometer-alt nav-icon"></i>
        <p>Dashboard</p>
      </a>
    </li>
  @endif
  @if($menu != '')
    @forelse ($menu->parent_items->where('active', true) as $item)
      <?php $linkActive = $item->link(); ?>
      @if($item->children->isEmpty())
        @if($linkActive == '#')
          <li class="nav-header">{{ Str::title($item->title) }}</li>
        @else
          @can($item->permission)            
            <li class="nav-item">
              <a href="{{ $linkActive }}" class="nav-link {{ ($pat == $linkActive) ? 'active' : '' }}">
                <i class="{{ $item->icon_class ?? 'far fa-cirle' }} nav-icon"></i>
                <p>{{ Str::upper($item->title)}}</p>
              </a>
            </li>
          @endcan
        @endif
      @else
        <li class="nav-header">{{ Str::title($item->title) }}</li>
        @foreach ($item->children->where('active', true) as $subItem)
          @if($subItem->children->isEmpty())
            <?php $subActive = $subItem->link(); ?>
            @if($subActive == '#')
              <li class="nav-header">{{ Str::title($subItem->title) }}</li>
            @else
              @can($subItem->permission)              
              <li class="nav-item">
                <a href="{{ $subActive }}" class="nav-link {{ ($pat == $subActive) ? 'active' : '' }}">
                  <i class="{{ $subItem->icon_class ?? 'far fa-cirle' }} nav-icon "></i>
                  <p>{{ Str::upper($subItem->title) }}</p>
                </a>
              </li>
              @endcan
            @endif
          @else
            <?php $subActive = $subItem->link(); ?>
            <li class="nav-item {{ ($pat == $subActive) ? 'menu-open' : '' }}">
              <a href="{{ $subActive }}" class="nav-link">
                <i class="nav-icon {{ $subItem->icon_class ?? 'far fa-cirle' }}"></i>
                <p>{{ Str::upper($subItem->title) }}
                  <i class="right fas fa-angle-left"></i>
                </p>                
              </a>
              <ul class="nav nav-treeview">
                @foreach($subItem->children->where('active', true) as $deepSub)
                  <?php $deepSubActive = $deepSub->link(); ?>
                  @can($deepSub->permission)
                  <li class="nav-item">
                    <a href="{{ $deepSubActive }}" class="nav-link {{ ($pat == $deepSubActive) ? 'active' : '' }}">
                      <i class="nav-icon {{ $deepSub->icon_class ?? 'far fa-cirle' }}"></i>
                      <p>{{ Str::upper($deepSub->title) }}</p>
                    </a>
                  </li>
                  @endcan
                @endforeach
              </ul>
            </li>
          @endif
        @endforeach
      @endif
    @empty
    
    @endforelse
  @endif
</ul>