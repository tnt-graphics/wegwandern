<?php
/*
Plugin Name: Formidable Mailchimp
Description: Add new Mailchimp contacts from your Formidable forms
Version: 2.08
Plugin URI: https://formidableforms.com/
Author URI: https://formidableforms.com/
Author: Strategy11
Text Domain: frmmlcmp
*/

function frm_mlcmp_forms_autoloader( $class_name ) {
	$path = __DIR__;

	// Only load FrmMlcmp classes here
	if ( ! preg_match( '/^FrmMlcmp.+$/', $class_name ) ) {
		return;
	}

	if ( preg_match( '/^.+Helper$/', $class_name ) ) {
		$path .= '/helpers/' . $class_name . '.php';
	} else if ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$path .= '/controllers/' . $class_name . '.php';
	} else {
		$path .= '/models/' . $class_name . '.php';
	}

	if ( file_exists( $path ) ) {
		include $path;
	}
}

// Add the autoloader
spl_autoload_register( 'frm_mlcmp_forms_autoloader' );

// Load hooks and languages
add_action( 'plugins_loaded', 'FrmMlcmpHooksController::load_hooks' );
add_action( 'plugins_loaded', 'FrmMlcmpAppController::load_lang' );
