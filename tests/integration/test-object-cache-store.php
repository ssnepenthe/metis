<?php

namespace Metis_Tests\Integration;

use WP_UnitTestCase;
use Metis\Cache\Object_Cache_Store;

class Object_Cache_Store_Test extends WP_UnitTestCase {
	protected $prefix;
	protected $store;
	protected $prefixed_store;

	function setUp() {
		parent::setUp();

		global $wp_object_cache;

		$this->prefix = 'pfx';
		$this->store = new Object_Cache_Store( $wp_object_cache );
		$this->prefixed_store = new Object_Cache_Store(
			$wp_object_cache,
			$this->prefix
		);
	}

	function tearDown() {
		parent::tearDown();

		$this->prefix = null;
		$this->store = null;
		$this->prefixed_store = null;
	}

	protected function generate_real_cache_key( $key ) {
		return hash( 'sha1', $key );
	}

	protected function cache_get( $key, $prefixed = false ) {
		return wp_cache_get(
			$this->generate_real_cache_key( $key ),
			$prefixed ? $this->prefix : ''
		);
	}

	protected function prefixed_cache_get( $key ) {
		return $this->cache_get( $key, true );
	}

	/** @test */
	function sanity() {
		$this->store->put( 'test', 'one two three', 0 );
		$this->prefixed_store->put( 'test', 'four five six', 0 );

		$this->assertEquals( 'one two three', $this->cache_get( 'test' ) );
		$this->assertEquals( 'four five six', $this->prefixed_cache_get( 'test' ) );
	}

	/** @test */
	function it_can_decrement_an_entry() {
		$this->store->forever( 'test', 3 );
		$this->store->decrement( 'test' );

		$this->assertEquals( 2, $this->cache_get( 'test' ) );
	}

	/** @test */
	function it_can_save_an_entry_forever() {
		// @todo ???
	}

	/** @test */
	function it_can_flush_all_entries() {
		$this->store->forever( 'one', 'apple' );
		$this->store->forever( 'two', 'banana' );
		$this->store->forever( 'three', 'cherry' );

		$this->assertTrue( $this->store->flush() );

		foreach ( [ 'one', 'two', 'three' ] as $key ) {
			$this->assertFalse( $this->prefixed_cache_get( $key ) );
		}

		// Core cache function should always return true.
		$this->assertTrue( $this->store->flush() );
	}

	/** @test */
	function it_can_forget_an_entry() {
		wp_cache_set( $this->generate_real_cache_key( 'test' ), 'value' );

		// Returns true when entry is forgotten.
		$this->assertTrue( $this->store->forget( 'test' ) );

		// Non existent entry returns null.
		$this->assertNull( $this->store->get( 'test' ) );

		// Returns false when entry does not exist.
		$this->assertFalse( $this->store->forget( 'test' ) );
	}

	/** @test */
	function it_can_get_an_entry() {
		wp_cache_set( $this->generate_real_cache_key( 'test' ), 'value' );

		// Non existent should return null, not false.
		$this->assertNull( $this->store->get( 'nope' ) );
		$this->assertEquals( 'value', $this->store->get( 'test' ) );
	}

	/** @test */
	function it_can_get_many_entries() {
		$entries = [
			'one' => 'uno',
			'two' => 'dos',
			'three' => 'tres',
		];

		foreach ( $entries as $key => $value ) {
			wp_cache_set( $this->generate_real_cache_key( $key ), $value );
		}

		$this->assertEqualSetsWithIndex(
			$entries,
			$this->store->get_many( array_keys( $entries ) )
		);

		// Includes non existent entries with a value of null.
		$this->assertEqualSetsWithIndex(
			array_merge( $entries, [ 'four' => null ] ),
			$this->store->get_many(
				array_merge( array_keys( $entries ), [ 'four' ] )
			)
		);
	}

	/** @test */
	function it_can_increment_an_entry() {
		// It returns false if entry does not already exist.
		$this->assertFalse( $this->store->increment( 'test' ) );

		// It restarts from 0 if entry exists but is not numeric.
		$this->store->forever( 'test', 'value' );
		$this->assertSame( 1, $this->store->increment( 'test' ) );

		// Otherwise it increments from the previously set value.
		$this->store->forever( 'test', 4 );
		$this->assertSame( 5, $this->store->increment( 'test' ) );

		// And can accept an arbitrary number to increment by.
		$this->store->forever( 'test', 10 );
		$this->assertSame( 25, $this->store->increment( 'test', 15 ) );
	}

	/** @test */
	function it_can_put_an_entry() {
		$this->assertTrue( $this->store->put( 'test', 'value', 60 ) );
		$this->assertEquals( 'value', $this->cache_get( 'test' ) );
	}

	/** @test */
	function it_can_put_many_entries() {
		$entries = [
			'one' => 'uno',
			'two' => 'dos',
			'three' => 'tres',
		];

		$this->store->put_many( $entries, 0 );

		foreach ( $entries as $key => $value ) {
			$this->assertEquals( $value, $this->cache_get( $key ) );
		}
	}
}
