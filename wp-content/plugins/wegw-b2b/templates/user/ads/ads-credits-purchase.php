<?php
ob_start();
get_header();
b2b_user_menu_callback();
wegwb_b2b_check_user_role_access();

$user_ID      = get_current_user_id();
$response_msg = '';

$instanceName = WEGW_B2B_PAYREXX_INSTANCE;
$secret       = WEGW_B2B_PAYREXX_API_KEY;
$payrexx      = new \Payrexx\Payrexx( $instanceName, $secret );

if ( ! session_id() ) {
	session_start();
}

if ( isset( $_GET['payment'] ) && $_GET['payment'] != '' ) {

	if ( $_GET['payment'] == 'success' ) {
		if ( isset( $_SESSION['reference_number'] ) && $_SESSION['reference_number'] != '' ) {

			/* Unsetting session variable - $_SESSION['reference_number'] */
			unset( $_SESSION['reference_number'] );

			$_SESSION['payment_status'] = 1;
			header( 'Location: ' . site_url( WEGW_B2B_AD_CREATE ) );
		}
	}

	if ( $_GET['payment'] == 'failed' ) {
		$response_msg = '<p>' . __( 'Payment failed.', 'wegw-b2b' ) . '</p>';
	}

	if ( $_GET['payment'] == 'cancel' ) {
		$response_msg = '<p>' . __( 'Payment cancelled.', 'wegw-b2b' ) . '</p>';
	}
}
?>

<div class="container">
	<div class="credit_purchase">
		<div class="c_p_detail_container">
			<h3><?php echo __( "Klick's kaufen", 'wegw-b2b' ); ?></h3>
			<div class="c_p_detail">
				<div class="c_p_proceed_content">
					<form method="POST">
					<?php wp_nonce_field( 'b2b_credits_purchase' ); ?>
						<div class="c_p_proceed_content_select">
							<?php
								$b2b_available_credits = wegwb_b2b_user_ads_credits_balance();
							 echo '<p>' . __( 'Ihr Guthaben beträgt ' . $b2b_available_credits, 'wegw-b2b' ) . '</p>';
							?>
							<p>Anzahl Klicks buchen</p>
							<?php
							if ( have_rows( 'credits_price_settings', 'option' ) ) :
								/* Loop through rows. */
								while ( have_rows( 'credits_price_settings', 'option' ) ) :
									the_row();

									$b2b_ad_clicks_count = get_sub_field( 'b2b_ad_clicks_count' );
									$b2b_ad_clicks_price = get_sub_field( 'b2b_ad_clicks_price' );

									echo '<input type="radio" id="clicks' . $b2b_ad_clicks_count . '" name="b2b_credits" value="' . $b2b_ad_clicks_count . '-' . $b2b_ad_clicks_price . '">';
									echo '<label for="clicks' . $b2b_ad_clicks_count . '">' . $b2b_ad_clicks_count . ' Klicks = CHF ' . $b2b_ad_clicks_price . '</label><br>';

								  endwhile;
								reset_rows();
						  endif;
							?>
						</div>
						<div class="payment-btn">
							<div class="payment-btn-text"><input type="submit" name="purchase_credit" id="purchase_credit" value="Zur Zahlung"></div>
						</div>
					</form>

					<?php
					if ( isset( $_REQUEST['purchase_credit'] ) ) {
						if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'b2b_credits_purchase' ) ) {
							die();
						}

						if ( isset( $_POST['b2b_credits'] ) && $_POST['b2b_credits'] ) {
							$current_user       = wp_get_current_user();
							$current_user_email = $current_user->user_email;
							$b2b_credit         = isset( $_POST['b2b_credits'] ) ? sanitize_text_field( $_POST['b2b_credits'] ) : '';
							$b2b_credits_data   = explode( '-', $b2b_credit );
							$credit_count       = sanitize_text_field( $b2b_credits_data[0] );
							$credit_amount      = sanitize_text_field( $b2b_credits_data[1] );
							$referenceID        = uniqid( 'B2B_' );

							if ( ! empty( $credit_amount ) ) {

								$signatureCheck = new \Payrexx\Models\Request\SignatureCheck();
								if ( $payrexx->getOne( $signatureCheck ) ) {

									$user_anrede     = get_user_meta( $current_user->ID, 'anrede', true );
									$user_vorname    = get_user_meta( $current_user->ID, 'first_name', true );
									$user_nachname   = get_user_meta( $current_user->ID, 'last_name', true );
									$user_firmenname = get_user_meta( $current_user->ID, 'b2b_firmname', true );
									$user_strasse    = get_user_meta( $current_user->ID, 'b2b_street_num', true );
									$user_plz        = get_user_meta( $current_user->ID, 'b2b_plz', true );
									$user_ort        = get_user_meta( $current_user->ID, 'b2b_ort', true );
									$user_telefon    = get_user_meta( $current_user->ID, 'b2b_phn', true );
									$gateway         = new \Payrexx\Models\Request\Gateway();
									$gateway->setAmount( $credit_amount * 100 );
									$gateway->setCurrency( 'CHF' );
									$gateway->setSuccessRedirectUrl( site_url( WEGW_B2B_CREDIT_PURCHASE_PAGE . '?payment=success' ) );
									$gateway->setFailedRedirectUrl( site_url( WEGW_B2B_CREDIT_PURCHASE_PAGE . '?payment=failed' ) );
									$gateway->setCancelRedirectUrl( site_url( WEGW_B2B_CREDIT_PURCHASE_PAGE . '?payment=cancel' ) );
									// $gateway->setPm( array( 'visa', 'mastercard' ) );
									$gateway->setPreAuthorization( false );
									$gateway->setReservation( false );
									$gateway->setReferenceId( $referenceID );
									
									$gateway->addField( $type = 'custom_field_1', $value = $credit_count, $name = 'Anzahl gekaufter Klicks' );
									$gateway->addField( $type = 'custom_field_2', $value = $user_ID, $name = 'B2B Benutzer ID' );
									$gateway->addField( $type = 'custom_field_3', $value = $user_anrede, $name = 'Anrede' );
									$gateway->addField( $type = 'forename', $value = $user_vorname, $name = 'Vorname' );
									$gateway->addField( $type = 'surname', $value = $user_nachname, $name = 'Nachname' );
									if($user_firmenname) {
										$gateway->addField( $type = 'company', $value = $user_firmenname, $name = 'Firmenname' );
									}
									$gateway->addField( $type = 'street', $value = $user_strasse, $name = 'Strasse und Hausnummer' );
									$gateway->addField( $type = 'postcode', $value = $user_plz, $name = 'PLZ' );
									$gateway->addField( $type = 'place', $value = $user_ort, $name = 'Ort' );
									$gateway->addField( $type = 'phone', $value = $user_telefon, $name = 'Telefonnummer' );
									$gateway->addField( $type = 'email', $value = $current_user_email );
									
									$response                     = $payrexx->create( $gateway );
									$_SESSION['reference_number'] = $referenceID;

									$payment_url = $response->getLink();
									header( 'Location: ' . $payment_url );
									die();
								} else {
									echo '<p>' . __( 'Authorization error!', 'wegw-b2b' ) . '</p>';
								}
							}
						} else {
							echo '<p>' . __( 'Please select credit!', 'wegw-b2b' ) . '</p>';
							$response_msg = '';
						}
					}

					if ( isset( $response_msg ) && $response_msg != '' ) {
						echo $response_msg;
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>
