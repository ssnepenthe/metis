<?php
/**
 * Abstract_Store class.
 *
 * @package metis
 */

namespace Metis\Cache;

/**
 * Defines the abstract cache store class.
 */
abstract class Abstract_Store implements Store_Interface {
	/**
	 * Cache prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Decrement a cache entry.
	 *
	 * @param  string $key    Cache key.
	 * @param  int    $amount The amount to increment by.
	 *
	 * @return bool|int        False on failure, current value otherwise.
	 */
	public function decrement( string $key, int $amount = 1 ) {
		return $this->increment( $key, $amount * -1 );
	}

	/**
	 * Save an non-expiring entry to the cache.
	 *
	 * @param  string $key   Cache key.
	 * @param  mixed  $value The value to save in the cache.
	 *
	 * @return bool
	 */
	public function forever( string $key, $value ) {
		return $this->put( $key, $value, 0 );
	}

	/**
	 * Get many entries from the cache.
	 *
	 * Should be overridden in sub-classes where optimization is possible/practical.
	 *
	 * @param  array $keys List of keys to get from the cache.
	 *
	 * @return array
	 */
	public function get_many( array $keys ) {
		$values = array_map( function( $key ) {
			return $this->get( $key );
		}, $keys );

		return array_combine( $keys, $values );
	}

	/**
	 * Get the current cache prefix.
	 *
	 * @return string
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * Increment a cache entry.
	 *
	 * @param  string $key    The cache key.
	 * @param  int    $amount The amount to increment by.
	 *
	 * @return bool|int         False on failure, current value otherwise.
	 */
	public function increment( string $key, int $amount = 1 ) {
		// My preference would be to pre-set value to 0 if it doesn't already exist
		// in cache and return false if current value is non-numeric but it is
		// written this way instead to match WP object cache implmentation.
		$current = $this->get( $key );

		if ( is_null( $current ) ) {
			return false;
		}

		if ( ! is_numeric( $current ) ) {
			$current = 0;
		}

		$amount += $current;

		$this->forever( $key, $amount );

		return $amount;
	}

	/**
	 * Put many entries into the cache.
	 *
	 * Should be overridden in sub-classes where optimization is possible/practical.
	 *
	 * @param  array $values  List of keys to get from the cache.
	 * @param  int   $seconds Time to cache expiration in seconds.
	 */
	public function put_many( array $values, int $seconds ) {
		array_walk( $values, function( $value, $key ) use ( $seconds ) {
			$this->put( $key, $value, $seconds );
		} );
	}

	/**
	 * Hash a given key to create the real cache key.
	 *
	 * @param  string $key Desired cache key.
	 *
	 * @return string
	 */
	protected function hash_key( string $key ) {
		return hash( 'sha1', $key );
	}

	/**
	 * Set the cache prefix.
	 *
	 * @param string $prefix Cache prefix.
	 */
	protected function set_prefix( string $prefix ) {
		$this->prefix = $prefix;
	}
}
