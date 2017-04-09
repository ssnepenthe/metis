<?php
/**
 * Service_Provider_Interface interface.
 *
 * @package metis
 */

namespace Metis\Container;

/**
 * Defines the service provider interface.
 */
interface Service_Provider_Interface {
	/**
	 * Perform provider specific registrations.
	 */
	public function register();
}
