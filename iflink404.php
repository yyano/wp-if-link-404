<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Special thanks!  https://wppb.me/
 *
 * @link              https://plus.google.com/u/0/+YANOYasuhiro/
 * @since             1.0.0
 * @package           Iflink404
 *
 * @wordpress-plugin
 * Plugin Name:       if link is 404(broken), then set to private.
 * Plugin URI:        https://github.com/yyano/wp-if-link-404
 * Description:       If link is 404(broken), then Update PostStatus( publish to private ).
 * Version:           1.0.0.20170916
 * Author:            yyano
 * Author URI:        https://github.com/yyano/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       iflink404
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-iflink404-activator.php
 */
function activate_iflink404() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-iflink404-activator.php';
	Iflink404_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-iflink404-deactivator.php
 */
function deactivate_iflink404() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-iflink404-deactivator.php';
	Iflink404_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_iflink404' );
register_deactivation_hook( __FILE__, 'deactivate_iflink404' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-iflink404.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_iflink404() {

	$plugin = new Iflink404();
	$plugin->run();

}
run_iflink404();
