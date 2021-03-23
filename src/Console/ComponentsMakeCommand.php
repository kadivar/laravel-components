<?php

namespace Kadivar\Components\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ComponentsMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:component';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new component (folder structure)';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Component';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:component {name}  {--migration} {--translation}';

    /**
     * The current stub.
     *
     * @var string
     */
    protected $currentStub;


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        // Check if component exists
        if ($this->files->exists(app_path().'/Components/'.$this->getNameInput())) {
            return $this->error($this->type.' already exists!');
        }

        // Create Controller
        $this->generate('controller');

        // Create Model
        $this->generate('model');

        // Create Views folder
        $this->generate('view');

        // Create translations
        if (!$this->option('no-translation')) {
            $this->generate('translation');
        }

        // Create Routes file
        $this->generate('route');

        // Create Helper file
        $this->generate('helper');

        // Create Request Folder
        $this->generate('request');

        // Create migrations
        if (!$this->option('no-migration')) {
            $this->generate('migration');
        }

        $this->info($this->type.' created successfully.');
    }

    /**
     * Core logic.
     *
     */
    protected function generate($type)
    {
        switch ($type) {
            case 'model':
                $file_name = Str::studly(class_basename($this->getNameInput()));
                break;

            case 'migration':
                $component_name = $this->argument('name');
                $table = Str::plural(Str::snake(class_basename($this->argument('name'))));
                $path = app_path().'/Components/'.$component_name.'/Migrations';
                $this->call('make:migration', [
                    'name' => "create_{$table}_table",
                    '--create' => $table,
                    '--path' => $path
                ]);
                break;

            case 'view':
                $file_name = 'index.blade';
                break;

            case 'translation':
                $file_name = 'en';
                break;

            case 'route':
                $file_name = 'routes';
                break;

            case 'helper':
                $file_name = 'helper';
                break;

            default:
                $file_name = Str::studly(class_basename($this->getNameInput()).ucfirst($type));
                break;
        }

        $folder = ($type != 'helper') ? ucfirst($type).'s\\'.($type === 'translation' ? 'en\\' : '') : '';
        $name = $this->getFileClassNamespace($folder, $file_name);
        $path = $this->getPath($name);

        if ($this->files->exists($path)) {
            return $this->error($this->type.' already exists!');
        }

        $this->makeDirectory($path);

        if ($type == 'route') {
            $name = $this->getFileClassNamespace($folder, 'web');
            $path = $this->getPath($name);
            $this->currentStub = __DIR__.'/stubs/web.stub';
            $this->files->put($path, $this->buildClass($name));
            $name = $this->getFileClassNamespace($folder, 'api');
            $path = $this->getPath($name);
            $this->currentStub = __DIR__.'/stubs/api.stub';
            $this->files->put($path, $this->buildClass($name));
        } else {
            $this->currentStub = __DIR__.'/stubs/'.$type.'.stub';
            $this->files->put($path, $this->buildClass($name));
        }
    }

    /**
     * Get the class full namespace as string.
     *
     * @param  string  $folder
     * @param  string  $file_name
     *
     * @return string
     */
    protected function getFileClassNamespace($folder, $file_name): string
    {
        $name = $name = (string) ('Components\\'.Str::studly(ucfirst($this->getNameInput())).'\\'.$folder.$file_name);

        return $name;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceName($stub, $this->getNameInput())->replaceNamespace($stub, $name)->replaceClass($stub,
            $name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return $this->currentStub;
    }

    /**
     * Replace the name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return string
     */
    protected function replaceName(&$stub, $name): string
    {
        $stub = str_replace('DummyTitle', $name, $stub);
        $stub = str_replace('DummyUCtitle', ucfirst(Str::studly($name)), $stub);

        return $this;
    }

    /**
     * Get the full namespace name for a given class.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function getNamespace($name): string
    {
        return trim(implode('\\', array_map('ucfirst', array_slice(explode('\\', Str::studly($name)), 0, -1))), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name): string
    {
        $class = class_basename($name);

        return str_replace('DummyClass', $class, $stub);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return array(
            ['name', InputArgument::REQUIRED, 'Component name.'],
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return array(
            ['no-migration', null, InputOption::VALUE_NONE, 'Create new without migration files.'],
            ['no-translation', null, InputOption::VALUE_NONE, 'Create component without translation files.'],
        );
    }

}
