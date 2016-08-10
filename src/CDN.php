<?php

namespace SSNepenthe\Metis;

class CDN {
	const CACHE_GROUP = 'metis:cdn';

	protected $args;
	protected $elements;
	protected $replace;
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
			 * If falsy only URLs passed through the metis.cdn.url filter will
			 * be modified.
			 *
			 * If truthy the entire document will be searched for assets that
			 * can be offloaded to the CDN domain.
			 */
			'aggressive' => apply_filters(
				'metis.cdn.aggressive.default',
				true
			),

			/**
			 * The domain from which you would like your static assets served.
			 */
			'domain' => apply_filters(
				'metis.cdn.domain.default',
				sprintf( 'static.%s.%s', $domain, $tld )
			),

			/**
			 * Must be an array of file extensions which will be fed to the
			 * regex pattern. These will not be escaped so you can include
			 * character classes, quantifiers, etc.
			 */
			'extensions' => apply_filters(
				'metis.cdn.extensions.default',
				[ 'css', 'gif', 'ico', 'jpe?g', 'js', 'png', 'svg' ]
			),
		];

		$this->args = wp_parse_args( $args, $defaults );

		$this->assert_string( $this->args['domain'] );
		$this->assert_array_of_strings( $this->args['extensions'] );

		$this->elements = [
			'img' => [ 'src', 'srcset' ],
			'link' => [ 'href' ],
			'meta' => [ 'content' ],
			'script' => [ 'src' ],
		];

		$this->replace = sprintf(
			'\1%s\2\3',
			$this->args['domain']
		);

		$this->search = sprintf(
			'/(https?\:(?:\\\\)?\/(?:\\\\)?\/)%s((?:\\\\)?\/[^\'"]*?)(\.(?:%s))/',
			preg_quote( $host ),
			implode( '|', $this->args['extensions'] )
		);
	}

	public function init() {
		add_action( 'template_redirect', [ $this, 'template_redirect' ] );

		add_filter( 'script_loader_src', [ $this, 'loader_src' ] );
		add_filter( 'style_loader_src', [ $this, 'loader_src' ] );
		add_filter( 'metis.cdn.url', [ $this, 'loader_src' ] );
	}

	public function loader_src( $url ) {
		if ( ! $this->is_frontend_request() ) {
			return $url;
		}

		if ( $this->args['aggressive'] ) {
			return $url;
		}

		$url = preg_replace( $this->search, $this->replace, $url );

		return $url;
	}

	public function template_redirect() {
		if ( ! $this->is_frontend_request() ) {
			return;
		}

		if ( ! $this->args['aggressive'] ) {
			return;
		}

		ob_start( [ $this, 'ob_callback' ] );
	}

	protected function assert_string( $string ) {
		if ( ! is_string( $string ) ) {
			throw new \RuntimeException( sprintf(
				'String required, %s given',
				gettype( $string )
			) );
		}
	}

	protected function assert_array_of_strings( array $array ) {
		if ( empty( $array ) ) {
			throw new \RuntimeException( 'Empty array not allowed' );
		}

		array_walk( $array, function( $value ) {
			if ( ! is_string( $value ) ) {
				throw new \RuntimeException( sprintf(
					'Array of strings required, found %s',
					gettype( $value )
				) );
			}
		} );
	}

	protected function is_frontend_request() {
		global $pagenow;

		$is_login = 'wp-login.php' === $pagenow;

		// @todo Might want to allow this to run on feeds...
		$is_backend = is_feed() || is_robots() || is_trackback() ||
			is_comment_feed() || is_admin() || $is_login;

		return ! $is_backend;
	}

	protected function ob_callback( $buffer ) {
		// \DOMDocument doesn't like html5 elements...
		$original_error_state = libxml_use_internal_errors( true );

		$document = new \DOMDocument;
		$document->formatOutput = true;
		$document->loadHTML( $buffer );

		$query = sprintf(
			'.//%s',
			implode( '|.//', array_keys( $this->elements ) )
		);
		$xpath = new \DOMXPath( $document );
		$elements = $xpath->query( $query );

		foreach ( $elements as $element ) {
			$this->prepare_node( $element );
		}

		libxml_use_internal_errors( $original_error_state );

		return $document->saveHTML();
	}

	protected function prepare_node( \DOMElement $element ) {
		if ( ! isset( $this->elements[ $element->tagName ] ) ) {
			return;
		}

		foreach ( $this->elements[ $element->tagName ] as $attr ) {
			if ( ! $element->hasAttribute( $attr ) ) {
				continue;
			}

			$element->setAttribute( $attr, preg_replace(
				$this->search,
				$this->replace,
				$element->getAttribute( $attr )
			) );
		}

		if (
			'a' === $element->parentNode->tagName &&
			$element->parentNode->hasAttribute( 'href' )
		) {
			$element->parentNode->setAttribute( 'href', preg_replace(
				$this->search,
				$this->replace,
				$element->parentNode->getAttribute( 'href' );
			) );
		}
	}
}
