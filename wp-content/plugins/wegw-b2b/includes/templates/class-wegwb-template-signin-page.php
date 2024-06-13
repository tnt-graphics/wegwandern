<?php
/**
 * Wegwandern Signin/Registration Template Generate
 *
 * Generate template for B2B `Signin` page
 *
 * @package WEGW_B2B\Templates\Signin
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template filter class
 */
class WEGW_B2B_Template_Signin {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'wegwb_signin_page_template' ) );
		add_filter( 'theme_page_templates', array( $this, 'wegwb_add_signin_page_template_to_select' ), 10, 4 );
	}

	/**
	 * Load `Signin` template from specific page
	 */
	function wegwb_signin_page_template( $page_template ) {
		if ( get_page_template_slug() == 'user-signin.php' ) {
			$page_template = WEGW_B2B_PATH . 'templates/user/auth/user-signin.php';
		}
		return $page_template;
	}

	/**
	 * Add `Signin`template to page attirbute template section
	 */
	function wegwb_add_signin_page_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
		/* Add custom template named user-signin.php to select dropdown */
		$post_templates['user-signin.php'] = __( 'B2B Sign In' );
		return $post_templates;
	}

}

wegwb_new_instance( 'WEGW_B2B_Template_Signin' );
