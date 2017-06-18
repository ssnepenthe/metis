<?php
/**
 * Proxy class.
 *
 * @package metis
 */

namespace Metis;

use Pimple\Container as PimpleContainer;

/**
 * Defines the proxy class.
 */
class Proxy {
	/**
	 * Container instance.
	 *
	 * @var PimpleContainer
	 */
	protected $container;

	/**
	 * Container entry key.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Proxy all method calls to underlying container entry.
	 *
	 * @param  string $method Method name.
	 * @param  array  $args   Arguments passed to $method.
	 *
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		return call_user_func_array( array( $this->container[ $this->key ], $method ), $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param PimpleContainer $container Container instance.
	 * @param string          $key       Container entry key.
	 */
	public function __construct( PimpleContainer $container, $key ) {
		$this->container = $container;
		$this->key = strval( $key );
	}
}
