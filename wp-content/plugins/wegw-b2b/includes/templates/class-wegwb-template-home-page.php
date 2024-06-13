<?php
/**
 * Wegwandern Home_Page Template Generate
 *
 * Generate template for B2B Home Page
 *
 * @package WEGW_B2B\Templates\Home_Page
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template filter class
 */
class WEGW_B2B_Template_Home_Page {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'wegwb_ads_home_page_template' ) );
		add_filter( 'theme_page_templates', array( $this, 'wegwb_add_ads_home_page_template_to_select' ), 10, 4 );
	}

	/**
	 * Load `B2B Home Page` template from specific page
	 */
	function wegwb_ads_home_page_template( $page_template ) {
		if ( get_page_template_slug() == 'ads-home-page.php' ) {
			$page_template = WEGW_B2B_PATH . 'templates/user/ads/ads-home-page.php';
		}
		return $page_template;
	}

	/**
	 * Add `B2B Home Page` template to page attirbute template section
	 */
	function wegwb_add_ads_home_page_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
		/* Add custom template named ads-home-page.php to select dropdown */
		$post_templates['ads-home-page.php'] = __( 'B2B Home Page' );
		return $post_templates;
	}

}

wegwb_new_instance( 'WEGW_B2B_Template_Home_Page' );
