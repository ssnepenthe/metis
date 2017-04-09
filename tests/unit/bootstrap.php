<?php

if ( ! function_exists( '_require_once_if_exists' ) ) {
	function _require_once_if_exists( $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

_require_once_if_exists( __DIR__ . '/../../vendor/autoload.php' );
