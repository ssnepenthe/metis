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
use Metis\WordPress\WordPress_Provider;
use Metis\Container\Abstract_Bootable_Service_Provider;

/**
 * Defines the command provider class.
 *
 * @todo Consider implementing a command to warm full page caches.
 */
class Command_Provider extends Abstract_Bootable_Service_Provider {
	/**
	 * Provider specific boot logic.
	 */
	public function boot() {
		if ( ! $this->is_cli() ) {
			return;
		}

		WP_CLI::add_command(
			'metis:cache',
			$this->get_container()->make( 'metis.command.cache' )
		);

		WP_CLI::add_command(
			'metis:maintenance',
			$this->get_container()->make( 'metis.command.maintenance' )
		);

		WP_CLI::add_command(
			'metis:transient',
			$this->get_container()->make( 'metis.command.transient' )
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

		$this->get_container()->register(
			// Needed by cache and transient commands.
			new Cache_Provider( $this->get_container() )
		);

		$this->get_container()->register(
			// Maintenance command depends on $wp_filesystem.
			// Technically this is already registered by Cache_Provider.
			new WordPress_Provider( $this->get_container() )
		);

		$this->get_container()->singleton(
			'metis.command.cache',
			function( Container $container ) {
				return new Cache( $container->make( 'metis.cache' ) );
			}
		);

		$this->get_container()->singleton(
			'metis.command.maintenance',
			function( Container $container ) {
				return new Maintenance( $container->make( 'wp.filesystem' ) );
			}
		);

		$this->get_container()->singleton(
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
	 * @return boolean
	 */
	protected function is_cli() {
		return defined( 'WP_CLI' ) && WP_CLI;
	}
}
