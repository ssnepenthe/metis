<?php

namespace Metis\View;

interface Template_Interface {
	public function output( string $name, array $data = [] );
	public function render( string $name, array $data = [] );
}
