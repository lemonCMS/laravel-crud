namespace App\Events\{{$namespace}};

use LemonCMS\LaravelCrud\Events\CrudEvent;
use Illuminate\Http\Request;

class {{$event}} extends CrudEvent
{
    /**
     * {{$event}} constructor.
     *
     * @param $id
     * @param string $model
     */
    public function __construct($id, string $model)
    {
        parent::__construct($id, $model);
    }

    /**
    * @param $id
    * @param string $model
    * @param array $payload
    * @return DeleteEvent|CrudEvent
    */
    public static function fromPayload($id, $model, array $payload)
    {
        return new self(
            $id,
            $model
        );
    }

    /**
    * @return array
    */
    public function toPayload(): array
    {
        return [
            'id' => $this->getId()
        ];
    }

    /**
    * @param Request $request
    * @return array
    */
    public static function rules(Request $request): array
    {
        return [];
    }
}
