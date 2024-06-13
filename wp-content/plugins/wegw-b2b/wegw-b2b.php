<?php
/**
 * Plugin Name: Wegwandern B2B
 * Plugin URI:  https://www.pitsolutions.ch/en/
 * Description: This plugin enables B2B section for Wegwandern.
 * Version:     1.0.0
 * Author:      PIT Solutions
 * Author URI:  https://www.pitsolutions.ch/en/
 * Text Domain: wegw-b2b
 * Domain Path: /lang
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:  https://www.pitsolutions.ch/
 */

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WEGW_B2B' ) ) :

	class WEGW_B2B {

		/**
		 *
		 *
		 * @var string The plugin version number.
		 */
		var $version = '1.0.0';

		/**
		 *
		 *
		 * @var array The plugin settings array.
		 */
		var $settings = array();

		/**
		 *
		 *
		 * @var array The plugin data array.
		 */
		var $data = array();

		/**
		 *
		 *
		 * @var array Storage for class instances.
		 */
		var $instances = array();

		/**
		 * __construct
		 *
		 * A dummy constructor to ensure WEGW_B2B is only setup once.
		 */
		function __construct() {
			// Do nothing.
		}

		/**
		 * initialize
		 *
		 * Sets up the WEGW B2B plugin.
		 */
		function initialize() {
			 global $wpdb;

			/**
			 * Define Constants.
			 */
			$this->define( 'WEGW_B2B', true );
			$this->define( 'WEGW_B2B_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'WEGW_B2B_ASSETS_PATH', plugin_dir_url( __FILE__ ) . 'assets/' );
			$this->define( 'WEGW_B2B_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WEGW_B2B_VERSION', $this->version );

			/* B2B Pages Slug */
			$this->define( 'WEGW_B2B_LOGIN_PAGE', 'b2b-portal' );
			$this->define( 'WEGW_B2B_FROGOT_PASSWORD_PAGE', 'passwort-zurucksetzen' );
			$this->define( 'WEGW_B2B_DASHBOARD', 'b2b-portal-dashboard' );
			$this->define( 'WEGW_B2B_PROFILE', 'profil' );
			$this->define( 'WEGW_B2B_AD_CREATE', 'angebote-erfassen' );
			$this->define( 'WEGW_B2B_AD_LISTING', 'status-angebote' );
			$this->define( 'WEGW_B2B_CREDIT_PURCHASE_PAGE', 'credits-kaufen' );
			$this->define( 'WEGW_B2B_PAYREXX_INSTANCE', 'test-wegwandern' );
			$this->define( 'WEGW_B2B_PAYREXX_API_KEY', 'QMcm9SxWDqj63o7DZUOcCJiEm7gSXK' );

			/* Hide B2B Links for not completed profiles  */
			$this->define('WEGW_B2B_HIDE_MENUS', array( 'Angebote erfassen',  'Status Angebote', 'Weitere Werbemöglichkeiten', "Klick's kaufen" ));

			/**
			 * Essential functions.
			 */
			include_once WEGW_B2B_PATH . 'includes/wegwb-utility-functions.php';
			include_once WEGW_B2B_PATH . 'includes/wegwb-custom-post-types.php';

			/**
			 * Core classes.
			 */
			include_once WEGW_B2B_PATH . 'includes/class-wegwb-frontend-scripts.php';
			include_once WEGW_B2B_PATH . 'includes/ajax/class-wegwb-ajax.php';
			include_once WEGW_B2B_PATH . 'includes/api/class-wegwb-api-payment-settings.php';
			include_once WEGW_B2B_PATH . 'includes/api/class-wegwb-api-cron-settings.php';
			// include_once WEGW_B2B_PATH . 'includes/class-wegwb-generate-ui-templates.php';
			include_once WEGW_B2B_PATH . 'includes/templates/class-wegwb-template-signin-page.php';
			include_once WEGW_B2B_PATH . 'includes/templates/class-wegwb-template-ads-create-page.php';
			include_once WEGW_B2B_PATH . 'includes/templates/class-wegwb-template-ads-credits-purchase-page.php';
			include_once WEGW_B2B_PATH . 'includes/templates/class-wegwb-template-ads-listing-page.php';
			include_once WEGW_B2B_PATH . 'includes/templates/class-wegwb-template-home-page.php';
			include_once WEGW_B2B_PATH . 'includes/templates/class-wegwb-template-user-profile-edit.php';
			include_once WEGW_B2B_PATH . 'templates/user/auth/b2b-login.php';
			include_once WEGW_B2B_PATH . 'templates/user/auth/b2b-user-menu.php';

			include_once WEGW_B2B_PATH . 'includes/wegw-angebote-slider.php';

			/**
			 * Include admin only classes.
			 */
			if ( is_admin() ) {
				wegwb_include( 'includes/admin/class-wegwb-admin.php' );
			}
		}

		/**
		 * define
		 *
		 * Defines a constant if doesnt already exist.
		 */
		function define( $name, $value = true ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}
	}

	function wegw_b2b() {
		global $wegw_b2b;

		/**
		 * Instantiate only once.
		 */
		if ( ! isset( $wegw_b2b ) ) {
			$wegw_b2b = new WEGW_B2B();
			$wegw_b2b->initialize();
		}
		return $wegw_b2b;
	}

	/**
	 * Create custom table in the database to save B2B payment details
	 */
	function wegw_b2b_payment_table_create() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'b2b_payment_details';
		$charset_collate = $wpdb->get_charset_collate();

		/* Check to see if the table exists already, if not, then create it */
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {

			$sql = "CREATE TABLE $table_name (
				`ID` mediumint(9) NOT NULL AUTO_INCREMENT,
				`user_id` int(9) NOT NULL,
				`amount` VARCHAR(50) NOT NULL,
				`purchased_credits` VARCHAR(50) NOT NULL,
				`reference_id` VARCHAR(100) NOT NULL,
				`transaction_id` VARCHAR(200) NULL,
				`payment_method` VARCHAR(100) NULL,
				`payment_status` VARCHAR(100) NULL,
				`payment_details` LONGTEXT NULL,
				`payment_date` VARCHAR(100) NOT NULL,
				PRIMARY KEY  (ID)
			) $charset_collate;";
			dbDelta( $sql );
		}
	}

	/**
	 * Create custom table in the database to save B2B payment details
	 */
	function wegw_b2b_ad_clicks_table_create() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'b2b_ad_clicks';
		$charset_collate = $wpdb->get_charset_collate();

		/* Check to see if the table exists already, if not, then create it */
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {

			$sql = "CREATE TABLE $table_name (
				`ID` mediumint(9) NOT NULL AUTO_INCREMENT,
				`ad_id` int(9) NOT NULL,
				`ip` VARCHAR(100) NULL,
				`click_date` VARCHAR(100) NOT NULL,
				`status` int(5) NOT NULL,
				PRIMARY KEY  (ID)
			) $charset_collate;";
			dbDelta( $sql );
		}
	}

	/* Instantiate. */
	wegw_b2b();
	wegw_b2b_payment_table_create();
	wegw_b2b_ad_clicks_table_create();

endif;
