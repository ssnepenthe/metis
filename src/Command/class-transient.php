<?php

namespace Metis\Command;

use wpdb;
use WP_CLI;
use WP_CLI_Command;
use WP_CLI\Formatter;
use Metis\Cache\Factory;
use function WP_CLI\Utils\get_flag_value;
use function WP_CLI\Utils\make_progress_bar;

/**
 * Advanced transient management.
 */
class Transient extends WP_CLI_Command {
	protected $cache;
	protected $db;

	public function __construct( wpdb $db, Factory $cache ) {
		$this->db = $db;
		$this->cache = $cache;
	}

	/**
	 * Set a transient value if it does not already exist.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The transient key.
	 *
	 * <value>
	 * : The transient value.
	 *
	 * <expiration>
	 * : Time until transient expiration in seconds.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to prepend to the transient key.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient add some_key "some value" 600
	 */
	public function add( array $args, array $assoc_args ) {
		list( $key, $value, $expiration ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );
		$success = $repository->add( $key, $value, absint( $expiration ) );

		if ( $success ) {
			WP_CLI::success( "Transient [{$label}] set to [{$value}]" );
		} else {
			if ( $repository->has( $key ) ) {
				WP_CLI::warning( "Transient [{$label}] already exists" );
			} else {
				WP_CLI::warning( "Unable to set transient [{$label}]" );
			}
		}
	}

	public function decrement() {}

	/**
	 * Set a non-expiring transient value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The transient key.
	 *
	 * <value>
	 * : The transient value.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to prepend to the transient key.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient forever some_key "some value"
	 */
	public function forever( array $args, array $assoc_args ) {
		list( $key, $value ) = $args;

		// Just easier to use put command...
		$command = "metis:transient put {$key} {$value} 0";

		if ( isset( $assoc_args['prefix'] ) ) {
			$command .= " --prefix={$assoc_args['prefix']}";
		}

		WP_CLI::runcommand( $command );
	}

	/**
	 * Delete a transient value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The transient key.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to prepend to the transient key.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient forget some_key
	 */
	public function forget( array $args, array $assoc_args ) {
		list( $key ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );
		$success = $repository->forget( $key );

		if ( $success ) {
			WP_CLI::success( "Transient [{$label}] deleted successfully" );
		} else {
			if ( $repository->has( $key ) ) {
				WP_CLI::error( "Transient [{$label}] exists but was not deleted" );
			} else {
				WP_CLI::warning( "Transient [{$label}] does not exist" );
			}
		}
	}

	/**
	 * Delete all transients.
	 *
	 * ## OPTIONS
	 *
	 * [--expired]
	 * : If set, only flush transients that have expired.
	 *
	 * [--prefix=<prefix>]
	 * : If provided, only transients with keys using this prefix will be deleted.
	 *
	 * [--yes]
	 * : Bypass the "are you sure" message.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient flush
	 */
	public function flush( array $_, array $assoc_args ) {
		$repository = $this->make_repository( $assoc_args );
		$expired = get_flag_value( $assoc_args, 'expired' );

		if ( ! $expired ) {
			WP_CLI::confirm(
				'Are you sure you want to flush all transients?',
				$assoc_args
			);
		}

		$success = $repository->{$expired ? 'flush_expired' : 'flush'}();

		if ( $success ) {
			WP_CLI::success( 'Transients flushed successfully' );
		} else {
			WP_CLI::error( 'Unable to flush transients' );
		}

		if ( wp_using_ext_object_cache() ) {
			WP_CLI::warning(
				'Your site appears to be using an external object cache'
				. ' - this command only flushes transients from the database'
			);
		}
	}

	/**
	 * Generate transients (for testing purposes only).
	 *
	 * ## OPTIONS
	 *
	 * [--count=<count>]
	 * : The number of transients to generate.
	 *
	 * [--expiration=<expiration>]
	 * : Transient expiration time in seconds.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to prepend to the transient key.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient generate --count=100
	 */
	public function generate( array $_, array $assoc_args ) {
		if ( wp_using_ext_object_cache() ) {
			WP_CLI::error(
				'Your site appears to be using an external object cache'
				. ' - this command is only intended for the database'
			);
		}

		$count = isset( $assoc_args['count'] )
			? absint( $assoc_args['count'] )
			: 100;
		$expiration = isset( $assoc_args['expiration'] )
			? absint( $assoc_args['expiration'] )
			: 600;
		$repository = $this->make_repository( $assoc_args );
		$title = bin2hex( random_bytes( 5 ) );
		$value = bin2hex( random_bytes( 5 ) );

		$progress = make_progress_bar( 'Generating transients', $count );

		for ( $i = 1; $i <= $count; $i++ ) {
			$repository->put( $title . '-' . $i, $value . '-' . $i, $expiration );
			$progress->tick();
		}

		$progress->finish();
	}

