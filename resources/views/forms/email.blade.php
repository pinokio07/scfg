<div class="form-group form-group-sm">
  <label for="{{$idf ?? ''}}">{{$label ?? ''}}</label>
  <input type="email" name="{{$name ?? ''}}" id="{{$idf ?? ''}}" class="form-control form-control-sm" placeholder="{{$label ?? ''}}"
    @isset($prop)
      @foreach ($prop as $p)
        {{$p}}
      @endforeach
    @endisset
  >
</div>