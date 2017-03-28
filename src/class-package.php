<?php

namespace Metis;

use Metis\Container\Container_Aware_Trait;
use Illuminate\Contracts\Container\Container;
use Metis\Container\Container_Aware_Interface;

class Package implements Container_Aware_Interface {
	use Container_Aware_Trait;

	public function __construct(
		array $providers = [],
		Container $container = null
	) {
		$this->set_container( $container ?: app() );

		foreach ( $providers as $provider ) {
			$this->register( $provider );
		}
	}

	public function init() {
		$this->container->init();
	}

	public function register( $provider ) {
		if ( is_string( $provider ) ) {
			$provider = new $provider( $this->container );
		}

		$this->container->register( $provider );
	}
}
