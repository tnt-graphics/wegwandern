<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.pitsolutions.ch/en/
 * @since      1.0.0
 *
 * @package    WEGW_B2B
 * @subpackage WEGW_B2B/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WEGW_B2B_Admin' ) ) :

	class WEGW_B2B_Admin {
		/**
		 * Constructor.
		 */
		function __construct() {
			/*
			 Add Actions */
			// add_action( 'admin_menu', array( $this, 'admin_menu' ) )
			add_action( 'admin_register_scripts', array( $this, 'admin_register_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'acf/init', array( $this, 'admin_wegwb_ads_credits_purchase_settings' ) );

			/* API Actions  */
			add_action( 'admin_footer-post.php', array( $this, 'wegb_append_post_status_list' ) );
			add_action( 'admin_footer-edit.php', array( $this, 'wegb_append_post_status' ) );
		}

		/**
		 * Adds the OTG Careers menu item.
		 */
		function admin_menu() {
		}

		/**
		 * Registering styles/scripts for admin side
		 */
		function admin_register_scripts() {
			/* Plugin stylesheet */
		}

		/**
		 * Enqueuing styles/scripts for admin side
		 */
		function admin_enqueue_scripts() {
			/* Plugin stylesheet */
		}

		/**
		 * Create ACF `acf_add_options_sub_page` for B2B Ads credits price settings page
		 */
		function admin_wegwb_ads_credits_purchase_settings() {

			/* Check if function exists ACF */
			if ( function_exists( 'acf_add_options_sub_page' ) ) {
				acf_add_options_sub_page(
					array(
						'page_title'  => 'B2B Einstellungen',
						'menu_title'  => 'B2B Einstellungen',
						'menu_slug'   => 'b2b-settings',
						'parent_slug' => 'edit.php?post_type=b2b-werbung',
						'capability'  => 'edit_posts',
						'redirect'    => false,
					)
				);
			}
		}

		function wegb_append_post_status_list() {
			global $post;

			if ( $post->post_type != 'b2b-werbung' ) {
				return;
			}

			$selected  = 'false';
			$setStatus = '';
			if ( $post->post_status == 'rejected' ) {
				$selected  = 'true';
				$setStatus = 'document.getElementById("post-status-display").innerHTML = "Rejected";';

				echo '<script>
					document.getElementById("post_status").appendChild(new Option("Rejected", "rejected", ' . $selected . '));
					' . $setStatus . '
				</script>';
			}
		}

		function wegb_append_post_status() {
			global $post;
			if ( $post->post_type != 'b2b-werbung' ) {
				return;
			}

			echo '<script>
				document.querySelectorAll("select[name=\"_status\"]").forEach((s) => {
					s.appendChild(new Option("Rejected", "rejected"));
				});
			</script>';
		}
	}

	wegwb_new_instance( 'WEGW_B2B_Admin' );

endif;
