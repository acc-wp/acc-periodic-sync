<?php
/*
 *
 *	ENQUEUES AND DEQUEUE
 *
 * 
 */
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/************************
 *	    JS ET CSS     	*
 ************************/

add_action( 'admin_enqueue_scripts', 'kfg_admin_styles_scripts' );
function kfg_admin_styles_scripts($hook) {
	wp_register_style( 'kfg-adminstyle', KFG_PLUGIN_DIR . '/assets/styles/admin.css');
	wp_enqueue_style( 'kfg-adminstyle' );
}
