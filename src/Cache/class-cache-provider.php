<?php
/**
 * Cache_Provider class.
 *
 * @package metis
 */

namespace Metis\Cache;

use Metis\Container\Container;
use Metis\WordPress\WordPress_Provider;
use Metis\Container\Abstract_Service_Provider;

/**
 * Defines the cache provider class.
 */
class Cache_Provider extends Abstract_Service_Provider {
	/**
	 * Provider specific registration logic.
	 */
	public function register() {
		$this->get_container()->register(
			// Transient store depends on $wpdb, object cache on $wp_object_cache.
			new WordPress_Provider( $this->get_container() )
		);

		$this->container->singleton(
			'metis.cache',
			function( Container $container ) {
				return new Factory( $container );
			}
		);
	}
}
