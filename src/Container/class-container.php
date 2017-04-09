<?php
/**
 * Container class.
 *
 * @package metis
 */

namespace Metis\Container;

use Illuminate\Container\Container as IlluminateContainer;
use Metis\Container\Service_Provider_Interface as Service_Provider;
use Metis\Container\Bootable_Service_Provider_Interface as Bootable_Service_Provider;

/**
 * Defines the container class.
 */
class Container extends IlluminateContainer {
	/**
	 * List of bootable providers and whether they have been booted.
	 *
	 * @var array
	 */
	protected $booted_providers = [];

	/**
	 * Whether the current instance has been hooked in to WordPress.
	 *
	 * @var boolean
	 */
	protected $initialized = false;

	/**
	 * List of registered providers.
	 *
	 * @var array
	 */
	protected $registered_providers = [];

	/**
	 * Loop through registered providers and call boot where applicable.
	 */
	public function boot() {
		foreach ( $this->booted_providers as $name => $booted ) {
			if ( $booted ) {
				continue;
			}

			$this->boot_provider( $this->registered_providers[ $name ] );
		}
	}

	/**
	 * Hook the container instance in to WordPress.
	 */
	public function init() {
		if ( $this->initialized ) {
			return;
		}

		add_action( 'muplugins_loaded', [ $this, 'boot' ], 0 );
		add_action( 'plugins_loaded', [ $this, 'boot' ], 0 );
		add_action( 'after_setup_theme', [ $this, 'boot' ], 0 );

		$this->initialized = true;
	}

	/**
	 * Register a provider instance.
	 *
	 * @param  Service_Provider $provider Provider instance.
	 * @param  boolean          $force    Override existing provider of same type.
	 */
	public function register( Service_Provider $provider, bool $force = false ) {
		$name = get_class( $provider );

		if ( isset( $this->registered_providers[ $name ] ) && ! $force ) {
			return;
		}

		$this->register_provider( $provider );

		if ( ! $provider instanceof Bootable_Service_Provider ) {
			return;
		}

		$this->booted_providers[ $name ] = false;
	}

	/**
	 * Call the boot method on a given provider instance.
	 *
	 * @param  Bootable_Service_Provider $provider Bootable provider instance.
	 */
	protected function boot_provider( Bootable_Service_Provider $provider ) {
		$provider->boot();
		$this->booted_providers[ get_class( $provider ) ] = true;
	}

	/**
	 * Register a provider instance.
	 *
	 * @param  Service_Provider $provider Provider instance.
	 */
	protected function register_provider( Service_Provider $provider ) {
		$provider->register();
		$this->registered_providers[ get_class( $provider ) ] = $provider;
	}
}
