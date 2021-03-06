namespace App\Listeners\{{$namespace}};

use LemonCMS\LaravelCrud\Listeners\CrudListener;
use App\Events\{{$namespace}}\{{$event}};
use App\Models\{{$model}};

class {{$listener}} extends CrudListener
{
    /**
     * Typecasting $this->event
     *
     * @var {{$event}}
     */
    protected $event;

    /**
     * Handle the event.
     *
     * @param {{$event}} $event
     * @return void
     */
    public function handle({{$event}} $event)
    {
        $this->process($event);
    }

    public function beforeRun()
    {
        //todo add some code
    }

    public function afterSave()
    {
        //todo add some code
    }

    /**
    *
    * Access data from event with magic functions
    * public function set{StudlyProperty}($value)
    *
    */
    // public function setName($value)
    // {
    //    $this->entity->name = $value;
    // }
}
