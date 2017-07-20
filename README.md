# metis
[Pimple](https://pimple.sensiolabs.org/), with some some helpful tweaks for working in WordPress.

## Requirements
PHP 5.3 or later and Composer.

## Installation
```
$ composer require ssnepenthe/metis
```

## Usage
This is basically Pimple but you will use `Metis\Container` instead of `Pimple\Container`.

The following features have been introduced:

**Handle activation and deactivation logic within your service providers**

```
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

```
$container = new Metis\Container;
$container->register( new Some_Provider );

register_activation_hook( __FILE__, array( $container, 'activate' ) );
register_deactivation_hook( __FILE__, array( $container, 'deactivate' ) );
```

**Handle boot logic (add_action/add_filter calls) within your service providers**

```
class Another_Provider implements Pimple\ServiceProviderInterface {
    public function boot( Pimple\Container $container ) {
        add_action( 'init', array( $container['service'], 'init' ) );
    }

    // ...
}
```

And then call the corresponding method on your container instance.

```
$container = new Metis\Container;
$container->register( new Another_Provider );

add_action( 'plugins_loaded', array( $container, 'boot' ) );
```

**Defer object creation until the first method call**

This is useful when you want to attach an object to WordPress but not actually create it until it is needed.

Consider the following:

```
class Admin_Provider implements Pimple\ServiceProviderInterface {
    public function boot( Pimple\Container $container ) {
        add_action( 'admin_init', array( $container['admin_page'], 'add_page' ) );
        add_action( 'admin_menu', array( $container['admin_page'], 'add_options' ) );
    }

    // ...
}

$container = new Metis\Container;
$container->register( new Admin_Provider );

add_action( 'plugins_loaded', array( $container, 'boot' ) );
```

This will create an `admin_page` instance on every request even though it is only needed on requests within wp-admin.

There are many ways to work around this, but Metis provides service proxies for this exact purpose.

Here it is rewritten with proxies:

```
class Admin_Provider implements Pimple\ServiceProviderInterface {
    public function boot( Pimple\Container $container ) {
        add_action( 'admin_init', array( $container->proxy( 'admin_page' ), 'add_page' ) );
        add_action( 'admin_menu', array( $container->proxy( 'admin_page' ), 'add_options' ) );
    }

    // ...
}

$container = new Metis\Container;
$container->register( new Admin_Provider );

add_action( 'plugins_loaded', array( $container, 'boot' ) );
```

This prevents the `admin_page` instance from being created until one of the `admin_init` or `admin_menu` hooks has actually fired.

This was a trivial example, of course, but it is perfect for cases when a class has many dependencies and is only used on a small number of requests.

**Access WordPress globals from the container**

Use the `WordPress_Provider` class to get access to frequently used WordPress globals from the container.

```
$container = new Metis\Container;
$container->register( new Metis\WordPress_Provider );

$container['wp'] === $GLOBALS['wp']; // true
```

`$wp`, `$wpdb`, `$wp_query`, `$wp_rewrite`, `$wp_filesystem` and `$wp_object_cache` are all added to the container.

Be careful about timing when using these - each returns null if it has not yet been defined.
