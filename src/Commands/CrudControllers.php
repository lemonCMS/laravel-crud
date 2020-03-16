<?php

namespace LemonCMS\LaravelCrud\Commands;

use File;
use Illuminate\Console\Command;
use View;

class CrudControllers extends Command
{
    use CrudCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controllers
        {--always : Overwrite all existing files}
        {--never : Never overwrite existing files}
        {--config= : Location of your custom config file}
        {--path= : Path from where file should get stored}
        {--config= : Custom config file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse json and generate controllers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $json = $this->loadConfig();
        $this->parseJson($json['routes']);
        $this->renderControllers();
    }

    private function renderControllers()
    {
        foreach ($this->controllers as $controller) {
            $meta = $controller[0]['meta'];
            $template = View::make('crud::generators.controllers.controller', ['controller' => $controller]);
            $path = $this->getPath(['Http', 'Controllers', $meta['path']]);
            $file = implode(DIRECTORY_SEPARATOR, [$path, $meta['controller'].'.php']);
            if ($this->getConfirmation($file)) {
                File::put($file, "<?php\r\n".$template);
            }
        }
    }
}
