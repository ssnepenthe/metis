<?php
/**
 * Bootable_Service_Provider_Interface interface.
 *
 * @package metis
 */

namespace Metis\Container;

/**
 * Defines the bootable service provider interface.
 */
interface Bootable_Service_Provider_Interface extends Service_Provider_Interface {
	/**
	 * Perform provider specific boot actions.
	 */
	public function boot();
}
