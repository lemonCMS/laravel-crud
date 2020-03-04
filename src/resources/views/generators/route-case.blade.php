@foreach($routes as $route => $values)
@includeWhen($values['type'] ==='resource', 'generators.route-resource', ['name' => $route, 'resource' => $values])
@includeWhen($values['type'] ==='middleware', 'generators.route-middleware', ['name' => $route, 'middleware' => $values])
@includeWhen($values['type'] ==='group', 'generators.route-group', ['name' => $route, 'group' => $values])
@includeWhen($values['type'] ==='namespace', 'generators.route-namespace', ['name' => $route, 'namespace' => $values])
@includeWhen($values['type'] ==='action', 'generators.route-action', ['name' => $route, 'action' => $values])
@endforeach
