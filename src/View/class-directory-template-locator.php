<?php
/**
 * Directory_Template_Locator class.
 *
 * @package metis
 */

namespace Metis\View;

/**
 * Defines the directory template locator class.
 */
class Directory_Template_Locator implements Template_Locator_Interface {
	/**
	 * Directory in which to look for templates.
	 *
	 * @var string
	 */
	protected $dir;

	/**
	 * Class constructor.
	 *
	 * @param string $dir Directory in which to look for templates.
	 */
	public function __construct( string $dir ) {
		$this->dir = realpath( $dir );
	}

	/**
	 * Locate the first available template in a list of template candidates.
	 *
	 * @param  array $templates List of templates.
	 *
	 * @return string
	 */
	public function locate( array $templates ) {
		foreach ( $templates as $template ) {
			$template = trailingslashit( $this->dir ) . $template;

			if ( file_exists( $template ) ) {
				return $template;
			}
		}

		return '';
	}
}
