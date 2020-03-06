<?php

namespace Tester\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use Mockery;
use Orchestra\Testbench\TestCase;
use TestApp\EventServiceProvider;
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
                ],
                [
                    'id' => 2,
                    'title' => 'Blog post 2',
                    'description' => 'Description of a blog post NO 2',
                    'created_at' => '',
                    'updated_at' => '',
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
            ], $collection);

            return true;
        })->andReturnSelf();

        Response::shouldReceive('setStatusCode')->once()->with(200)->andReturnSelf();
        Response::shouldReceive('send')->once();

        $this->getJson('api/blogs/1');
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

        $response = $this->getJson('api/blogs/1?include=tags');
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
        $user = User::first();

        $response = $this->actingAs($user)->postJson('api/dashboard/blogs', [
            'title' => 'Best blog in the world!',
            'description' => 'The Netherlands second'
        ]);

        $response->assertStatus(401);
    }

    public function testFetchResourceNotFound()
    {
        $response = $this->getJson('api/blogs/404');
        $response->assertStatus(404);
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

        $app['router']->group(['prefix' => 'api/dashboard',  'middleware' => 'auth'], function ($group) use ($app) {
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
            //'Sentry' => 'Cartalyst\Sentry\Facades\Laravel\Sentry',
            //'YourPackage' => 'YourProject\YourPackage\Facades\YourPackage',
        ];
    }
}
