<?php
/**
 * View_Factory class.
 *
 * @package metis
 */

namespace Metis\View;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;

/**
 * Defines the view factory class.
 */
class View_Factory {
	use Container_Aware_Trait;

	/**
	 * Class constructor.
	 *
	 * @param Container $container Container instance.
	 */
	public function __construct( Container $container ) {
		$this->set_container( $container );
	}

	/**
	 * Create a template instance which first looks in the current theme dir before
	 * falling back to the specified directory.
	 *
	 * @param  string|array $dirs Directories in which to look for templates.
	 *
	 * @return Template
	 */
	public function overridable( $dirs ) {
		$key = $this->get_instance_key( 'overridable', $dirs );

		if ( ! $this->get_container()->bound( $key ) ) {
			$this->get_container()->singleton( $key, function() use ( $dirs ) {
				$locator = new Template_Locator_Stack;
				$locator->push( new Theme_Template_Locator );

				foreach ( $this->get_directory_locators( $dirs ) as $dir_locator ) {
					$locator->push( $dir_locator );
				}

				return new Template( $locator );
			} );
		}

		return $this->get_container()->make( $key );
	}

	/**
	 * Create a template instance which looks in a specific directory for templates.
	 *
	 * @param  string|array $dirs Directories in which to look for templates.
	 *
	 * @return Template
	 */
	public function plugin( $dirs ) {
		$key = $this->get_instance_key( 'plugin', $dirs );

		if ( ! $this->get_container()->bound( $key ) ) {
			$this->get_container()->singleton( $key, function() use ( $dirs ) {
				$locators = $this->get_directory_locators( $dirs );

				if ( 1 < count( $locators ) ) {
					$locator = new Template_Locator_Stack( $locators );
				} else {
					$locator = reset( $locators );
				}

				return new Template( $locator );
			} );
		}

		return $this->get_container()->make( $key );
	}

	/**
	 * Create a template instance which looks in the current theme directory for
	 * templates.
	 *
	 * @return Template
	 */
	public function theme() {
		$key = $this->get_instance_key( 'theme' );

		if ( ! $this->get_container()->bound( $key ) ) {
			$this->get_container()->singleton( $key, function() {
				return new Template( new Theme_Template_Locator );
			} );
		}

		return $this->get_container()->make( $key );
	}

	/**
	 * Create template locator instances from a list of directories.
	 *
	 * @param  string|array $dirs Template directories.
	 *
	 * @return Directory_Template_Locator[]
	 */
	protected function get_directory_locators( $dirs ) {
		return array_map( function( $dir ) {
			return new Directory_Template_Locator( $dir );
		}, (array) $dirs );
	}

	/**
	 * Generates a per-instance key for storage in and retrieval from the container.
	 *
	 * @param  string $type       Template instance type.
	 * @param  mixed  $identifier Data which uniquely identifies this instance.
	 *
	 * @return string
	 */
	protected function get_instance_key( string $type, $identifier = null ) {
		$key = "metis.view.$type";

		if ( $identifier ) {
			$key .= '.' . hash( 'sha1', serialize( $identifier ) );
		}

		return $key;
	}
}
