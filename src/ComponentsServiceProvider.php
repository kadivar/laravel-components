<?php

namespace Kadivar\Components;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class ComponentsServiceProvider extends ServiceProvider
{

    protected $files;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (is_dir(app_path().'/Components/')) {
            $components = config("components.enable") ?: array_map('class_basename',
                $this->files->directories(app_path().'/Components/'));
            foreach ($components as $component) {
                // Allow routes to be cached
                $migrations = app_path().'/Components/'.$component.'/Migrations';
                $factories = app_path().'/Components/'.$component.'/Factories';
                $seeds = app_path().'/Components/'.$component.'/Seeds';
                $models = app_path().'/Components/'.$component.'/Models';
                $helper = app_path().'/Components/'.$component.'/helper.php';
                $trans = app_path().'/Components/'.$component.'/Translations';
                $views = app_path().'/Components/'.$component.'/Views';
                $requests = app_path().'/Components/'.$component.'/Requests';
                $routes = app_path().'/Components/'.$component.'/Routes';
                $api_resources = app_path().'/Components/'.$component.'/Resources/api';
                $controllers = app_path().'/Components/'.$component.'/Controllers';

                if ($this->files->isDirectory($migrations)) {
                    $this->loadMigrationsFrom($migrations);
                }
                if ($this->files->isDirectory($models)) {
                    foreach (glob($models.'/*.php') as $filename) {
                        include $filename;
                    }
                }
                if ($this->files->isDirectory($factories)) {
                    foreach (glob($factories.'/*.php') as $filename) {
                        include $filename;
                    }
                }
                if ($this->files->isDirectory($seeds)) {
                    foreach (glob($seeds.'/*.php') as $filename) {
                        include $filename;
                    }
                }
                if ($this->files->exists($helper)) {
                    include $helper;
                }
                if ($this->files->isDirectory($trans)) {
                    $this->loadTranslationsFrom($trans, $component);
                }
                if ($this->files->isDirectory($views)) {
                    $this->loadViewsFrom($views, $component);
                }
                if ($this->files->isDirectory($requests)) {
                    foreach (glob($requests.'/*.php') as $filename) {
                        include $filename;
                    }
                }
                if ($this->files->isDirectory($routes)) {
                    if (!$this->app->routesAreCached()) {
                        if ($this->files->exists($routes.'/web.php')) {
                            include $routes.'/web.php';
                        }
                        if ($this->files->exists($routes.'/api.php')) {
                            include $routes.'/api.php';
                        }
                    }
                }
                if ($this->files->isDirectory($api_resources)) {
                    foreach (glob($api_resources.'/*.php') as $filename) {
                        include $filename;
                    }
                }
                if ($this->files->isDirectory($controllers)) {
                    foreach (glob($controllers.'/*.php') as $filename) {
                        include $filename;
                    }
                }
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->files = new Filesystem;
        $this->registerMakeCommand();
    }

    /**
     * Register the "make:component" console command.
     *
     * @return Console\ComponentsMakeCommand
     */
    protected function registerMakeCommand()
    {

        $this->commands('components.make');

        $bind_method = method_exists($this->app, 'bindShared') ? 'bindShared' : 'singleton';

        $this->app->{$bind_method}('components.make', function ($app) {
            return new Console\ComponentsMakeCommand($this->files);
        });
    }

}
