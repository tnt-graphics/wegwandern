<?php
/**
 * Cron Settings
 *
 * Used for the cron settings page in WEGW B2B plugin
 *
 * @package WEGW_B2B/API
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WEGW_B2B_API_Cron_Settings {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_wegwb_check_ads_end_date_cron_job', array( $this, 'wegwb_check_ads_end_date_cron_job' ) );
		add_action( 'wp_wegwb_delete_ads_not_renewed_cron_job', array( $this, 'wegwb_delete_ads_not_renewed_cron_job' ) );
		add_action( 'wp_wegwb_ad_expiry_notification_cron_job', array( $this, 'wegwb_ad_expiry_notification_cron_job' ) );
		add_action( 'wp_wegwb_account_deactivate_cron_job', array( $this, 'wegwb_account_deactivate_cron_job' ) );

	}

	/**
	 * Cron to check if any published ads have reached the `end_date` (date manually set by the user at the time of ad creation)
	 * If end date reached update ad status to `Draft` and add 2 post_meta fields
	 * `wegw_b2b_ad_credits_end` - denote ended ad (boolean)
	 * `wegw_b2b_ad_credits_end_date` - date which the ad is ended (date)
	 */
	public static function wegwb_check_ads_end_date() {
		$args = array(
			'post_type'      => 'b2b-werbung',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		$result = new WP_Query( $args );

		if ( $result->have_posts() ) :
			while ( $result->have_posts() ) :
				$result->the_post();

				$ad_ID       = get_the_ID();
				$ad_end_date = get_post_meta( $ad_ID, 'wegw_b2b_ad_end_date', true );

				/* Check if the `wegw_b2b_ad_end_date` has a value. */
				if ( ! empty( $ad_end_date ) ) {
					$ad_end_date_formatted = date( 'Y-m-d', strtotime( $ad_end_date ) );
					$current_date   = date( 'Y-m-d' );

					if ( $current_date >= $ad_end_date_formatted ) {
						add_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end', 1 );
						add_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end_date', date( 'Y-m-d H:i:s' ) );

						$b2b_ads_args = array(
							'ID'          => $ad_ID,
							'post_status' => 'draft',
						);

						wp_update_post( $b2b_ads_args );
					}
				}

			endwhile;
		endif;

		wp_reset_postdata();
		wp_die();
	}

	/**
	 * Call self function: wegwb_check_ads_end_date
	 */
	public function wegwb_check_ads_end_date_cron_job() {
		self::wegwb_check_ads_end_date();
	}

	/**
	 * Cron to check if any `Draft` ads have not updated in 3 months
	 * If not delete the ad and its post_meta fields
	 * `wegw_b2b_ad_credits_end` - denote ended ad (boolean)
	 * `wegw_b2b_ad_credits_end_date` - date which the ad is ended (date)
	 */
	public static function wegwb_delete_ads_not_renewed() {
		$args = array(
			'post_type'      => 'b2b-werbung',
			'post_status'    => 'draft',
			'posts_per_page' => -1,
		);

		$result = new WP_Query( $args );

		if ( $result->have_posts() ) :
			while ( $result->have_posts() ) :
				$result->the_post();

				$ad_ID         = get_the_ID();
				$ad_ended      = get_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end', true );
				$ad_ended_date = get_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end_date', true );

				/* Check if the `wegw_b2b_ad_end_date` has a value. */
				if ( ! empty( $ad_ended_date ) ) {

					if ( $ad_ended == 1 ) {
						/* Check if ad is ended before 90 days */
						$ad_delete_date = date( 'Y-m-d', strtotime( '+90 days', strtotime( $ad_ended_date ) ) );
						$current_date   = date( 'Y-m-d' );

						/*
						* Check if ad is ended before 90 days. If yes delete the ad and post_meta fields
						*/
						if ( $current_date > $ad_delete_date ) {
							$b2b_existing_credits_count = metadata_exists( 'post', $ad_ID, 'wegw_b2b_credits_count' ) ? get_post_meta( $ad_ID, 'wegw_b2b_credits_count', true ) : 0;

							if ( $b2b_existing_credits_count > 0 ) {
								/* Update balance ad credit to user */
								$author_id                 = get_post_field( 'post_author', $ad_ID );
								$b2b_available_credits     = wegwb_b2b_user_ads_credits_balance( $author_id );
								$b2b_available_credits_upd = $b2b_available_credits + $b2b_existing_credits_count;
								update_user_meta( (int) $author_id, 'wegw_b2b_ads_credits_balance', $b2b_available_credits_upd );
							}

							wp_delete_post( $ad_ID );
						}
					}
				}

			endwhile;
		endif;

		wp_reset_postdata();
		wp_die();
	}

	/**
	 * Call self function: wegwb_check_ads_not_renewed
	 */
	public function wegwb_delete_ads_not_renewed_cron_job() {
		self::wegwb_delete_ads_not_renewed();
	}

	public static function wegwb_ad_expiry_notification() {
		$todate     = date( 'Y-m-d 00:00:00' );
		$after_date = date( 'Y-m-d 00:00:00', strtotime( $todate . ' + 2 days' ) );
		$args       = array(
			'post_type'      => 'b2b-werbung',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish' ), // , 'pending', 'draft', 'future'
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'wegw_b2b_ad_end_date',
					'value'   => array( $todate, $after_date ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',

				),
				array(
					'key'     => 'wegw_b2b_credits_count',
					'value'   => 5,
					'compare' => '<=',
					'type'    => 'numeric',

				),
			),

		);

		$user_arr    = array();
		$user_mail   = array();
		$user_credit = array();
		global $wpdb;
		$b2b_ads = get_posts( $args );
		if ( ! empty( $b2b_ads ) ) {
			foreach ( $b2b_ads as $ads ) {
				$user_arr[] =
				array(
					'author'   => $ads->post_author,
					'ad_title' => $ads->post_title,
				);

			}
		}//print_r($user_arr);exit;
		foreach ( $user_arr as $user ) {
			// $balance = get_user_meta( $user, 'wegw_b2b_ads_credits_balance', true );
			// if ( $balance > 5 ) {
				$user_data  = get_userdata( $user['author'] );
				$ad_title   = $user['ad_title'];
				$user_email = $user_data->user_email;
				$message    = 'Your ad ' . $ad_title . 'is going to expire';
				send_email_ad_expiry( $user_email, $message );
			// }
		}
	}



	/**
	 * Call self function: wegwb_ad_expirt_notification
	 */
	public function wegwb_ad_expiry_notification_cron_job() {
		self::wegwb_ad_expiry_notification();
	}

	public static function wegwb_account_deactivate() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'users';
		$get_users  = $wpdb->get_results( "SELECT * FROM `{$table_name}` WHERE `user_registered` < DATE_SUB(NOW(), INTERVAL 24 HOUR) AND `user_activation_key` != ''" );
		if ( ! empty( $get_users ) ) {

			foreach ( $get_users as $usr ) {
				$user_id = $usr->ID;
				$user_id = wp_update_user(
					array(
						'ID'                  => $user_id,
						'user_activation_key' => '',
					)
				);
				wp_delete_user( $user_id );
				if ( is_wp_error( $user_id ) ) {
					echo 'error';
				} else {
					echo 'user deactivated';
				}
			}
		}
	}
	/**
	 * Call self function: wegwb_ad_expirt_notification
	 */
	public function wegwb_account_deactivate_cron_job() {
		self::wegwb_account_deactivate();
	}
}

wegwb_new_instance( 'WEGW_B2B_API_Cron_Settings' );
