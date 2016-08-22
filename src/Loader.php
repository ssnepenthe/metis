<?php

namespace SSNepenthe\Metis;

/**
 * Inspired by Scribu.
 *
 * @link http://scribu.net/wordpress/reflection-on-filters.html
 */
class Loader {
	public static function attach( $object ) {
		if ( ! is_object( $object ) ) {
			_doing_it_wrong(
				__METHOD__,
				'The object parameter must be an instantiated object',
				null
			);

			return;
		}

		$reflection = new \ReflectionClass( $object );

		foreach ( $reflection->getMethods() as $method ) {
			if ( ! $method->isPublic() || $method->isConstructor() ) {
				continue;
			}

			if ( false === strpos( $method->getDocComment(), '@hook' ) ) {
				continue;
			}

			$hooks = static::get_hooks( $method );

			static::do_hooks( $object, $method, $hooks );
		}
	}

	protected static function get_hooks( \ReflectionMethod $method ) {
		$hooks = [];
		$tags = [];
		$priorities = [];

		if ( preg_match_all(
			'/@tag\s+(.+)/',
			$method->getDocComment(),
			$matches
		) ) {
			$tags = $matches[1];
		}

		if ( preg_match_all(
			'/@priority\s+([0-9]+)/',
			$method->getDocComment(),
			$matches
		) ) {
			$priorities = $matches[1];
		}

		if ( empty( $tags ) ) {
			$tags[] = $method->name;
		}

		if ( empty( $priorities ) ) {
			$priorities[] = 10;
		}

		foreach ( $tags as $tag ) {
			foreach ( $priorities as $priority ) {
				$hooks[] = [
					'priority' => $priority,
					'tag' => $tag,
				];
			}
		}

		return $hooks;
	}

	protected static function do_hooks(
		$object,
		\ReflectionMethod $method,
		array $hooks
	) {
		foreach ( $hooks as $hook ) {
			if ( preg_match( '/%%(.+)%%/', $hook['tag'], $match ) ) {
				$hook['tag'] = str_replace(
					'%%' . $match[1] . '%%',
					$object->{$match[1]},
					$hook['tag']
				);
			}

			// Add_action is an alias of add_filter.
			add_filter(
				$hook['tag'],
				[ $object, $method->name ],
				$hook['priority'],
				$method->getNumberOfParameters()
			);
		}
	}
}
