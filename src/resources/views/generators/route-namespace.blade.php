Route::namespace('{{$namespace['namespace']}}')
@if ($namespace['prefix'] ?? false)->prefix('{{$namespace['prefix']}}')@endif
@if ($namespace['middleware'] ?? false)->prefix(<?php /** @var array|string $namespace */ echo json_encode($namespace['middleware']); ?>)@endif
->group(function () {
    @include('crud::generators.route-case', ['routes' => $namespace['routes'] ?? []])
});
