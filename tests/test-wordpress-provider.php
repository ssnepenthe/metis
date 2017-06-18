<?php

namespace Metis_Tests;

use WP_UnitTestCase;
use Metis\WordPress_Provider;
use Metis\Container as MetisContainer;

class WordPress_Provider_Test extends WP_UnitTestCase {
	/** @test */
	public function it_provides_access_to_wp_globals_from_container() {
		$container = new MetisContainer;
		$container->register( new WordPress_Provider );

		$this->assertSame( $GLOBALS['wp'], $container['wp'] );
		$this->assertSame( $GLOBALS['wpdb'], $container['wpdb'] );
		$this->assertSame( $GLOBALS['wpdb'], $container['wpdb'] );
		$this->assertSame( $GLOBALS['wp_query'], $container['wp_query'] );
		$this->assertSame( $GLOBALS['wp_rewrite'], $container['wp_rewrite'] );
		$this->assertSame( $GLOBALS['wp_object_cache'], $container['wp_object_cache'] );
	}
}
