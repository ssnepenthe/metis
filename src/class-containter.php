<?php

namespace Metis;

use Pimple\ServiceProviderInterface;
use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer {
	protected $providers = array();
	protected $proxies = array();

	public function activate() {
		$this->invoke_on_providers( 'activate' );
	}

	public function boot() {
		$this->invoke_on_providers( 'boot' );
	}

	public function deactivate() {
		$this->invoke_on_providers( 'deactivate' );
	}

	public function proxy( $key ) {
		if ( isset( $this->proxies[ $key ] ) ) {
			return $this->proxies[ $key ];
		}

		$this->proxies[ $key ] = new Proxy( $this, $key );

		return $this->proxies[ $key ];
	}

	public function register( ServiceProviderInterface $provider, array $values = array() ) {
		parent::register( $provider, $values );

		$this->providers[] = $provider;

		return $this;
	}

	protected function invoke_on_providers( $method ) {
		foreach ( $this->providers as $provider ) {
			if ( method_exists( $provider, $method ) ) {
				$provider->{$method}( $this );
			}
		}
	}
}
