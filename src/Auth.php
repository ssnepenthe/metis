<?php

namespace SSNepenthe\Metis;

/**
 * This class provides easy-to-use hooks for managing conditions under which a
 * user must be authenticated and have authorization in order to continue.
 */
class Auth {
	/**
	 * @hook
	 */
	public function template_include( $template ) {
		if ( ! apply_filters( 'metis.auth.is_forbidden', false ) ) {
			return $template;
		}

		return get_query_template( '403' );
	}

	/**
	 * @hook
	 */
	public function template_redirect() {
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
			'metis',
			'1',
			trailingslashit( wp_login_url( home_url( $redirect_path ) ) )
		);

		wp_safe_redirect( $redirect_url );
		die;
	}

	/**
	 * @hook
	 */
	public function wp_login_errors( \WP_Error $errors, $redirect_to ) {
		if ( ! isset( $_GET['metis'] ) ) {
			return $errors;
		}

		$errors->add(
			'login_required',
			'You must be logged in to view this page.',
			'message'
		);

		return $errors;
	}
}
