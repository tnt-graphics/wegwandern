<?php
/**
 * Wegwandern User Profile Edit Template Generate
 *
 * Generate template for B2B `User Profile Edit` page
 *
 * @package WEGW_B2B\Templates\User_Profile_Edit
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template filter class
 */
class WEGW_B2B_Template_User_Profile_Edit {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'wegwb_user_profile_edit_page_template' ) );
		add_filter( 'theme_page_templates', array( $this, 'wegwb_user_profile_edit_page_template_to_select' ), 10, 4 );
	}

	/**
	 * Load `User Profile Edit` template from specific page
	 */
	function wegwb_user_profile_edit_page_template( $page_template ) {
		if ( get_page_template_slug() == 'user-profile-edit.php' ) {
			$page_template = WEGW_B2B_PATH . 'templates/user/auth/user-profile-edit.php';
		}
		return $page_template;
	}

	/**
	 * Add `User Profile Edit`template to page attirbute template section
	 */
	function wegwb_user_profile_edit_page_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
		/* Add custom template named user-profile-edit.php to select dropdown */
		$post_templates['user-profile-edit.php'] = __( 'B2B User Profile Edit' );
		return $post_templates;
	}

}

wegwb_new_instance( 'WEGW_B2B_Template_User_Profile_Edit' );
