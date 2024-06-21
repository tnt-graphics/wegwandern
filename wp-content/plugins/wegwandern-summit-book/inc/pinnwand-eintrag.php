<?php
/**
 * Functions for Pinnwand Eintrag or Pinwall ads
 *
 * @package wegwandern-summit-book
 */

define(
	'SUMMIT_BOOK_PINNWAND_EINTRAG_STATUS',
	array(
		'saved'          => __( 'Gespeichert', 'wegwandern-summit-book' ),
		'inVerification' => __( 'In Prüfung', 'wegwandern-summit-book' ),
		'published'      => __( 'Veröffentlicht', 'wegwandern-summit-book' ),
		'rejected'       => __( 'Inserat Abgelehnt', 'wegwandern-summit-book' ),
		'expired'        => __( 'Abgelaufen', 'wegwandern-summit-book' ),
	)
);

/**
 * Set the view of a pinwand ad
 *
 * @param int $pinwand_ad_id id of the ad to be displayed.
 */
function get_pinwand_ad_view( $pinwand_ad_id, $display_location = null ) {
	ob_start();
	$all_post_meta  = get_post_meta( $pinwand_ad_id );
	$entry_id       = FrmDb::get_var( 'frm_items', array( 'post_id' => $pinwand_ad_id ), 'id' );
	$pinwand_status = isset( $all_post_meta['pinwand_status'][0] ) ? $all_post_meta['pinwand_status'][0] : '';
	$published_date = get_the_date( 'd.m.Y', $pinwand_ad_id );

	if ( $display_location === 'user-dashboard' ) {
		$pinwand_ad_div_class = 'each-pinwall-ad-dashboard';
	} else {
		$pinwand_ad_div_class = 'each-pinwall-ad';
	}
	?>

	<div class="<?php echo esc_attr( $pinwand_ad_div_class ); ?>" id="data-pinwand-ad-<?php echo esc_attr( $entry_id ); ?>">

		<?php
		if ( $display_location === 'user-dashboard' ) {
			echo "<div class='each-pinwall-ad-content'>"; }

		if ( 'published' === $pinwand_status ) {
			?>
			<div class="pinwall-ad-publishing-date"><?php echo esc_attr( $published_date ); ?></div>
		<?php } ?>

		<h3 class="pinwall-ad-headline">
			<?php echo isset( $all_post_meta['pinwand_titel'][0] ) ? esc_attr( $all_post_meta['pinwand_titel'][0] ) : ''; ?>
		</h3>
		<p class="pinwall-ad-text">
			<?php echo isset( $all_post_meta['pinwand_dein_text'][0] ) ? esc_attr( wp_strip_all_tags( $all_post_meta['pinwand_dein_text'][0] ) ) : ''; ?>
		</p>
		<?php
		$ad_user_id = get_post_meta( $pinwand_ad_id, 'pinwand_user', true );
		?>
		<div class="pinwall-ad-user-avatar">
			<?php
			echo get_user_avatar( $ad_user_id );
			?>
		</div>
		<div class="pinwall-ad-user-name">
			<?php
			$usermeta       = get_user_meta( $ad_user_id );
			$user_nick_name = get_user_meta( $ad_user_id, 'summit_nickname', true );

			$user_fname = isset( $usermeta['first_name'][0] ) && ( $usermeta['first_name'][0] != '' ) ? $usermeta['first_name'][0] : '';
			// $user_lname = isset( $usermeta['last_name'][0] ) && ( $usermeta['last_name'][0] != '' ) ? $usermeta['last_name'][0] : '';
			$real_name  = $user_fname;

			if ( $user_nick_name !== '' ) {
				echo esc_attr( $user_nick_name );
			} else {
				echo esc_attr( $real_name );
			}
			?>
		</div>
		<div class="pinwall-ad-connect">
			<?php
			$email_text    = __( 'E-Mail', 'wegwandern-summit-book' );
			$phone_text    = __( 'Tel. ', 'wegwandern-summit-book' );
			$email_contact = isset( $all_post_meta['pinwand_e_mail'][0] ) ? $all_post_meta['pinwand_e_mail'][0] : '';

			if ( '' !== $email_contact ) {
				$href = '';
				if ( is_user_logged_in() ) {
					$mail = $email_contact;
					$href = 'href="mailto:' . $mail . '"';
				} else {
					$mail = wegwandern_summit_book_obfuscate_email( $email_contact );
				}

				echo '<a class="hide-connect-1" ' . $href . '>' . $email_text . '</a>';
			}

			$phone_contact = isset( $all_post_meta['pinwand_telefon'][0] ) ? $all_post_meta['pinwand_telefon'][0] : '';

			if ( '' !== $phone_contact ) {
				if ( is_user_logged_in() ) {
					$phone = $phone_text . '+' . $phone_contact;
				} else {
					$phone = $phone_text . '**********';
				}

				echo '<div class="hide-connect-2">' . $phone . '</div>';
			}
			?>
		</div>

		<?php
		if ( $display_location === 'user-dashboard' ) {
			echo '</div>'; }

		if ( ! is_admin() && ! is_page( 'pinwand' ) ) {
			?>
			<div class="pinwall-ad-actions">
				<div class="pinwall-ad-status-info">
					<?php
					echo esc_attr( SUMMIT_BOOK_PINNWAND_EINTRAG_STATUS[ $pinwand_status ] );
					if ( 'published' === $pinwand_status ) {
						$validity = isset( $all_post_meta['pinwand_laufzeit_des_inserats'][0] ) ? $all_post_meta['pinwand_laufzeit_des_inserats'][0] : '';
						if ( $validity ) {
							$validity_text = __( 'bis ', 'wegwandern-summit-book' );
							echo '<br>' . esc_attr( $validity_text . gmdate( 'd.m.Y', strtotime( $validity ) ) );
						}
					}
					?>
				</div>
				
				<div class="pinwand-ad-action-links">
					<?php
					$entry_id = FrmDb::get_var( 'frm_items', array( 'post_id' => $pinwand_ad_id ), 'id' );
					if ( 'saved' === $pinwand_status ) {
						$edit_page_id = url_to_postid( INSERAT_ERSTELLEN_URL );
						echo "<a href='" . do_shortcode( "[frm-entry-edit-link id=$entry_id label=0 page_id=$edit_page_id class='edit-pinwand-ad']" ) . "'><div class='pinwand-ad-edit-link'></div></a>";
					}
					?>
					<div class='delete-pinwand-ad' id="delete-pinwand-ad_<?php echo esc_attr( $entry_id ); ?>">
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

	<?php
	ob_flush();
}
