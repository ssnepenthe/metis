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
	 * @param  PimpleContainer $container The container instance.
	 *
	 * @return void
	 */
	public function register( PimpleContainer $container ) {
		$container['wp'] = $container->factory( function() {
			return isset( $GLOBALS['wp'] ) ? $GLOBALS['wp'] : null;
		} );

		$container['wpdb'] = $container->factory( function() {
			return isset( $GLOBALS['wpdb'] ) ? $GLOBALS['wpdb'] : null;
		} );

		$container['wp_query'] = $container->factory( function() {
			return isset( $GLOBALS['wp_query'] ) ? $GLOBALS['wp_query'] : null;
		} );

		$container['wp_rewrite'] = $container->factory( function() {
			return isset( $GLOBALS['wp_rewrite'] ) ? $GLOBALS['wp_rewrite'] : null;
		} );

		$container['wp_filesystem'] = $container->factory( function() {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
			}

			if ( ! isset( $GLOBALS['wp_filesystem'] ) ) {
				WP_Filesystem();
			}

			return $GLOBALS['wp_filesystem'];
		} );

		$container['wp_object_cache'] = $container->factory( function() {
			if ( ! isset( $GLOBALS['wp_object_cache'] ) && function_exists( 'wp_cache_init' ) ) {
				wp_cache_init();
			}

			return isset( $GLOBALS['wp_object_cache'] ) ? $GLOBALS['wp_object_cache'] : null;
		} );
	}
}
