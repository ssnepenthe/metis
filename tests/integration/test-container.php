<?php

namespace Metis_Tests\Integration;

use WP_UnitTestCase;
use function Metis\app;
use Metis\Cache\Cache_Factory;
use Metis\Container\Container;
use Metis\Cache\Cache_Provider;

class Container_Test extends WP_UnitTestCase {
	/** @test */
	function app_provides_static_instance_to_container_instance() {
		$this->assertInstanceOf( Container::class, app() );
		$this->assertSame( app(), app() );
	}

	/** @test */
	function app_provides_shorthand_for_resolving_service_from_container() {
		app()->register( new Cache_Provider( app() ) );

		$this->assertInstanceOf( Cache_Factory::class, app( 'metis.cache' ) );
	}

	/** @test */
	function init_method_hooks_in_to_all_major_extension_loaded_hooks() {
		$c = new Container;

		$hooks = [ 'muplugins_loaded', 'plugins_loaded', 'after_setup_theme' ];

		foreach ( $hooks as $hook ) {
			$this->assertFalse( has_action( $hook, [ $c, 'boot' ] ) );
		}

		$c->init();

		foreach ( $hooks as $hook ) {
			$this->assertNotFalse( has_action( $hook, [ $c, 'boot' ] ) );
		}
	}
}
