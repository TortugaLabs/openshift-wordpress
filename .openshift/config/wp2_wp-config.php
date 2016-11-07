<?php
/*
|--------------------------------------------------------------------------
| WordPress Configuration
|--------------------------------------------------------------------------
|
| This file has been configured for use with OpenShift.
|
| To learn more about managing WordPress on Openshift, see:
| https://developers.openshift.com/en/php-wordpress.html
|
*/

/*
|--------------------------------------------------------------------------
| OpenShift Recommended Add-on: SendGrid
|--------------------------------------------------------------------------
|
| By default, WordPress uses PHP's mail function to send emails. We
| strongly recommend using SendGrid to ensure messages are delivered to
| both you and your users.
|
| To learn more installing SendGrid, see:
| https://developers.openshift.com/en/marketplace-sendgrid.html#php-wordpress
|
*/

/**
 * Code provided for users following SendGrid instructions linked above.
 */
//define('SENDGRID_USERNAME', getenv('SENDGRID_USERNAME'));
//define('SENDGRID_PASSWORD', getenv('SENDGRID_PASSWORD'));
//define('SENDGRID_SEND_METHOD', 'api');

/*
|--------------------------------------------------------------------------
| WordPress Database Table Prefix
|--------------------------------------------------------------------------
|
| You can have multiple installations in one database if you give each a unique
| prefix. Only numbers, letters, and underscores please!
|
*/

$table_prefix  = 'wp_';

/*
|--------------------------------------------------------------------------
| WordPress Administration Panel
|--------------------------------------------------------------------------
|
| Determine whether the administration panel should be viewed over SSL. We
| prefer to be secure by default.
|
*/

define('FORCE_SSL_ADMIN', true);

/*
|--------------------------------------------------------------------------
| WordPress Debugging Mode - MODIFICATION NOT RECOMMENDED (see below)
|--------------------------------------------------------------------------
| 
| Set OpenShift's APPLICATION_ENV environment variable in order to enable 
| detailed PHP and WordPress error messaging during development.
|
| Set the variable, then restart your app. Using the `rhc` client:
|
|   $ rhc env set APPLICATION_ENV=development -a <app-name>
|   $ rhc app restart -a <app-name>
|
| Set the variable to 'production' and restart your app to deactivate error 
| reporting.
|
| For more information about the APPLICATION_ENV variable, see:
| https://developers.openshift.com/en/php-getting-started.html#development-mode
|
| WARNING: We strongly advise you NOT to run your application in this mode 
|          in production.
|
*/

define('WP_DEBUG', getenv('APPLICATION_ENV') == 'development' ? true : false);

/*
|--------------------------------------------------------------------------
| MySQL Settings - DO NOT MODIFY
|--------------------------------------------------------------------------
|
| WordPress will automatically connect to your OpenShift MySQL database
| by making use of OpenShift environment variables configured below.
|
| For more information on using environment variables on OpenShift, see:
| https://developers.openshift.com/en/managing-environment-variables.html
|
*/

define('DB_NAME', getenv('OPENSHIFT_APP_NAME'));
define('DB_USER', getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
define('DB_PASSWORD', getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
define('DB_HOST', getenv('OPENSHIFT_MYSQL_DB_HOST') . ':' . getenv('OPENSHIFT_MYSQL_DB_PORT'));
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

/*
|--------------------------------------------------------------------------
| Authentication Unique Keys and Salts - DO NOT MODIFY
|--------------------------------------------------------------------------
|
| Keys and Salts are automatically configured below.
|
*/

require_once(getenv('OPENSHIFT_REPO_DIR') . '.openshift/openshift.inc');

/** Prepare for multisite config **/
define( 'WP_ALLOW_MULTISITE', true );
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
define('DOMAIN_CURRENT_SITE', 'iliu.net');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

// Disable WP_CRON from the web server
define('DISABLE_WP_CRON', true);

/*
|--------------------------------------------------------------------------
| That's all, stop editing! Happy blogging.
|--------------------------------------------------------------------------
*/

// absolute path to the WordPress directory
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/');

// tell WordPress where the plugins directory really is
if ( !defined('WP_PLUGIN_DIR') && is_link(ABSPATH . '/wp-content/plugins') )
  define('WP_PLUGIN_DIR', realpath(ABSPATH . '/wp-content/plugins'));

// sets up WordPress vars and included files
require_once(ABSPATH . 'wp-settings.php');
