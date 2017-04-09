<?php
/**
 * View_Factory class.
 *
 * @package metis
 */

namespace Metis\View;

/**
 * Defines the view factory class.
 */
class View_Factory {
	/**
	 * Create a template instance which first looks in the current theme dir before
	 * falling back to the specified directory.
	 *
	 * @param  string|array $dirs Directories in which to look for templates.
	 *
	 * @return Template
	 */
	public function overridable( $dirs ) {
		$locator = new Template_Locator_Stack;
		$locator->push( new Theme_Template_Locator );

		foreach ( $this->get_directory_locators( $dirs ) as $dir_locator ) {
			$locator->push( $dir_locator );
		}

		return new Template( $locator );
	}

	/**
	 * Create a template instance which looks in a specific directory for templates.
	 *
	 * @param  string|array $dirs Directories in which to look for templates.
	 *
	 * @return Template
	 */
	public function plugin( $dirs ) {
		$locators = $this->get_directory_locators( $dirs );

		if ( 1 < count( $dirs ) ) {
			$locator = new Template_Locator_Stack( $locators );
		} else {
			$locator = reset( $locators );
		}

		return new Template( $locator );
	}

	/**
	 * Create a template instance which looks in the current theme directory for
	 * templates.
	 *
	 * @return Template
	 */
	public function theme() {
		return new Template( new Theme_Template_Locator );
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
}
