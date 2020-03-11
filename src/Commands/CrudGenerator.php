<?php

namespace LemonCMS\LaravelCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class CrudGenerator extends Command
{
    private $path;
    private $config;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generate 
    {--all : Overwrite all existing files}
    {--never : Never overwrite existing files}
    {--config= : Location of your custom config file}
    {--path= : Path to write the api routes to}
    {--config= : Custom config file}';

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
        $this->config = $this->option('config') ?: base_path('.crud-specs.json');

        if (! File::exists($this->config)) {
            throw new \Exception(new FileNotFoundException('File not found at ' . $this->config));
        }
        $data = File::get($this->config);
        $json = json_decode($data, true);
        $routes = "<?php\r\n".\View::make('crud::generators.route', ['data' => $json]);
        $routes = preg_replace('/\t+/', ' ', $routes);

        // $routes = trim(preg_replace('/\s+/', ' ', $routes));
        $routes = str_replace([') ->', '; '], [')->', ";\r\n"], $routes);

        $path = $this->option('path') ?: base_path('routes/api.php');

        \File::put($path, $routes);
    }
}
