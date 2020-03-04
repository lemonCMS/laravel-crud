<?php

namespace LemonCMS\LaravelCrud\Commands;

use Illuminate\Console\Command;

class CrudGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse json and generate all the stuff.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $json = json_decode(\File::get(app_path('/console/Commands/crud-specs.json')), true);

        $routes = "<?php\r\n".\View::make('generators.route', ['data' => $json]);
        $routes = preg_replace('/\t+/', ' ', $routes);

        $routes = trim(preg_replace('/\s+/', ' ', $routes));
        $routes = str_replace([') ->', '; '], [')->', ";\r\n"], $routes);

        \File::put(base_path('routes/api.test.php'), $routes);
    }
}
