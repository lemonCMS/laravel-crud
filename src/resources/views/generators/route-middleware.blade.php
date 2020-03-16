Route::middleware(@php
    /** @var array $middleware */
echo json_encode($middleware['middleware'])
@endphp)
@if($middleware['namespace'] ?? false)->namespace('{{$middleware['namespace']}}')@endif
@if($middleware['prefix'] ?? false)->prefix('{{$middleware['prefix']}}')@endif
->group(function () {
    @include('crud::generators.route-case', ['routes' => $middleware['routes'] ?? []])
});
