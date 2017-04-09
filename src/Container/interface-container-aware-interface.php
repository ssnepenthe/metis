<?php
/**
 * Container_Aware_Interface Interface.
 *
 * @package metis
 */

namespace Metis\Container;

/**
 * Defines the container aware interface.
 */
interface Container_Aware_Interface {
	/**
	 * Get the container instance.
	 *
	 * @return Container
	 */
	public function get_container() : Container;

	/**
	 * Set the container instance.
	 *
	 * @param Container $container Container instance.
	 */
	public function set_container( Container $container );
}
