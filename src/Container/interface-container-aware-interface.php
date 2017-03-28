<?php

namespace Metis\Container;

interface Container_Aware_Interface {
	public function get_container() : Container;
	public function set_container( Container $container );
}
