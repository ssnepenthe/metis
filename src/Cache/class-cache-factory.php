<?php

namespace Metis\Cache;

use Metis\Container\Container;
use Metis\Container\Container_Aware_Trait;

class Factory {
	use Container_Aware_Trait;

	public function __construct( Container $container ) {
		$this->set_container( $container );
	}

	public function transient( $prefix = '' ) {
		$key = $this->get_instance_key( 'transient', $prefix );

		if ( ! $this->container->bound( $key ) ) {
			$this->container->instance(
				$key,
				new Repository( new Transient_Store(
					$this->container->make( 'wp.db' ),
					$prefix
				) )
			);
		}

		return $this->container->make( $key );
	}

	public function object_cache( $prefix = '' ) {
		$key = $this->get_instance_key( 'object_cache', $prefix );

		if ( ! $this->container->bound( $key ) ) {
			$this->container->instance(
				$key,
				new Repository( new Object_Cache_Store(
					$this->container->make( 'wp.object_cache' ),
					$prefix
				) )
			);
		}

		return $this->container->make( $key );
	}

	protected function get_instance_key( string $type, string $prefix = '' ) {
		$key = "metis.cache.$type";

		if ( $prefix ) {
			$key .= ".$prefix";
		}

		return $key;
	}
}
