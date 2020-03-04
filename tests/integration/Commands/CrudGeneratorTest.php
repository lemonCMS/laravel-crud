<?php

use LemonCMS\LaravelCrud\ServiceProvider;

// When testing inside of a Laravel installation, the base class would be Tests\TestCase
class CrudGeneratorTest extends Orchestra\Testbench\TestCase
{
    /** @test */
    public function visit_test_route()
    {
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
