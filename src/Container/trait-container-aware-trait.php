<?php

namespace Metis\Container;

trait Container_Aware_Trait {
	protected $container;

	public function get_container() : Container {
		return $this->container;
	}

	public function set_container( Container $container ) {
		$this->container = $container;
	}
}
