<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CrudEventsTest extends Orchestra\Testbench\TestCase
{
    /** @test */
    public function testCrudController()
    {
        $json = <<<JSON
{
  "routes": {
    "blog": {
        "type": "resource",
        "controller": "Api\\\UsersController",
        "path": "users",
        "options": {
            "only": ["index", "show", "store", "update"]
        }
    },
    "recipes": {
        "type": "resource",
        "controller": "Api\\\RecipesController",
        "path": "recipes",
        "actions": [
            {
                "method": "post",
                "action": "downloadAll",
                "path": "/recipes/download-all"
            }
        ]
    }
  }
}
JSON;
        File::shouldReceive('exists')->once()->with(base_path('.crud-specs.json'))->andReturn(true);
        File::shouldReceive('get')->once()->with(base_path('.crud-specs.json'))->andReturn($json);
        File::partialMock();

        $path = (realpath(__DIR__.'/../../test-data'));

        $this->artisan('crud:events', [
            '--path' => $path,
            '--always' => true,
        ]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $path = (realpath(__DIR__.'/../../test-data'));
        File::deleteDirectory(implode(DIRECTORY_SEPARATOR, [$path, 'Events']));
        File::deleteDirectory(implode(DIRECTORY_SEPARATOR, [$path, 'Listeners']));
        File::deleteDirectory(implode(DIRECTORY_SEPARATOR, [$path, 'Models']));
        File::deleteDirectory(implode(DIRECTORY_SEPARATOR, [$path, 'Policies']));
    }

    /** @test */
    public function testUnknownConfig()
    {
        $this->expectException(Exception::class);
        $this->artisan('crud:generate', [
            '--config' => 'something.json',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            'LemonCMS\LaravelCrud\ServiceProvider',
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $crudConfig = (include realpath(__DIR__.'/../../TestApp/config.php'));
        $app['config']->set('crud', $crudConfig);
    }
}
