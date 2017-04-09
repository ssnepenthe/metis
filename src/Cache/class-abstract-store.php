<?php

namespace Metis\Cache;

abstract class Abstract_Store implements Store_Interface {
	protected $prefix;

	public function decrement( string $key, int $value = 1 ) {
		return $this->increment( $key, $value * -1 );
	}

	public function forever( string $key, $value ) {
		return $this->put( $key, $value, 0 );
	}

	// Can optimize on a per-store basis where possible/practical.
	public function get_many( array $keys ) {
		$values = array_map( function( $key ) {
			return $this->get( $key );
		}, $keys );

		return array_combine( $keys, $values );
	}

	public function get_prefix() {
		return $this->prefix;
	}

	public function increment( string $key, int $value = 1 ) {
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

		$value += $current;

		$this->forever( $key, $value );

		return $value;
	}

	// Can optimize on a per-store basis where possible/practical.
	public function put_many( array $values, int $seconds ) {
		array_walk( $values, function( $value, $key ) use ( $seconds ) {
			$this->put( $key, $value, $seconds );
		} );
	}

	protected function hash_key( string $key ) {
		return hash( 'sha1', $key );
	}

	protected function set_prefix( string $prefix ) {
		$this->prefix = $prefix;
	}
}
