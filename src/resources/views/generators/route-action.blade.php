Route::{{$action['method']}}('{{$action['path']}}', '{{$action['action']}}')
@if ($action['middleware'] ?? false)
->middleware({!! json_encode($action['middleware']) !!})
@endif;
