@foreach($routes as $route => $values)
@includeWhen(!isset($values['type']), 'crud::generators.route-action', ['name' => $route, 'action' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='resource', 'crud::generators.route-resource', ['name' => $route, 'resource' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='middleware', 'crud::generators.route-middleware', ['name' => $route, 'middleware' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='group', 'crud::generators.route-group', ['name' => $route, 'group' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='namespace', 'crud::generators.route-namespace', ['name' => $route, 'namespace' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='prefix', 'crud::generators.route-prefix', ['name' => $route, 'prefix' => $values])
@includeWhen(isset($values['type']) && $values['type'] ==='action', 'crud::generators.route-action', ['name' => $route, 'action' => $values])
@endforeach
