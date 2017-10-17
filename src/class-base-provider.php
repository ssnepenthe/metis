<?php
/**
 * Base provider class.
 *
 * @package metis
 */

namespace Metis;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Defines the base provider abstract class.
 */
abstract class Base_Provider implements ServiceProviderInterface {
	/**
	 * Cached proxy objects.
	 *
	 * @var Proxy[]
	 */
	protected $proxies = array();

	/**
	 * Get a proxy object for a given container entry.
	 *
	 * @param  Container $container Container instance.
	 * @param  string    $key       Container key.
	 *
	 * @return Proxy
	 */
	public function proxy( Container $container, $key ) {
		if ( isset( $this->proxies[ $key ] ) ) {
			return $this->proxies[ $key ];
		}

		$this->proxies[ $key ] = new Proxy( $container, $key );

		return $this->proxies[ $key ];
	}
}
