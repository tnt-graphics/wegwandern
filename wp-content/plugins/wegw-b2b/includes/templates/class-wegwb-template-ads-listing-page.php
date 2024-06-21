<?php
/**
 * Wegwandern Ads_Listing Template Generate
 *
 * Generate template for B2B `Ads Listing` page
 *
 * @package WEGW_B2B\Templates\Ads_Listing
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template filter class
 */
class WEGW_B2B_Template_Ads_Listing {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'wegwb_ads_listing_page_template' ) );
		add_filter( 'theme_page_templates', array( $this, 'wegwb_add_ads_listing_page_template_to_select' ), 10, 4 );
	}

	/**
	 * Load `Ads Listing` template from specific page
	 */
	function wegwb_ads_listing_page_template( $page_template ) {
		if ( get_page_template_slug() == 'ads-listing.php' ) {
			$page_template = WEGW_B2B_PATH . 'templates/user/ads/ads-listing.php';
		}
		return $page_template;
	}

	/**
	 * Add `Ads Listing` template to page attirbute template section
	 */
	function wegwb_add_ads_listing_page_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
		/* Add custom template named ads-listing.php to select dropdown */
		$post_templates['ads-listing.php'] = __( 'B2B Ads Listing' );
		return $post_templates;
	}

}

wegwb_new_instance( 'WEGW_B2B_Template_Ads_Listing' );
