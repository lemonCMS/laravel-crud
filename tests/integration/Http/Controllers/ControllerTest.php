<?php

namespace Tester\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use LemonCMS\LaravelCrud\Exceptions\MissingEventException;
use LemonCMS\LaravelCrud\Exceptions\MissingListenerException;
use LemonCMS\LaravelCrud\Exceptions\MissingModelException;
use LemonCMS\LaravelCrud\Model\EventsTable;
use Mockery;
use Orchestra\Testbench\TestCase;
use TestApp\Models\Blog;
use TestApp\Models\User;

class ControllerTest extends TestCase
{
    public function testIndex()
    {
        Response::shouldReceive('json')->once()->withArgs(function (LengthAwarePaginator $response) {
            $collection = $response->getCollection()->toArray();
            $this->assertEqualsCanonicalizing([
                [
                    'id' => 1,
                    'title' => 'Blog post 1',
                    'description' => 'Description of a blog post NO 1',
                    'created_at' => '',
                    'updated_at' => '',
                    'deleted_at' => '',
                ],
                [
                    'id' => 2,
                    'title' => 'Blog post 2',
                    'description' => 'Description of a blog post NO 2',
                    'created_at' => '',
                    'updated_at' => '',
                    'deleted_at' => '',
                ],
            ], $collection);
            $this->assertEquals(2, $response->count());

            return true;
        })->andReturnSelf();

        Response::shouldReceive('setStatusCode')->once()->with(200)->andReturnSelf();
        Response::shouldReceive('send')->once();

        $this->getJson('api/blogs');
    }

    public function testIndexWithInclude()
    {
        try {
            Response::shouldReceive('json')->once()->withArgs(function (LengthAwarePaginator $response) {
                $collection = $response->getCollection()->toArray();
                $this->assertEqualsCanonicalizing([
                    [
                        'id' => 1,
                        'title' => 'Blog post 1',
                        'description' => 'Description of a blog post NO 1',
                        'created_at' => '',
                        'updated_at' => '',
                        'deleted_at' => '',
                        'tags' => [
                            ['id' => 1, 'blog_id' => 1, 'tag' => 'Tag 1'],
                            ['id' => 2, 'blog_id' => 1, 'tag' => 'Tag 2'],
                            ['id' => 3, 'blog_id' => 1, 'tag' => 'Tag 3'],
                        ],
                    ],
                ], $collection);
                $this->assertEquals(1, $response->count());

                return true;
            })->andReturnSelf();

            Response::shouldReceive('setStatusCode')->once()->with(200)->andReturnSelf();
            Response::shouldReceive('send')->once();

            $this->getJson('api/blogs?id=1&include=tags');
        } catch (\Exception $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }
    }

