<?php

namespace Metis_Tests;

use WP_UnitTestCase;
use Metis\Base_Provider;
use Metis\Container as MetisContainer;
use Pimple\Container as PimpleContainer;

class Base_Provider_Test extends WP_UnitTestCase {
	/** @test */
	function it_is_instantiable() {
		$provider = new BPT_Provider;

		$this->assertInstanceOf( 'Pimple\\ServiceProviderInterface', $provider );
	}

	/** @test */
	function it_can_create_a_proxy_instance() {
		$container = new MetisContainer;
		$provider = new BPT_Provider;

		$this->assertInstanceOf( 'Metis\\Proxy', $provider->create_proxy( $container, 'a' ) );
	}

	/** @test */
	function it_caches_proxy_instances() {
		$container = new MetisContainer;
		$provider = new BPT_Provider;

		$a = $provider->create_proxy( $container, 'a' );

		$this->assertSame( $a, $provider->create_proxy( $container, 'a' ) );
	}
}

class BPT_Provider extends Base_Provider {
	public function create_proxy( $container, $key ) {
		return $this->proxy( $container, $key );
	}

	public function get_proxies() {
		return $this->proxies;
	}

	public function register( PimpleContainer $container ) {
		$container['a'] = new BPT_A;
	}
}

class BPT_A {}
