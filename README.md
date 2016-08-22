# metis
A small framework for simplifying some WordPress tasks.

## Usage
With Metis you can very quickly and easily:

* Configure sections of your site to require authentication or authorization
* Serve static assets from a CDN
* Hook an object in to WordPress using method docblocks
* Add custom menus to the admin bar

### Authentication/Authorization
To begin, create the `403.php` and `login.php` files in your theme directory. `403.php` should display a "forbidden" message indicating that the user is trying to access a page that they are not allowed to access. `login.php` should contain a login form keeping in mind that `$_GET['redirect_to']` may or may not be set, and if it is, will contain a path relative to your home URL.

Then make sure to create an instance of `SSNepenthe\Metis\Auth` and hook it in to WordPress.

```php
add_action( 'plugins_loaded', function() {
    // Learn about the Loader class further down in this readme.
    SSNepenthe\Metis\Loader::attach( new SSNepenthe\Metis\Auth );
} );
```

Finally, use the `metis.auth.is_forbidden` and `metis.auth.login_required` hooks to configure.

```php
// Return true to prevent the user from viewing this page, false to proceed as normal.
add_filter( 'metis.auth.is_forbidden', function( $is_forbidden ) {
    if ( ! is_singular( 'some_custom_post_type' ) ) {
        return $is_forbidden
    }

    if ( ! current_user_can( 'some_custom_capability' ) ) {
        return true;
    }

    return false;
} );
```

In the above example, users will be forbidden from viewing singular pages of the post type `some_custom_post_type` unless they have the capability `some_custom_capability`.

```php
// Return true to require the user to be logged in to view this page, false to continue as normal.
add_filter( 'metis.auth.login_required', function( $login_required ) {
    if ( ! is_singular( 'another_custom_post_type' ) ) {
        return $login_required;
    }

    return true;
} );
```

In this example, users will be required to log in before viewing singular pages of the post type `another_custom_post_type`.

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

### WordPress hooks via docblocks

By adding the `@hook` tag to the docblock of a public method it will automatically be attached to a hook with the same name as the method. Default priority is 10 and number of parameters is automatically determined from the method definition.

```php
class SomeAwesomeClass {
    /**
     * Equivalent of add_action( 'init', [ $this, 'init' ], 10, 0 );
     *
     * @hook
     */
    public function init() {
        // ...
    }
}
```

You can adjust the priority via the `@priority` tag.

```php
class SomeAwesomeClass {
    /**
     * Equivalent of add_action( 'init', [ $this, 'init' ], 5, 0 );
     *
     * @hook
     *
     * @priority 5
     */
    public function init() {
        // ...
    }
}
```

If you would like to use a method name that doesn't match the hook name, you can define the hook name with the `@tag` tag.

```php
class SomeAwesomeClass {
    /**
     * Equivalent of add_action( 'init', [ $this, 'some_method' ], 10, 0 );
     *
     * @hook
     *
     * @tag init
     */
    public function some_method() {
        // ...
    }
}
```

And lastly, you can define multiple hooks and multiple priorities and each possible combination will be used.

```php
class SomeAwesomeClass {
    /**
     * Equivalent of:
     * add_action( 'plugins_loaded', [ $this, 'some_method' ], 5, 0 );
     * add_action( 'plugins_loaded', [ $this, 'some_method' ], 15, 0 );
     * add_action( 'init', [ $this, 'some_method' ], 5, 0 );
     * add_action( 'init', [ $this, 'some_method' ], 15, 0 );
     *
     * @hook
     *
     * @tag plugins_loaded
     * @tag init
     *
     * @priority 5
     * @priority 15
     */
    public function some_method() {
        // ...
    }
}
```

To use the loader, simply call `SSNepenthe\Metis\Loader::attach()` and pass in an instance of whatever class needs to be hooked in to WordPress.

```php
add_action( 'plugins_loaded', function() {
    SSNepenthe\Metis\Loader::attach( new SomeAwesomeClass );
} );
```

### Admin Bar Menu
```php
add_action( 'init', function() {
    $toolbar = new SSNepenthe\Metis\Toolbar;

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
