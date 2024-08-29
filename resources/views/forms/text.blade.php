<div class="form-group form-group-sm">
  <label for="{{$idf ?? ''}}">{{$label ?? ''}}</label>
  <input type="text" name="{{$name ?? ''}}" id="{{$idf ?? ''}}" class="form-control form-control-sm" placeholder="{{$label ?? ''}}" @isset($value) value="{{$value}}" @endif
    @isset($prop)
      @foreach ($prop as $p)
        {{$p}}
      @endforeach
    @endisset
  >
</div>