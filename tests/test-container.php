<?php

namespace Metis_Tests;

use Metis\Proxy;
use WP_UnitTestCase;
use Pimple\ServiceProviderInterface;
use Metis\Container as MetisContainer;
use Pimple\Container as PimpleContainer;

class Container_Test extends WP_UnitTestCase {
	public function setUp() {
		global $metis_container_test_global;

		$metis_container_test_global = array();
	}

	/** @test */
	public function it_can_invoke_activate_on_all_providers() {
		global $metis_container_test_global;

		$container = new MetisContainer;
		$container->register( new C_A_Provider );

		$this->assertEmpty( $metis_container_test_global );

		$container->activate();

		$this->assertEquals( array( 'C_A_Provider activated' ), $metis_container_test_global );
	}

	/** @test */
	public function it_can_invoke_boot_on_all_providers() {
		global $metis_container_test_global;

		$container = new MetisContainer;
		$container->register( new C_A_Provider );

		$this->assertEmpty( $metis_container_test_global );

		$container->boot();

		$this->assertEquals( array( 'C_A_Provider booted' ), $metis_container_test_global );
	}

	/** @test */
	public function it_can_invoke_deactivate_on_all_providers() {
		global $metis_container_test_global;

		$container = new MetisContainer;
		$container->register( new C_A_Provider );

		$this->assertEmpty( $metis_container_test_global );

		$container->deactivate();

		$this->assertEquals( array( 'C_A_Provider deactivated' ), $metis_container_test_global );
	}

	/** @test */
	public function it_can_invoke_them_all() {
		global $metis_container_test_global;

		$container = new MetisContainer;
		$container->register( new C_A_Provider );
		$container->register( new C_B_Provider );
		$container->register( new C_C_Provider );

		$this->assertEmpty( $metis_container_test_global );

		$container->activate();
		$container->boot();
		$container->deactivate();

		$this->assertEquals(
			array(
				'C_A_Provider activated',
				'C_B_Provider activated',
				'C_A_Provider booted',
				'C_C_Provider booted',
				'C_A_Provider deactivated',
			),
			$metis_container_test_global
		);
	}

	/** @test */
	public function it_can_create_a_proxy_instance() {
		$container = new MetisContainer;
		$container->register( new A_Provider );

		$this->assertInstanceOf( 'Metis\\Proxy', $container->proxy( 'a' ) );
	}
}

class C_A_Provider implements ServiceProviderInterface {
	public function activate() {
		global $metis_container_test_global;

		$metis_container_test_global[] = 'C_A_Provider activated';
	}

	public function boot() {
		global $metis_container_test_global;

		$metis_container_test_global[] = 'C_A_Provider booted';
	}

	public function deactivate() {
		global $metis_container_test_global;

		$metis_container_test_global[] = 'C_A_Provider deactivated';
	}

	public function register( PimpleContainer $container ) {}
}

class C_B_Provider implements ServiceProviderInterface {
	public function activate() {
		global $metis_container_test_global;

		$metis_container_test_global[] = 'C_B_Provider activated';
	}

	public function register( PimpleContainer $container ) {}
}

class C_C_Provider implements ServiceProviderInterface {
	public function boot() {
		global $metis_container_test_global;

		$metis_container_test_global[] = 'C_C_Provider booted';
	}

	public function register( PimpleContainer $container ) {}
}
