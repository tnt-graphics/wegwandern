<?php
/*
Plugin Name: Formidable Registration
Plugin URI: https://formidableforms.com/knowledgebase/user-registration/
Description: Register users through a Formidable form
Author: Strategy11
Author URI: https://formidableforms.com/
Version: 3.0.1
Text Domain: frmreg
*/

/**
 * @return void
 */
function frmreg_forms_autoloader( $class_name ) {
	// Only load FrmReg classes here
	if ( ! preg_match( '/^FrmReg.+$/', $class_name ) ) {
		return;
	}

	$path = __DIR__;

	if ( preg_match( '/^.+Helper$/', $class_name ) ) {
		$path .= '/helpers/' . $class_name . '.php';
	} else if ( preg_match( '/^.+Controller$/', $class_name ) ) {
		$path .= '/controllers/' . $class_name . '.php';
	} else if ( preg_match( '/^.+View$/', $class_name ) ) {
		$path .= '/views/' . $class_name . '.php';
	} else {
		$path .= '/models/' . $class_name . '.php';
	}

	if ( file_exists( $path ) ) {
		include $path;
	}
}

// Add the autoloader
spl_autoload_register( 'frmreg_forms_autoloader' );

add_action( 'plugins_loaded', 'FrmRegHooksController::load_hooks' );
add_action( 'init', 'FrmRegAppController::load_lang', 0 );
