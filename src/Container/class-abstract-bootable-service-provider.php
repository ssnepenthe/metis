<?php

namespace Metis\Container;

use Metis\Container\Abstract_Service_Provider as ASP;
use Metis\Container\Bootable_Service_Provider_Interface as BSPI;

abstract class Abstract_Bootable_Service_Provider extends ASP implements BSPI {
	abstract public function boot();
}
