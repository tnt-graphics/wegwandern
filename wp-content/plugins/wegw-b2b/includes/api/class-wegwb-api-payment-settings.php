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

class WEGW_B2B_API_Payment_Settings {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_post_get_payment_details', array( $this, 'wegwb_payment_integration_webhook' ), 10 );
		add_action( 'admin_post_nopriv_get_payment_details', array( $this, 'wegwb_payment_integration_webhook' ), 10 );
	}

	/**
	 * Cron to check if any published ads have reached the `end_date` (date manually set by the user at the time of ad creation)
	 * If end date reached update ad status to `Draft` and add 2 post_meta fields
	 * `wegw_b2b_ad_credits_end` - denote ended ad (boolean)
	 * `wegw_b2b_ad_credits_end_date` - date which the ad is ended (date)
	 */
	public function wegwb_payment_integration_webhook() {
		$path = preg_replace( '/wp-content(?!.*wp-content).*/', '', __DIR__ );
		require_once $path . 'wp-load.php';

		if ( isset( $_GET['token'] ) && $_GET['token'] == 'F2QT4fwpMeJf36POk6yJV_adQs' ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'b2b_payment_details';

			$request = file_get_contents( 'php://input' );
			$data    = json_decode( $request, true );

			if ( ! empty( $data ) ) {
				$user_id           = $data['transaction']['invoice']['custom_fields'][1]['value'];
				$amount            = $data['transaction']['amount'];
				$purchased_credits = $data['transaction']['invoice']['custom_fields'][0]['value'];
				$reference_id      = $data['transaction']['referenceId'];
				$transaction_id    = $data['transaction']['id'];
				$payment_method    = $data['transaction']['payment'];
				$payment_status    = $data['transaction']['status'];

				/* Check duplicate transaction */
				$transaction_id_exists = $wpdb->get_var( "SELECT `transaction_id` FROM $table_name WHERE transaction_id = '" . $transaction_id . "'" );

				if ( $transaction_id_exists ) {
					echo json_encode( 'Duplicate entry' );
				} else {
					$data = array(
						'user_id'           => $user_id,
						'amount'            => $amount,
						'purchased_credits' => $purchased_credits,
						'reference_id'      => $reference_id,
						'transaction_id'    => $transaction_id,
						'payment_method'    => json_encode( $payment_method ),
						'payment_status'    => $payment_status,
						'payment_details'   => json_encode( $data ),
						'payment_date'      => date( 'Y-m-d H:i:s' ),
					);

					$payment_details_sql = $wpdb->insert( $table_name, $data );

					if ( $payment_details_sql ) {

						/* Update user credit balance */
						if ( $payment_status == 'confirmed' ) {
							if ( metadata_exists( 'user', $user_id, 'wegw_b2b_ads_credits_balance' ) ) {
								$b2b_available_credits = metadata_exists( 'user', $user_id, 'wegw_b2b_ads_credits_balance' ) ? get_user_meta( $user_id, 'wegw_b2b_ads_credits_balance', true ) : 0;
								$b2b_updated_credits   = $purchased_credits + $b2b_available_credits;
								update_user_meta( $user_id, 'wegw_b2b_ads_credits_balance', $b2b_updated_credits );
							} else {
								add_user_meta( $user_id, 'wegw_b2b_ads_credits_balance', $purchased_credits, true );
							}
						}

						echo json_encode( 'Details added successfully!' );
					} else {
						$wpdb->show_errors();
					}
				}
			}
		} else {
			echo json_encode( 'Token Error!' );
			die();
		}
	}
}

wegwb_new_instance( 'WEGW_B2B_API_Payment_Settings' );
