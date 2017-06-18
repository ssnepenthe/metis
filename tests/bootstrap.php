<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

if ( ! function_exists( '_metis_require_once_if_exists' ) ) {
	function _metis_require_once_if_exists( $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

function _metis_manually_load_plugin() {
	_metis_require_once_if_exists( __DIR__ . '/../vendor/autoload.php' );
}
tests_add_filter( 'muplugins_loaded', '_metis_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
