<?php
/**
 * Container class.
 *
 * @package metis
 */

namespace Metis;

use Pimple\ServiceProviderInterface;
use Pimple\Container as PimpleContainer;

/**
 * Defines the container class.
 */
class Container extends PimpleContainer {
	/**
	 * List of registered providers.
	 *
	 * @var ServiceProviderInterface[]
	 */
	protected $providers = array();

	/**
	 * Cached proxy objects.
	 *
	 * @var Proxy[]
	 */
	protected $proxies = array();

	/**
	 * Loop through all registered providers and call the activate method.
	 *
	 * @return void
	 */
	public function activate() {
		$this->invoke_on_providers( 'activate' );
	}

	/**
	 * Loop through all registered providers and call the boot method.
	 *
	 * @return void
	 */
	public function boot() {
		$this->invoke_on_providers( 'boot' );
	}

	/**
	 * Loop through all registered providers and call the deactivate method.
	 *
	 * @return void
	 */
	public function deactivate() {
		$this->invoke_on_providers( 'deactivate' );
	}

	/**
	 * Get a proxy object for a given container entry.
	 *
	 * @param  string $key Container key.
	 *
	 * @return Proxy
	 */
	public function proxy( $key ) {
		if ( isset( $this->proxies[ $key ] ) ) {
			return $this->proxies[ $key ];
		}

		$this->proxies[ $key ] = new Proxy( $this, $key );

		return $this->proxies[ $key ];
	}

	/**
	 * Register a service provider.
	 *
	 * @param  ServiceProviderInterface $provider Provider instance.
	 * @param  array                    $values   Values to customize the provider.
	 *
	 * @return static
	 */
	public function register( ServiceProviderInterface $provider, array $values = array() ) {
		parent::register( $provider, $values );

		$this->providers[] = $provider;

		return $this;
	}

	/**
	 * Invoke a method (if it exists) on all registered providers.
	 *
	 * @param  string $method Method name.
	 *
	 * @return void
	 */
	protected function invoke_on_providers( $method ) {
		foreach ( $this->providers as $provider ) {
			if ( method_exists( $provider, $method ) ) {
				$provider->{$method}( $this );
			}
		}
	}
}
