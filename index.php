<?php
/**
 * Plugin Name: ACC Periodic Sync
 * Description: Periodic synchronization of ACC members list. An add-on to ACC User Importer plugin.
 * Version: 1.3.0
 * Author: Karine Frenette-G, Francois Bessette
 * Author URI: https://karinegaufre.com/
 * License: GPL2
 * Text Domain: acc-periodic-sync
 */

/*
	License: GPL2
	== Copyright ==
	Copyright 2013-2015 Karine Frenette-Gaudreault
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	define('KFG_BASE_DIR', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));
	define('KFG_PLUGIN_DIR', plugins_url() . "/karinegaufre/");

/**
 * Required files
 *
 *
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

include_once( KFG_BASE_DIR . '/functions/queues.php' );

if( is_plugin_active( 'razpeel-acc-user-manager-9039b45362c8/acc-importer.php' ) ) {
	include_once( KFG_BASE_DIR . '/functions/acc-user-manager.php' );
	
}  //is this plugin activating


register_activation_hook( __FILE__, 'kfg_cron_activate' ); 
register_deactivation_hook( __FILE__, 'kfg_cron_deactivate' ); 