<?php

namespace Metis\View;

class Template_Locator_Stack implements Template_Locator_Interface {
	protected $stack = [];

	public function __construct( array $locators = [] ) {
		foreach ( $locators as $locator ) {
			$this->push( $locator );
		}
	}

	public function locate( array $templates ) {
		foreach ( $this->stack as $locator ) {
			$template = $locator->locate( $templates );

			if ( ! empty( $template ) ) {
				return $template;
			}
		}

		return '';
	}

	public function push( Template_Locator_Interface $locator ) {
		$this->stack[] = $locator;
	}
}
