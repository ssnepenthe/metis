<?php
/**
 * View_Provider class.
 *
 * @package metis.
 */

namespace Metis\View;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;
use Metis\Container\Service_Provider_Interface;

/**
 * Defines the view provider class.
 */
class View_Provider implements Service_Provider_Interface {
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
	 * Perform view registrations.
	 */
	public function register() {
		$this->container->bind( 'metis.view', function( Container $container ) {
			return new View_Factory( $container );
		} );
	}
}
