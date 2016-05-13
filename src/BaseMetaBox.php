<?php

namespace SSNepenthe\Metis;

abstract class BaseMetaBox {
	protected $args = [];
	protected $meta_keys = [];
	protected $nonce = null;

	public function __construct() {
		if ( ! isset( $this->args['id'] ) || ! isset( $this->args['title'] ) ) {
			throw new \DomainException(
				'Classes that inherit from BaseMetaBox must define an ID and title.'
			);
		}

		$this->args = wp_parse_args( $this->args, [
			'callback' => [ $this, 'render' ],
			'screen' => null,
			'context' => 'normal',
			'priority' => 'default',
			'cb_args' => null,
		] );
	}

	public function add() {
		add_meta_box(
			$this->args['id'],
			$this->args['title'],
			$this->args['callback'],
			$this->args['screen'],
			$this->args['context'],
			$this->args['priority'],
			$this->args['cb_args']
		);
	}

	public function register_meta() {
		if ( empty( $this->meta_keys ) ) {
			return;
		}

		foreach ( $this->meta_keys as $key ) {
			register_meta( 'post', $key, [ $this, 'sanitize' ] );
		}
	}

	abstract public function render( \WP_Post $post );

	public function sanitize( $input ) {
		if ( is_array( $input ) ) {
			return array_filter(
				array_map( 'trim', array_map( 'wp_kses_post', $input ) )
			);
		}

		if ( is_string( $input ) ) {
			return trim( wp_kses_post( $input ) );
		}

		return '';
	}

	public function save( $post_id ) {
		if ( ! is_int( $post_id ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The post_id parameter is required to be int, was: %s',
				gettype( $post_id )
			) );
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( empty( $this->meta_keys ) ) {
			return;
		}

		if ( is_null( $this->nonce ) ) {
			return;
		}

		// Not sanitizing intentionally, feed it to wp_verify_nonce raw.
		$nonce = filter_input( INPUT_POST, $this->nonce['name'] );

		if ( is_null( $nonce ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, $this->nonce['action'] ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $this->meta_keys as $meta_key ) {
			// Not sanitizing because it is hooked to $this->sanitize().
			$input = filter_input( INPUT_POST, $meta_key );

			if ( ! $input ) {
				continue;
			}

			update_post_meta( $post_id, $meta_key, $input );
		}
	}

	public function set_nonce( array $nonce ) {
		if ( ! isset( $nonce['name'] ) || ! isset( $nonce['action'] ) ) {
			throw new \DomainException(
				'Nonce supplied to meta box classes must include a name and action.'
			);
		}

		$this->nonce = $nonce;
	}
}
