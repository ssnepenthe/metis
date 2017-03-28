<?php

namespace Metis\Container;

interface Bootable_Service_Provider_Interface extends Service_Provider_Interface {
	public function boot();
}
