<?php
/**
 * Theme_Template_Locator class.
 *
 * @package metis
 */

namespace Metis\View;

/**
 * Defines the theme template locator class.
 */
class Theme_Template_Locator implements Template_Locator_Interface {
	/**
	 * Locate the first available template in a list of tempaltes.
	 *
	 * @param  array $templates List of template candidates.
	 *
	 * @return string
	 */
	public function locate( array $templates ) {
		return locate_template( $templates );
	}
}
