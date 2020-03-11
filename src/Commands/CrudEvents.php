<?php

namespace LemonCMS\LaravelCrud\Commands;

use Illuminate\Console\Command;
use View;

class CrudEvents extends Command
{
    use CrudCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:events
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
    protected $description = 'Parse json and generate events and listeners.';

    /**
     * Keep track of already handled files.
     * @var array
     */
    private $keepTrack = [];

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
        $this->parseEvents();
        $this->render();
    }

    private function render()
    {
        foreach ($this->events as $event) {
            $this->renderEvent('event', $event);
            $this->renderEvent('listener', $event);
            $this->renderEvent('model', $event);
            $this->renderEvent('policy', $event);
        }
    }

    private function renderEvent(string $type, array $event)
    {
        $template = View::make('crud::generators.events.'.$type, $event);
        $path = $this->getPath([\Str::Studly(\Str::plural($type)), $event['path']]);

        if ($type === 'model') {
            $path = $this->getPath('Models');
        }

        if ($type === 'policy') {
            $path = $this->getPath(['Models', 'Policies']);
        }

        $file = implode(DIRECTORY_SEPARATOR, [
            $path,
            $event[$type].'.php',
        ]);

        if (in_array($file, $this->keepTrack)) {
            // skip already handled
            return;
        }

        $this->keepTrack[] = $file;

        if (\File::exists($file) && ! $this->getConfirmation($file)) {
            // Skip
            $this->warn($type.' skipped: '.$file);

            return;
        }

        \File::put($file, "<?php\r\n".$template->render());
        $this->info($type.' created: '.$file);
    }
}
