<?php

namespace Metis\Cache;

use WP_Object_Cache;

// @todo Is it appropriate to use prefix as group like this?
// @todo Look into intended use of global cache groups.

class Object_Cache_Store extends Abstract_Store {
	protected $cache;

	public function __construct( WP_Object_Cache $cache, string $prefix = '' ) {
		$this->cache = $cache;
		$this->set_prefix( $prefix );
	}

	public function add( string $key, $value, int $seconds ) {
		return $this->cache->add(
			$this->hash_key( $key ),
			$value,
			$this->prefix,
			max( 0, $seconds )
		);
	}

	public function decrement( string $key, int $amount = 1 ) {
		return $this->cache->decr( $this->hash_key( $key ), $amount, $this->prefix );
	}

	public function flush() {
		if ( ! empty( $this->prefix ) ) {
			return $this->flush_group();
		}

		return $this->cache->flush();
	}

	public function flush_expired() {
		return false;
	}

	public function forget( string $key ) {
		return $this->cache->delete( $this->hash_key( $key ), $this->prefix );
	}

	public function get( string $key ) {
		$value = $this->cache->get(
			$this->hash_key( $key ),
			$this->prefix,
			false,
			$found
		);

		return false === $found ? null : $value;
	}

	public function increment( string $key, int $amount = 1 ) {
		return $this->cache->incr( $this->hash_key( $key ), $amount, $this->prefix );
	}

	public function put( string $key, $value, int $seconds ) {
		return $this->cache->set(
			$this->hash_key( $key ),
			$value,
			$this->prefix,
			max( 0, $seconds )
		);
	}

	protected function flush_group() {
		// Pantheon redis object cache implements this method...
		if ( ! method_exists( $this->cache, 'delete_group' ) ) {
			return false;
		}

		return $this->cache->delete_group( $this->prefix );
	}
}
