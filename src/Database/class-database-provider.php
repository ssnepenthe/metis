<?php
/**
 * Database_Provider class.
 *
 * @package metis
 */

namespace Metis\Database;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;
use Metis\Container\Service_Provider_Interface;

/**
 * Defines the database provider class.
 */
class Database_Provider implements Service_Provider_Interface {
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
	 * Makes the global $wpdb instance available from the container.
	 */
	public function register() {
		$this->container->bind( 'wp.db', function() {
			global $wpdb;

			return $wpdb;
		} );
	}
}
