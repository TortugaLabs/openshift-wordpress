# WordPress on OpenShift #

WordPress is an open source, semantic, blogging and content management 
platform written in PHP with a MySQL backend focusing on aesthetics, web 
standards, and usability.

The easiest way to install this application is to use the [OpenShift
Instant Application][template]. If you'd like to install it
manually, follow [these directions](#manual-installation).

For additional deployment and management considerations, see [Deploying WordPress 
on OpenShift](https://developers.openshift.com/en/php-wordpress.html).

For a live demo, vist [blog.openshift.com](https://blog.openshift.com/) :)

## OpenShift Considerations ##
These are some special considerations you may need to keep in mind when
running your application on OpenShift.

### WordPress Configuration ###
One of the most important files in your WordPress installation is the wp-config.php 
file. The file is located at `.openshift/config/wp-config` within your project 
directory. This file will be copied into the root of your remote WordPress installation
during deployment (each time you `git push`).

The wp-config.php file has been pre-configured to automatically connect to your OpenShift 
MySQL database, automatically set security keys, and provide helpful presets and links 
to further documentation. Visit [editing wp-config.php](https://codex.wordpress.org/Editing_wp-config.php)
for more information.

### htaccess ###
A basic .htaccess configuration file has been included at `.openshift/config/.htaccess`.
This file will be copied into the root of your remote WordPress installation during 
deployment (each time you `git push`). For more information about htaccess settings 
for WordPress, please visit [htaccess](https://codex.wordpress.org/htaccess).

### Plugins and Themes ###
When you upload plugins and themes, they'll get put into your OpenShift 
data directory on the gear ($OPENSHIFT_DATA_DIR).

If you'd like to check these into source control, download the plugins 
and themes directories and then check them directly into 
`.openshift/themes` and `.openshift/plugins`.

### Development Mode ###
When you develop your WordPress application on OpenShift, you can also enable 
the 'development' environment by setting the `APPLICATION_ENV` environment 
variable using the `rhc` client, like:

```
$ rhc env set APPLICATION_ENV=development -a <app-name>
```

Then, restart your application:

```
$ rhc app restart -a <app-name>
```

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

### Security Considerations ###
OpenShift automatically generates unique secret keys for your deployment 
in wp-config.php, but you may feel more comfortable following the WordPress 
documentation directly.

## Manual Installation ##

Create a php-5.4 application (you can call your application whatever you want)

    rhc app create wordpress php-5.4 mysql-5.5 --from-code=https://github.com/openshift/wordpress-example

That's it, you can now checkout your application at:

    https://wordpress-$yournamespace.rhcloud.com

You'll be prompted to set an admin password and name your WordPress site the first time you visit this
page.

[template]: https://hub.openshift.com/quickstarts/1-wordpress-4