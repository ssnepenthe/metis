<?php
/**
 * Repository class.
 *
 * @package metis
 */

namespace Metis\Cache;

use Closure;

/**
 * Defines the cache repository class.
 *
 * @todo Repository interface?
 */
class Repository {
	/**
	 * Cache store instance.
	 *
	 * @var Store_Interface
	 */
	protected $store;

	/**
	 * Magic call method - proxies non-existent method calls to the cache store.
	 *
	 * @param  string $method     Method being called.
	 * @param  array  $parameters Parameters passed to the method.
	 *
	 * @return mixed
	 */
	public function __call( $method, $parameters ) {
		return $this->store->{$method}( ...$parameters );
	}

	/**
	 * Class constructor.
	 *
	 * @param Store_Interface $store Store instance.
	 */
	public function __construct( Store_Interface $store ) {
		$this->store = $store;
	}

	/**
	 * Add an entry to the cache if it doesn't exists. Overridable within a store.
	 *
	 * @param  string  $key     Cache key.
	 * @param  mixed   $value   The value to put in the cache.
	 * @param  integer $seconds Time to cache expiration in seconds.
	 *
	 * @return boolean
	 */
	public function add( string $key, $value, int $seconds ) {
		if ( method_exists( $this->store, 'add' ) ) {
			return $this->store->add( $key, $value, $seconds );
		}

		if ( $this->has( $key ) ) {
			return false;
		}

		return $this->put( $key, $value, $seconds );
	}

	/**
	 * Get an entry from the cache, return user provided default if not set.
	 *
	 * @param  string $key     Cache key.
	 * @param  mixed  $default User provded default value.
	 *
	 * @return mixed
	 */
	public function get( string $key, $default = null ) {
		$value = $this->store->get( $key );

		return is_null( $value ) ? $default : $value;
	}

	/**
	 * Get the store instance.
	 *
	 * @return Store_Interface
	 */
	public function get_store() {
		return $this->store;
	}

	/**
	 * Check whether a cache entry exists.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return boolean
	 */
	public function has( string $key ) {
		return ! is_null( $this->get( $key ) );
	}

	/**
	 * Get and remove an entry from the cache.
	 *
	 * @param  string $key     Cache key.
	 * @param  mixed  $default Default value.
	 *
	 * @return mixed
	 */
	public function pull( string $key, $default = null ) {
		$value = $this->get( $key, $default );

		$this->forget( $key );

		return $value;
	}

	/**
	 * Save the result of a closure call to the cache.
	 *
	 * @param  string  $key      Cache key.
	 * @param  integer $seconds  Time to cache expiration in seconds.
	 * @param  Closure $callback Callback to generate cache value.
	 *
	 * @return mixed
	 */
	public function remember( string $key, int $seconds, Closure $callback ) {
		$value = $this->get( $key );

		if ( ! is_null( $value ) ) {
			return $value;
		}

		$value = $callback();

		$this->put( $key, $value, $seconds );

		return $value;
	}

	/**
	 * Save the result of a closure call to the cache without an expiration.
	 *
	 * @param  string  $key      Cache key.
	 * @param  Closure $callback Callback to generate cache value.
	 *
	 * @return mixed
	 */
	public function remember_forever( string $key, Closure $callback ) {
		$this->remember( $key, 0, $callback );
	}
}
