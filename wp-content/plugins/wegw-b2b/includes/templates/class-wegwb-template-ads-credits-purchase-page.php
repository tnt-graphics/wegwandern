<?php
/**
 * Wegwandern Ads_Credits_Purchase Template Generate
 *
 * Generate template for B2B `Credits Purchase` page
 *
 * @package WEGW_B2B\Templates\Ads_Credits_Purchase
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template filter class
 */
class WEGW_B2B_Template_Ads_Credits_Purchase {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'wegwb_ads_credits_purchase_page_template' ) );
		add_filter( 'theme_page_templates', array( $this, 'wegwb_add_ads_credits_purchase_page_template_to_select' ), 10, 4 );
		spl_autoload_register( array( $this, 'wegwb_ads_load_payrexx' ) );
	}

	/**
	 * Load `Credits Purchase` template from specific page
	 */
	function wegwb_ads_credits_purchase_page_template( $page_template ) {
		if ( get_page_template_slug() == 'ads-credits-purchase.php' ) {
			$page_template = WEGW_B2B_PATH . 'templates/user/ads/ads-credits-purchase.php';
		}
		return $page_template;
	}

	/**
	 * Add `Credits Purchase` template to page attirbute template section
	 */
	function wegwb_add_ads_credits_purchase_page_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
		/* Add custom template named ads-credits-purchase.php to select dropdown */
		$post_templates['ads-credits-purchase.php'] = __( 'B2B Credits Purchase' );
		return $post_templates;
	}

	/**
	 * Autoload `Payrexx` payment gateway library classes
	 */
	function wegwb_ads_load_payrexx( $class ) {
		$root      = dirname( __DIR__ );
		$classFile = $root . '/lib/' . str_replace( '\\', '/', $class ) . '.php';
		if ( file_exists( $classFile ) ) {
			require_once $classFile;
		}
	}
}

wegwb_new_instance( 'WEGW_B2B_Template_Ads_Credits_Purchase' );
