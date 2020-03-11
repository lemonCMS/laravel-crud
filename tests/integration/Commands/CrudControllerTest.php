<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CrudControllerTest extends Orchestra\Testbench\TestCase
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

        $this->artisan('crud:controller', [
            '--path' => $path,
            '--always' => true,
        ]);

        $file = implode(DIRECTORY_SEPARATOR, [$path, 'Http', 'Controllers', 'Api', 'UsersController.php']);
        $this->fileExists($file);
        $content = File::get($file);

        preg_match("/^Class\s(.*)\sextends\sController/m", $content, $matches);
        $this->assertEquals('UsersController', $matches[1]);

        preg_match("/use\s(CrudControllerTrait);/m", $content, $matches);
        $this->assertEquals('CrudControllerTrait', $matches[1]);
        File::deleteDirectory(implode(DIRECTORY_SEPARATOR, [$path, 'Http']));
    }

    /** @test */
    public function testUnknownConfig()
    {
        $this->expectException(Exception::class);
        $this->artisan('crud:generate', [
            '--config' => 'somthing.json',
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
