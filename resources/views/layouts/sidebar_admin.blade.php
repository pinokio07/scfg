@php
$base = Request::segment(1); 
$sub = Request::segment(2);
$menu = getMenu('admin');
@endphp

<ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
  
  
  @if($menu != '')
    @forelse ($menu->parent_items as $item)      
      @if($item->title == 'Dashboard')
        <li class="nav-item">
          <a href="{{ $item->link() }}" class="nav-link @if($base == 'administrator' && $sub == '') active @endif">
            <i class="fas fa-tachometer-alt nav-icon"></i>
            <p>{{ Str::title($item->title)}}</p>
          </a>
        </li>
      @else
        <?php $linkActive = Str::replace(' ', '-', Str::lower($item->title)); ?>
        @if($item->children->isEmpty())            
          <li class="nav-item">
            <a href="{{ $item->link() }}" class="nav-link @if($sub == $linkActive) active @endif">
              <i class="{{ $item->icon_class ?? 'fas fa-cirle' }} nav-icon"></i>
              <p>{{ Str::title($item->title)}}</p>
            </a>
          </li>
        @else
          <li class="nav-header">{{ Str::title($item->title) }}</li>
          @foreach ($item->children as $subItem)
          <?php $subActive = Str::replace(' ', '-', Str::lower($subItem->title)); ?>
            @if($subItem->children->isEmpty())
              <li class="nav-item">
                <a href="{{ $subItem->link() }}" class="nav-link @if($sub2 == $subActive) active @endif">
                  <i class="nav-icon {{ $subItem->icon_class ?? 'fas fa-cirle' }}"></i>
                  <p>{{ Str::title($subItem->title) }}</p>
                </a>
              </li>
            @else
              <li class="nav-item">
                <a href="{{ $subItem->link() }}" class="nav-link">
                  <i class="nav-icon {{ $subItem->icon_class ?? 'fas fa-cirle' }}"></i>
                  <p>{{ Str::title($subItem->title) }}</p>
                  <i class="right fas fa-angle-left"></i>
                </a>
                <ul class="nav nav-treeview">
                  @foreach($subItem->children as $deepSub)
                    <?php $deepSubActive = Str::replace(' ', '-', Str::lower($deepSub->title)); ?>
                    <li class="nav-item">
                      <a href="{{ $deepSub->link() }}" class="nav-link @if($sub3 == $deepSubActive) active @endif">
                        <i class="{{ $deepSub->icon_class ?? 'fas fa-cirle' }} nav-icon"></i>
                        <p>{{ Str::title($deepSub->title) }}</p>
                      </a>
                    </li>
                  @endforeach
                </ul>
              </li>
            @endif
          @endforeach
        @endif
      @endif
      
    @empty
    
    @endforelse
  @endif
</ul>