<?php
/**
 * WordPress_Provider class.
 *
 * @package metis
 */

namespace Metis\WordPress;

use Metis\Container\Abstract_Service_Provider;

/**
 * Defines the WordPress provider class.
 */
class WordPress_Provider extends Abstract_Service_Provider {
	/**
	 * Provider specific registration logic.
	 */
	public function register() {
		global $wp, $wpdb, $wp_rewrite, $wp_query;

		$this->get_container()->instance( 'wp', $wp );
		$this->get_container()->instance( 'wp.db', $wpdb );
		$this->get_container()->instance( 'wp.query', $wp_query );
		$this->get_container()->instance( 'wp.rewrite', $wp_rewrite );

		$this->get_container()->bind( 'wp.filesystem', function() {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
			}

			if ( ! isset( $GLOBALS['wp_filesystem'] ) ) {
				WP_Filesystem();
			}

			return $GLOBALS['wp_filesystem'];
		} );

		$this->get_container()->bind( 'wp.object_cache', function() {
			if (
				! isset( $GLOBALS['wp_object_cache'] )
				&& function_exists( 'wp_cache_init' )
			) {
				wp_cache_init();
			}

			return $GLOBALS['wp_object_cache'] ?? null;
		} );
	}
}
