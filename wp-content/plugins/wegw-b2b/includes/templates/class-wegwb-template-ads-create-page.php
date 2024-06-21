<?php
/**
 * Wegwandern Ads_Create Template Generate
 *
 * Generate template for B2B `Create Ads` page
 *
 * @package WEGW_B2B\Templates\Ads_Create
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template filter class
 */
class WEGW_B2B_Template_Ads_Create {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'wegwb_ads_create_page_template' ) );
		add_filter( 'theme_page_templates', array( $this, 'wegwb_add_ads_create_page_template_to_select' ), 10, 4 );

		/* B2B Ad edit details url rewrite actions */
		add_action( 'init', array( $this, 'wegwb_ads_url_rewrite_tag' ), 10, 0 );
		add_action( 'query_vars', array( $this, 'wegwb_ads_custom_query_vars' ) );
	}

	/**
	 * Load `Create Ads` template from specific page
	 */
	function wegwb_ads_create_page_template( $page_template ) {
		if ( get_page_template_slug() == 'ads-create.php' ) {
			$page_template = WEGW_B2B_PATH . 'templates/user/ads/ads-create.php';
		}
		return $page_template;
	}

	/**
	 * Add `Create Ads` template to page attirbute template section
	 */
	function wegwb_add_ads_create_page_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
		/* Add custom template named ads-create.php to select dropdown */
		$post_templates['ads-create.php'] = __( 'B2B Create Ads' );
		return $post_templates;
	}

	/**
	 * `Edit Ads` URL rewrite rule
	 */
	function wegwb_ads_url_rewrite_tag() {
		add_rewrite_tag( '%edit%', '([^&]+)' );
		add_rewrite_rule(
			'^' . WEGW_B2B_AD_CREATE . '/edit/([^/]*)/?',
			'index.php?pagename=' . WEGW_B2B_AD_CREATE . '&edit=$matches[1]',
			'top'
		);

		flush_rewrite_rules();
	}

	/**
	 * `Edit Ads` URL rewrite rule create custom tags
	 */
	function wegwb_ads_custom_query_vars( $vars ) {
		$vars[] = 'edit';
		return $vars;
	}
}

wegwb_new_instance( 'WEGW_B2B_Template_Ads_Create' );
