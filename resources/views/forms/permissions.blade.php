@section('header')
  <style>
    /* Remove default bullets */
    ul, #treeUl {
      list-style-type: none;
    }

    /* Remove margins and padding from the parent ul */
    #treeUl {
      margin: 0;
      padding: 0;
    }

    /* Style the caret/arrow */
    .caret {
      cursor: pointer;
      user-select: none; /* Prevent text selection */
    }

    /* Create the caret/arrow with a unicode, and style it */
    .caret::before {
      content: "\229E";
      color: black;
      display: inline-block;
      margin-right: 6px;
    }

    /* Rotate the caret/arrow icon when clicked on (using JavaScript) */
    .caret-down::before {
      content: "\25A2";
      /* transform: rotate(90deg); */
    }

    /* Hide the nested list */
    .nested {
      display: none;
    }

    /* Show the nested list when the user clicks on the caret/arrow (with JavaScript) */
    .active {
      display: block;
    }
  </style>
@endsection

<?php $rolePermissions = $role->permissions->pluck('name')->toArray(); ?>
<div class="row">
  @forelse ($permissions->groupBy('group') as $key => $permission)
  <div class="col-12">
    <ul id="treeUl">
      <li>
        <span class="caret">          
          <label>{{Str::title($key)}}</label>          
        </span>
        <input type="checkbox" class="check-all all_{{$key}}" data-key="{{$key}}">
        <ul class="nested">          
          @foreach ($permission->sortBy('name') as $p)
            <li>
              <div class="form-check">
                <input class="form-check-input child_{{$key}} child" type="checkbox" name="permission[]" value="{{$p->name}}" @if(in_array($p->name, $rolePermissions)) checked @endif data-key="{{$key}}">
                <label class="form-check-label card-text">{{$p->name}}</label>
              </div>
            </li>
          @endforeach
        </ul>
      </li>
    </ul>
  </div>
  @empty
    Permissions Empty
  @endforelse 
</div>