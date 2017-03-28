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
