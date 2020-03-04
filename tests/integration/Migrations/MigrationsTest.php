<?php

namespace Orchestra\Testbench\Tests\Databases;

use Orchestra\Testbench\TestCase;

class MigrationsTest extends TestCase
{
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
            '--path' => realpath(__DIR__.'/../../../src/database/migrations'),
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            //'Cartalyst\Sentry\SentryServiceProvider',
            //'YourProject\YourPackage\YourPackageServiceProvider',
        ];
    }

    /**
     * Get package aliases.  In a normal app environment these would be added to
     * the 'aliases' array in the config/app.php file.  If your package exposes an
     * aliased facade, you should add the alias here, along with aliases for
     * facades upon which your package depends, e.g. Cartalyst/Sentry.
     *
     * @param  \Illuminate\Foundation\Application  $app
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

    /** @test */
    public function it_runs_the_migrations()
    {
        \DB::table('events')->insert([
            'event' => 'Command',
            'payload' => json_encode(['command' => 'Event/']),
            'checksum' => 'qwerty',
        ]);
        $event = \DB::table('events')->where('id', '=', 1)->first();

        $this->assertEquals('Command', $event->event);
        $this->assertEquals('qwerty', $event->checksum);
        $this->assertEquals(json_encode(['command' => 'Event/']), $event->payload);
    }
}
