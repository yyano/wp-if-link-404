<?php

/**
 * Fired during plugin activation
 *
 * @link       https://plus.google.com/u/0/+YANOYasuhiro/
 * @since      1.0.0
 *
 * @package    Iflink404
 * @subpackage Iflink404/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Iflink404
 * @subpackage Iflink404/includes
 * @author     yyano <yano.yasuhiro@gmail.com>
 */
class Iflink404_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		wp_schedule_event( time(), 'hourly', 'iflink404_check_links' );
	}

}
