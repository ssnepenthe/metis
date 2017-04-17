<?php

namespace Metis_Tests\Integration;

use WP_UnitTestCase;
use Metis\View\Template;
use Metis\View\View_Provider;
use Metis\Container\Container;
use Metis\View\Template_Locator_Stack;
use Metis\View\Theme_Template_Locator;
use Metis\View\Directory_Template_Locator;

class View_Factory_Test extends WP_UnitTestCase {
	protected $container;

	public function setUp() {
		parent::setUp();

		$this->container = new Container;
		$this->container->register( new View_Provider( $this->container ) );
	}

	function tearDown() {
		parent::tearDown();

		$this->container = null;
	}

	/** @test */
	function it_can_make_an_overridable_template() {
		$t = $this->container->make( 'metis.view' )->overridable( __DIR__ );

		$this->assertInstanceOf( Template::class, $t );
		$this->assertInstanceOf( Template_Locator_Stack::class, $t->locator() );
	}

	/** @test */
	function it_can_make_a_plugin_specific_template() {
		$t = $this->container->make( 'metis.view' )->plugin( __DIR__ );

		$this->assertInstanceOf( Template::class, $t );
		$this->assertInstanceOf( Directory_Template_Locator::class, $t->locator() );
	}

	/** @test */
	function it_can_make_a_plugin_specific_template_with_multiple_directories() {
		$t = $this->container->make( 'metis.view' )->plugin( [
			__DIR__ . '/../unit',
			__DIR__,
		] );

		$this->assertInstanceOf( Template::class, $t );
		$this->assertInstanceOf( Template_Locator_Stack::class, $t->locator() );
	}

	/** @test */
	function it_can_make_a_theme_specific_template() {
		$t = $this->container->make( 'metis.view' )->theme();

		$this->assertInstanceOf( Template::class, $t );
		$this->assertInstanceOf( Theme_Template_Locator::class, $t->locator() );
	}

	/** @test */
	function it_recycles_template_instances() {
		$o = $this->container->make( 'metis.view' )->overridable( __DIR__ );
		$p = $this->container->make( 'metis.view' )->plugin( __DIR__ );
		$t = $this->container->make( 'metis.view' )->theme();

		$this->assertSame(
			$o,
			$this->container->make( 'metis.view' )->overridable( __DIR__ )
		);
		$this->assertNotSame(
			$o,
			$this->container->make( 'metis.view' )->overridable(
				__DIR__ . '/../unit'
			)
		);

		$this->assertSame(
			$p,
			$this->container->make( 'metis.view' )->plugin( __DIR__ )
		);
		$this->assertNotSame(
			$p,
			$this->container->make( 'metis.view' )->plugin( __DIR__ . '/../unit' )
		);

		$this->assertSame( $t, $this->container->make( 'metis.view' )->theme() );

		$this->assertNotSame( $o, $p );
		$this->assertNotSame( $o, $t );
		$this->assertNotSame( $p, $t );
	}
}
