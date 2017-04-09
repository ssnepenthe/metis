<?php

namespace Metis_Tests\Integration;

use WP_UnitTestCase;
use Metis\Cache\Transient_Store;

class Transient_Store_Test extends WP_UnitTestCase {
	protected $prefix;
	protected $store;
	protected $prefixed_store;

	function setUp() {
		parent::setUp();

		global $wpdb;

		$this->prefix = 'pfx';
		$this->store = new Transient_Store( $wpdb );
		$this->prefixed_store = new Transient_Store( $wpdb, $this->prefix );
	}

	function tearDown() {
		parent::tearDown();

		$this->prefix = null;
		$this->store = null;
		$this->prefixed_store = null;
	}

	protected function generate_real_transient_key( $key, $prefix = false ) {
		$realKey = hash( 'sha1', $key );

		if ( $prefix ) {
			$realKey = $this->prefix . '_' . $realKey;
		}

		return $realKey;
	}

	protected function get_transient( $key ) {
		return get_transient( $this->generate_real_transient_key( $key ) );
	}

	protected function get_transient_timeout( $key ) {
		return get_option(
			'_transient_timeout_' . $this->generate_real_transient_key( $key )
		);
	}

	protected function get_prefixed_transient( $key ) {
		return get_transient( $this->generate_real_transient_key( $key, true ) );
	}

	/** @test */
	function sanity() {
		$this->store->put( 'test', 'one two three', 1 );
		$expiration = time() + 1;
		$this->prefixed_store->put( 'test', 'four five six', 0 );

		$this->assertEquals( 'one two three', $this->get_transient( 'test' ) );
		$this->assertSame( $expiration, $this->get_transient_timeout( 'test' ) );
		$this->assertEquals(
			'four five six',
			$this->get_prefixed_transient( 'test' )
		);
	}

	/** @test */
	function it_can_decrement_an_entry() {
		$this->store->forever( 'test', 3 );
		$this->store->decrement( 'test' );

		$this->assertEquals( 2, $this->get_transient( 'test' ) );
	}

	/** @test */
	function it_can_save_an_entry_forever() {
		$this->store->forever( 'test', 'apple' );

		$this->assertFalse( $this->get_transient_timeout( 'test' ) );
	}

	/** @test */
	function it_can_flush_all_entries() {
		// @todo Test without prefix?
		$this->prefixed_store->forever( 'one', 'apple' );
		$this->prefixed_store->forever( 'two', 'banana' );
		$this->prefixed_store->forever( 'three', 'cherry' );

		$this->assertTrue( $this->prefixed_store->flush() );

		// Make sure we are actually checking the database and not object cache.
		wp_cache_flush();

		foreach ( [ 'one', 'two', 'three' ] as $key ) {
			$this->assertFalse( $this->get_prefixed_transient( $key ) );
		}

		// Returns true on successful flush even if no entries are affected.
		// Only returns false on failure.
		$this->assertTrue( $this->prefixed_store->flush() );
	}

	/** @test */
	function it_can_flush_all_expired_entries() {
		// @todo Test without prefix?
		$this->prefixed_store->forever( 'one', 'apple' );
		$this->prefixed_store->put( 'two', 'banana', 1 );

		// Wait for two to expire.
		sleep( 2 );

		// Should return 1 (count of deleted rows).
		$this->assertTrue( $this->prefixed_store->flush_expired() );

		// Make sure we are actually checking the database and not object cache.
		wp_cache_flush();

		// No expiration so one remains.
		$this->assertEquals( 'apple', $this->get_prefixed_transient( 'one' ) );

		// Two expired so should return false.
		$this->assertFalse( $this->get_prefixed_transient( 'two' ) );

		// Returns true even though no affected rows (only false on error).
		$this->assertTrue( $this->prefixed_store->flush_expired() );
	}

	/** @test */
	function it_can_forget_an_entry() {
		set_transient( $this->generate_real_transient_key( 'test' ), 'value' );

		// Returns true when entry is forgotten.
		$this->assertTrue( $this->store->forget( 'test' ) );

		// Non existent entry returns null.
		$this->assertNull( $this->store->get( 'test' ) );

		// Returns false when entry does not exist.
		$this->assertFalse( $this->store->forget( 'test' ) );
	}

	/** @test */
	function it_can_get_an_entry() {
		set_transient( $this->generate_real_transient_key( 'test' ), 'value' );

		// Non existent should return null, not false.
		$this->assertNull( $this->store->get( 'nope' ) );

		$this->assertEquals( 'value', $this->store->get( 'test' ) );
	}

	/** @test */
	function it_can_get_many_entries() {
		$result = [
			'one' => 'uno',
			'two' => 'dos',
			'three' => 'tres',
		];

		foreach ( $result as $key => $value ) {
			set_transient( $this->generate_real_transient_key( $key ), $value );
		}

		$this->assertEqualSetsWithIndex(
			$result,
			$this->store->get_many( array_keys( $result ) )
		);

		// Includes non existent entries with a value of null.
		$this->assertEqualSetsWithIndex(
			array_merge( $result, [ 'four' => null ] ),
			$this->store->get_many(
				array_merge( array_keys( $result ), [ 'four' ] )
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
		$this->assertEquals( 'value', $this->get_transient( 'test' ) );
	}

	/** @test */
	function it_can_put_many_entries() {
		$transients = [
			'one' => 'uno',
			'two' => 'dos',
			'three' => 'tres',
		];

		$this->store->put_many( $transients, 0 );

		foreach ( $transients as $key => $value ) {
			$this->assertEquals( $value, $this->get_transient( $key ) );
		}
	}
}
