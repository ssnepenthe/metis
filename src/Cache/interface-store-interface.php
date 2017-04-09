<?php

namespace Metis\Cache;

interface Store_Interface {
	public function decrement( string $key, int $value = 1 );
	public function flush();
	public function flush_expired();
	public function forever( string $key, $value );
	public function forget( string $key );
	public function get( string $key );
	public function get_many( array $keys );
	public function get_prefix();
	public function increment( string $key, int $value = 1 );
	public function put( string $key, $value, int $seconds );
	public function put_many( array $values, int $seconds );
}
