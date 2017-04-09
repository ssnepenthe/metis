<?php
/**
 * Maintenance command class.
 *
 * @package metis
 */

namespace Metis\Command;

use WP_CLI;
use WP_CLI_Command;
use WP_Filesystem_Base;

/**
 * Handle maintenance mode for the site.
 */
class Maintenance extends WP_CLI_Command {
	/**
	 * WP filesystem instance.
	 *
	 * @var WP_Filesystem_Base
	 */
	protected $filesystem;

	/**
	 * Class constructor.
	 *
	 * @param WP_Filesystem_Base $filesystem WP filesystem instance.
	 */
	public function __construct( WP_Filesystem_Base $filesystem ) {
		$this->filesystem = $filesystem;
	}

	/**
	 * Put the site in maintenance mode.
	 *
	 * ## EXAMPLES
	 *
	 *     wp metis:maintenance down
	 */
	public function down() {
		$file = $this->filesystem->abspath() . '.maintenance';

		$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
		$this->filesystem->delete( $file );
		$success = $this->filesystem->put_contents(
			$file,
			$maintenance_string,
			FS_CHMOD_FILE
		);

		if ( ! $success ) {
			WP_CLI::error( 'Something went wrong! Please try again.' );
		}

		WP_CLI::success( 'Maintenance mode enabled.' );
	}

	/**
	 * Check whether the site is currently in maintenance mode.
	 *
	 * ## EXAMPLES
	 *
	 *     wp metis:maintenance status
	 */
	public function status() {
		$file = $this->filesystem->abspath() . '.maintenance';

		if ( $this->filesystem->exists( $file ) ) {
			WP_CLI::log( 'Maintenance mode is currently enabled.' );
		} else {
			WP_CLI::log( 'Maintenance mode is currently disabled.' );
		}
	}

	/**
	 * Toggle maintenance mode.
	 *
	 * ## EXAMPLES
	 *
	 *     wp metis:maintenance toggle
	 */
	public function toggle() {
		$status = WP_CLI::runcommand( 'metis:maintenance status', [
			'return' => true,
		] );

		if ( false === strpos( $status, 'enabled' ) ) {
			$command = 'down';
		} else {
			$command = 'up';
		}

		WP_CLI::runcommand( "metis:maintenance $command" );
	}

	/**
	 * Take the site out of maintenance mode.
	 *
	 * ## EXAMPLES
	 *
	 *     wp metis:maintenance up
	 */
	public function up() {
		$file = $this->filesystem->abspath() . '.maintenance';

		$success = $this->filesystem->delete( $file );

		if ( ! $success ) {
			WP_CLI::error( 'Something went wrong! Please try again.' );
		}

		WP_CLI::success( 'Maintenance mode disabled.' );
	}
}
