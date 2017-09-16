<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://plus.google.com/u/0/+YANOYasuhiro/
 * @since      1.0.0
 *
 * @package    Iflink404
 * @subpackage Iflink404/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Iflink404
 * @subpackage Iflink404/includes
 * @author     yyano <yano.yasuhiro@gmail.com>
 */
class Iflink404_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'iflink404_check_links' );
	}

}
