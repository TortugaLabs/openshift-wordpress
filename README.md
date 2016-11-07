# WordPress on OpenShift

This is my WordPress on OpenShift template (forked from
[OpenShift](https://github.com/openshift/wordpress-example)).
See the [Original README](README-orig.md) for details on how
this works in general.

When cloning this repository, make sure you use the command:

- git clone --recursive $repo_url

This is to make sure that all submodules are pulled accordingly.

## Configuration

The main configuration files are:

- .openshift/config/wp-config
- .openshift/config/.htacess

## Plugins and Themes

Plugins and themes can be installed in several ways:

- From the control panel
  - While this is indeed possible, extensions installed this way will not
    work for autoscaling.  However, it is useful if you want to do quick
    tests
- Placing them in these folders:
  - .openshift/plugins
  - .openshift/themes
  - These plugins will work when autoscaling them.  Note that removing plugins
    from here will not uninstall them.
- Registering a plugin/theme in `.openshift/wp-manifest.txt`.
  - Plugins here can autoscale.  **(This has NOT been tested)**.
  - The deploy and build hooks will automatically download the required versions
    from the Internet.
  - Like extensions in folders, removing plugins from the `wp-manifest` will
    **NOT** un-install.

Removing the plugins/themes from the `.openshift` folders or `wp-manifest`
will **NOT** uninstall them.  To un-install you must first remove them
from the `.openshift` folders and/or `wp-manifest`, then go through the
control panel and de-activate and un-install.  This  is needed to make
sure that plugins/themes are de-activated first so that any needed clean-ups
are executed.

## Development Mode

When you develop your WordPress application on OpenShift, you can also enable 
the 'development' environment by setting the `APPLICATION_ENV` environment 
variable using the `rhc` client, like:

    $ rhc env set APPLICATION_ENV=development -a $appname

Then, restart your application:


    $ rhc app restart -a $appname

If you do so, OpenShift will run your application under 'development' mode.
In development mode, your application will:

* Enable WordPress debugging (sets `WP_DEBUG` to TRUE)
* Show more detailed errors in browser
* Display startup errors
* Enable the [Xdebug PECL extension](http://xdebug.org/)
* Enable [APC stat check](http://php.net/manual/en/apc.configuration.php#ini.apc.stat)
* Ignore your composer.lock file

Set the variable to 'production' and restart your app to deactivate error reporting 
and resume production PHP settings.

Using the development environment can help you debug problems in your application
in the same way as you do when developing on your local machine. However, we 
strongly advise you not to run your application in this mode in production.


## Manual Installation

Create a php-5.4 application (you can call your application whatever you want)

    rhc app create wordpress php-5.4 mysql-5.5 --from-code=https://github.com/openshift/wordpress-example

Follow the instructions for the [DIYdeploy](https://github.com/iliu-net/openshift-ci-utils)
script.

You can now checkout your application at:

    https://wordpress-$yournamespace.rhcloud.com

You'll be prompted to set an admin password and name your WordPress site the first time you visit this
page.

