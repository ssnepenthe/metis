<?php

namespace SSNepenthe\Metis;

/**
 * This class provides easy-to-use hooks for managing conditions under which a
 * user must be autheticated and have authorization in order to continue.
 */
class Auth {
	/**
	 * @hook
	 */
	public function init() {
		add_rewrite_rule(
			'^login/?',
			'index.php?cpp_login=true',
			'top'
		);
	}

	/**
	 * Use the 403.php template when the pageview is forbidden.
	 *
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
	public function query_vars( array $query_vars ) {
		if ( false === array_search( 'cpp_login', $query_vars ) ) {
			$query_vars[] = 'cpp_login';
		}

		return $query_vars;
	}

	/**
	 * Use the login.php template for the 'login' endpoint.
	 *
	 * @hook
	 *
	 * @tag template_include
	 */
	public function login_template_include( $template ) {
		global $wp_query;

		if ( ! $wp_query->get( 'cpp_login' ) ) {
			return $template;
		}

		return get_query_template( 'login' );
	}

	/**
	 * Redirect user to the 'login' endpoint when authentication is required.
	 *
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

		$redirect_arg = '';

		if (
			array_key_exists( 'REQUEST_URI', $_SERVER ) &&
			! empty( $_SERVER['REQUEST_URI'] ) &&
			$url = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL )
		) {
			$redirect_arg = $url;
		}

		$redirect_url = add_query_arg(
			'redirect_to',
			urlencode( $redirect_arg ),
			home_url( 'login/' )
		);

		wp_safe_redirect( $redirect_url );
		die;
	}
}
