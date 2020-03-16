namespace App\Events\{{$namespace}};

use LemonCMS\LaravelCrud\Events\CrudEvent;

class {{$event}} extends CrudEvent
{
    /**
     * {{$event}} constructor.
     *
     * @param $id
     */
    public function __construct($id)
    {
        parent::__construct($id);
    }

    public static function fromPayload($id, array $payload)
    {
        return new self(
            $id
        );
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->getId()
        ];
    }

    public static function rules(Request $request): array
    {
        return [];
    }
}
