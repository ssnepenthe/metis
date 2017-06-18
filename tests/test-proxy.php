<?php

namespace Metis_Tests;

use WP_UnitTestCase;
use Metis\Container;

class Proxy_Test extends WP_UnitTestCase {
	/** @test */
	public function it_lazily_resolves_container_entries() {
		global $metis_proxy_test_global;

		$container = new Container;
		$container['a'] = function() {
			return new A;
		};

		$a = $container->proxy( 'a' );

		// Ensure A::__construct() has not been called yet.
		$this->assertNull( $metis_proxy_test_global );

		$a->testing();

		$this->assertEquals( 'adjusted', $metis_proxy_test_global );
	}

	/** @test */
	public function it_proxies_all_method_calls() {
		$container = new Container;
		$container['b'] = function() {
			return new B;
		};

		$this->assertSame( '1', $container->proxy( 'b' )->one() );
		$this->assertSame( '2', $container->proxy( 'b' )->two() );
	}
}

class A {
	public function __construct() {
		global $metis_proxy_test_global;
		$metis_proxy_test_global = 'constructed';
	}

	public function testing() {
		global $metis_proxy_test_global;
		$metis_proxy_test_global = 'adjusted';
	}
}

class B {
	public function one() {
		return '1';
	}

	public function two() {
		return '2';
	}
}
