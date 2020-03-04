Route::namespace('{{$namespace['namespace']}}')
@if ($namespace['prefix'] ?? false)
->prefix('{{$namespace['prefix']}}')
@endif
->group(function () {
    @include('generators.route-case', ['routes' => $namespace['routes'] ?? []])
});
