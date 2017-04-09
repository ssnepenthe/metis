<?php
/**
 * Template_Interface Interface
 *
 * @package metis
 */

namespace Metis\View;

/**
 * Defines the template interface.
 */
interface Template_Interface {
	/**
	 * Render and print a template file.
	 *
	 * @param  string $name The template name.
	 * @param  array  $data Data to make available in the template.
	 */
	public function output( string $name, array $data = [] );

	/**
	 * Render a template file and return as a string.
	 *
	 * @param  string $name The template name.
	 * @param  array  $data Data to make available in the template.
	 *
	 * @return string
	 */
	public function render( string $name, array $data = [] );
}
