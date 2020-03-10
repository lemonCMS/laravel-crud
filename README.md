# -------- WORK IN PROGRESS --------

# CRUD & Event logging for laravel 6/7

## Why?

This package makes it easy to setup a crud microservice with event logging in Laravel.

## What can it do?

it can generate Provide a JSON-file with the api-routes you need and generate the code.

It will generate
 - routes/api.php
 - Controllers
 - Models
 - Events
 - Listeners 
 
It will NOT generate
 - migrations
 - working code and never will
 
 After the files are generate you will need to implement your code.
 
 
 # How to use
 
Setup the laravel ```EventServiceProvider``` to autodiscover events.
```php
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }
```

In your project root create a file called [crud.json](crud-example.json) containing the routes their middleware etc.

Then run:
```shell script
php artisan crud:generate
```

This will create all the files necessary. For existing files you will be asked what to do.


# How to implement in an existing project

You are not required to use the generator. It is mostly convenient when you are starting a new project. 

There are two Traits available for your Controller and Model classes.

And thow extended classes for your Event and Listener.


Controller.php
````php

use Illuminate\Routing\Controller;
use LemonCMS\LaravelCrud\Http\Controllers\CrudControllerTrait;

class BlogController extends Controller
{
    use CrudControllerTrait;
}
````

Model.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LemonCMS\LaravelCrud\Model\CrudTrait;

class Blog extends Model
{
    use CrudTrait;
    use SoftDeletes;

    protected $includes = [
        'tags',
    ];

    protected $orderFields = [
        'id', 'title', 'created_at', 'modified_at',
    ];

    public function tags()
    {
        return $this->hasMany('App\Models\BlogTag');
    }

    protected function search()
    {
        return [
            'id',
            'title' => function (Builder $query, $value) {
                return $query->where('title', 'like', "%{$value}%");
            },
        ];
    }
}

```

Event.php
````php
<?php

namespace App\Events;

use Illuminate\Http\Request;
use LemonCMS\LaravelCrud\Events\CrudEvent;

class BaseBlogEvent extends CrudEvent
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;

    /**
     * AccountUpdate constructor.
     *
     * @param $id
     * @param string $model
     * @param string $title
     * @param string $description
     */
    public function __construct($id, string $model, string $title, string $description)
    {
        parent::__construct($id, $model);
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @param $id
     * @param string $model
     * @param array $payload
     * @return BaseBlogEvent
     */
    public static function fromPayload($id, string $model, array $payload)
    {
        return new static(
            null,
            $model,
            $payload['title'],
            $payload['description']
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public static function rules(Request $request): array
    {
        return [
            'title' => 'required',
            'description' => 'required',
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
        ];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}

````

Listener.php
````php
<?php

namespace App\Listeners;

use LemonCMS\LaravelCrud\Listeners\CrudListener;
use TestApp\Events\BlogStoreEvent;

class BlogStoreListener extends CrudListener
{
    /**
     * @var BlogStoreEvent
     */
    protected $event;

    /**
     * @param BlogStoreEvent $event
     */
    public function handle(BlogStoreEvent $event)
    {
        $this->process($event);
    }

    public function beforeRun()
    {
        $this->entity->title = $this->event->getTitle();
        $this->entity->description = $this->event->getDescription();
    }

    public function beforeSave()
    {
        // Do stuff
    }

    public function afterSave()
    {
        // Do stuff
    }
}

````

#Todo
 - Testing policies
 - Implement Collection support
 

