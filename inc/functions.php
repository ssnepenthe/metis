<?php

namespace SSNepenthe\Metis\Functions;

function get_redirect_to_url() {
	// Ensure URL is valid and host exists in the valid hosts whitelist.
	$default = wp_validate_redirect( filter_var(
		apply_filters( 'metis.auth.redirect_to.default', home_url() ),
		FILTER_VALIDATE_URL
	), false );

	// $default can be a valid URL, empty string or false at this point.
	if ( ! $default ) {
		_doing_it_wrong(
			'Filter: metis.auth.redirect_to.default',
			'You have supplied an invalid default. Reverting to value of home_url()',
			null
		);

		$default = home_url();
	}

	$requested = filter_input( INPUT_GET, 'redirect_to', FILTER_VALIDATE_URL );

	if ( ! $requested ) {
		$requested = $default;
	}

	$url = wp_validate_redirect( $requested, $default );

	return $url;
}
