<?php

namespace SSNepenthe\Metis;

class Toolbar {
	protected $defaults;
	protected $nodes = [];

	public function __construct( array $args = [] ) {
		$this->defaults = wp_parse_args( $args, [
			// Mirror the defaults in \WP_Admin_Bar->add_node().
			'group'      => false,
			'href'       => false,
			'id'         => false,
			'meta'       => [],
			'parent'     => false,
			'title'      => false,

			// Some Metis-specific extras.

			// Callback to fire when the link is clicked.
			'action_cb'  => '__return_true',
			// User capability needed to fire action_cb.
			'capability' => 'edit_theme_options',
			// Callback to determine whether this node should be shown.
			'display_cb' => '__return_true',
			// Whether or not to generate an href if one is not provided.
			'no_href'    => false,
			// Additional query args to append to href.
			'query_args' => [],
		] );
	}

	public function add_node( array $args ) {
		$args = $this->parse_args( $args );

		if ( ! $args['id'] ) {
			return false;
		}

		if ( ! current_user_can( $args['capability'] ) ) {
			return false;
		}

		if (
			! is_callable( $args['action_cb'] ) ||
			! is_callable( $args['display_cb'] )
		) {
			return false;
		}

		$this->nodes[ $args['id'] ] = $args;

		// After the first node is added, set it as the default parent node.
		if ( 1 === count( $this->nodes ) ) {
			$this->defaults['parent'] = $args['id'];
		}

		return true;
	}

	public function add_nodes( array $nodes ) {
		foreach ( $nodes as $args ) {
			if ( ! is_array( $args ) ) {
				continue;
			}

			$this->add_node( $args );
		}
	}

	/**
	 * @hook
	 *
	 * @priority 999
	 */
	public function admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {
		if ( empty( $this->nodes ) ) {
			return;
		}

		foreach ( $this->nodes as $id => $args ) {
			if ( call_user_func( $args['display_cb'] ) ) {
				$wp_admin_bar->add_node( $args );
			}
		}
	}

	/**
	 * @hook
	 *
	 * @priority 999
	 */
	public function admin_init() {
		if ( empty( $this->nodes ) ) {
			return;
		}

		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$nonce = filter_input( INPUT_GET, '_mtbnonce', FILTER_SANITIZE_STRING );

		if ( ! $action || ! $nonce ) {
			// Variable is not set or sanitization failed.
			return;
		}

		$action = sanitize_html_class( $action );

		if ( ! $action ) {
			// Secondary sanitization failed.
			return;
		}

		if ( is_null( $node = $this->get_node( $action ) ) ) {
			// Action doesn't correspond to a valid toolbar node.
			return;
		}

		$intended = wp_verify_nonce(
			$nonce,
			sprintf( '%s\\%s', __CLASS__, $action )
		);
		$allowed = current_user_can( $node['capability'] );

		// I don't see a reason to allow an nonce older than 12 hours...
		if ( 1 !== $intended || ! $allowed ) {
			return;
		}

		if ( ! is_callable( $node['action_cb'] ) ) {
			// Can't add a node if action_cb is not callable, but just in case.
			return;
		}

		call_user_func( $node['action_cb'] );
		wp_safe_redirect( wp_get_referer() );
		exit;
	}

	public function get_node( $id ) {
		if ( ! is_string( $id ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The id parameter is required to be string, was: %s',
				gettype( $id )
			) );
		}

		if ( isset( $this->nodes[ $id ] ) ) {
			return $this->nodes[ $id ];
		}

		return null;
	}

	public function remove_node( $id ) {
		if ( ! is_string( $id ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The id parameter is required to be string, was: %s',
				gettype( $id )
			) );
		}

		if ( ! isset( $this->nodes[ $id ] ) ) {
			return false;
		}

		// Don't remove the primary node unless it is the only node.
		if ( $id === $this->defaults['parent'] && 1 < count( $this->nodes ) ) {
			return false;
		}

		unset( $this->nodes[ $id ] );

		return true;
	}

	protected function parse_args( array $args ) {
		$args = wp_parse_args( $args, $this->defaults );

		// Sanitize node ID and set title as needed.
		if ( $args['id'] ) {
			$args['id'] = sanitize_html_class( $args['id'] );

			if ( '' === $args['id'] ) {
				$args['id'] = false;
			}
		}

		if ( ! $args['title'] && $args['id'] ) {
			$args['title'] = ucwords( str_replace(
				[ '-', '_' ],
				' ',
				$args['id']
			) );
		}

		// Return early if href is not needed.
		if ( $args['no_href'] ) {
			return $args;
		}

		// Otherwise let's make sure our href is set up.
		if ( ! $args['href'] ) {
			$args['href'] = admin_url( 'index.php' );
		}

		$args['href'] = add_query_arg(
			'action',
			$args['id'],
			$args['href']
		);

		// If you supply 'action', it will override 'action' set above.
		if ( $args['query_args'] && is_array( $args['query_args'] ) ) {
			$args['href'] = add_query_arg(
				$args['query_args'],
				$args['href']
			);
		}

		$args['href'] = wp_nonce_url(
			$args['href'],
			sprintf( '%s\\%s', __CLASS__, $args['id'] ),
			'_mtbnonce'
		);

		return $args;
	}
}
