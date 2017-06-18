<?php
/**
 * WordPress_Provider class.
 *
 * @package metis
 */

namespace Metis;

use Pimple\ServiceProviderInterface;
use Pimple\Container as PimpleContainer;

/**
 * Defines the WordPress provider class.
 */
class WordPress_Provider implements ServiceProviderInterface {
	/**
	 * Register a number of WordPress globals in the container.
	 *
	 * @param  Container $container The container instance.
	 *
	 * @return void
	 */
	public function register( PimpleContainer $container ) {
		global $wp, $wpdb, $wp_rewrite, $wp_query;

		$container['wp'] = $wp;
		$container['wpdb'] = $wpdb;
		$container['wp_query'] = $wp_query;
		$container['wp_rewrite'] = $wp_rewrite;

		$container['wp_filesystem'] = function( PimpleContainer $c ) {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
			}

			if ( ! isset( $GLOBALS['wp_filesystem'] ) ) {
				WP_Filesystem();
			}

			return $GLOBALS['wp_filesystem'];
		};

		$container['wp_object_cache'] = function( PimpleContainer $c ) {
			if ( ! isset( $GLOBALS['wp_object_cache'] ) && function_exists( 'wp_cache_init' ) ) {
				wp_cache_init();
			}

			return isset( $GLOBALS['wp_object_cache'] ) ? $GLOBALS['wp_object_cache'] : null;
		};
	}
}
