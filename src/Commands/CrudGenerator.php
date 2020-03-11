<?php

namespace LemonCMS\LaravelCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class CrudGenerator extends Command
{
    use CrudCommandTrait;

    private $path;
    private $config;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generate 
    {--always : Overwrite all existing files}
    {--never : Never overwrite existing files}
    {--config= : Location of your custom config file}
    {--output= : full pathname to write the api routes to}
    {--config= : Custom config file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse json and generate api routes';

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
     * Parse JSON and create api.php routes.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $json = $this->loadConfig();

        $routes = "<?php\r\n".\View::make('crud::generators.route', ['data' => $json]);
        $routes = preg_replace('/\t+/', ' ', $routes);

        // $routes = trim(preg_replace('/\s+/', ' ', $routes));
        $routes = str_replace([') ->', '; '], [')->', ";\r\n"], $routes);

        $output = $this->option('output') ?: base_path('routes/api.php');

        if (\File::exists($output) && ! $this->getConfirmation($output)) {
            $this->error('File already exists'.$output);

            return;
        }
        \File::put($output, $routes);
    }
}
