<?php

namespace Metis\View;

class Directory_Template_Locator implements Template_Locator_Interface {
	protected $dir;

	public function __construct( string $dir ) {
		$this->dir = realpath( $dir );
	}

	public function locate( array $templates ) {
		foreach ( $templates as $template ) {
			$template = trailingslashit( $this->dir ) . $template;

			if ( file_exists( $template ) ) {
				return $template;
			}
		}

		return '';
	}
}
