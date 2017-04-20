<?php
/**
 * Package class.
 *
 * @package metis
 */

namespace Metis;

use Metis\Container\Container_Aware_Trait;
use Illuminate\Contracts\Container\Container;

/**
 * Defines the package class.
 */
class Package {
	use Container_Aware_Trait;

	/**
	 * Class constructor.
	 *
	 * @param array          $providers List of providers.
	 * @param Container|null $container Container instance.
	 */
	public function __construct(
		array $providers = [],
		Container $container = null
	) {
		$this->set_container( $container ?: app() );

		foreach ( $providers as $provider ) {
			$this->register( $provider );
		}
	}

	/**
	 * Initialize the container.
	 */
	public function init() {
		$this->get_container()->init();
	}

	/**
	 * Register a provider with the container.
	 *
	 * @param  Service_Provider_Interface|string $provider Provider or class name.
	 */
	public function register( $provider ) {
		if ( is_string( $provider ) ) {
			$provider = new $provider( $this->get_container() );
		}

		$this->get_container()->register( $provider );
	}
}
