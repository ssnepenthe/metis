<?php
/**
 * Simple CDN class for WordPress.
 *
 * @package metis
 */

namespace SSNepenthe\Metis;

/**
 * This class handles automatically replacing static assets within img, link,
 * meta and script elements.
 *
 * They are replaced using the 'static' subdomain of home_url().
 *
 * View the readme for more info.
 */
class CDN {
	/**
	 * Configuration array.
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * Array of elements to search and their respective attributes to modify.
	 *
	 * @var array
	 */
	protected $elements;

	/**
	 * Replacement string fed to preg_replace().
	 *
	 * @var string
	 */
	protected $replace;

	/**
	 * CDN regex pattern.
	 *
	 * @var string
	 */
	protected $search;

	/**
	 * Class constructor.
	 *
	 * @param array $args Configuration array.
	 *
	 * @throws \RuntimeException If home_url() cannot be parsed properly.
	 */
	public function __construct( array $args = [] ) {
		// Revisit - at a minimum this won't hold up against ccSLDs.
		$host = wp_parse_url( home_url() );

		if ( ! isset( $host['host'] ) ) {
			// @todo
			throw new \RuntimeException();
		}

		$host = $host['host'];

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

	/**
	 * Modify a given URL to point to the CDN domain.
	 *
	 * @hook
	 *
	 * @param  string $url URL to modify.
	 *
	 * @return string
	 *
	 * @tag script_loader_src
	 * @tag style_loader_src
	 * @tag metis.cdn.url
	 */
	public function loader_src( $url ) {
		if ( ! $this->is_frontend_request() ) {
			return $url;
		}

		if ( $this->args['aggressive'] ) {
			return $url;
		}

		$this->assert_string( $url );

		$url = preg_replace( $this->search, $this->replace, $url );

		return $url;
	}

	/**
	 * Enable output buffering when instantiated with aggressive == true.
	 *
	 * @hook
	 */
	public function template_redirect() {
		if ( ! $this->is_frontend_request() ) {
			return;
		}

		if ( ! $this->args['aggressive'] ) {
			return;
		}

		ob_start( [ $this, 'ob_callback' ] );
	}

	/**
	 * Assert that a given variable is a string.
	 *
	 * @param  mixed $string Value to check.
	 *
	 * @throws \RuntimeException If the given value is not a string.
	 */
	protected function assert_string( $string ) {
		if ( ! is_string( $string ) ) {
			throw new \RuntimeException( sprintf(
				'String required, %s given',
				gettype( $string )
			) );
		}
	}

	/**
	 * Assert that a given array is not empty and contains only strings.
	 *
	 * @param  array $array Array to check.
	 *
	 * @throws \RuntimeException If an empty array is provided.
	 * @throws \RuntimeException If any value in the array is not a string.
	 */
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

	/**
	 * Determine whether the current request is for a frontend (i.e. themed) page.
	 *
	 * @return boolean [description]
	 */
	protected function is_frontend_request() {
		global $pagenow;

		$is_login = 'wp-login.php' === $pagenow;

		// @todo Might want to allow this to run on feeds...
		$is_backend = is_feed() || is_robots() || is_trackback() ||
			is_comment_feed() || is_admin() || $is_login;

		return ! $is_backend;
	}

	/**
	 * Output buffering callback used to search document for assets to rewrite.
	 *
	 * @param  string $buffer Buffer from output buffering.
	 *
	 * @return string
	 */
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

	/**
	 * Make the necessary modification to a DOM Node so that any assets used are
	 * rewritten to the CDN URL.
	 *
	 * @param  \DOMElement $element DOM Node to modify.
	 */
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
				$element->parentNode->getAttribute( 'href' )
			) );
		}
	}
}
