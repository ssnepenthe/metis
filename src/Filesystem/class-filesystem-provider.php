<?php
/**
 * Filesystem_Provider class.
 *
 * @package metis
 */

namespace Metis\Filesystem;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;
use Metis\Container\Service_Provider_Interface;

/**
 * Defines the filesystem provider class
 */
class Filesystem_Provider implements Service_Provider_Interface {
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
	 * Make the global $wp_filesystem instance available in the container.
	 */
	public function register() {
		$this->container->bind( 'wp.filesystem', function() {
			global $wp_filesystem;

			// @todo ???
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
			}

			if ( is_null( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			return $wp_filesystem;
		} );
	}
}
