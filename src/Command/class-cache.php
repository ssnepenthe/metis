<?php
/**
 * Cache command class.
 *
 * @package metis
 */

namespace Metis\Command;

use WP_CLI;
use WP_CLI_Command;
use Metis\Cache\Factory;

/**
 * Advanced object cache management.
 *
 * @todo Consider adding increment/decrement commands.
 */
class Cache extends WP_CLI_Command {
	/**
	 * Cache factory instance.
	 *
	 * @var Factory
	 */
	protected $cache;

	/**
	 * Class constructor.
	 *
	 * @param Factory $cache Cache factory instance.
	 */
	public function __construct( Factory $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Set a cache value if it does not already exist.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The cache key.
	 *
	 * <value>
	 * : The cache value.
	 *
	 * <expiration>
	 * : Time until cache expiration in seconds.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to use as the cache group.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:cache add some_key "some value" 600
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function add( array $args, array $assoc_args ) {
		$this->non_persistent_warning();

		list( $key, $value, $expiration ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );
		$success = $repository->add( $key, $value, absint( $expiration ) );

		if ( $success ) {
			WP_CLI::success( "Cache entry [{$label}] set to [{$value}]" );
		} else {
			if ( $repository->has( $key ) ) {
				WP_CLI::warning( "Cache entry [{$label}] already exists" );
			} else {
				WP_CLI::warning( "Unable to set cache entry [{$label}]" );
			}
		}
	}

	/**
	 * Set a non-expiring cache value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The cache key.
	 *
	 * <value>
	 * : The cache value.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to use as the cache group.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:cache forever some_key "some value"
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function forever( array $args, array $assoc_args ) {
		$this->non_persistent_warning();

		list( $key, $value ) = $args;

		// Just easier to use put command...
		$command = "metis:cache put {$key} {$value} 0";

		if ( isset( $assoc_args['prefix'] ) ) {
			$command .= " --prefix={$assoc_args['prefix']}";
		}

		WP_CLI::runcommand( $command );
	}

	/**
	 * Delete a cache value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The cache key.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to use as the cache group.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:cache forget some_key
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function forget( array $args, array $assoc_args ) {
		$this->non_persistent_warning();

		list( $key ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );
		$success = $repository->forget( $key );

		if ( $success ) {
			WP_CLI::success( "Cache entry [{$label}] deleted successfully" );
		} else {
			if ( $repository->has( $key ) ) {
				WP_CLI::error( "Cache entry [{$label}] exists but was not deleted" );
			} else {
				WP_CLI::warning( "Cache entry [{$label}] does not exist" );
			}
		}
	}

	/**
	 * Delete all cache entries.
	 *
	 * ## OPTIONS
	 *
	 * [--prefix=<prefix>]
	 * : If provided, only cache entries in this group will be deleted.
	 *
	 * [--yes]
	 * : Bypass the "are you sure" message.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:cache flush
	 *
	 * @param array $_          Unusued positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function flush( array $_, array $assoc_args ) {
		$this->non_persistent_warning();

		if (
			isset( $assoc_args['prefix'] )
			&& ! function_exists( 'wp_cache_delete_group' )
		) {
			WP_CLI::error( 'Your object cache does not support prefixed flush' );
		}

		$repository = $this->make_repository( $assoc_args );

		WP_CLI::confirm(
			'Are you sure you want to flush all cache entries?',
			$assoc_args
		);

		$success = $repository->flush();

		if ( $success ) {
			WP_CLI::success( 'Cache flushed successfully' );
		} else {
			WP_CLI::error( 'Unable to flush cache' );
		}
	}

	/**
	 * Get a cache value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The cache key.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to use as the cache group.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:cache get some_key
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function get( array $args, array $assoc_args ) {
		$this->non_persistent_warning();

		list( $key ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );
		$value = $repository->get( $key );

		if ( is_null( $value ) ) {
			WP_CLI::error( "Cache entry [{$label}] does not exist" );
		}

		WP_CLI::print_value( $value );
	}

	/**
	 * Check if a cache entry exists.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The cache key.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to use as the cache group.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:cache has some_key
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function has( array $args, array $assoc_args ) {
		$this->non_persistent_warning();

		list( $key ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );

		if ( $repository->has( $key ) ) {
			WP_CLI::success( "Cache entry [{$label}] is set" );
		} else {
			WP_CLI::warning( "Cache entry [{$label}] is not set" );
		}
	}

	/**
	 * Set a cache value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The cache key.
	 *
	 * <value>
	 * : The cache value.
	 *
	 * <expiration>
	 * : The cache expiration in seconds.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to use as the cache group.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:cache put some_key "some value" 600
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function put( array $args, array $assoc_args ) {
		$this->non_persistent_warning();

		list( $key, $value, $expiration ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );
		$success = $repository->put( $key, $value, absint( $expiration ) );

		if ( $success ) {
			WP_CLI::success( "Cache entry [{$label}] set to [{$value}]" );
		} else {
			if ( $value === $repository->get( $key ) ) {
				WP_CLI::warning(
					"Cache entry [{$label}] is already set to [{$value}]"
				);
			} else {
				WP_CLI::error( "Unable to set cache entry [{$label}]" );
			}
		}
	}

	/**
	 * Generate a label for a given transient from the key and prefix.
	 *
	 * @param  string $key        Transient key.
	 * @param  array  $assoc_args Associative arguments.
	 *
	 * @return string
	 */
	protected function generate_label( string $key, array $assoc_args ) {
		return isset( $assoc_args['prefix'] )
			? $assoc_args['prefix'] . ':' . $key
			: $key;
	}

	/**
	 * Cache repository factory method.
	 *
	 * @param  array $assoc_args Associative arguments.
	 *
	 * @return Metis\Cache\Repository
	 */
	protected function make_repository( array $assoc_args ) {
		$prefix = '';

		if ( isset( $assoc_args['prefix'] ) ) {
			$prefix = (string) $assoc_args['prefix'];
		}

		return $this->cache->object_cache( $prefix );
	}

	/**
	 * Warn the user if they are not using a persitent object cache backend.
	 */
	protected function non_persistent_warning() {
		if ( wp_using_ext_object_cache() ) {
			return;
		}

		WP_CLI::warning(
			'Your site does not appear to be using an external object cache'
			. ' - cache entries will not persist across requests.'
		);
	}
}
