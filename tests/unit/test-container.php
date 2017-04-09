<?php

namespace Metis_Tests\Unit;

use Metis\Container\Container;
use PHPUnit_Framework_TestCase;
use Metis\Container\Service_Provider_Interface;
use Metis\Container\Bootable_Service_Provider_Interface;

class Container_Test extends PHPUnit_Framework_TestCase {
	/** @test */
	function boot_is_called_on_all_eligible_providers() {
		$c = new Container;
		$c->register( new class( $c ) implements Service_Provider_Interface {
			protected $c;
			public function __construct( $c ) { $this->c = $c; }
			public function boot() {
				$this->c->bind( 'normal', function() { return 'boot'; } );
			}
			public function register() {
				$this->c->bind( 'normal', function() { return 'register'; } );
			}
		} );
		$c->register( new class( $c ) implements Bootable_Service_Provider_Interface {
			protected $c;
			public function __construct( $c ) { $this->c = $c; }
			public function boot() {
				$this->c->bind( 'bootable', function() { return 'boot'; } );
			}
			public function register() {
				$this->c->bind( 'bootable', function() { return 'register'; } );
			}
		} );
		$c->boot();

		$this->assertEquals( 'register', $c->make( 'normal' ) );
		$this->assertEquals( 'boot', $c->make( 'bootable' ) );
	}

	/** @test */
	function boot_is_only_called_once_per_provider() {
		$c = new Container;
		$c->register( new class( $c ) implements Bootable_Service_Provider_Interface {
			protected $c;
			protected $test = 0;
			public function __construct( $c ) { $this->c = $c; }
			public function boot() {
				$value = $this->test++;
				$this->c->bind( 'test', function() use ( $value ) {
					return (string) $value;
				} );
			}
			public function register() { /* Not important... */ }
		} );
		$c->boot();
		$c->boot();

		$this->assertSame( '0', $c->make( 'test' ) );
	}

	/** @test */
	function a_provider_can_be_re_registered() {
		$c = new Container;
		$p = new class( $c ) implements Service_Provider_Interface {
			protected $c;
			protected $test = 0;
			public function __construct( $c ) { $this->c = $c; }
			public function register() {
				$value = $this->test++;
				$this->c->bind( 'test', function() use ( $value ) {
					return (string) $value;
				} );
			}
		};

		// First registration.
		$c->register( $p );
		$this->assertSame( '0', $c->make( 'test' ) );

		// Second registration without force is ignored.
		$c->register( $p );
		$this->assertSame( '0', $c->make( 'test' ) );

		// Third registration with force updates.
		$c->register( $p, true );
		$this->assertSame( '1', $c->make( 'test' ) );
	}

	/** @test */
	function register_method_is_called_on_provider_when_registered_in_container() {
		$c = new Container;
		$c->register( new class( $c ) implements Service_Provider_Interface {
			protected $c;
			public function __construct( $c ) { $this->c = $c; }
			public function register() {
				$this->c->bind( 'test', function() { return 'register'; } );
			}
		} );

		$this->assertEquals( 'register', $c->make( 'test' ) );
	}
}
