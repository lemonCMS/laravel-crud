@foreach($routes as $route => $values)
@includeWhen(!isset($values['type']), 'generators.route-action', ['name' => $route, 'action' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='resource', 'generators.route-resource', ['name' => $route, 'resource' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='middleware', 'generators.route-middleware', ['name' => $route, 'middleware' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='group', 'generators.route-group', ['name' => $route, 'group' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='namespace', 'generators.route-namespace', ['name' => $route, 'namespace' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='action', 'generators.route-action', ['name' => $route, 'action' => $values])
@endforeach
