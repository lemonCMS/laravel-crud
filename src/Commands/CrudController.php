<?php

namespace LemonCMS\LaravelCrud\Commands;

use Illuminate\Console\Command;
use View;

class CrudController extends Command
{
    use CrudCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse json and generate controllers.';

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
        $json = json_decode(\File::get(app_path('/console/Commands/crud-specs.json')), true);
        $this->parseJson($json['routes']);
        $this->renderControllers();
    }

    private function renderControllers()
    {
        foreach ($this->controllers as $controller) {
            $meta = $controller[0]['meta'];
            $template = View::make('generators.controllers.controller', ['controller' => $controller]);
            $path = base_path(implode(DIRECTORY_SEPARATOR, ['app', 'Http', 'Controllers', $meta['path']]));

            if (! \File::isDirectory($path)) {
                \File::makeDirectory($path, 493, true);
            }
            \File::put(implode(DIRECTORY_SEPARATOR, [$path, $meta['controller'].'-test.php']), "<?php\r\n".$template);
        }
    }
}
