Route::resource('{{\Str::kebab(\Str::plural($resource['path'] ?? $route))}}', '{{\Str::studly(\Str::plural($route))}}Controller'@if($resource['options'] ?? false),[
@foreach($resource['options'] as $name => $option)
    '{{$name}}' =>
    @if (is_array($option))
        @php(print json_encode($option))
    @else
        '{{$option}}'
    @endif
@endforeach
]@endif);

