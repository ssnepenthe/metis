<?php

namespace Metis_Tests\Integration;

use WP_UnitTestCase;
use Metis\Cache\Repository;
use Metis\Container\Container;
use Metis\Cache\Cache_Provider;
use Metis\Cache\Transient_Store;
use Metis\Cache\Object_Cache_Store;

class Cache_Factory_Test extends WP_UnitTestCase {
	protected $container;

	public function setUp() {
		parent::setUp();

		$this->container = new Container;
		$this->container->register( new Cache_Provider( $this->container ) );
	}

	function tearDown() {
		parent::tearDown();

		$this->container = null;
	}

	/** @test */
	function it_can_make_a_transient_repository() {
		$r = $this->container->make( 'metis.cache' )->transient();
		$rp = $this->container->make( 'metis.cache' )->transient( 'pfx' );

		$this->assertInstanceOf( Repository::class, $r );
		$this->assertInstanceOf( Transient_Store::class, $r->get_store() );
		// @todo Object cache store returns without "_" - consistency?
		$this->assertEquals( 'pfx_', $rp->get_prefix() );
	}

	/** @test */
	function it_can_make_an_object_cache_repository() {
		$r = $this->container->make( 'metis.cache' )->object_cache();
		$rp = $this->container->make( 'metis.cache' )->object_cache( 'pfx' );

		$this->assertInstanceOf( Repository::class, $r );
		$this->assertInstanceOf( Object_Cache_Store::class, $r->get_store() );
		$this->assertEquals( 'pfx', $rp->get_prefix() );
	}

	/** @test */
	function it_recycles_repository_instances() {
		$t1 = $this->container->make( 'metis.cache' )->transient();
		$t2 = $this->container->make( 'metis.cache' )->transient( 'pfx' );

		$o1 = $this->container->make( 'metis.cache' )->object_cache();
		$o2 = $this->container->make( 'metis.cache' )->object_cache( 'pfx' );

		$this->assertSame( $t1, $this->container->make( 'metis.cache' )->transient() );
		$this->assertSame( $t2, $this->container->make( 'metis.cache' )->transient( 'pfx' ) );
		$this->assertNotSame( $t2, $this->container->make( 'metis.cache' )->transient( 'xfp' ) );

		$this->assertSame( $o1, $this->container->make( 'metis.cache' )->object_cache() );
		$this->assertSame( $o2, $this->container->make( 'metis.cache' )->object_cache( 'pfx' ) );
		$this->assertNotSame( $o2, $this->container->make( 'metis.cache' )->object_cache( 'xfp' ) );
	}
}
