<?php
/**
 * Abstract_Bootable_Service_Provider class.
 *
 * @package metis
 */

namespace Metis\Container;

use Metis\Container\Abstract_Service_Provider as ASP;
use Metis\Container\Bootable_Service_Provider_Interface as BSPI;

/**
 * Defines the abstract bootable service provider class.
 */
abstract class Abstract_Bootable_Service_Provider extends ASP implements BSPI {
	/**
	 * Provider specific boot logic.
	 */
	abstract public function boot();
}
