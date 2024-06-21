<?php
/**
 * Handle frontend styles & scripts.
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend scripts class.
 */
class WEGW_B2B_Frontend_Scripts {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wegwb_frontend_scripts' ) );
	}

	public function wegwb_frontend_scripts() {
		/**
		 * Register styles & scripts
		 */
		wp_register_style( 'wegwb-plugin', WEGW_B2B_ASSETS_PATH . 'css/wegwb-style.css', false, _S_VERSION, 'all' );

		// if ( is_page( WEGW_B2B_AD_CREATE ) || is_page( WEGW_B2B_AD_LISTING ) ) {
			wp_register_style( 'wegwb-plugin-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css?ver=1.0.0', false, _S_VERSION, 'all' );
			wp_enqueue_style( 'wegwb-plugin-font-awesome' );
		// }

		if ( is_page( WEGW_B2B_AD_LISTING ) ) {
			wp_register_style( 'wegwb-plugin-datatables', WEGW_B2B_ASSETS_PATH . 'css/jquery-dataTables.min.css', false, _S_VERSION, 'all' );
			wp_enqueue_style( 'wegwb-plugin-datatables' );

			wp_register_script( 'wegwb-plugin-datatables', WEGW_B2B_ASSETS_PATH . 'js/jquery-dataTables.min.js', array( 'jquery' ), _S_VERSION, true );
			wp_enqueue_script( 'wegwb-plugin-datatables' );
		}

		wp_register_script( 'wegwb-plugin', WEGW_B2B_ASSETS_PATH . 'js/wegwb-scripts.js', array( 'jquery' ), _S_VERSION, true );

		wp_localize_script(
			'wegwb-plugin',
			'b2b_params',
			array(
				'email_label'      => __( 'E-Mail-Adresse *', 'wegw-b2b' ),
				'paswd_label'      => __( 'Neues Passwort *', 'wegw-b2b' ),
				'conf_paswd_label' => __( 'Neues Passwort bestätigen *', 'wegw-b2b' ),
				'b2b_edit_prof_conf_paswd_label' => __( 'Neues Passwort bestätigen', 'wegw-b2b' ),
				'mail_pasd_blank'  => __( 'E-Mail und Passwort dürfen nicht leer sein', 'wegw-b2b' ),
				'min_length'       => __( 'Das Passwort muss mindestens 12 Zeichen lang sein', 'wegw-b2b' ),
				'min_number'       => __( 'Das Passwort muss mindestens eine Zahl enthalten', 'wegw-b2b' ),
				'uppercase'        => __( 'Das Passwort muss mindestens einen Großbuchstaben enthalten', 'wegw-b2b' ),
				'lowercase'        => __( 'Das Passwort muss mindestens einen Kleinbuchstaben enthalten', 'wegw-b2b' ),
				'special_char'     => __( 'Das Passwort muss mindestens ein Sonderzeichen enthalten', 'wegw-b2b' ),

			)
		);
		
		/**
		 * Enqueuing styles & scripts
		 */
		wp_enqueue_style( 'wegwb-plugin' );

		wp_enqueue_script( 'wegwb-plugin' );

		wp_enqueue_script( 'wegwb-plugin-ajax', WEGW_B2B_ASSETS_PATH . 'js/wegwb-ajax-scripts.js', array(), _S_VERSION, false );
		wp_localize_script(
			'wegwb-plugin-ajax',
			'ajax_object',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'ajax-nonce' ),
			)
		);
	}
}

wegwb_new_instance( 'WEGW_B2B_Frontend_Scripts' );
