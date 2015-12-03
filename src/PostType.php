<?php

namespace SSNepenthe\Metis;

/**
 * @todo Can we modify the 'post published' and 'post updated' admin notices?
 *       And the post trashed and post permanently deleted.
 */
class PostType {
	protected $args;
	protected $meta_boxes = [];
	protected $nonce;
	protected $slug;
	protected $taxonomies = [];

	public function __construct( $name_singular, array $args = [], $name_plural = null ) {
		if ( ! is_string( $name_singular ) ) {
			throw new \InvalidArgumentException(
				'PostType class constructor expects $name of type: string, ' . gettype( $name_singular ) . ' given.'
			);
		}

		$name_singular = trim( strtolower( $name_singular ) );

		if ( is_null( $name_plural ) ) {
			$name_plural = $name_singular . 's';
		}

		$singular_capitalized = ucwords( $name_singular );
		$plural_capitalized = ucwords( $name_plural );

		$slug = str_replace( [ '-', ' ' ], '_', $name_singular );
		$slug = preg_replace( '/[^a-z_]/', '', $slug );

		if ( 20 < strlen( $slug ) ) {
			$slug = substr( $slug, 0, 20 );
		}

		$default_labels = [
			'name' => $plural_capitalized,
			'singular_name' => $singular_capitalized,
			'all_items' => sprintf( 'All %s', $plural_capitalized ),
			'add_new_item' => sprintf( 'Add New %s', $singular_capitalized ),
			'edit_item' => sprintf( 'Edit %s', $singular_capitalized ),
			'view_item' => sprintf( 'View %s', $singular_capitalized ),
			'search_items' => sprintf( 'Search %s', $plural_capitalized ),
			'not_found' => sprintf( 'No %s found.', $name_singular ),
			'not_found_in_trash' => sprintf( 'No %s found in Trash.', $name_singular ),
		];

		$default_args = [
			'public' => true,
			'supports' => [ 'title', 'editor', 'thumbnail' ],
			'register_meta_box_cb' => [ $this, 'meta_box_cb' ],
			'taxonomies' => [],
			'has_archive' => true,
			'rewrite' => [ 'with_front' => false ],
		];

		if ( ! isset( $args['labels'] ) || empty( $args['labels'] ) ) {
			$args['labels'] = [];
		}

		$args['labels'] = wp_parse_args( $args['labels'], $default_labels );

		$this->args = wp_parse_args( $args, $default_args );
		$this->nonce = [
			'name' => 'metis_save_' . $slug . '_nonce',
			'action' => 'metis_save_' . $slug,
		];
		$this->slug = $slug;
	}

	public function add_meta_box( BaseMetaBox $meta_box ) {
		$meta_box->set_nonce( $this->nonce );

		$this->meta_boxes[] = $meta_box;
	}

	public function add_taxonomy( Taxonomy $taxonomy ) {
		$taxonomy->set_object( $this->slug );

		$this->taxonomies[] = $taxonomy;
		$this->args['taxonomies'][] = $taxonomy->slug();
	}

	public function init() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'init', [ $this, 'initialize_meta_boxes' ] );
	}

	public function initialize_meta_boxes() {
		if ( empty( $this->meta_boxes ) ) {
			return;
		}

		add_action( 'edit_form_top', [ $this, 'nonce' ] );

		foreach ( $this->meta_boxes as $meta_box ) {
			$meta_box->register_meta();

			add_action( 'save_post_' . $this->slug, [ $meta_box, 'save' ] );
		}
	}

	public function nonce( $post ) {
		if ( $this->slug !== $post->post_type ) {
			return;
		}

		// Single nonce field to be used for all meta boxes.
		wp_nonce_field( $this->nonce['action'], $this->nonce['name'] );
	}

	public function meta_box_cb( $post ) {
		if ( empty( $this->meta_boxes ) ) {
			return;
		}

		foreach ( $this->meta_boxes as $meta_box ) {
			$meta_box->add();
		}
	}

	public function register_post_type() {
		register_post_type( $this->slug, $this->args );
	}

	public function register_taxonomies() {
		if ( empty( $this->taxonomies ) ) {
			return;
		}

		foreach ( $this->taxonomies as $taxonomy ) {
			$taxonomy->register_taxonomy();
		}
	}
}
