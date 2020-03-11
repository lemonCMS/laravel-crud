namespace App\Http\Controllers\{{$controller[0]['meta']['namespace']}};

use App\Crud\Http\Controllers\CrudControllerTrait;
use App\Http\Controllers\Controller;
use Request;

Class {{$controller[0]['meta']['controller']}} extends Controller
{
    use CrudControllerTrait;

    @foreach($controller as $entry)
        @includeWhen('action' === ($entry['type'] ?? false), 'crud::generators.controllers.action', ['action' => $entry])
        @foreach(($entry['actions'] ?? []) as $action)
            @include('crud::generators.controllers.action', ['action' => $action])
        @endforeach
    @endforeach

    /*----------------------------------------
    |
    | Add extra clauses on to the query build
    | E.g. only return the resources of the
    | authorized user.
    |
    */
    // protected function withQuery(Builder $query)
    // {
    //     //TODO change this to match your relation.
    //     return $query->whereHas('users', function (Builder $hasQuery) {
    //         $hasQuery->where('user_id', Request::user()->id);
    //     });
    // }
}
