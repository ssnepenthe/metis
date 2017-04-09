<?php
/**
 * Template class.
 *
 * @package metis
 */

namespace Metis\View;

/**
 * This class mimics the locate_template()/load_template() functionality from
 * WordPress core with two significant differences:
 *
 * 1) The act of locating a template is delegated to a locator which means we can
 *    employ different or multiple strategies for locating templates. In practice
 *    this means that we can easily define plugin templates which can be overridden
 *    from within a theme.
 * 2) Templates are loaded with the same variables in scope as they are when using
 *    load_template() with the added bonus of being able to explicitly pass in extra
 *    data. In practice this means data can be prepared outside of template files
 *    instead of relying on globals and template tags.
 */
class Template implements Template_Interface {
	/**
	 * Template locator instance.
	 *
	 * @var Template_Locator_Interface
	 */
	protected $locator;

	/**
	 * Class constructor.
	 *
	 * @param Template_Locator_Interface $locator Template locator instance.
	 */
	public function __construct( Template_Locator_Interface $locator ) {
		$this->locator = $locator;
	}

	/**
	 * Locator getter.
	 *
	 * @return Template_Locator_Interface
	 */
	public function locator() {
		return $this->locator;
	}

	/**
	 * Render and print a template file.
	 *
	 * @param  string $name Template name.
	 * @param  array  $data Data to make available to the template.
	 */
	public function output( string $name, array $data = [] ) {
		$template = $this->locator->locate( $this->candidates( $name ) );

		if ( empty( $template ) ) {
			return;
		}

		static::include_template( $template, $data );
	}

	/**
	 * Render a template file and return as a string.
	 *
	 * @param  string $name Name of the template file.
	 * @param  array  $data Data to make available to the template.
	 *
	 * @return string
	 */
	public function render( string $name, array $data = [] ) {
		ob_start();

		$this->output( $name, $data );

		$view = ob_get_contents();
		ob_end_clean();

		return $view;
	}

	/**
	 * Gets a list of template candidates based on a template name.
	 *
	 * @param  string $template Template name.
	 *
	 * @return array
	 */
	protected function candidates( $template ) {
		$template = str_replace( '.', DIRECTORY_SEPARATOR, $template ) . '.php';

		$candidates = (array) $template;

		if ( 'templates' . DIRECTORY_SEPARATOR !== substr( $template, 0, 10 ) ) {
			array_unshift(
				$candidates,
				'templates' . DIRECTORY_SEPARATOR . $template
			);
		}

		return $candidates;
	}

	/**
	 * Include a template file in a static context to prevent template from access
	 * current instance as $this.
	 *
	 * @param  string $template Template name.
	 * @param  array  $data     Array of data to make available to the template.
	 */
	protected static function include_template( $template, $data ) {
		global $comment,
			   $id,
			   $posts,
			   $post,
			   $user_ID,
			   $wp,
			   $wp_did_header,
			   $wp_query,
			   $wp_rewrite,
			   $wp_version,
			   $wpdb;

		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP );
		}

		if ( isset( $s ) ) {
			$s = esc_attr( $s );
		}

		extract( $data, EXTR_SKIP );

		include $template;
	}
}
