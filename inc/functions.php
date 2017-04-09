<?php
/**
 * Metis helper functions.
 *
 * @package metis
 */

namespace Metis;

if ( ! function_exists( 'app' ) ) {
	/**
	 * Get the global container instance.
	 *
	 * @param  string $abstract   Abstract key to resolve from container.
	 * @param  array  $parameters Parameters to pass to container.
	 *
	 * @return mixed
	 */
	function app( $abstract = null, $parameters = [] ) {
		if ( is_null( $abstract ) ) {
			return Container\Container::getInstance();
		}

		return empty( $parameters )
			? Container\Container::getInstance()->make( $abstract )
			: Container\Container::getInstance()->makeWith( $abstract, $parameters );
	}
}
