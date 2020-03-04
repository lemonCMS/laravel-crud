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
    protected $signature = 'crud:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse json and generate events and listeners.';

    /**
     * Permission to overwrite all existing files.
     *
     * @var bool
     */
    protected $allConfirmed = null;

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
        $json = json_decode(\File::get(app_path('/console/Commands/crud-specs.json')), true);
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
        $template = View::make('generators.events.'.$type, $event);
        $path = base_path(implode(DIRECTORY_SEPARATOR, ['app', \Str::Studly(\Str::plural($type)), $event['path']]));
        if ($type === 'model') {
            $path = base_path(implode(DIRECTORY_SEPARATOR, ['app', 'Models']));
        }

        if ($type === 'policy') {
            $path = base_path(implode(DIRECTORY_SEPARATOR, ['app', 'Models', 'Policies']));
        }

        if (! \File::isDirectory($path)) {
            \File::makeDirectory($path, 493, true);
        }

        $file = implode(DIRECTORY_SEPARATOR, [
            $path,
            $event[$type].'-test.php',
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

    private function getConfirmation($file)
    {
        if (true === $this->allConfirmed) {
            return true;
        }

        if (false === $this->allConfirmed) {
            return false;
        }

        do {
            $this->info('File: "'.$file.'" already exists');
            $answer = $this->anticipate('overwrite? (never, no, yes, all)',
                ['never', 'no', 'yes', 'all'], 'no');
            $answer = strtolower($answer);
        } while (! in_array($answer, ['never', 'no', 'yes', 'all', 'y', 'n', 'a']));

        if (in_array($answer, ['all', 'a'])) {
            $this->allConfirmed = true;

            return true;
        }

        if ('never' === $answer) {
            $this->allConfirmed = false;

            return false;
        }

        return in_array($answer, ['yes', 'y']);
    }
}
