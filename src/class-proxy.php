<?php

namespace Metis;

use Pimple\Container;

class Proxy {
	protected $container;
	protected $key;

	public function __call( $method, $args ) {
		return call_user_func_array( [ $this->container[ $this->key ], $method ], $args );
	}

	public function __construct( Container $container, $key ) {
		$this->container = $container;
		$this->key = strval( $key );
	}
}
