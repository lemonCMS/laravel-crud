<?php

use Illuminate\Support\Facades\File;
use LemonCMS\LaravelCrud\ServiceProvider;

// When testing inside of a Laravel installation, the base class would be Tests\TestCase
class CrudGeneratorTest extends Orchestra\Testbench\TestCase
{
    /** @test */
    public function testCrudGenerate()
    {
        $json = <<<JSON
{
  "routes": {
    "blog": {
        "type": "resource",
        "controller": "Api\\\UsersController",
        "path": "users"
    }
  }
}
JSON;
        File::shouldReceive('exists')->once()->with(base_path('.crud-specs.json'))->andReturn(true);
        File::shouldReceive('get')->once()->with(base_path('.crud-specs.json'))->andReturn($json);
        File::partialMock();

        $path = (realpath(__DIR__.'/../../TestApp/crud/routes')) . '/api.php';

        $this->artisan('crud:generate', [
            '--path' => $path
        ]);

        $this->fileExists($path);
        $content = File::get($path);

        preg_match("/^(.*)\:\:(.*)\(\'(.*)\',\s\'(.*)\'\)/m", $content, $matches);
        $this->assertEquals('Route', $matches[1]);
        $this->assertEquals('resource', $matches[2]);
        $this->assertEquals('users', $matches[3]);
        $this->assertEquals('Api\UsersController', $matches[4]);
        File::delete($path);
    }

    /** @test */
    public function testUnknownConfig()
    {   $this->expectException(Exception::class);
        $this->artisan('crud:generate', [
            '--config' => 'somthing.json'
        ]);
    }

    // When testing inside of a Laravel installation, this is not needed
    protected function getPackageProviders($app)
    {
        return [
            'LemonCMS\LaravelCrud\ServiceProvider',
        ];
    }

    // When testing inside of a Laravel installation, this is not needed
    protected function setUp(): void
    {
        parent::setUp();
    }
}
