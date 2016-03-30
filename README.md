# metis
A small framework for simplifying some common WordPress tasks.

## Usage
Metis can help you to quickly register content types with custom meta boxes and add custom menus to the admin bar.

### Content Types
To create a new post type:

```php
use SSNepenthe\Metis\PostType;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book' );
	$book->init();
} );
```

The PostType class accepts an optional array of args as the second parameter and string representing the plural post label as the third parameter:

```php
use SSNepenthe\Metis\PostType;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book', [ 'menu_position' => 5 ], 'books' );
	$book->init();
} );
```

To add a custom taxonomy to your post type:

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

The Taxonomy class also accepts and array of args and plural label as the optional second and third parameter:

```php
use SSNepenthe\Metis\PostType;
use SSNepenthe\Metis\Taxonomy;

add_action( 'plugins_loaded', function() {
	$book = new PostType( 'book' );
	$genre = new Taxonomy( 'genre', [ 'show_tagcloud' => true ], 'genres' );

	$book->add_taxonomy( $genre );
	$book->init();
} );
```

To add a metabox to the edit page for your post type, create a class which extends `\SSNepenthe\Metis\BaseMetaBox`.

At a minimum, you need to supply a protected `$args` array which contains values for `id` and `title`, as well as a public `render` method to print the meta box content.

Post meta can be automatically registered, sanitized and saved by supplying a protected `$meta_keys` array with an entry that corresponds to the name attribute of each field you want to be automatically handled.

It might look something like this:

```php
use SSNepenthe\Metis\BaseMetaBox;

class SourceMetaBox extends BaseMetaBox {
	protected $args = [
		'id' => 'myplugin-source-meta-box',
		'title' => 'Source',
	];

	protected $meta_keys = [
		'myplugin-book-isbn',
		'myplugin-book-author'
	];

	public function render( WP_Post $post ) {
		$page = get_post_meta( $post->ID, 'myplugin-book-isbn', true );
		$url = get_post_meta( $post->ID, 'myplugin-book-author', true );

		echo '<label for="myplugin-book-isbn">Source Page:</label>';
		printf(
			'<input name="myplugin-book-isbn" type="text" value="%s">',
			esc_attr( $page )
		);

		echo '<label for="myplugin-book-author">Source URL:</label>';
		printf(
			'<input class="code" name="myplugin-book-author" type="url" value="%s">',
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

### Admin Bar Menu
```php
use SSNepenthe\Metis\Toolbar;

add_action( 'init', function() {
	$toolbar = new Toolbar;

	$toolbar->add_nodes( [
		[
			'id' => 'plugin-parent-item',
			'title' => 'Parent Item',
			'no_href' => true,
		],
		[
			'action_cb' => 'plugin_child_one_callback',
			'display_cb' => 'plugin_child_one_display_callback',
			'id' => 'plugin-child-item-one',
			'query_args' => [ 'context' => 'something' ],
			'title' => 'First Child Item',
		],
		[
			'action_cb' => 'plugin_child_two_callback',
			'display_cb' => 'plugin_child_two_display_callback',
			'id' => 'plugin-child-item-two',
			'title' => 'Second Child Item',
		],
	] );

	$toolbar->init();
} );
```

`id` is the only required arg, although it is recommended to include `action_cb` as well.

In addition to the args you would normally use with `WP_Admin_Bar::add_node`, the following args are also valid:
* `action_cb` - This function will fire when the menu link is clicked, default is `__return_true`
* `capability` - `current_user_can( $args['capability'] )` is checked before firing `$args['action_cb']`, default is `edit_theme_options`
* `display_cb` - This function is called to determine whether or not to display the node, should return `true` or `false`, default is `__return_true`
* `no_href` - Whether `$args['href']` should be generated if not supplied by the user, default is `false`
* `query_args` - An array of query args to append to `$args['href']`, default is `[]`

All nodes will be added as a child of the first node specified.