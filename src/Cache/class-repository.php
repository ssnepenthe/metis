<?php

namespace Metis\Cache;

use Closure;

// @todo Repository interface?

class Repository {
	protected $store;

	public function __call( $method, $parameters ) {
		return $this->store->{$method}( ...$parameters );
	}

	public function __construct( Store_Interface $store ) {
		$this->store = $store;
	}

	public function add( string $key, $value, int $seconds ) {
		if ( method_exists( $this->store, 'add' ) ) {
			return $this->store->add( $key, $value, $seconds );
		}

		if ( $this->has( $key ) ) {
			return false;
		}

		return $this->put( $key, $value, $seconds );
	}

	public function get( string $key, $default = null ) {
		$value = $this->store->get( $key );

		return is_null( $value ) ? $default : $value;
	}

	public function get_store() {
		return $this->store;
	}

	public function has( string $key ) {
		return ! is_null( $this->get( $key ) );
	}

	public function pull( string $key, $default = null ) {
		$value = $this->get( $key, $default );

		$this->forget( $key );

		return $value;
	}

	public function remember( string $key, int $seconds, Closure $callback ) {
		$value = $this->get( $key );

		if ( ! is_null( $value ) ) {
			return $value;
		}

		$value = $callback();

		$this->put( $key, $value, $seconds );

		return $value;
	}

	public function remember_forever( string $key, Closure $callback ) {
		$this->remember( $key, 0, $callback );
	}
}
