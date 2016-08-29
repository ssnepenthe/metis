<?php

namespace SSNepenthe\Metis;

/**
 * This class provides easy-to-use hooks for managing conditions under which a
 * user must be authenticated and have authorization in order to continue.
 *
 * It also lays the foundation for frontend login.
 */
class Auth {
	/**
	 * @hook
	 *
	 * @tag template_include
	 */
	public function authorization_template_include( $template ) {
		if ( ! apply_filters( 'metis.auth.is_forbidden', false ) ) {
			return $template;
		}

		return get_query_template( '403' );
	}

	/**
	 * @hook
	 */
	public function document_title_parts( array $title ) {
		if ( ! $this->is_login() ) {
			return $title;
		}

		$title['title'] = 'Log In';

		return $title;
	}

	/**
	 * @hook
	 */
	public function init() {
		add_rewrite_rule(
			'^login/?$',
			'index.php?metis_auth=login',
			'top'
		);

		// Remove built-in admin redirects.
		remove_action(
			'template_redirect',
			'wp_redirect_admin_locations',
			1000
		);
	}

	/**
	 * @hook
	 *
	 * @tag template_redirect
	 */
	public function logged_in_template_redirect() {
		if ( ! $this->is_login() ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_safe_redirect( home_url() );
		die;
	}

	/**
	 * @hook
	 *
	 * @tag template_include
	 */
	public function login_template_include( $template ) {
		if ( ! $this->is_login() ) {
			return $template;
		}

		return get_query_template( 'login' );
	}

	/**
	 * @hook
	 *
	 * @tag template_redirect
	 */
	public function login_template_redirect() {
		if ( is_user_logged_in() ) {
			return;
		}

		if ( ! apply_filters( 'metis.auth.login_required', false ) ) {
			return;
		}

		// @todo Filterable fallback path?
		$redirect_path = '';
		$request_uri = '';

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = filter_var(
				$_SERVER['REQUEST_URI'],
				FILTER_SANITIZE_URL
			);
		}

		if ( $request_uri && '/' !== $request_uri ) {
			$redirect_path = add_query_arg(
				'redirect_to',
				false,
				$request_uri
			);
		}

		$redirect_url = add_query_arg(
			[ 'redirect_to' => urlencode( home_url( $redirect_path ) ) ],
			home_url( 'login/' )
		);

		wp_safe_redirect( $redirect_url );
		die;
	}

	/**
	 * @hook
	 */
	public function parse_query( \WP_Query $query ) {
		$query->is_metis_login = false;

		if ( ! isset( $query->query_vars['metis_auth'] ) ) {
			return;
		}

		$query->is_home = false;

		if ( 'login' === $query->query_vars['metis_auth'] ) {
			$query->is_metis_login = true;
		}
	}

	/**
	 * @hook
	 */
	public function pre_handle_404( $short_circuit, \WP_Query $query ) {
		if ( ! $query->is_metis_login ) {
			return $short_circuit;
		}

		return true;
	}

	/**
	 * @hook
	 */
	public function query_vars( array $query_vars ) {
		return array_merge( $query_vars, [ 'metis_auth' ] );
	}

	protected function is_login() {
		global $wp_query;

		if ( $wp_query->is_metis_login ) {
			return true;
		}

		return false;
	}
}
