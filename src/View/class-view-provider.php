<?php
/**
 * View_Provider class.
 *
 * @package metis.
 */

namespace Metis\View;

use Metis\Container\Container;
use Metis\Container\Abstract_Service_Provider;

/**
 * Defines the view provider class.
 */
class View_Provider extends Abstract_Service_Provider {
	/**
	 * Perform view registrations.
	 */
	public function register() {
		$this->container->bind( 'metis.view', function( Container $container ) {
			return new View_Factory( $container );
		} );
	}
}
