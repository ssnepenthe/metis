<?php

namespace Metis;

if ( ! function_exists( 'app' ) ) {
	function app( $abstract = null, $parameters = [] ) {
		if ( is_null( $abstract ) ) {
			return Container\Container::getInstance();
		}

		return empty( $parameters )
			? Container\Container::getInstance()->make( $abstract )
			: Container\Container::getInstance()->makeWith( $abstract, $parameters );
	}
}

if ( ! function_exists( 'dd' ) ) {
	/**
	 * Die and dump.
	 */
	function dd( $data ) {
		$func = function_exists( 'dump' ) ? 'dump' : 'var_dump';

		$func( $data );
		die;
	}
}

if ( ! function_exists( 'fd' ) ) {
	/**
	 * Footer dump.
	 */
	function fd( $data ) {
		// Use custom hook so multiple dumps are grouped.
		add_action( 'metis_footer_dump', function() use ( $data ) {
			if ( function_exists( 'dump' ) ) {
				$func = 'dump';
				$tag = 'p';
			} else {
				$func = 'var_dump';
				$tag = 'pre';
			}

			printf( '<%s>', esc_html( $tag ) );
			$func( $data );
			printf( '</%s>', esc_html( $tag ) );
		} );
	}

	add_action( 'wp_footer', function() {
		do_action( 'metis_footer_dump' );
	}, 99 );

	add_action( 'in_admin_footer', function() {
		do_action( 'metis_footer_dump' );
	}, 99 );
}
