Route::middleware(@php
    /** @var TYPE_NAME $middleware */
echo json_encode($middleware['middleware'])
@endphp)->group(function () {
    @include('crud::generators.route-case', ['routes' => $middleware['routes'] ?? []])
});
