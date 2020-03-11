Route::namespace('{{$namespace['namespace']}}')
@if ($namespace['prefix'] ?? false)
->prefix('{{$namespace['prefix']}}')
@endif
->group(function () {
    @include('crud::generators.route-case', ['routes' => $namespace['routes'] ?? []])
});
