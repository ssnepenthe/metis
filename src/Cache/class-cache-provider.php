<?php
/**
 * Cache_Provider class.
 *
 * @package metis
 */

namespace Metis\Cache;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;
use Metis\Container\Service_Provider_Interface;
use Metis\WordPress\WordPress_Provider;

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
