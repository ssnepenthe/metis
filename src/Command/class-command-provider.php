<?php
/**
 * Command_Provider class.
 *
 * @package metis
 */

namespace Metis\Command;

use WP_CLI;
use Metis\Container\Container;
use Metis\Cache\Cache_Provider;
use Metis\Filesystem\Filesystem_Provider;
use Metis\Container\Container_Aware_Trait;
use Metis\Container\Bootable_Service_Provider_Interface;

/**
 * Defines the command provider class.
 *
 * @todo Consider implementing a command to warm full page caches.
 */
class Command_Provider implements Bootable_Service_Provider_Interface {
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
	 * Provider specific boot logic.
	 */
	public function boot() {
		if ( ! $this->is_cli() ) {
			return;
		}

		WP_CLI::add_command(
			'metis:cache',
			$this->container->make( 'metis.command.cache' )
		);

		WP_CLI::add_command(
			'metis:maintenance',
			$this->container->make( 'metis.command.maintenance' )
		);

		WP_CLI::add_command(
			'metis:transient',
			$this->container->make( 'metis.command.transient' )
		);
	}

	/**
	 * Provider specific registration logic.
	 */
	public function register() {
		// Commands extend WP_CLI_Command which might not exist.
		if ( ! $this->is_cli() ) {
			return;
		}

		$this->container->register( new Cache_Provider( $this->container ) );

		$this->container->register( new Filesystem_Provider( $this->container ) );

		$this->container->bind(
			'metis.command.cache',
			function( Container $container ) {
				return new Cache( $container->make( 'metis.cache' ) );
			}
		);

		$this->container->bind(
			'metis.command.maintenance',
			function( Container $container ) {
				return new Maintenance( $container->make( 'wp.filesystem' ) );
			}
		);

		$this->container->bind(
			'metis.command.transient',
			function( Container $container ) {
				return new Transient(
					$container->make( 'wp.db' ),
					$container->make( 'metis.cache' )
				);
			}
		);
	}

	/**
	 * Determine if the current request is via WP-CLI.
	 *
	 * @return bool
	 */
	protected function is_cli() {
		return defined( 'WP_CLI' ) && WP_CLI;
	}
}
