<?php

namespace Metis\Cache;

use wpdb;

// @todo How to handle *_site_transient() functions?
class Transient_Store extends Abstract_Store {
	protected $db;

	public function __construct( wpdb $db, string $prefix = '' ) {
		$this->db = $db;
		$this->set_prefix( $prefix );
	}

	public function flush() {
		if ( wp_using_ext_object_cache() ) {
			return false;
		}

		$sql = "DELETE FROM {$this->db->options}
			WHERE option_name LIKE %s";

		$count = $this->db->query( $this->db->prepare(
			$sql,
			$this->db->esc_like( '_transient_' . $this->prefix ) . '%'
		) );

		return false === $count ? false : true;
	}

	public function flush_expired() {
		if ( wp_using_ext_object_cache() ) {
			return false;
		}

		$now = time();

		$transient_prefix = '_transient_' . $this->prefix;
		$timeout_prefix = '_transient_timeout_' . $this->prefix;
		$length = strlen( $transient_prefix ) + 1;

		$sql = "DELETE a, b FROM {$this->db->options} a, {$this->db->options} b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( %s, SUBSTRING( a.option_name, %d ) )
			AND b.option_value < %d";

		$count = $this->db->query( $this->db->prepare(
			$sql,
			$this->db->esc_like( $transient_prefix ) . '%',
			$this->db->esc_like( $timeout_prefix ) . '%',
			$timeout_prefix,
			$length,
			$now
		) );

		return false === $count ? false : true;
	}

	public function forget( string $key ) {
		return delete_transient( $this->hash_key( $key ) );
	}

	public function get( string $key ) {
		$value = get_transient( $this->hash_key( $key ) );

		return false === $value ? null : $value;
	}

	// If saving to DB, will return false if existing value is same as new value.
	public function put( string $key, $value, int $seconds ) {
		return set_transient(
			$this->hash_key( $key ),
			$value,
			max( 0, $seconds )
		);
	}

	protected function hash_key( string $key ) {
		return $this->prefix . parent::hash_key( $key );
	}

	protected function set_prefix( string $prefix ) {
		// SHA1 plus "_" take 41 characters which leaves us with 131 for our prefix.
		if ( 131 < strlen( $prefix ) ) {
			$prefix = substr( $prefix, 0, 131 );
		}

		$this->prefix = empty( $prefix ) ? '' : $prefix . '_';
	}
}
