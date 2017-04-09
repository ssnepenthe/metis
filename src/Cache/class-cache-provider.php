<?php
/**
 * Cache_Provider class.
 *
 * @package metis
 */

namespace Metis\Cache;

use Metis\Container\Container;
use Metis\Database\Database_Provider;
use Metis\Container\Container_Aware_Trait;
use Metis\Container\Service_Provider_Interface;

/**
 * Defines the cache provider class.
 */
class Cache_Provider implements Service_Provider_Interface {
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
	 * Provider specific registration logic.
	 */
	public function register() {
		$this->container->register( new Database_Provider( $this->container ) );

		$this->container->bind( 'wp.object_cache', function() {
			global $wp_object_cache;

			return $wp_object_cache;
		} );

		$this->container->singleton(
			'metis.cache',
			function( Container $container ) {
				return new Factory( $container );
			}
		);
	}
}
