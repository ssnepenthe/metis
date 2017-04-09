<?php

namespace Metis\Database;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;
use Metis\Container\Service_Provider_Interface;

class Database_Provider implements Service_Provider_Interface {
	use Container_Aware_Trait;

	public function __construct( Container $container ) {
		$this->set_container( $container );
	}

	public function register() {
		$this->container->bind( 'wp.db', function() {
			global $wpdb;

			return $wpdb;
		} );
	}
}
