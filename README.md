# metis
A small framework for simplifying some WordPress tasks.

## Usage
Metis can help you to quickly add custom menus to the admin bar and serve your
sites static assets from a domain of your choice.

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

### CDN
```php
add_action( 'init', function() {
    $cdn = new SSNepenthe\Metis\CDN( [
        'aggressive' => false, // default is true
        'domain' => 'cdn.mysite.com', // default would be 'static.mysite.com'
        'extensions' => [ 'css', 'js' ], // default also includes gif, ico, jpe?g, png and svg
    ] );
    $cdn->init();
} );
```

The `$args` array is completely optional. Alternatively, you can modify the defaults via the following filters:

```
metis.cdn.aggressive.default
metis.cdn.domain.default
metis.cdn.extensions.default
```

When `$args['aggressive']` is truthy, the full document will be searched for assets to rewrite. When falsy, only strings passed through the `metis.cdn.url` filter will be modified.

By default, this is only scripts and stylesheets enqueued through the WordPress core APIs, but you can manually filter URLs as well (e.g. `apply_filters( 'metis.cdn.url', 'http://mysite.com/some/file/to/modify.jpg' );` ).
