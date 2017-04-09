<?php
/**
 * Store_Interface interface.
 *
 * @package metis
 */

namespace Metis\Cache;

/**
 * Defines the cache store interface.
 */
interface Store_Interface {
	/**
	 * Decrement a cache entry.
	 *
	 * @param  string  $key    Cache key.
	 * @param  integer $amount The amount to increment by.
	 *
	 * @return boolean|integer False on failure, current value otherwise.
	 */
	public function decrement( string $key, int $amount = 1 );

	/**
	 * Flush the cache.
	 *
	 * @return boolean
	 */
	public function flush();

	/**
	 * Flush expired entries from the cache.
	 *
	 * @return boolean
	 */
	public function flush_expired();

	/**
	 * Save an non-expiring entry to the cache.
	 *
	 * @param  string $key   Cache key.
	 * @param  mixed  $value The value to save in the cache.
	 *
	 * @return boolean
	 */
	public function forever( string $key, $value );

	/**
	 * Remove an entry from the cache.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return boolean
	 */
	public function forget( string $key );

	/**
	 * Get an entry from the cache.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return mixed
	 */
	public function get( string $key );

	/**
	 * Get many entries from the cache.
	 *
	 * @param  array $keys List of keys to get from the cache.
	 *
	 * @return array
	 */
	public function get_many( array $keys );

	/**
	 * Get the current cache prefix.
	 *
	 * @return string
	 */
	public function get_prefix();

	/**
	 * Increment a cache entry.
	 *
	 * @param  string  $key    The cache key.
	 * @param  integer $amount The amount to increment by.
	 *
	 * @return boolean|integer False on failure, current value otherwise.
	 */
	public function increment( string $key, int $amount = 1 );

	/**
	 * Put an entry in the cache.
	 *
	 * @param  string  $key     Cache key.
	 * @param  mixed   $value   The value to put in the cache.
	 * @param  integer $seconds Time to cache expiration in seconds.
	 *
	 * @return boolean
	 */
	public function put( string $key, $value, int $seconds );

	/**
	 * Put many entries into the cache.
	 *
	 * Should be overridden in sub-classes where optimization is possible/practical.
	 *
	 * @param  array   $values  List of keys to get from the cache.
	 * @param  integer $seconds Time to cache expiration in seconds.
	 */
	public function put_many( array $values, int $seconds );
}
