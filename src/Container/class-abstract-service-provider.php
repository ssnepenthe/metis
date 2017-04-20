<?php

namespace Metis\Container;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;

abstract class Abstract_Service_Provider implements Service_Provider_Interface {
	use Container_Aware_Trait;

	public function __construct( Container $container ) {
		$this->set_container( $container );
	}

	abstract public function register();
}
