<?php
/**
 * Object_Cache_Store class.
 *
 * @package metis
 */

namespace Metis\Cache;

use WP_Object_Cache;

/**
 * Defines the object cache store class.
 *
 * @todo Is it appropriate to use prefix as a cache group like this?
 *       Look into the intended use of global cache groups.
 */
class Object_Cache_Store extends Abstract_Store {
	/**
	 * WP object cache instance.
	 *
	 * @var WP_Object_Cache
	 */
	protected $cache;

	/**
	 * Class constructor.
	 *
	 * @param WP_Object_Cache $cache  WP object cache instance.
	 * @param string          $prefix Cache prefix.
	 */
	public function __construct( WP_Object_Cache $cache, string $prefix = '' ) {
		$this->cache = $cache;
		$this->set_prefix( $prefix );
	}

	/**
	 * Add an entry to the cache if it does not already exist.
	 *
	 * @param string  $key     Cache key.
	 * @param mixed   $value   Value to save to the cache.
	 * @param integer $seconds Time to cache expiration in seconds.
	 *
	 * @return boolean
	 */
	public function add( string $key, $value, int $seconds ) {
		return $this->cache->add(
			$this->hash_key( $key ),
			$value,
			$this->prefix,
			max( 0, $seconds )
		);
	}

	/**
	 * Decrement a cache value.
	 *
	 * @param  string  $key    Cache key.
	 * @param  integer $amount Amount to decrement by.
	 *
	 * @return boolean|integer False on failure, current value otherwise.
	 */
	public function decrement( string $key, int $amount = 1 ) {
		return $this->cache->decr( $this->hash_key( $key ), $amount, $this->prefix );
	}

	/**
	 * Flush the cache.
	 *
	 * @return boolean
	 */
	public function flush() {
		if ( ! empty( $this->prefix ) ) {
			return $this->flush_group();
		}

		return $this->cache->flush();
	}

	/**
	 * Flush expired entries from the cache.
	 *
	 * The object cache does not provide a method for handling this operation.
	 *
	 * @return boolean
	 */
	public function flush_expired() {
		return false;
	}

	/**
	 * Remove an entry from the cache.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return boolean
	 */
	public function forget( string $key ) {
		return $this->cache->delete( $this->hash_key( $key ), $this->prefix );
	}

	/**
	 * Get an entry from the cache.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return mixed
	 */
	public function get( string $key ) {
		$value = $this->cache->get(
			$this->hash_key( $key ),
			$this->prefix,
			false,
			$found
		);

		return false === $found ? null : $value;
	}

	/**
	 * Increment a cache entry.
	 *
	 * @param  string  $key    Cache key.
	 * @param  integer $amount The amount to increment by.
	 *
	 * @return boolean|integer False on failure, current value otherwise.
	 */
	public function increment( string $key, int $amount = 1 ) {
		return $this->cache->incr( $this->hash_key( $key ), $amount, $this->prefix );
	}

	/**
	 * Put an entry in the cache.
	 *
	 * @param  string  $key     Cache key.
	 * @param  mixed   $value   The value to put in the cache.
	 * @param  integer $seconds Time to cache expiration in seconds.
	 *
	 * @return boolean
	 */
	public function put( string $key, $value, int $seconds ) {
		return $this->cache->set(
			$this->hash_key( $key ),
			$value,
			$this->prefix,
			max( 0, $seconds )
		);
	}

	/**
	 * Flush a cache group if the installed object cache supports it.
	 *
	 * @return boolean
	 */
	protected function flush_group() {
		// Pantheon redis object cache implements this method...
		if ( ! method_exists( $this->cache, 'delete_group' ) ) {
			return false;
		}

		return $this->cache->delete_group( $this->prefix );
	}
}
