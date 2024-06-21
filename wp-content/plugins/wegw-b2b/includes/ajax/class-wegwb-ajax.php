<?php

/**
 * All the Ajax functionality of the plugin.
 *
 * @link       https://www.pitsolutions.ch/en/
 * @since      1.0.0
 *
 * @package    WEGW_B2B
 * @subpackage WEGW_B2B/Ajax
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WEGW_B2B_Ajax' ) ) :

	class WEGW_B2B_Ajax {


		/**
		 * Constructor.
		 */
		function __construct() {
			/*
			 * All Ajax actions
			 */
			/* Ads save status `Draft` */
			add_action( 'wp_ajax_nopriv_wegwb_ads_save_draft', array( $this, 'wegwb_ads_save_draft' ) );
			add_action( 'wp_ajax_wegwb_ads_save_draft', array( $this, 'wegwb_ads_save_draft' ) );

			/* Ads save status `Published` */
			add_action( 'wp_ajax_nopriv_wegwb_ads_send_approval', array( $this, 'wegwb_ads_send_approval' ) );
			add_action( 'wp_ajax_wegwb_ads_send_approval', array( $this, 'wegwb_ads_send_approval' ) );

			/* Ads Delete */
			add_action( 'wp_ajax_nopriv_wegwb_ads_delete', array( $this, 'wegwb_ads_delete' ) );
			add_action( 'wp_ajax_wegwb_ads_delete', array( $this, 'wegwb_ads_delete' ) );

			/* Ads clicks calculate */
			add_action( 'wp_ajax_nopriv_wegwb_ads_click_calculate', array( $this, 'wegwb_ads_click_calculate' ) );
			add_action( 'wp_ajax_wegwb_ads_click_calculate', array( $this, 'wegwb_ads_click_calculate' ) );

			/* Ads clicks popup display */
			add_action( 'wp_ajax_nopriv_wegwb_ads_clicks_popup_timeline_display', array( $this, 'wegwb_ads_clicks_popup_timeline_display' ) );
			add_action( 'wp_ajax_wegwb_ads_clicks_popup_timeline_display', array( $this, 'wegwb_ads_clicks_popup_timeline_display' ) );

			/* Sub region filtering based on Parent region - `Create Ad Page` */
			add_action( 'wp_ajax_nopriv_wegw_ads_filter_subregion_from_region', array( $this, 'wegw_ads_filter_subregion_from_region' ) );
			add_action( 'wp_ajax_wegw_ads_filter_subregion_from_region', array( $this, 'wegw_ads_filter_subregion_from_region' ) );

			/* load more angebote */
			add_action( 'wp_ajax_nopriv_wegw_angebote_loadmore', array( $this, 'wegw_angebote_loadmore' ) );
			add_action( 'wp_ajax_wegw_angebote_loadmore', array( $this, 'wegw_angebote_loadmore' ) );
		}

		/**
		 * Save Ad in `Draft` status, all other details are saved in postmeta. And moves on to second section to select no of clicks/credits and activation date.
		 * Also checking if page is in Add/Edit mode
		 */
		function wegwb_ads_save_draft() {
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				die();
			}

			global $wpdb;
			$response                 = array();
			$errors                   = array();
			$kategorie_arr            = array();
			$kategorie_id             = array();
			$wanderregionen_arr       = array();
			$user_ID                  = get_current_user_id();
			$upload_dir               = wp_upload_dir();
			$b2b_ads_image_upload_dir = $upload_dir['basedir'] . '/b2b';
			$b2b_ads_image_upload_url = $upload_dir['baseurl'] . '/b2b';
			$edit_mode                = ( isset( $_POST['b2b_ad_edit_mode'] ) && $_POST['b2b_ad_edit_mode'] != '' ) ? $_POST['b2b_ad_edit_mode'] : 0;

			if ( ! file_exists( $b2b_ads_image_upload_dir ) ) {
				wp_mkdir_p( $b2b_ads_image_upload_dir );
			}

			if ( ! session_id() ) {
				session_start();
			}

			if ( isset( $_FILES['file'] ) ) {
				$file_name         = $_FILES['file']['name'];
				$file_name         = str_replace( ' ', '-', $file_name );
				$file_size         = $_FILES['file']['size'];
				$file_tmp          = $_FILES['file']['tmp_name'];
				$file_type         = $_FILES['file']['type'];
				$file_ext          = strtolower( end( explode( '.', $_FILES['file']['name'] ) ) );
				$extensions        = array( 'jpeg', 'jpg', 'png' );
				$time              = strtotime( 'now' );
				$updated_file_name = $time . '_' . $file_name;

				if ( in_array( $file_ext, $extensions ) === false ) {
					$errors[] = 'extension not allowed, please choose a JPG, JPEG or PNG file.';
				}

				if ( $file_size > 2097152 ) {
					$errors[] = 'File size must be excately 2 MB';
				}
			}

			/* Get `kategorie` term slug */
			$b2b_ad_kategorie = $_POST['b2b_ad_kategorie'];
			if ( $b2b_ad_kategorie != '' ) {
				$kategorie_arr = explode( ', ', $b2b_ad_kategorie );

				if ( ! empty( $kategorie_arr ) ) {
					foreach ( $kategorie_arr as $k ) {
						$kategorie_termObj = get_term_by( 'id', (int) $k, 'kategorie' );
						$kategorie_id[]    = $kategorie_termObj->term_id;
					}
				}
			}

			$b2b_ad_land = isset( $_POST['b2b_ad_land'] ) ? sanitize_text_field( $_POST['b2b_ad_land'] ) : '';
			if ( $b2b_ad_land == 'schweiz' ) {
				$b2b_ad_region = isset( $_POST['b2b_ad_region'] ) ? sanitize_text_field( $_POST['b2b_ad_region'] ) : '';
				if ( $b2b_ad_region != '' ) {
					$wanderregionen_termObj = get_term_by( 'id', $b2b_ad_region, 'wanderregionen' );
					array_push( $wanderregionen_arr, $wanderregionen_termObj->term_id );
				}

				$b2b_ad_subregion = isset( $_POST['b2b_ad_subregion'] ) ? sanitize_text_field( $_POST['b2b_ad_subregion'] ) : '';
				if ( $b2b_ad_subregion != '' ) {
					$wanderregionen_subregion_termObj = get_term_by( 'id', $b2b_ad_subregion, 'wanderregionen' );
					array_push( $wanderregionen_arr, $wanderregionen_subregion_termObj->term_id );
				}
			} else {
				// $kategorie_id       = array();
				$wanderregionen_arr = array();
				$b2b_ad_region      = '';
				$b2b_ad_subregion   = '';
			}

			$b2b_ad_title = isset( $_POST['b2b_ad_title'] ) ? sanitize_text_field( $_POST['b2b_ad_title'] ) : '';
			// $b2b_ad_main_title  = isset( $_POST['b2b_ad_main_title'] ) ? sanitize_text_field( $_POST['b2b_ad_main_title'] ) : '';
			$b2b_ad_description = isset( $_POST['b2b_ad_description'] ) ? sanitize_textarea_field( $_POST['b2b_ad_description'] ) : '';
			$b2b_ad_bold_text   = isset( $_POST['b2b_ad_bold_text'] ) ? sanitize_text_field( $_POST['b2b_ad_bold_text'] ) : '';
			$b2b_ad_link        = isset( $_POST['b2b_ad_link'] ) ? sanitize_text_field( $_POST['b2b_ad_link'] ) : '';

			/* Check Ad title duplicates exists in add create page */
			if ( $b2b_ad_title != '' ) {
				$whereQuery_adID = '';

				if ( $edit_mode == 0 && ( isset( $_SESSION['b2b_ads_ID'] ) && $_SESSION['b2b_ads_ID'] != '' ) ) {
					$whereQuery_adID = " AND ID != '" . $_SESSION['b2b_ads_ID'] . "'";
				}

				if ( $edit_mode == 1 && ( isset( $_POST['b2b_ad_id'] ) && $_POST['b2b_ad_id'] != '' ) ) {
					$whereQuery_adID = " AND ID != '" . $_POST['b2b_ad_id'] . "'";
				}

				$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s AND post_title = '" . $b2b_ad_title . "'" . $whereQuery_adID, 'b2b-werbung' ), ARRAY_A );

				if ( $results ) {
					$response['ad_title_duplicate_error'] = __( 'Der Anzeigentitel ist bereits vorhanden.', 'wegw-b2b' );
					echo json_encode( $response );
					die();
				}
			}

			if ( empty( $errors ) == true ) {

				/* Check if edit page or not */
				if ( $edit_mode == 0 ) {

					/*
					 * Check if ad is edited in the `Create Ad` page itself(by clicking edit icon in the preview div) without publishing
					 */
					if ( isset( $_SESSION['b2b_ads_ID'] ) && $_SESSION['b2b_ads_ID'] != '' ) {

						$ad_ID_update = isset( $_SESSION['b2b_ads_ID'] ) ? sanitize_text_field( $_SESSION['b2b_ads_ID'] ) : '';

						/* Create ad post object */
						$b2b_ad_update_args = array(
							'ID'           => $ad_ID_update,
							'post_title'   => wp_strip_all_tags( $b2b_ad_title ),
							'post_content' => $b2b_ad_description,
							'post_status'  => 'draft',
						// 'tax_input'    => array(
						// 'kategorie'      => $kategorie_arr,
						// 'wanderregionen' => $wanderregionen_arr,
						// ),
						);

						/* Insert the post into the database */
						if ( wp_update_post( $b2b_ad_update_args ) ) {
							ob_clean();

							if ( isset( $_FILES['file'] ) ) {
								$uploadfile = $b2b_ads_image_upload_dir . '/' . $updated_file_name;
								move_uploaded_file( $file_tmp, $uploadfile );
								update_post_meta( $ad_ID_update, 'wegw_b2b_ad_image', $b2b_ads_image_upload_url . '/' . $updated_file_name );

								$filename    = basename( $uploadfile );
								$wp_filetype = wp_check_filetype( basename( $filename ), null );
								$attachment  = array(
									'post_mime_type' => $wp_filetype['type'],
									'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
									'post_content'   => '',
									'post_status'    => 'inherit',
									'menu_order'     => $_i + 1000,
								);

								$attach_id = wp_insert_attachment( $attachment, $uploadfile );
								set_post_thumbnail( $ad_ID_update, $attach_id );
							}

							wp_set_object_terms( (int) $ad_ID_update, $kategorie_id, 'kategorie' );
							wp_set_object_terms( (int) $ad_ID_update, $wanderregionen_arr, 'wanderregionen' );
							update_post_meta( $ad_ID_update, 'wegw_b2b_ad_kategorie', $b2b_ad_kategorie );
							update_post_meta( $ad_ID_update, 'wegw_b2b_ad_land', $b2b_ad_land );
							update_post_meta( $ad_ID_update, 'wegw_b2b_ad_region', $b2b_ad_region );
							update_post_meta( $ad_ID_update, 'wegw_b2b_ad_subregion', $b2b_ad_subregion );
							// update_post_meta( $ad_ID_update, 'wegw_b2b_ad_main_title', $b2b_ad_main_title );
							update_post_meta( $ad_ID_update, 'wegw_b2b_ad_bold_text', $b2b_ad_bold_text );
							update_post_meta( $ad_ID_update, 'wegw_b2b_ad_link', $b2b_ad_link );
						}
					} else {

						/* Create ad post object */
						$b2b_ad = array(
							'post_type'    => 'b2b-werbung',
							'post_title'   => wp_strip_all_tags( $b2b_ad_title ),
							'post_content' => $b2b_ad_description,
							'post_status'  => 'draft',
							'post_author'  => $user_ID,
						// 'tax_input'    => array(
						// 'kategorie'      => $kategorie_arr,
						// 'wanderregionen' => $wanderregionen_arr,
						// ),
						);

						/* Insert the post into the database */
						$b2b_ad_ID = wp_insert_post( $b2b_ad );

						if ( $b2b_ad_ID ) {
							$_SESSION['b2b_ads_ID'] = $b2b_ad_ID;

							if ( isset( $_FILES['file'] ) ) {
								$uploadfile = $b2b_ads_image_upload_dir . '/' . $updated_file_name;
								move_uploaded_file( $file_tmp, $uploadfile );
								add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_image', $b2b_ads_image_upload_url . '/' . $updated_file_name );

								$filename    = basename( $uploadfile );
								$wp_filetype = wp_check_filetype( basename( $filename ), null );
								$attachment  = array(
									'post_mime_type' => $wp_filetype['type'],
									'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
									'post_content'   => '',
									'post_status'    => 'inherit',
									'menu_order'     => $_i + 1000,
								);

								$attach_id = wp_insert_attachment( $attachment, $uploadfile );
								set_post_thumbnail( $b2b_ad_ID, $attach_id );
							}

							wp_set_object_terms( (int) $b2b_ad_ID, $kategorie_id, 'kategorie' );
							wp_set_object_terms( (int) $b2b_ad_ID, $wanderregionen_arr, 'wanderregionen' );
							add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_kategorie', $b2b_ad_kategorie );
							add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_land', $b2b_ad_land );
							add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_region', $b2b_ad_region );
							add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_subregion', $b2b_ad_subregion );
							// add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_image', $b2b_ads_image_upload_url . '/' . $updated_file_name );
							// add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_main_title', $b2b_ad_main_title );
							add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_bold_text', $b2b_ad_bold_text );
							add_post_meta( $b2b_ad_ID, 'wegw_b2b_ad_link', $b2b_ad_link );
						}
					}

					die();
				} else {
					/* Ad Edit Mode = on */
					$ad_ID_update = isset( $_POST['b2b_ad_id'] ) ? sanitize_text_field( $_POST['b2b_ad_id'] ) : '';

					/* Create ad post object */
					$b2b_ad_update_args = array(
						'ID'           => $ad_ID_update,
						'post_title'   => wp_strip_all_tags( $b2b_ad_title ),
						'post_content' => $b2b_ad_description,
						'post_status'  => 'draft',
					);

					/* Insert the post into the database */
					if ( wp_update_post( $b2b_ad_update_args ) ) {
						ob_clean();

						if ( isset( $_FILES['file'] ) ) {
							$uploadfile = $b2b_ads_image_upload_dir . '/' . $updated_file_name;
							move_uploaded_file( $file_tmp, $uploadfile );
							update_post_meta( $ad_ID_update, 'wegw_b2b_ad_image', $b2b_ads_image_upload_url . '/' . $updated_file_name );

							$filename    = basename( $uploadfile );
							$wp_filetype = wp_check_filetype( basename( $filename ), null );
							$attachment  = array(
								'post_mime_type' => $wp_filetype['type'],
								'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
								'post_content'   => '',
								'post_status'    => 'inherit',
								'menu_order'     => $_i + 1000,
							);

							$attach_id = wp_insert_attachment( $attachment, $uploadfile );
							set_post_thumbnail( $ad_ID_update, $attach_id );
						}

						wp_set_object_terms( (int) $ad_ID_update, $kategorie_id, 'kategorie' );
						wp_set_object_terms( (int) $ad_ID_update, $wanderregionen_arr, 'wanderregionen' );
						update_post_meta( $ad_ID_update, 'wegw_b2b_ad_kategorie', $b2b_ad_kategorie );
						update_post_meta( $ad_ID_update, 'wegw_b2b_ad_land', $b2b_ad_land );
						update_post_meta( $ad_ID_update, 'wegw_b2b_ad_region', $b2b_ad_region );
						update_post_meta( $ad_ID_update, 'wegw_b2b_ad_subregion', $b2b_ad_subregion );
						// update_post_meta( $ad_ID_update, 'wegw_b2b_ad_main_title', $b2b_ad_main_title );
						update_post_meta( $ad_ID_update, 'wegw_b2b_ad_bold_text', $b2b_ad_bold_text );
						update_post_meta( $ad_ID_update, 'wegw_b2b_ad_link', $b2b_ad_link );
					}
				}
			} else {
				die();
			}

			die();
		}

		/**
		 * Save Ad in `Pending` status.
		 */
		function wegwb_ads_send_approval() {
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				die();
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'b2b_ad_clicks';
			$response   = array();

			$edit_mode = isset( $_POST['b2b_ad_edit_mode'] ) ? sanitize_text_field( $_POST['b2b_ad_edit_mode'] ) : 0;
			if ( $edit_mode == 0 ) {
				if ( ! session_id() ) {
					session_start();
				}
				$ad_ID        = $_SESSION['b2b_ads_ID'];
				$resp_message = __( 'Ad created successfully!', 'wegw-b2b' );
			} else {
				$ad_ID        = isset( $_POST['b2b_ad_id'] ) ? sanitize_text_field( $_POST['b2b_ad_id'] ) : '';
				$resp_message = __( 'Ad updated successfully!', 'wegw-b2b' );
			}

			$user_ID               = get_current_user_id();
			$b2b_available_credits = wegwb_b2b_user_ads_credits_balance();
			$wegw_admin_email      = get_option( 'admin_email' );

			$b2b_credits_count = isset( $_POST['b2b_credits_count'] ) ? sanitize_text_field( $_POST['b2b_credits_count'] ) : 0;
			if ( $b2b_credits_count == 'custom' ) {
				$b2b_credits_count = isset( $_POST['b2b_credits_count_custom'] ) ? abs( sanitize_text_field( $_POST['b2b_credits_count_custom'] ) ) : 0;
			}

			$b2b_ad_activate_date = '';
			if ( isset( $_POST['b2b_ad_activate_date'] ) && $_POST['b2b_ad_activate_date'] != '' ) {
				$b2b_ad_activate_date = str_replace( '/', '-', $_POST['b2b_ad_activate_date'] );
			}

			$b2b_ad_end_date = '';
			if ( isset( $_POST['b2b_ad_end_date'] ) && $_POST['b2b_ad_end_date'] != '' ) {
				$b2b_ad_end_date = str_replace( '/', '-', $_POST['b2b_ad_end_date'] );
			}

			if ( isset( $ad_ID ) && $ad_ID != '' ) {

				if ( $edit_mode == 0 ) {
					$b2b_updated_credits_count = $b2b_credits_count;
				} else {
					$b2b_credits_count_old     = metadata_exists( 'post', $ad_ID, 'wegw_b2b_credits_count' ) ? get_post_meta( $ad_ID, 'wegw_b2b_credits_count', true ) : 0;
					$b2b_updated_credits_count = $b2b_credits_count_old + $b2b_credits_count;
				}

				/*
				 * Check Balance credits available to create/edit Ad
				 */
				if ( $b2b_available_credits >= $b2b_credits_count ) {
					/* Update B2B user balance credits */
					$b2b_balance_credits = $b2b_available_credits - $b2b_credits_count;

					$b2b_ad_postdate_stamp = strtotime( $b2b_ad_activate_date );
					$b2b_ad_postdate       = date( 'Y-m-d H:i:s', $b2b_ad_postdate_stamp );
					$b2b_ad_postdate_gmt   = get_gmt_from_date( $b2b_ad_postdate );

					/* Update post_status to Pending and post_date to a future date */
					$b2b_ads_args = array(
						'ID'            => $ad_ID,
						'post_date'     => $b2b_ad_postdate,
						'post_date_gmt' => $b2b_ad_postdate_gmt,
						'post_status'   => 'pending',
						'edit_date'     => true,
					);

					$b2b_update_post = wp_update_post( $b2b_ads_args );

					/* Check if post status updated to Pending */
					if ( $b2b_update_post ) {
						ob_clean();

						if ( $b2b_ad_end_date != '' ) {
							$b2b_ad_end_date_stamp    = strtotime( $b2b_ad_end_date );
							$b2b_ad_end_date_formated = date( 'Y-m-d 00:00:00', $b2b_ad_end_date_stamp );
						} else {
							$b2b_ad_end_date_formated = '';
						}

						/* Send mail to admin - Approval */
						wp_mail(
							$wegw_admin_email,
							'Anzeigengenehmigung',
							__( 'Neue Anzeige zur Genehmigung eingetroffen.', 'wegw-b2b' )
						);

						if ( $edit_mode == 0 ) {
							/* Add booked credits to the ad */
							add_post_meta( $ad_ID, 'wegw_b2b_credits_booked', $b2b_credits_count );
							add_post_meta( $ad_ID, 'wegw_b2b_credits_count', $b2b_credits_count );
							add_post_meta( $ad_ID, 'wegw_b2b_ad_end_date', $b2b_ad_end_date_formated );
						} else {
							/* Update booked credits to the ad total credits */
							if ( metadata_exists( 'post', $ad_ID, 'wegw_b2b_credits_booked' ) ) {
								$b2b_ads_total_credits_booked = get_post_meta( $ad_ID, 'wegw_b2b_credits_booked', true );
								$b2b_ads_credits_booked       = $b2b_ads_total_credits_booked + $b2b_credits_count;
								update_post_meta( $ad_ID, 'wegw_b2b_credits_booked', $b2b_ads_credits_booked );
							} else {
								add_post_meta( $ad_ID, 'wegw_b2b_credits_booked', $b2b_credits_count );
							}

							/* Update booked credits to the ad balance credits */
							if ( metadata_exists( 'post', $ad_ID, 'wegw_b2b_credits_count' ) ) {
								update_post_meta( $ad_ID, 'wegw_b2b_credits_count', $b2b_updated_credits_count );
							} else {
								add_post_meta( $ad_ID, 'wegw_b2b_credits_count', $b2b_updated_credits_count );
							}

							/* Update ad end date */
							if ( metadata_exists( 'post', $ad_ID, 'wegw_b2b_ad_end_date' ) ) {
								update_post_meta( $ad_ID, 'wegw_b2b_ad_end_date', $b2b_ad_end_date_formated );
							} else {
								add_post_meta( $ad_ID, 'wegw_b2b_ad_end_date', $b2b_ad_end_date_formated );
							}

							/* Remove ad end date post_meta field - wegw_b2b_ad_credits_end_date */
							if ( metadata_exists( 'post', $ad_ID, 'wegw_b2b_ad_credits_end_date' ) ) {
								delete_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end_date' );
							}

							/* Remove ad ending post_meta field - wegw_b2b_ad_credits_end */
							if ( metadata_exists( 'post', $ad_ID, 'wegw_b2b_ad_credits_end' ) ) {
								delete_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end' );

								/*
								 * Restart ad clicks/credits. Update old clicks status = 0
								 */
								$wpdb->update(
									$table_name,
									array(
										'status' => 0,
									),
									array(
										'ad_id' => $ad_ID,
									)
								);
							}

							/* Remove ad clicks till date post_meta field - wegw_b2b_ad_clicks */
							if ( metadata_exists( 'post', $ad_ID, 'wegw_b2b_ad_clicks' ) ) {
								delete_post_meta( $ad_ID, 'wegw_b2b_ad_clicks' );
							}
						}

						update_user_meta( $user_ID, 'wegw_b2b_ads_credits_balance', $b2b_balance_credits );

						$response['msg']          = $resp_message;
						$response['redirect_url'] = site_url( WEGW_B2B_AD_LISTING );

						/*
						 Unsetting session variable - $_SESSION['b2b_ads_ID']
						// unset( $_SESSION['b2b_ads_ID'] );
						// die(); */
					} else {
						$response['msg'] = __( 'Ad cannot be created. Please try again.', 'wegw-b2b' );
					}
				} else {
					$response['msg'] = __( 'No required credits. Please purchase credits and try again.', 'wegw-b2b' );
				}
			} else {
				$response['msg'] = __( 'Ads cannot be created. Please contact Administrator.', 'wegw-b2b' );
			}

			echo json_encode( $response );
			die();
		}

		/**
		 * Delete Ad
		 */
		function wegwb_ads_delete() {
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				die();
			}

			if ( ! session_id() ) {
				session_start();
			}

			$response = array();
			$ad_ID    = ( isset( $_SESSION['b2b_ads_ID'] ) && $_SESSION['b2b_ads_ID'] != '' ) ? sanitize_text_field( $_SESSION['b2b_ads_ID'] ) : $_POST['b2b_ad_id'];

			$b2b_existing_credits_count = metadata_exists( 'post', $ad_ID, 'wegw_b2b_credits_count' ) ? get_post_meta( $ad_ID, 'wegw_b2b_credits_count', true ) : 0;

			if ( $b2b_existing_credits_count > 0 ) {
				/* Update balance ad credit to user */
				$author_id                 = get_post_field( 'post_author', $ad_ID );
				$b2b_available_credits     = wegwb_b2b_user_ads_credits_balance();
				$b2b_available_credits_upd = $b2b_available_credits + $b2b_existing_credits_count;
				// update_post_meta( $ad_ID, 'wegw_b2b_credits_count', 0 );
				update_user_meta( (int) $author_id, 'wegw_b2b_ads_credits_balance', $b2b_available_credits_upd );
			}
			$b2b_ad_delete = wp_delete_post( $ad_ID );

			if ( $b2b_ad_delete ) {
				$response['msg']          = __( 'Inserat erfolgreich gelöscht! Guthaben wurde aktualisiert.', 'wegw-b2b' );
				$response['redirect_url'] = site_url( WEGW_B2B_AD_LISTING );
				echo json_encode( $response );
				unset( $_SESSION['b2b_ads_ID'] );
			}

			die();
		}

		/**
		 * On click B2B Ad clicks/credits calculate
		 */
		function wegwb_ads_click_calculate() {
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				die();
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'b2b_ad_clicks';

			$ad_ID = isset( $_POST['b2b_ad_id'] ) ? sanitize_textarea_field( $_POST['b2b_ad_id'] ) : '';
			/* Check Ad Id exists */
			if ( $ad_ID != '' ) {
				$current_ad_status = get_post_status( $ad_ID );

				/* Check the ad is `Published` */
				if ( $current_ad_status == 'publish' ) {
					$b2b_credits_count = metadata_exists( 'post', $ad_ID, 'wegw_b2b_credits_count' ) ? get_post_meta( $ad_ID, 'wegw_b2b_credits_count', true ) : 0;

					if ( $b2b_credits_count <= 1 ) {
						$author_id  = get_post_field( 'post_author', $ad_ID );
						$ad_title   = get_the_title( $ad_ID );
						$user_data  = get_userdata( $author_id );
						$user_email = $user_data->user_email;

						$headers  = array( 'Content-Type: text/html; charset=UTF-8' );
						$message  = 'Ihr Inserat "' . $ad_title . '" auf ' . "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . ' ist abgelaufen. Erneuern Sie jetzt Ihr Inserat oder setzen Sie ein neues auf.' . '<br /><br />';
						$message .= 'Beste Grüsse' . '<br />' . 'Yvonne Zürrer und Claudia Ruf' . '<br />' . 'Ihr ' . "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . ' Team' . '<br /><br />';
						$message .= "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . '<br />' . 'Marchwartstrasse 72' . '<br />' . '8038 Zürich' . '<br /><a href="mailto:info@wegwandern.ch">' . 'info@wegwandern.ch' . '</a><br />' . '+41 43 537 70 58' . '<br /><br />';
						$message .= 'Liebe Grüsse' . '<br />' . 'Yvonne und Claudia';

						wp_mail(
							$user_email,
							'Ihr Inserat auf WegWandern.ch ist abgelaufen',
							__( $message, 'wegw-b2b' ),
							$headers
						);
						// send_email_ad_expiry ( $user_email, $message );
					}

					/* Check if Ad credit count balance is greater than 0 */
					if ( $b2b_credits_count > 0 ) {
						/* Check COOKIE already set. If yes do not enter click count to database */
						if ( ! isset( $_COOKIE[ 'B2B_AD_CLICKED_' . $ad_ID ] ) ) {
							$click_data = array(
								'ad_id'      => $ad_ID,
								'ip'         => wegwb_get_user_ip(),
								'click_date' => date( 'Y-m-d H:i:s' ),
								'status'     => 1,
							);

							$ad_click_insert = $wpdb->insert( $table_name, $click_data );
							if ( $ad_click_insert ) {

								/* Update each clicks count in post_meta - `wegw_b2b_ad_clicks` */
								if ( metadata_exists( 'post', $ad_ID, 'wegw_b2b_ad_clicks' ) ) {
									$ad_clicks = get_post_meta( $ad_ID, 'wegw_b2b_ad_clicks', true );
									$ad_clicks = $ad_clicks + 1;
									update_post_meta( $ad_ID, 'wegw_b2b_ad_clicks', $ad_clicks );
								} else {
									add_post_meta( $ad_ID, 'wegw_b2b_ad_clicks', 1 );
								}

								$b2b_ad_credits_balance = $b2b_credits_count - 1;
								update_post_meta( $ad_ID, 'wegw_b2b_credits_count', $b2b_ad_credits_balance );

								/*
								* Check if `Ad credit balance` == 0
								* If 0, end ad display and make it in draft status
								* Also add new post_meta `wegw_b2b_ad_credits_end`
								*/
								if ( $b2b_ad_credits_balance == 0 ) {
									add_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end', 1 );
									add_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end_date', date( 'Y-m-d H:i:s' ) );

									$b2b_ads_args = array(
										'ID'          => $ad_ID,
										'post_status' => 'draft',
									);

									$b2b_update_post = wp_update_post( $b2b_ads_args );
								}
							}
						}
					} else {
						echo 'No required credit balance.';
					}
				}
			} else {
				echo 'Click cannot be calculated. Ad ID not passed.';
			}
			die();
		}

		/**
		 * On click open B2B Ad clicks popup to display Ad timeline clicks
		 */
		function wegwb_ads_clicks_popup_timeline_display() {
			/* Check if user logged in */
			if ( is_user_logged_in() ) {

				if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
					die();
				}

				$ad_ID = isset( $_POST['b2b_ad_id'] ) ? sanitize_textarea_field( $_POST['b2b_ad_id'] ) : '';
				if ( $ad_ID != '' ) {
					global $wpdb;
					$table_name       = $wpdb->prefix . 'b2b_ad_clicks';
					$get_clicks_count = $wpdb->get_results( "SELECT * FROM `{$table_name}` WHERE `ad_id` = '{$ad_ID}' AND `status` = 1", ARRAY_A );
					if ( $get_clicks_count ) {
						$clicks = array();

						foreach ( $get_clicks_count as $c => $val ) {
							$click_date = date( 'd.m.Y', strtotime( $val['click_date'] ) );

							if ( ! in_array( $click_date, array_column( $clicks, 'date' ) ) ) {
								$clicks[ $c ]['date']  = $click_date;
								$clicks[ $c ]['count'] = wegwb_b2b_ads_clicks_count( $ad_ID, $click_date );
							}
						}
						$total_cnt    = count( $clicks );
						$clicks_date  = array_column( $clicks, 'date' );
						$clicks_count = array_column( $clicks, 'count' );
						// print_r( $clicks_date );
						// print_r( $clicks_count );
						$html          = '';
						$empty_col_cnt = $total_cnt;
						if ( $total_cnt > 10 ) {
							$empty_col_cnt = 10;
						}
						for ( $z = 0; $z <= $total_cnt; $z = $z + 10 ) {

							for ( $i = $z; $i < ( $z + 10 ); $i++ ) {
								if ( $i == $z ) {
									$html .= '<tr><td>' . esc_html__( 'Datum', 'wegw-b2b' ) . '</td>';
								}
								if ( $clicks_date[ $i ] != '' ) {
									$html .= '<td>' . $clicks_date[ $i ] . '</td>';
								}
							}

							for ( $j = $z; $j < ( $z + 10 ); $j++ ) {
								if ( $j == $z ) {
									$html .= '</tr><tr><td>' . esc_html__( 'Clicks', 'wegw-b2b' ) . '</td>';
								}
								if ( $clicks_date[ $j ] != '' ) {
									$html .= '<td>' . $clicks_count[ $j ] . '</td>';
								}
							}
							$html .= '</tr>';
							if ( $z > ( $total_cnt - 10 ) ) {
								break;
							}
							$html .= '<tr class="empty-row">';
							for ( $e = 0; $e <= $empty_col_cnt; $e++ ) {
								$html .= '<td> </td>';
							}
							$html .= '</tr>';
						}

						// $html = '<tr><td>Date</td>';

						// foreach ( $clicks_date as $cd ) {
						// $html .= '<td>' . $cd . '</td>';
						// }

						// $html .= '</tr><tr><td>Count</td>';

						// foreach ( $clicks_count as $cc ) {
						// $html .= '<td>' . $cc . '</td>';
						// }

						// $html .= '</tr>';

						echo $html;
					}
				}
			}
			die();
		}

		/**
		 * Filter subregion based on region - Ad create pation dropdown
		 */
		function wegw_ads_filter_subregion_from_region() {
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				die();
			}

			$sub_region_arr = array();
			$b2b_region_id  = isset( $_POST['b2b_region_id'] ) ? $_POST['b2b_region_id'] : '';

			if ( $b2b_region_id != '' ) {
				$terms = get_terms(
					'wanderregionen',
					array(
						'parent'     => $b2b_region_id,
						'orderby'    => 'slug',
						'hide_empty' => false,
					)
				);

				if ( ! empty( $terms ) ) {
					foreach ( $terms as $s ) {
						$sub_region_arr[] = '<option value="' . $s->term_id . '">' . $s->name . '</option>';
					}
				}
			}

			echo json_encode( $sub_region_arr, JSON_UNESCAPED_UNICODE );
			die();
		}

		/**
		 * Function to load more angebote on click
		 */
		function wegw_angebote_loadmore() {
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
				die();
			}

			$count     = $_POST['count'];
			$page_type = $_POST['page_type'];
			$args      = array(
				'post_type'      => 'b2b-werbung',
				'posts_per_page' => 3,
				'offset'         => $count,
			);
			if ( '' != $page_type ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'kategorie',
						'field'    => 'term_id',
						'terms'    => (int) $page_type,
					),
				);
			}

			$angebote = '';
			// $post_query = new WP_Query( $args );
			$post_query = get_posts( $args );
			if ( ! empty( $post_query ) ) {
				global $post;
				foreach ( $post_query as $post ) {
					setup_postdata( $post );
					$post_id       = get_the_ID();
					$ad_image      = get_post_meta( $post_id, 'wegw_b2b_ad_image', true );
					$ad_bold_text  = get_post_meta( $post_id, 'wegw_b2b_ad_bold_text', true );
					$ad_bold_html  = ( '' != $ad_bold_text ) ? '<b>' . wp_trim_words( $ad_bold_text, 50, '...' ) . '</b>' : '';
					$ad_link       = get_post_meta( $post_id, 'wegw_b2b_ad_link', true );
					$category_html = '';
					$kategories    = get_the_terms( $post_id, 'kategorie' );
					if ( ! empty( $kategories ) ) {
						$kategories_array = array();
						foreach ( $kategories as $each_kat ) {
							$kat_link           = get_category_link( $each_kat->term_id );
							$kategories_array[] = "<a href='$kat_link'>$each_kat->name</a>";
						}
						$category_html .= implode( ' ', $kategories_array );
					}
					$angebote .= '<div class="blog-wander angebote-wander">
							<div class="blog-wander-img">
								<img class="blog-img" src="' . $ad_image . '">
							</div>
							<h6>' . $category_html . '</h6>
							<h2>' . get_the_title( $post_id ) . '</h2>
							<div class="blog-desc">';
					$angebote .= wp_trim_words( get_the_content(), 110, '...' ) . ' ' . $ad_bold_html;
					$angebote .= '</div>';
					$angebote .= '<a href="' . $ad_link . '" target="_blank" onclick="b2b_ad_click_calculate(' . $post_id . ')"><span></span>' . $ad_link . '</a>';
					$angebote .= '</div>';
				}
			} else {
				$angebote .= '<h2 class="noWanderung">' . __( 'Keine Angebote gefunden', 'wegwandern' ) . '</h2>';
			}
			$ar_posts[] = $angebote;
			wp_reset_postdata();
			echo json_encode( $ar_posts );
			die();
		}
	}

	wegwb_new_instance( 'WEGW_B2B_Ajax' );

endif;
