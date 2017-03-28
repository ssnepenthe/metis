<?php

namespace Metis\Container;

use Illuminate\Container\Container as IlluminateContainer;
use Metis\Container\Service_Provider_Interface as Service_Provider;
use Metis\Container\Bootable_Service_Provider_Interface as Bootable_Service_Provider;

class Container extends IlluminateContainer {
	protected $booted_providers = [];
	protected $initialized = false;
	protected $registered_providers = [];

	public function boot() {
		foreach ( $this->booted_providers as $name => $booted ) {
			if ( $booted ) {
				continue;
			}

			$this->boot_provider( $this->registered_providers[ $name ] );
		}
	}

	public function init() {
		if ( $this->initialized ) {
			return;
		}

		add_action( 'muplugins_loaded', [ $this, 'boot' ], 0 );
		add_action( 'plugins_loaded', [ $this, 'boot' ], 0 );
		add_action( 'after_setup_theme', [ $this, 'boot' ], 0 );

		$this->initialized = true;
	}

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

		if ( $this->finished_booting() ) {
			$this->boot_provider( $provider );
		}
	}

	protected function boot_provider( Bootable_Service_Provider $provider ) {
		$provider->boot();
		$this->booted_providers[ get_class( $provider ) ] = true;
	}

	protected function finished_booting() {
		return did_action( 'after_setup_theme' )
			&& 'after_setup_theme' !== current_action();
	}

	protected function register_provider( Service_Provider $provider ) {
		$provider->register();
		$this->registered_providers[ get_class( $provider ) ] = $provider;
	}
}
