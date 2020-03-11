@php

$controller = $resource['controller'] ?? \Str::studly(\Str::plural($route)) . 'Controller';

@endphp

@if($resource['actions'] ?? false)
    @foreach($resource['actions'] as $action)
        Route::{{$action['method']}}('{{$action['path']}}', '{{$controller}}{{'@'}}{{$action['action']}}')
        @if ($action['middleware'] ?? false)
            ->middleware({!! json_encode($action['middleware']) !!})
        @endif;
    @endforeach
@endif

Route::resource('{{\Str::kebab(\Str::plural($resource['path'] ?? $route))}}', '{{$controller}}')@if($resource['options'] ?? false)
@foreach($resource['options'] as $name => $option)
    ->{{$name}}(@if (is_array($option))@php(print json_encode($option))@else'{{$option}}'@endif)
@endforeach
@endif;

