# metis
Easy custom post types, taxonomies and metaboxes in WordPress.

## Usage
To create a new post type, hook in at `plugins_loaded` like so:

```php
use SSNepenthe\Metis\PostType;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book' );
	$book->init();
} );
```

If you want finer control over your post type, pass an array of args as the second parameter. For example, to override the `menu_postition`:

```php
use SSNepenthe\Metis\PostType;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book', [ 'menu_position' => 5 ] );
	$book->init();
} );
```

Plural labels are generated simply by appending the letter 's' which will not work in all cases. If necessary, you can supply the plural form of the name as the third parameter:

```php
use SSNepenthe\Metis\PostType;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book', [ 'menu_position' => 5 ], 'books' );
	$book->init();
} );
```

To add a custom taxonomy to your newly created post type:

```php
use SSNepenthe\Metis\PostType;
use SSNepenthe\Metis\Taxonomy;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book' );
	$genre = new Taxonomy( 'genre' );

	$book->add_taxonomy( $genre );
	$book->init();
} );
```

Once again, you can override individual args by passing an array as the second parameter:

```php
use SSNepenthe\Metis\PostType;
use SSNepenthe\Metis\Taxonomy;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book' );
	$genre = new Taxonomy( 'genre', [ 'show_tagcloud' => true ] );

	$book->add_taxonomy( $genre );
	$book->init();
} );
```

Adding meta boxes is a little more involved...

A new class which extends `\SSNepenthe\Metis\BaseMetaBox` should be created for each meta box.

At a minimum, you need to supply an `$args` array which contains values for `id` and `title`, as well as a `render` method to print the meta box content.

If you wish for your meta box fields to be automatically registered, sanitized and saved, you must also supply a `$meta_keys` array with an entry that corresponds to the name attribute of each field you want to be automatically handled.

It might look something like this:

```php
use SSNepenthe\Metis\BaseMetaBox;

class SourceMetaBox extends BaseMetaBox {
	protected $args = [
		'id' => 'myplugin-source-meta-box',
		'title' => 'Source',
	];

	protected $meta_keys = [
		'myplugin-source-page',
		'myplugin-source-url'
	];

	public function render( $post ) {
		$page = get_post_meta( $post->ID, 'myplugin-source-page', true );
		$url = get_post_meta( $post->ID, 'myplugin-source-url', true );

		echo '<label for="myplugin-source-page">Source Page:</label>';
		printf(
			'<input name="myplugin-source-page" type="text" value="%s">',
			esc_attr( $page )
		);

		echo '<label for="myplugin-source-url">Source URL:</label>';
		printf(
			'<input class="code" name="myplugin-source-url" type="url" value="%s">',
			esc_attr( $url )
		);
	}
}
```

And then to add it to your post type:

```php
use SSNepenthe\Metis\PostType;
use SSNepenthe\Metis\Taxonomy;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book' );
	$genre = new Taxonomy( 'genre' );

	$book->add_taxonomy( $genre );
	$book->add_meta_box( new SourceMetaBox );
	$book->init();
} );
```
