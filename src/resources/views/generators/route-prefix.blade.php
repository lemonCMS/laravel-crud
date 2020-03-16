Route::prefix('{{$prefix['prefix']}}')
@if($prefix['namespace'] ?? false)->namespace('{{$prefix['namespace']}}')@endif
@if($prefix['middleware'] ?? false)->middleware(<?php /** @var array|string $prefix */ echo json_encode($prefix['middleware']); ?>)@endif
->group(function () {
    @include('crud::generators.route-case', ['routes' => $prefix['routes'] ?? []])
});
