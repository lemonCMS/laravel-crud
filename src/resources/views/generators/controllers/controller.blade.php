namespace App\Http\Controllers\{{$namespace}}

use App\Crud\Http\Controllers\CrudControllerTrait;
use App\Http\Controllers\Controller;

Class {{$controllerClass}} extends Controller
{
    use CrudControllerTrait;

    @foreach($actions as $action)

    /**
    *
    * @param Request $request
    */
    public function {{$action}}(Request $request) {
        // @TODO Implemend code
        // When this is a default laravel function
        // e.g. index, store, update, show, purge
        // You may delete this function to make use
        // of the CrudController trait
    }

    @endforeach


    /*----------------------------------------
    |
    | Default we say this controller is private
    | You can change or remove this function
    |
    */
    public function isPrivate(): boolean
    {
        return true;
    }
}
