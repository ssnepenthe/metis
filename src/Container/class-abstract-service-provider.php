<?php
/**
 * Abstract_Service_Provider class.
 *
 * @package metis
 */

namespace Metis\Container;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;

/**
 * Defines the abstract service provider class.
 */
abstract class Abstract_Service_Provider implements Service_Provider_Interface {
	use Container_Aware_Trait;

	/**
	 * Class constructor.
	 *
	 * @param Container $container Container instance.
	 */
	public function __construct( Container $container ) {
		$this->set_container( $container );
	}

	/**
	 * Provider specific registration logic.
	 */
	abstract public function register();
}
