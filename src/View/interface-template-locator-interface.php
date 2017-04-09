<?php
/**
 * Template_Locator_Interface Interface.
 *
 * @package metis
 */

namespace Metis\View;

/**
 * Defines the template locator interface.
 */
interface Template_Locator_Interface {
	/**
	 * Locate the first available template in a list of templates. Should return an
	 * empty string if no valid template is found.
	 *
	 * @param  string[] $templates List of templates to search for.
	 *
	 * @return string
	 */
	public function locate( array $templates );
}
