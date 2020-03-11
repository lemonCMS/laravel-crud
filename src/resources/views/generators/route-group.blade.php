Route::group(
[@if($group['options'] ?? false)
@foreach($group['options'] as $name => $option)
    '{{$name}}' =>
    @if (is_array($option))
        @php(print json_encode($option))
    @else
        '{{$option}}'
    @endif,
@endforeach
@endif]
,function() {
@include('crud::generators.route-case', ['routes' => $group['routes'] ?? []])
})@if ($action['middleware'] ?? false)
->middleware({!! json_encode($action['middleware']) !!})
@endif;
