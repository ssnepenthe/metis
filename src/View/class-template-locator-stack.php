<?php
/**
 * Template_Locator_Stack class.
 *
 * @package metis
 */

namespace Metis\View;

/**
 * Defines the template locator stack class.
 */
class Template_Locator_Stack implements Template_Locator_Interface {
	/**
	 * List of template locators.
	 *
	 * @var Template_Locator_Interface[]
	 */
	protected $stack = [];

	/**
	 * Class constructor.
	 *
	 * @param array $locators List of template locators.
	 */
	public function __construct( array $locators = [] ) {
		foreach ( $locators as $locator ) {
			$this->push( $locator );
		}
	}

	/**
	 * Loop through the list of locators returning the first valid template found.
	 *
	 * @param  array $templates List of template candidates.
	 *
	 * @return string
	 */
	public function locate( array $templates ) {
		foreach ( $this->stack as $locator ) {
			$template = $locator->locate( $templates );

			if ( ! empty( $template ) ) {
				return $template;
			}
		}

		return '';
	}

	/**
	 * Push a template locator on to the stack.
	 *
	 * @param  Template_Locator_Interface $locator Template locator instance.
	 */
	public function push( Template_Locator_Interface $locator ) {
		$this->stack[] = $locator;
	}
}
