<?php

namespace SSNepenthe\Metis;

class CDN {
	const CACHE_GROUP = 'metis:cdn';

	protected $args;
	protected $pointer = 0;
	protected $search;

	public function __construct( array $args = [] ) {
		/**
		 * @todo Sad attempt at domain parsing. This needs to be revisited
		 *       because, at a minimum, this won't hold up against ccSLDs.
		 */
		$host = parse_url( home_url(), PHP_URL_HOST );
		$host_parts = explode( '.', $host );
		$tld = array_pop( $host_parts );
		$domain = array_pop( $host_parts );

		$defaults = [
			/**
			 * If falsy we will only modify script and stylesheet source
			 * attributes via the *_loader_src hooks.
			 *
			 * If truthy we will attempt to modify all script, style, link and
			 * img tags as well as a tags which are parents of img tags.
			 */
			'aggressive' => apply_filters( 'metis.cdn.aggressive.default', true ),

			/**
			 * Must be an array of domains from which you would like your static
			 * assets served.
			 *
			 * If more than one domain is provided, assets will first be split
			 * by filetype and then each will be spread evenly across the
			 * domains.
			 *
			 * Further, an attempt is made to consistently serve a given asset
			 * from the same domain wherever it is used on the site, but this
			 * does require a persistent object cache backend such as redis.
			 */
			'domains' => apply_filters( 'metis.cdn.domains.default', [
				sprintf( 'static.%s.%s', $domain, $tld )
			] ),

			/**
			 * Must be an array of element tagnames you would like to search
			 * within for static assets.
			 */
			'elements' => apply_filters( 'metis.cdn.elements.default', [
				'img',
				'link',
				'meta',
				'script',
				'style',
			] ),

			/**
			 * Must be an array of file extensions which will be fed to the
			 * regex pattern. These will not be escaped so you can include
			 * character classes, quantifiers, etc.
			 */
			'extensions' => apply_filters( 'metis.cdn.extensions.default', [
				'css',
				'gif',
				'jpe?g',
				'js',
				'png',
				'svg',
			] ),
		];

		$this->args = wp_parse_args( $args, $defaults );

		$this->search = sprintf(
			'/(https?\:(?:\\\\)?\/(?:\\\\)?\/)%s((?:\\\\)?\/[^\'"]*?)(\.(?:%s))/',
			preg_quote( $host ),
			implode( '|', $this->args['extensions'] )
		);
	}

	public function init() {
		$this->assert_array_of_strings( $this->args['domains'] );
		$this->assert_array_of_strings( $this->args['elements'] );
		$this->assert_array_of_strings( $this->args['extensions'] );

		add_filter( 'script_loader_src', [ $this, 'loader_src' ] );
		add_filter( 'style_loader_src', [ $this, 'loader_src' ] );
	}

	public function loader_src( $src ) {
		if ( $this->args['aggressive'] ) {
			return $src;
		}

		$from_cache = true;
		$original = $src;

		if ( ! $domain = $this->cache_get( $src ) ) {
			$domain = $this->current_domain();
			$from_cache = false;

			$this->cache_set( $src, $domain );
		}

		$replace = sprintf(
			'\1%s\2\3',
			$domain
		);

		$src = preg_replace( $this->search, $replace, $src );

		if ( $src !== $original && ! $from_cache ) {
			$this->increment_pointer();
		}

		// WordPress core handles escaping this value before it is printed.
		return $src;
	}

	protected function assert_array_of_strings( array $array ) {
		if ( empty( $array ) ) {
			// @todo
			throw new \RuntimeException();
		}

		array_walk( $array, function( $value ) {
			if ( ! is_string( $value ) ) {
				// @todo
				throw new \RuntimeException();
			}
		} );
	}

	protected function cache_get( $source_url ) {
		// Bypass cache completely if there is only one domain.
		if ( 1 === count( $this->args['domains'] ) ) {
			return $this->current_domain();
		}

		$key = $this->get_cache_key( $source_url );

		$domain = wp_cache_get( $key, self::CACHE_GROUP );

		if ( $domain && ! in_array( $domain, $this->args['domains'] ) ) {
			wp_cache_delete( $key, self::CACHE_GROUP );

			return false;
		}

		return $domain;
	}

	protected function cache_set( $source_url, $cdn_domain ) {
		return wp_cache_set(
			$this->get_cache_key( $source_url ),
			$cdn_domain,
			self::CACHE_GROUP
		);
	}

	protected function current_domain() {
		return $this->args['domains'][ $this->pointer ];
	}

	protected function get_cache_key( $source_url ) {
		return hash( 'md5', $source_url );
	}

	protected function increment_pointer() {
		$this->pointer = ( count( $this->args['domains'] ) > $this->pointer + 1 ) ?
			$this->pointer + 1 :
			0;
	}
}
