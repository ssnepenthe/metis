<?php

namespace SSNepenthe\Metis;

class Taxonomy {
	protected $args;
	protected $object;
	protected $slug;

	public function __construct(
		string $name_singular,
		array $args = [],
		string $name_plural = null
	) {
		$name_singular = trim( strtolower( $name_singular ) );

		if ( is_null( $name_plural ) ) {
			$name_plural = $name_singular . 's';
		}

		$singular_capitalized = ucwords( $name_singular );
		$plural_capitalized = ucwords( $name_plural );

		$slug = str_replace( [ '-', ' ' ], '_', $name_singular );
		$slug = preg_replace( '/[^a-z_]/', '', $slug );

		if ( 32 < strlen( $slug ) ) {
			$slug = substr( $slug, 0, 32 );
		}

		$default_labels = [
			'name' => $plural_capitalized,
			'singular_name' => $singular_capitalized,
			'edit_item' => sprintf( 'Edit %s', $singular_capitalized ),
			'view_item' => sprintf( 'View %s', $singular_capitalized ),
			'add_new_item' => sprintf( 'Add New %s', $singular_capitalized ),
			'search_items' => sprintf( 'Search %s', $singular_capitalized ),
			'separate_items_with_commas' => sprintf(
				'Separate %s with commas.',
				$name_plural
			),
			'add_or_remove_items' => sprintf( 'Add or remove %s', $name_plural ),
			'choose_from_most_used' => sprintf(
				'Choose from the most used %s',
				$name_plural
			),
		];

		$default_args = [
			'show_tagcloud' => false,
		];

		if ( ! isset( $args['labels'] ) ) {
			$args['labels'] = [];
		}

		$args['labels'] = wp_parse_args( $args['labels'], $default_labels );

		$this->args = wp_parse_args( $args, $default_args );
		$this->slug = $slug;
		$this->object = $object;
	}

	public function register_taxonomy() {
		register_taxonomy( $this->slug, $this->object, $this->args );
	}

	public function set_object( $slug ) {
		if ( ! is_string( $slug ) && ! is_array( $slug ) ) {
			throw new \InvalidArgumentException( sprintf(
				'%s expects $slug of type array|string, %s given.',
				__METHOD__,
				gettype( $slug )
			) );
		}

		$this->object = $slug;
	}

	public function slug() {
		return $this->slug;
	}
}
