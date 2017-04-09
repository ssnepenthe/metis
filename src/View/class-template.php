<?php

namespace Metis\View;

class Template implements Template_Interface {
	protected $locator;

	public function __construct( Template_Locator_Interface $locator ) {
		$this->locator = $locator;
	}

	public function locator() {
		return $this->locator;
	}

	public function output( string $name, array $data = [] ) {
		$template = $this->locator->locate( $this->candidates( $name ) );

		if ( empty( $template ) ) {
			return;
		}

		static::include_template( $template, $data );
	}

	public function render( string $name, array $data = [] ) {
		ob_start();

		$this->output( $name, $data );

		$view = ob_get_contents();
		ob_end_clean();

		return $view;
	}

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