	/**
	 * Get a transient value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The transient key.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to prepend to the transient key.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient get some_key
	 */
	public function get( array $args, array $assoc_args ) {
		list( $key ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );
		$value = $repository->get( $key );

		if ( is_null( $value ) ) {
			WP_CLI::error( "Transient [{$label}] does not exist" );
		}

		WP_CLI::print_value( $value );
	}

	/**
	 * Check if a transient is set.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The transient key.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to prepend to the transient key.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient has some_key
	 */
	public function has( array $args, array $assoc_args ) {
		list( $key ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );

		if ( $repository->has( $key ) ) {
			WP_CLI::success( "Transient [{$label}] is set" );
		} else {
			WP_CLI::warning( "Transient [{$label}] is not set" );
		}
	}

	public function increment() {}

	/**
	 * List all transients in the database (including expired).
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format.
	 * ---
	 * default: table
	 * options:
	 *   - json
	 *   - yaml
	 *   - table
	 *   - csv
	 *   - count
	 * ---
	 *
	 * [--prefix=<prefix>]
	 * : If provided, only transients with keys that start with prefix are listed.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient list
	 */
	public function list( array $_, array $assoc_args ) {
		// @todo Some sort of pagination? Add expiration to output?
		// Transient keys will be hashed... May not be much use for this command.
		if ( wp_using_ext_object_cache() ) {
			WP_CLI::error(
				'Your site appears to have an external object cache'
				. ' - this command only works for transients stored in the database'
			);
		}

		$transient_prefix = '_transient';
		$timeout_prefix = '_transient_timeout';

		if ( isset( $assoc_args['prefix'] ) ) {
			$transient_prefix .= "_{$assoc_args['prefix']}";
			$timeout_prefix .= "_{$assoc_args['prefix']}";
		}

		$transient_prefix .= '_';
		$timeout_prefix .= '_';

		$results = $this->db->get_results( $this->db->prepare(
			"SELECT option_name, option_value FROM {$this->db->options}
			WHERE option_name LIKE %s
			AND option_name NOT LIKE %s",
			$this->db->esc_like( $transient_prefix ) . '%',
			$this->db->esc_like( $timeout_prefix ) . '%'
		), ARRAY_A );

		if ( empty( $results ) ) {
			WP_CLI::warning( 'There are no transients in the database' );
			exit;
		}

		$results = array_map( function( $transient ) {
			return [
				'Key' => str_replace( '_transient_', '', $transient['option_name'] ),
				'Value' => maybe_unserialize( $transient['option_value'] ),
			];
		}, $results );

		$formatter = new Formatter( $assoc_args, [ 'Key', 'Value' ] );
		$formatter->display_items( $results );
	}

	/**
	 * Set a transient value.
	 *
	 * ## OPTIONS
	 *
	 * <key>
	 * : The transient key.
	 *
	 * <value>
	 * : The transient value.
	 *
	 * <expiration>
	 * : The transient expiration in seconds.
	 *
	 * [--prefix=<prefix>]
	 * : A prefix to prepend to the transient key.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp metis:transient put some_key "some value" 600
	 */
	public function put( array $args, array $assoc_args ) {
		list( $key, $value, $expiration ) = $args;

		$label = $this->generate_label( $key, $assoc_args );
		$repository = $this->make_repository( $assoc_args );
		$success = $repository->put( $key, $value, absint( $expiration ) );

		if ( $success ) {
			WP_CLI::success( "Transient [{$label}] set to [{$value}]" );
		} else {
			if ( $value === $repository->get( $key ) ) {
				WP_CLI::warning(
					"Transient [{$label}] is already set to [{$value}]"
				);
			} else {
				WP_CLI::error( "Unable to set transient [{$label}]" );
			}
		}
	}

	protected function generate_label( string $key, array $assoc_args ) {
		return isset( $assoc_args['prefix'] )
			? $assoc_args['prefix'] . ':' . $key
			: $key;
	}

	protected function make_repository( array $assoc_args ) {
		$prefix = '';

		if ( isset( $assoc_args['prefix'] ) ) {
			$prefix = (string) $assoc_args['prefix'];
		}

		return $this->cache->transient( $prefix );
	}
}
