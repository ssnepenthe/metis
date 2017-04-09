<?php

namespace Metis_Tests\Unit;

use Mockery;
use Metis\Cache\Repository;
use PHPUnit_Framework_TestCase;
use Metis\Cache\Transient_Store;
use Metis\Cache\Object_Cache_Store;

class Cache_Repository_Test extends PHPUnit_Framework_TestCase {
	function tearDown() {
		Mockery::close();
	}

	/** @test */
	function it_proxies_non_existent_method_calls_to_store() {
		$store = Mockery::mock( Transient_Store::class )
			->shouldReceive( 'forget' )
			->once()
			->andReturn( true )
			->mock();

		$repository = new Repository( $store );

		$this->assertTrue( $repository->forget( 'test' ) );
	}

	/** @test */
	function it_proxies_add_method_if_exists_on_store() {
		$store = Mockery::mock( Object_Cache_Store::class )
			->shouldReceive( 'add' )
			->once()
			->andReturn( true )
			->mock();
		$repository = new Repository( $store );

		$this->assertTrue( $repository->add( 'test', 'value', 0 ) );
	}

	/** @test */
	function it_doesnt_add_entry_if_store_already_has_entry() {
		$store = Mockery::mock( Transient_Store::class )
			->shouldReceive( 'get' )
			->once()
			->andReturn( 'exists' )
			->mock();
		$repository = new Repository( $store );

		$this->assertFalse( $repository->add( 'test', 'value', 0 ) );
	}

	/** @test */
	function it_adds_entry_if_store_doesnt_have_entry() {
		$store = Mockery::mock( Transient_Store::class )
			->shouldReceive( 'get' )
			->once()
			->andReturnNull()
			->shouldReceive( 'put' )
			->once()
			->andReturn( true )
			->mock();
		$repository = new Repository( $store );

		$this->assertTrue( $repository->add( 'test', 'value', 0 ) );
	}

	/** @test */
	function it_returns_user_supplied_default_if_entry_does_not_exist() {
		$store = Mockery::mock( Transient_Store::class )
			->shouldReceive( 'get' )
			->once()
			->andReturnNull()
			->mock();
		$repository = new Repository( $store );

		$this->assertEquals( 'value', $repository->get( 'test', 'value' ) );
	}

	/** @test */
	function it_knows_if_an_entry_exists_in_store() {
		$store = Mockery::mock( Transient_Store::class )
			->shouldReceive( 'get' )
			->twice()
			->andReturn( null, 'value' )
			->mock();
		$repository = new Repository( $store );

		$this->assertFalse( $repository->has( 'test' ) );
		$this->assertTrue( $repository->has( 'test' ) );
	}

	/** @test */
	function it_returns_remembered_value_if_it_already_exists() {
		$store = Mockery::mock( Transient_Store::class )
			->shouldReceive( 'get' )
			->once()
			->andReturn( 'value' )
			->mock();
		$repository = new Repository( $store );

		$this->assertEquals(
			'value',
			$repository->remember( 'test', 0, function() {} )
		);
	}

	/** @test */
	function it_calls_callback_to_generate_remembered_value() {
		$store = Mockery::mock( Transient_Store::class )
			->shouldReceive( 'get' )
			->once()
			->andReturnNull()
			->shouldReceive( 'put' )
			->once()
			->andReturn( true )
			->mock();
		$repository = new Repository( $store );

		$this->assertEquals(
			'it works!',
			$repository->remember( 'test', 0, function() { return 'it works!'; } )
		);
	}
}
