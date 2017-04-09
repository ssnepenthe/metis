<?php

namespace Metis\View;

class View_Factory {
	public function overridable( $dirs ) {
		$locator = new Template_Locator_Stack;
		$locator->push( new Theme_Template_Locator );

		foreach ( $this->get_directory_locators( $dirs ) as $dir_locator ) {
			$locator->push( $dir_locator );
		}

		return new Template( $locator );
	}

	public function plugin( $dirs ) {
		$locators = $this->get_directory_locators( $dirs );

		if ( 1 < count( $dirs ) ) {
			$locator = new Template_Locator_Stack( $locators );
		} else {
			$locator = reset( $locators );
		}

		return new Template( $locator );
	}

	public function theme() {
		return new Template( new Theme_Template_Locator );
	}

	protected function get_directory_locators( $dirs ) {
		return array_map( function( $dir ) {
			return new Directory_Template_Locator( $dir );
		}, (array) $dirs );
	}
}
