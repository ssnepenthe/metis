# metis
[Pimple](https://pimple.sensiolabs.org/), with some some helpful tweaks for working in WordPress.

## Requirements
PHP 5.3 or later and Composer.

**Note:** Metis should continue to work down to 5.3 but is no longer tested below 5.4.

## Installation
```
$ composer require ssnepenthe/metis
```

## Usage
This is basically Pimple but you will use `Metis\Container` instead of `Pimple\Container`.

The following features have been introduced:

**Handle activation and deactivation logic within your service providers**

```php
class Some_Provider implements Pimple\ServiceProviderInterface {
    public function activate( Pimple\Container $container ) {
        // Handle activation here.
    }

    public function deactivate( Pimple\Container $container ) {
        // Handle deactivation here.
    }

    // ...
}
```

And then call the corresponsing method on your container instance.

```php
$container = new Metis\Container;
$container->register( new Some_Provider );

register_activation_hook( __FILE__, array( $container, 'activate' ) );
register_deactivation_hook( __FILE__, array( $container, 'deactivate' ) );
```

**Handle boot logic (add_action/add_filter calls) within your service providers**

```php
class Another_Provider implements Pimple\ServiceProviderInterface {
    public function boot( Pimple\Container $container ) {
        add_action( 'init', array( $container['service'], 'init' ) );
    }

    // ...
}
```

And then call the corresponding method on your container instance.

```php
$container = new Metis\Container;
$container->register( new Another_Provider );

add_action( 'plugins_loaded', array( $container, 'boot' ) );
```

**Service Proxies**

One of the many benefits of a dependency injection container like Pimple is that objects are created on demand as you access the various container entries.

This can be especially useful for functionality that is only needed on a limited number of requests (e.g. admin, cron, etc.).

Unfortunately this doesn't always work the way you might want in WordPress:

```php
class Admin_Provider implements Pimple\ServiceProviderInterface {
    public function boot( Pimple\Container $container ) {
        add_action( 'admin_init', array( $container['admin_page'], 'do_something' ) );
    }
}
```

Since the `boot()` method is typically attached to the `plugins_loaded` hook, the admin page object will always be created regardless of whether `admin_init` has been triggered.

A sensible approach would be to verify that the current request is for an admin page before calling `add_action()`:

```php
class Admin_Provider implements Pimple\ServiceProviderInterface {
    public function boot( Pimple\Container $container ) {
        if ( is_admin() ) {
            add_action( 'admin_init', array( $container['admin_page'], 'do_something' ) );
        }
    }
}
```

But this results in a boot method littered with conditionals.

An alternative would be to access the `admin_page` entry within a closure:

```php
class Admin_Provider implements Pimple\ServiceProviderInterface {
    public function boot( Pimple\Container $container ) {
        add_action( 'admin_init', function() use ( $container ) {
            $container['admin_page']->do_something();
        }
    }
}
```

But that gets tedious quickly and can result in a large number of unnecessary `Closure` objects floating around.

In cases like this, you might choose to extend `Metis\Base_Provider` and use the `proxy()` method instead:

```php
class Admin_Provider extends Metis\Base_Provider {
    public function boot( Pimple\Container $container ) {
        add_action( 'admin_init', array( $this->proxy( $container, 'admin_page' ), 'do_something' ) );
    }
}
```

This will create a `Metis\Proxy` object to be used in place of the admin page object. The proxy will correctly proxy all method calls to the underlying service from the container but hold off on creation of that service until it is actually needed.

This was a trivial example, of course, but proxies are perfect for cases when a class has many dependencies and is only used on a small number of requests.

**Access WordPress globals from the container**

Use the `WordPress_Provider` class to get access to frequently used WordPress globals from the container.

```php
$container = new Metis\Container;
$container->register( new Metis\WordPress_Provider );

$container['wp'] === $GLOBALS['wp']; // true
```

`$wp`, `$wpdb`, `$wp_query`, `$wp_rewrite`, `$wp_filesystem` and `$wp_object_cache` are all added to the container.

Be careful about timing when using these - each returns null if it has not yet been defined.
