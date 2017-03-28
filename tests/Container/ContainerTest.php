<?php

use Metis\Container\Container;
use Metis\Container\Service_Provider_Interface;
use Metis\Container\Bootable_Service_Provider_Interface;

class ContainerTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		WP_Mock::setUp();
	}

	public function tearDown() {
		WP_Mock::tearDown();
	}

	/** @test */
	function boot_is_called_on_all_eligible_providers() {
		WP_Mock::userFunction( 'did_action', [
			'args' => 'after_setup_theme',
			'return' => false,
			'times' => 1,
		] );

		$c = new Container;
		$c->register( new class( $c ) implements Bootable_Service_Provider_Interface {
			protected $app;

			public function __construct( $c ) {
				$this->app = $c;
			}

			public function boot() {
				$this->app->bind( 'test', function() { return 'boot'; } );
			}

			public function register() {
				// Do nothing...
			}
		} );
		$c->boot();

		$this->assertEquals( 'boot', $c->make( 'test' ) );
	}

	/** @test */
	function boot_is_only_called_once_per_provider() {
		WP_Mock::userFunction( 'did_action', [
			'args' => 'after_setup_theme',
			'return' => false,
			'times' => 1,
		] );

		$c = new Container;
		$c->register( new class( $c ) implements Bootable_Service_Provider_Interface {
			protected $app;
			protected $test = 0;

			public function __construct( $c ) {
				$this->app = $c;
			}

			public function boot() {
				$value = $this->test++;

				$this->app->bind( 'test', function() use ( $value ) {
					return (string) $value;
				} );
			}

			public function register() {
				// Do nothing...
			}
		} );
		$c->boot();
		$c->boot();

		$this->assertEquals( '0', $c->make( 'test' ) );
	}

	/** @test */
	function a_provider_can_be_re_registered() {
		$c = new Container;
		$p = new class( $c ) implements Service_Provider_Interface {
			protected $app;
			protected $test = 0;

			public function __construct( $c ) {
				$this->app = $c;
			}

			public function register() {
				$value = $this->test++;

				$this->app->bind( 'test', function() use ( $value ) {
					return (string) $value;
				} );
			}
		};
		$c->register( $p );

		$c->register( $p );
		$this->assertEquals( '0', $c->make( 'test' ) );

		$c->register( $p, true );
		$this->assertEquals( '1', $c->make( 'test' ) );
	}

	/** @test */
	function register_is_called_on_provider_registration() {
		$c = new Container;
		$c->register( new class( $c ) implements Service_Provider_Interface {
			protected $app;

			public function __construct( $c ) {
				$this->app = $c;
			}

			public function register() {
				$this->app->bind( 'test', function() { return 'register'; } );
			}
		} );

		$this->assertEquals( 'register', $c->make( 'test' ) );
	}

	/** @test */
	function boot_is_not_called_if_after_setup_theme_is_current_action() {
		// Container turns to reflection since 'test' is not bound.
		$this->expectException( ReflectionException::class );

		WP_Mock::userFunction( 'did_action', [
			'args' => 'after_setup_theme',
			'return' => 1,
			'times' => 1,
		] );
		WP_Mock::userFunction( 'current_action', [
			'return' => 'after_setup_theme',
			'times' => 1,
		] );

		$c = new Container;
		$c->register( new class( $c ) implements Bootable_Service_Provider_Interface {
			protected $app;

			public function __construct( $c ) {
				$this->app = $c;
			}

			public function boot() {
				$this->app->bind( 'test', function() {
					return 'auto boot';
				} );
			}

			public function register() {
				// Do nothing...
			}
		} );

		$this->assertEquals( 'auto boot', $c->make( 'test' ) );
	}

	/** @test */
	function boot_is_called_automatically_if_provider_is_registered_late() {
		WP_Mock::userFunction( 'did_action', [
			'args' => 'after_setup_theme',
			'return' => 1,
			'times' => 1,
		] );
		WP_Mock::userFunction( 'current_action', [
			'return' => 'fake_action',
			'times' => 1,
		] );

		$c = new Container;
		$c->register( new class( $c ) implements Bootable_Service_Provider_Interface {
			protected $app;

			public function __construct( $c ) {
				$this->app = $c;
			}

			public function boot() {
				$this->app->bind( 'test', function() {
					return 'auto boot';
				} );
			}

			public function register() {
				// Do nothing...
			}
		} );

		$this->assertEquals( 'auto boot', $c->make( 'test' ) );
	}
}
