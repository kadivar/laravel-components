<?php

namespace Kadivar\Components;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class ComponentsServiceProvider extends ServiceProvider {

	protected $files;

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {

		if ( is_dir( app_path() . '/Components/' ) ) {
			$components = config( "components.enable" ) ?: array_map( 'class_basename', $this->files->directories( app_path() . '/Components/' ) );
			foreach ( $components as $component ) {
				// Allow routes to be cached
				$helper = app_path() . '/Components/' . $component . '/helper.php';
				$routes = app_path() . '/Components/' . $component . '/Routes';
				$views  = app_path() . '/Components/' . $component . '/Views';
				$trans  = app_path() . '/Components/' . $component . '/Translations';

				if ( $this->files->exists( $helper ) ) {
					include $helper;
				}
				if ( $this->files->isDirectory( $routes ) ) {
					if ( ! $this->app->routesAreCached() ) {
						if ( $this->files->exists( $routes . '/web.php' ) ) {
							include $routes . '/web.php';
						}
						if ( $this->files->exists( $routes . '/api.php' ) ) {
							include $routes . '/api.php';
						}
					}
				}
				if ( $this->files->isDirectory( $views ) ) {
					$this->loadViewsFrom( $views, $component );
				}
				if ( $this->files->isDirectory( $trans ) ) {
					$this->loadTranslationsFrom( $trans, $component );
				}
			}
		}

	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register() {

		$this->files = new Filesystem;
		$this->registerMakeCommand();
	}

	/**
	 * Register the "make:component" console command.
	 *
	 * @return Console\ComponentsMakeCommand
	 */
	protected function registerMakeCommand() {

		$this->commands( 'components.make' );

		$bind_method = method_exists( $this->app, 'bindShared' ) ? 'bindShared' : 'singleton';

		$this->app->{$bind_method}( 'components.make', function ( $app ) {
			return new Console\ComponentsMakeCommand( $this->files );
		} );
	}

}
