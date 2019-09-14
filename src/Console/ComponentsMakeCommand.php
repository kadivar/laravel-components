<?php namespace Kadivar\Components\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ComponentsMakeCommand extends GeneratorCommand {

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
	public function handle() {
		// check if component exists
		if ( $this->files->exists( app_path() . '/Components/' . $this->getNameInput() ) ) {
			return $this->error( $this->type . ' already exists!' );
		}

		// Create Controller
		$this->generate( 'controller' );

		// Create Model
		$this->generate( 'model' );

		// Create Views folder
		$this->generate( 'view' );

		//Flag for no translation
		if ( $this->option( 'translation' ) ) // Create Translations folder
		{
			$this->generate( 'translation' );
		}

		// Create Routes file
		$this->generate( 'routes' );

		// Create Helper file
		$this->generate( 'helper' );

		// Create Request Folder
		$this->generate( 'request' );


		if ( $this->option( 'migration' ) ) {
			$table = Str::plural( Str::snake( class_basename( $this->argument( 'name' ) ) ) );
			$this->call( 'make:migration', [ 'name' => "create_{$table}_table", '--create' => $table ] );
		}

		$this->info( $this->type . ' created successfully.' );
	}


	protected function generate( $type ) {

		switch ( $type ) {
			case 'controller':
				$filename = Str::studly( class_basename( $this->getNameInput() ) . ucfirst( $type ) );
				break;

			case 'model':
				$filename = Str::studly( class_basename( $this->getNameInput() ) );
				break;

			case 'view':
				$filename = 'index.blade';
				break;

			case 'translation':
				$filename = 'example';
				break;

			case 'routes':
				$filename = 'routes';
				break;

			case 'helper':
				$filename = 'helper';
				break;
			case 'request':
				$filename = Str::studly( class_basename( $this->getNameInput() ) . ucfirst( $type ) );
				break;
		}

		$folder = ( $type != 'routes' && $type != 'helper' ) ? ucfirst( $type ) . 's\\' . ( $type === 'translation' ? 'en\\' : '' ) : '';

		$name = $name = (string) ( 'Components\\' . Str::studly( ucfirst( $this->getNameInput() ) ) . '\\' . $folder . $filename );
		if ( $this->files->exists( $path = $this->getPath( $name ) ) ) {
			return $this->error( $this->type . ' already exists!' );
		}

		$this->currentStub = __DIR__ . '/stubs/' . $type . '.stub';

		$this->makeDirectory( $path );
		$this->files->put( $path, $this->buildClass( $name ) );
	}

	/**
	 * Build the class with the given name.
	 *
	 * @param  string $name
	 *
	 * @return string
	 */
	protected function buildClass( $name ) {
		$stub = $this->files->get( $this->getStub() );

		return $this->replaceName( $stub, $this->getNameInput() )->replaceNamespace( $stub, $name )->replaceClass( $stub, $name );
	}

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub() {
		return $this->currentStub;
	}

	/**
	 * Replace the name for the given stub.
	 *
	 * @param  string $stub
	 * @param  string $name
	 *
	 * @return string
	 */
	protected function replaceName( &$stub, $name ) {
		$stub = str_replace( 'DummyTitle', $name, $stub );
		$stub = str_replace( 'DummyUCtitle', ucfirst( Str::studly( $name ) ), $stub );

		return $this;
	}

	/**
	 * Get the full namespace name for a given class.
	 *
	 * @param  string $name
	 *
	 * @return string
	 */
	protected function getNamespace( $name ) {
		return trim( implode( '\\', array_map( 'ucfirst', array_slice( explode( '\\', Str::studly( $name ) ), 0, - 1 ) ) ), '\\' );
	}

	/**
	 * Replace the class name for the given stub.
	 *
	 * @param  string $stub
	 * @param  string $name
	 *
	 * @return string
	 */
	protected function replaceClass( $stub, $name ) {
		$class = class_basename( $name );

		return str_replace( 'DummyClass', $class, $stub );
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments() {
		return array(
			[ 'name', InputArgument::REQUIRED, 'Component name.' ],
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() {
		return array(
			[ 'migration', null, InputOption::VALUE_NONE, 'Create new migration files.' ],
			[ 'translation', null, InputOption::VALUE_NONE, 'Create component translation filesystem.' ],
		);
	}

}