    public function testIndexWithSearchOnTitle()
    {
        try {
            Response::shouldReceive('json')->once()->withArgs(function (LengthAwarePaginator $response) {
                $collection = $response->getCollection()->toArray();
                $this->assertEqualsCanonicalizing([
                    [
                        'id' => 2,
                        'title' => 'Blog post 2',
                        'description' => 'Description of a blog post NO 2',
                        'created_at' => '',
                        'updated_at' => '',
                        'deleted_at' => '',
                    ],
                ], $collection);
                $this->assertEquals(1, $response->count());

                return true;
            })->andReturnSelf();

            Response::shouldReceive('setStatusCode')->once()->with(200)->andReturnSelf();
            Response::shouldReceive('send')->once();

            $this->getJson('api/blogs?title=Blog%20post%202');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function testIndexWithSearchOnId()
    {
        try {
            Response::shouldReceive('json')->once()->withArgs(function (LengthAwarePaginator $response) {
                $collection = $response->getCollection()->toArray();
                $this->assertEqualsCanonicalizing([
                    [
                        'id' => 2,
                        'title' => 'Blog post 2',
                        'description' => 'Description of a blog post NO 2',
                        'created_at' => '',
                        'updated_at' => '',
                        'deleted_at' => '',
                    ],
                ], $collection);
                $this->assertEquals(1, $response->count());

                return true;
            })->andReturnSelf();

            Response::shouldReceive('setStatusCode')->once()->with(200)->andReturnSelf();
            Response::shouldReceive('send')->once();

            $this->getJson('api/blogs?id=2');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function testIndexWithWrongControllerName()
    {
        $response = $this->getJson('api/does-not-work?id=2');
        $this->assertEquals('The Controller DoesNotWork must end with `Controller.php`', $response->exception->getMessage());
        $this->assertEquals(503, $response->exception->getCode());
    }

    public function testFetchResource()
    {
        Response::shouldReceive('json')->once()->withArgs(function (Blog $response) {
            $collection = $response->toArray();
            $this->assertEqualsCanonicalizing([
                'id' => 1,
                'title' => 'Blog post 1',
                'description' => 'Description of a blog post NO 1',
                'created_at' => '',
                'updated_at' => '',
                'deleted_at' => '',
            ], $collection);

            return true;
        })->andReturnSelf();

        Response::shouldReceive('setStatusCode')->once()->with(200)->andReturnSelf();
        Response::shouldReceive('send')->once();

        $response = $this->getJson('api/blogs/1');
        // dd($response);
    }

    public function testFetchResourceWithInclude()
    {
        Response::shouldReceive('json')->once()->withArgs(function (Blog $response) {
            $collection = $response->toArray();
            $this->assertEqualsCanonicalizing(
                [
                    'id' => 1,
                    'title' => 'Blog post 1',
                    'description' => 'Description of a blog post NO 1',
                    'created_at' => '',
                    'updated_at' => '',
                    'deleted_at' => '',
                    'tags' => [
                        ['id' => 1, 'blog_id' => 1, 'tag' => 'Tag 1'],
                        ['id' => 2, 'blog_id' => 1, 'tag' => 'Tag 2'],
                        ['id' => 3, 'blog_id' => 1, 'tag' => 'Tag 3'],
                    ],
                ], $collection);

            return true;
        })->andReturnSelf();

        Response::shouldReceive('setStatusCode')->once()->with(200)->andReturnSelf();
        Response::shouldReceive('send')->once();

        $response = $this->getJson('api/blogs/1?include=tags&order_field=title&order_dir=desc');
    }

    public function testFetchResourceUnknownInclude()
    {
        $response = $this->getJson('api/blogs/1?include=doesNoExists');
        $response->assertStatus(500);
    }

    public function testDashboardGet()
    {
        $response = $this->getJson('api/dashboard/blogs/1?include=doesNoExists');
        $response->assertStatus(401);

        Response::shouldReceive('json')->once()->withArgs(function ($response) {
            $collection = $response->toArray();
            $this->assertEqualsCanonicalizing([
                'id' => 1,
                'title' => 'Blog post 1',
                'description' => 'Description of a blog post NO 1',
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ], $collection);

            return true;
        })->andReturnSelf();

        Response::shouldReceive('setStatusCode')->once()->with(200)->andReturnSelf();
        Response::shouldReceive('send')->once();

        $user = User::first();
        $this->actingAs($user)->getJson('api/dashboard/blogs/1');
    }

    public function testDashboardStore()
    {
        $mock = Mockery::mock('Illuminate\Http\Response');
        $this->app->instance('Illuminate\Http\Response', $mock);

        $mock->shouldReceive('setContent')->withArgs(function (Model $record) {
            $this->assertEquals(3, $record->id);
            $this->assertEquals('Best blog in the world!', $record->title);
            $this->assertEquals('The Netherlands second', $record->description);

            return true;
        })->andReturnSelf();
        $mock->shouldReceive('setStatusCode')->with(201)->andReturnSelf();
        $mock->shouldReceive('send');

        $user = User::first();
        $response = $this->actingAs($user)->postJson('api/dashboard/blogs', [
            'title' => 'Best blog in the world!',
            'description' => 'The Netherlands second',
        ]);

        $response->assertStatus(200);
    }

    public function testDashboardUpdate()
    {
        $mock = Mockery::mock('Illuminate\Http\Response');
        $this->app->instance('Illuminate\Http\Response', $mock);

        $mock->shouldReceive('setContent')->withArgs(function (Model $record) {
            $this->assertEquals(1, $record->id);
            $this->assertEquals('Best blog in the world!', $record->title);
            $this->assertEquals('The Netherlands second', $record->description);

            return true;
        })->andReturnSelf();
        $mock->shouldReceive('setStatusCode')->with(201)->andReturnSelf();
        $mock->shouldReceive('send');

        $user = User::first();
        $response = $this->actingAs($user)->putJson('api/dashboard/blogs/1', [
            'title' => 'Best blog in the world!',
            'description' => 'The Netherlands second',
        ]);
        $response->assertStatus(200);

        $this->assertEquals(EventsTable::all()->count(), 1);
    }

    public function testDashboardDestroyAndRestore()
    {
        $mock = Mockery::mock('Illuminate\Http\Response');
        $this->app->instance('Illuminate\Http\Response', $mock);

        $mock->shouldReceive('setContent')->withArgs(function ($response) {
            $this->assertEquals(1, $response->id);
            $this->assertEquals('Blog post 1', $response->title);
            $this->assertEquals('Description of a blog post NO 1', $response->description);

            return true;
        })->andReturnSelf();
        $mock->shouldReceive('setStatusCode')->with(200)->andReturnSelf();
        $mock->shouldReceive('send');

        $user = User::first();
        $response = $this->actingAs($user)->deleteJson('api/dashboard/blogs/1');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->getJson('api/dashboard/blogs/1');
        $response->assertStatus(404);

        $mock = Mockery::mock('Illuminate\Http\Response');
        $this->app->instance('Illuminate\Http\Response', $mock);
        $mock->shouldReceive('setContent')->withArgs(function ($response) {
            $this->assertEquals(1, $response->id);
            $this->assertEquals('Blog post 1', $response->title);
            $this->assertEquals('Description of a blog post NO 1', $response->description);

            return true;
        })->andReturnSelf();
        $mock->shouldReceive('setStatusCode')->with(200)->andReturnSelf();
        $mock->shouldReceive('send');

        $response = $this->actingAs($user)->postJson('api/dashboard/blogs/1/restore', []);
        $response->assertStatus(200);
    }

    public function testDashboardStoreValidate()
    {
        $user = User::first();
        $response = $this->actingAs($user)->postJson('api/dashboard/blogs', [
        ]);
        $response->assertStatus(422);
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('title', $content['errors']);
        $this->assertArrayHasKey('description', $content['errors']);
    }

    public function testFetchResourceNotFound()
    {
        $response = $this->getJson('api/blogs/404');
        $response->assertStatus(404);
    }

    public function testNoModel()
    {
        $response = $this->postJson('api/no-model', []);
        $this->assertInstanceOf(MissingModelException::class, $response->exception);
    }

    public function testNoEvent()
    {
        $response = $this->postJson('api/no-event', []);
        $this->assertInstanceOf(MissingEventException::class, $response->exception);
    }

    public function testNoListener()
    {
        $response = $this->postJson('api/no-listener', []);
        $this->assertInstanceOf(MissingListenerException::class, $response->exception);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);

        // call migrations specific to our tests, e.g. to seed the db
        // the path option should be an absolute path.

        $this->loadMigrationsFrom([
            '--database' => 'testing',
        ]);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../../../fixtures'),
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $crudConfig = (include realpath(__DIR__.'/../../../TestApp/config.php'));
        $app['config']->set('crud', $crudConfig);
        $app['router']->get('hello', ['as' => 'hi', 'uses' => function () {
            return 'hello world';
        }]);

        $app['router']->resource('api/blogs', '\TestApp\Http\Controllers\BlogController');
        $app['router']->resource('api/does-not-work', '\TestApp\Http\Controllers\DoesNotWork');
        $app['router']->resource('api/no-model', '\TestApp\Http\Controllers\NoModelController');
        $app['router']->resource('api/no-event', '\TestApp\Http\Controllers\NoEventController');
        $app['router']->resource('api/no-listener', '\TestApp\Http\Controllers\NoListenerController');

        $app['router']->group(['prefix' => 'api/dashboard', 'middleware' => 'auth'], function ($group) use ($app) {
            $group->post('blogs/{blog}/restore', '\TestApp\Http\Controllers\BlogController@Restore');
            $group->resource('blogs', '\TestApp\Http\Controllers\BlogController');
        });
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'LemonCMS\LaravelCrud\ServiceProvider',
            'TestApp\EventServiceProvider',
            'TestApp\AuthServiceProvider',
        ];
    }

    /**
     * Get package aliases.  In a normal app environment these would be added to
     * the 'aliases' array in the config/app.php file.  If your package exposes an
     * aliased facade, you should add the alias here, along with aliases for
     * facades upon which your package depends, e.g. Cartalyst/Sentry.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            //'YourPackage' => 'YourProject\YourPackage\Facades\YourPackage',
        ];
    }
}
