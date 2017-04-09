<?php
/**
 * Container_Aware_Trait trait.
 *
 * @package metis
 */

namespace Metis\Container;

/**
 * Defines the container aware trait.
 */
trait Container_Aware_Trait {
	/**
	 * Container instance.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Get the container instance.
	 *
	 * @return Container
	 */
	public function get_container() : Container {
		return $this->container;
	}

	/**
	 * Set the container instance.
	 *
	 * @param Container $container Container instance.
	 */
	public function set_container( Container $container ) {
		$this->container = $container;
	}
}
