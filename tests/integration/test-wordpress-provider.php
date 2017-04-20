<?php

namespace Metis_Tests\Integration;

use WP;
use wpdb;
use WP_Query;
use WP_Rewrite;
use WP_Object_Cache;
use WP_UnitTestCase;
use WP_Filesystem_Base;
use Metis\Container\Container;
use Metis\WordPress\WordPress_Provider;

class WordPress_Provider_Test extends WP_UnitTestCase {
	protected $container;

	function setUp() {
		$this->container = new Container;
		$this->container->register( new WordPress_Provider( $this->container ) );
	}

	function tearDown() {
		$this->container = null;
	}

	/** @test */
	function it_makes_wp_global_available_in_container() {
		global $wp;

		$this->assertInstanceOf( WP::class, $this->container->make( 'wp' ) );
		$this->assertSame( $wp, $this->container->make( 'wp' ) );
		$this->assertSame(
			$this->container->make( 'wp' ),
			$this->container->make( 'wp' )
		);
	}

	/** @test */
	function it_makes_wpdb_global_available_in_container() {
		global $wpdb;

		$this->assertInstanceOf( wpdb::class, $this->container->make( 'wp.db' ) );
		$this->assertSame( $wpdb, $this->container->make( 'wp.db' ) );
		$this->assertSame(
			$this->container->make( 'wp.db' ),
			$this->container->make( 'wp.db' )
		);
	}

	/** @test */
	function it_makes_wp_rewrite_global_available_in_container() {
		global $wp_rewrite;

		$this->assertInstanceOf(
			WP_Rewrite::class,
			$this->container->make( 'wp.rewrite' )
		);
		$this->assertSame( $wp_rewrite, $this->container->make( 'wp.rewrite' ) );
		$this->assertSame(
			$this->container->make( 'wp.rewrite' ),
			$this->container->make( 'wp.rewrite' )
		);
	}

	/** @test */
	function it_makes_wp_query_global_available_in_container() {
		global $wp_query;

		$this->assertInstanceOf(
			WP_Query::class,
			$this->container->make( 'wp.query' )
		);
		$this->assertSame( $wp_query, $this->container->make( 'wp.query' ) );
		$this->assertSame(
			$this->container->make( 'wp.query' ),
			$this->container->make( 'wp.query' )
		);
	}

	/** @test */
	function it_makes_wp_filesystem_global_available_in_container() {
		global $wp_filesystem;

		$this->assertInstanceOf(
			WP_Filesystem_Base::class,
			$this->container->make( 'wp.filesystem' )
		);
		$this->assertSame(
			$wp_filesystem,
			$this->container->make( 'wp.filesystem' )
		);
		$this->assertSame(
			$this->container->make( 'wp.filesystem' ),
			$this->container->make( 'wp.filesystem' )
		);
	}

	/** @test */
	function it_makes_wp_object_cache_global_available_in_container() {
		global $wp_object_cache;

		$this->assertInstanceOf(
			WP_Object_Cache::class,
			$this->container->make( 'wp.object_cache' )
		);
		$this->assertSame(
			$wp_object_cache,
			$this->container->make( 'wp.object_cache' )
		);
		$this->assertSame(
			$this->container->make( 'wp.object_cache' ),
			$this->container->make( 'wp.object_cache' )
		);
	}
}
