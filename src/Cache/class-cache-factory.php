<?php
/**
 * Cache_Factory class.
 *
 * @package metis
 */

namespace Metis\Cache;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;

/**
 * Defines the cache factory class.
 */
class Cache_Factory {
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
	 * Make a transient repsitory.
	 *
	 * @param  string $prefix Cache prefix.
	 *
	 * @return Repository
	 */
	public function transient( $prefix = '' ) {
		$key = $this->get_instance_key( 'transient', $prefix );

		if ( ! $this->container->bound( $key ) ) {
			$this->container->instance(
				$key,
				new Repository( new Transient_Store(
					$this->container->make( 'wp.db' ),
					$prefix
				) )
			);
		}

		return $this->container->make( $key );
	}

	/**
	 * Make an object cache repository.
	 *
	 * @param  string $prefix Cache prefix.
	 *
	 * @return Repository
	 */
	public function object_cache( $prefix = '' ) {
		$key = $this->get_instance_key( 'object_cache', $prefix );

		if ( ! $this->container->bound( $key ) ) {
			$this->container->instance(
				$key,
				new Repository( new Object_Cache_Store(
					$this->container->make( 'wp.object_cache' ),
					$prefix
				) )
			);
		}

		return $this->container->make( $key );
	}

	/**
	 * Generates a per-instance key for storage in and retrieval from the container.
	 *
	 * @param  string $type   Cache store type.
	 * @param  string $prefix Cache prefix.
	 *
	 * @return string
	 */
	protected function get_instance_key( string $type, string $prefix = '' ) {
		$key = "metis.cache.$type";

		if ( $prefix ) {
			$key .= ".$prefix";
		}

		return $key;
	}
}
