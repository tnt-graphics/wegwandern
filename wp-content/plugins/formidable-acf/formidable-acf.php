<?php
/**
 * Plugin Name: Formidable ACF
 * Description: Map Formidable fields to Advanced Custom Fields.
 * Version: 1.0.3
 * Plugin URI: https://formidableforms.com/
 * Author URI: https://formidableforms.com/
 * Author: Strategy11
 * Text Domain: formidable-acf
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( class_exists( 'FrmAcfAppController', false ) ) {
	return;
}

/**
 * Loads all the classes for this plugin.
 *
 * @param string $class_name The name of the class to load.
 */
function frm_acf_autoloader( $class_name ) {
	$path = __DIR__;

	// Only load Frm classes here.
	if ( ! preg_match( '/^FrmAcf.+$/', $class_name ) ) {
		return;
	}

	if ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$path .= '/classes/controllers/' . $class_name . '.php';
	} elseif ( preg_match( '/^.+Helper$/', $class_name ) ) {
		$path .= '/classes/helpers/' . $class_name . '.php';
	} else {
		$path .= '/classes/models/' . $class_name . '.php';
	}

	if ( file_exists( $path ) ) {
		include $path;
	}
}
spl_autoload_register( 'frm_acf_autoloader' );

add_filter( 'frm_load_controllers', array( 'FrmAcfHooksController', 'add_hooks_controller' ) );
