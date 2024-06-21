<?php
/**
 * Functions for admin backend in Community Beitrag
 *
 * @package wegwandern-summit-book
 */

add_filter( 'manage_pinnwand_eintrag_posts_columns', 'pinnwand_eintrag_table_head' );

/**
 * Change the columns of community beitrag
 *
 * @param array $defaults array of columns to display in admin listing.
 */
function pinnwand_eintrag_table_head( $defaults ) {
	// unset( $defaults['title'] );
	// unset( $defaults['date'] );
	// $defaults['titel']        = __( 'Titel', 'wegwandern-summit-book' );
	$defaults['dein-text']    = __( 'Dein Text', 'wegwandern-summit-book' );
	$defaults['user-contact'] = __( 'Kontakt', 'wegwandern-summit-book' );
	$defaults['status']       = __( 'Status', 'wegwandern-summit-book' );
	// $defaults['actions']      = __( 'Actions', 'wegwandern-summit-book' );
	return $defaults;
}

add_action( 'manage_pinnwand_eintrag_posts_custom_column', 'pinnwand_eintrag_table_content', 10, 2 );

/**
 * Add the column contents for community beitrag
 *
 * @param string $column_name name of the column.
 * @param int    $post_id id of the post.
 */
function pinnwand_eintrag_table_content( $column_name, $post_id ) {
	if ( 'dein-text' === $column_name ) {
		$pinwand_dein_text = get_post_meta( $post_id, 'pinwand_dein_text', true );
		echo esc_attr( $pinwand_dein_text );
	}
	if ( 'user-contact' === $column_name ) {
		$user_id    = get_post_meta( $post_id, 'pinwand_user', true );
		$first_name = get_user_meta( $user_id, 'first_name', true );
		$last_name  = get_user_meta( $user_id, 'last_name', true );
		echo esc_attr( $first_name . ' ' . $last_name ) . '<br>';
		$pinwand_email = get_post_meta( $post_id, 'pinwand_e_mail', true );
		$email_text    = __( 'E-Mail: ', 'wegwandern-summit-book' );
		echo esc_attr( $email_text . $pinwand_email ) . '<br>';
		$pinwand_phone = get_post_meta( $post_id, 'pinwand_telefon', true );
		$phone_text    = __( 'Tel. ', 'wegwandern-summit-book' );
		if ( $pinwand_phone ) {
			echo esc_attr( $phone_text . $pinwand_phone ) . '<br>';
		}
		$pinwand_validity = get_post_meta( $post_id, 'pinwand_laufzeit_des_inserats', true );
		$vailidity_text   = __( 'Laufzeit des Inserats: ', 'wegwandern-summit-book' );
		if ( $pinwand_validity ) {
			echo esc_attr( $vailidity_text . gmdate( 'd.m.Y', strtotime( $pinwand_validity ) ) ) . '<br>';
		}
	}
	if ( 'status' === $column_name ) {
		$status         = get_post_meta( $post_id, 'pinwand_status', true );
		$status_display = isset( SUMMIT_BOOK_PINNWAND_EINTRAG_STATUS[ $status ] ) ? SUMMIT_BOOK_PINNWAND_EINTRAG_STATUS[ $status ] : '';
		echo esc_attr( $status_display );
	}
}

/**
 * Function to send mail to Ad author if status change done from post edit page.
 */
add_action( 'transition_post_status', 'wegwandern_summit_book_pinnwand_admin_transition_post_status', 10, 3 );

function wegwandern_summit_book_pinnwand_admin_transition_post_status( $new_status, $old_status, $post ) {
	$ad_ID             = $post->ID;
	$ad_title          = esc_html( get_the_title( $ad_ID ) );
	$post_author_ID    = get_post_field( 'post_author', $ad_ID );
	$post_author_email = get_the_author_meta( 'user_email', $post_author_ID );

	/* Check if the post type is 'b2b-werbung' */
	if ( $post->post_type == 'pinnwand_eintrag' ) {
		/* Check if the post status is changed to 'Rejected' */
		if ( $old_status == 'pending' && $new_status == 'rejected' ) {
			if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
				$args = array(
					'ID'          => $ad_ID,
					'post_status' => 'draft',
				);

				wp_update_post( $args );

				/* Update post meta `pinwand_status` */
				update_post_meta($ad_ID, 'pinwand_status', 'rejected');

				/* Send mail to user - 'Rejection' */
				$rejected_mail_subject  = __( 'Dein Inserat wurde abgelehnt', 'wegwandern-summit-book' );
				$headers  = array( 'Content-Type: text/html; charset=UTF-8' );
				$message  = 'Dein Inserat wurde zurückgewiesen. Bitte beachte unsere <a href="' . TERMS_OF_USE_URL . '">Nutzungsbedingungen</a>.' . '<br /><br />';
				$message .= 'Dein WegWandern.ch-Team' . '<br />';

				wp_mail(
					$post_author_email,
					$rejected_mail_subject,
					__( $message, 'wegwandern-summit-book' ),
					$headers
				);
			} else {
				echo wp_send_json_error( 'Access Denied.' );
			}
		}

		/* Check if the post status is changed to 'Publish' || 'Future' */
		if ( $old_status == 'pending' && ( $new_status == 'publish' || $new_status == 'future' ) ) {
			if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {

				/* Update post meta `pinwand_status` */
				update_post_meta($ad_ID, 'pinwand_status', 'published');
			} else {
				echo wp_send_json_error( 'Access Denied.' );
			}
		}
	}
}

/**
 * Change the post status of pinwall ad
 *
 * @param int $post_id id of the post to update.
 * @param int $ad_status status to be updated.
 */
function change_pinwall_ad_status( $post_id, $ad_status ) {
	switch ( $ad_status ) {
		case 'publish':
			$post_status      = 'publish';
			$post_meta_status = 'published';
			break;
		case 'reject':
			$post_status      = 'draft';
			$post_meta_status = 'rejected';
			break;
		case 'expiry':
			$post_status      = 'draft';
			$post_meta_status = 'expired';
			break;
		default:
			$post_status      = 'draft';
			$post_meta_status = 'saved';
	}
	
	// Update post status.
	wp_update_post(
		array(
			'ID'          => $post_id,
			'post_status' => $post_status,
		)
	);

	// Update entry status.
	update_post_meta( $post_id, 'pinwand_status', $post_meta_status );
}

/**
 * Set cron job to delete all expired ads
 */
function wegwandern_summit_book_pinwand_ad_expiry() {
	$current_date = date( 'Y-m-d' );

	$args = array(
		'post_type'   => 'pinnwand_eintrag',
		'post_status' => array( 'publish', 'draft' ),
	);

	$pinwand_ads = new WP_Query( $args );

	if ( $pinwand_ads->have_posts() ) :
		while ( $pinwand_ads->have_posts() ) :
			$pinwand_ads->the_post();

			$pinwand_ad_ID          = get_the_ID();
			$pinwand_post_status    = get_post_status( $pinwand_ad_ID );
			$pinwand_meta_status    = metadata_exists( 'post', $pinwand_ad_ID, 'pinwand_status' ) ? get_post_meta( $pinwand_ad_ID, 'pinwand_status', true ) : '';
			$pinwand_ad_expiry_date = metadata_exists( 'post', $pinwand_ad_ID, 'pinwand_laufzeit_des_inserats' ) ? date( 'Y-m-d', strtotime( get_post_meta( $pinwand_ad_ID, 'pinwand_laufzeit_des_inserats', true ) ) ) : '';
			$pinwand_ad_publish_date                 = get_the_date( 'Y-m-d' );
			$pinwand_ad_auto_expiry_frm_publish_date = date( 'Y-m-d', strtotime( '+6 months', strtotime( $pinwand_ad_publish_date ) ) );

			/* Make published ad Expired */
			if ( $pinwand_post_status == 'publish' && $pinwand_meta_status == 'published' ) {
				if ( $pinwand_ad_expiry_date != '' ) {

					if ( $current_date >= $pinwand_ad_expiry_date ) {
						change_pinwall_ad_status( $pinwand_ad_ID, 'expiry' );
					}
				} else {
					if ( $current_date >= $pinwand_ad_auto_expiry_frm_publish_date ) {
						wp_delete_post( $pinwand_ad_ID );
					}
				}
			}

			/* Delete Expired/Rejected ads after 6 months */
			if ( $pinwand_post_status == 'draft' && ( $pinwand_meta_status == 'expired' || $pinwand_meta_status == 'rejected' ) ) {
				if ( $pinwand_ad_expiry_date != '' ) {
					$pinwand_ad_removal_date = date( 'Y-m-d', strtotime( '+6 months', strtotime( $pinwand_ad_expiry_date ) ) );
				} else {
					$pinwand_ad_removal_date = date( 'Y-m-d', strtotime( '+6 months', strtotime( $pinwand_ad_publish_date ) ) );
				}

				if ( $current_date >= $pinwand_ad_removal_date ) {
					wp_delete_post( $pinwand_ad_ID );
				}
			}

		endwhile;
	endif;

	wp_reset_postdata();
	wp_die();
}

add_action( 'wegwandern_summit_book_pinnwand_eintrag_daily_cron', 'wegwandern_summit_book_pinwand_ad_expiry' );

if ( ! wp_next_scheduled( 'wegwandern_summit_book_pinnwand_eintrag_daily_cron' ) ) {
	wp_schedule_event( time(), 'daily', 'wegwandern_summit_book_pinnwand_eintrag_daily_cron' );
}

/**
 * Add `Reject` status to dropdown in Pinwand ads listing admin backend`
 */
add_action( 'admin_footer-post.php', 'wegwandern_summit_book_append_post_status_list' );
add_action( 'admin_footer-edit.php', 'wegwandern_summit_book_append_post_status' );

function wegwandern_summit_book_append_post_status_list() {
	global $post;
	if ( $post->post_type != 'pinnwand_eintrag' && $post->post_type != 'community_beitrag' ) {
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

function wegwandern_summit_book_append_post_status() {
	global $post;
	if ( $post->post_type != 'pinnwand_eintrag' && $post->post_type != 'community_beitrag' ) {
		return;
	}

	echo '<script>
		document.querySelectorAll("select[name=\"_status\"]").forEach((s) => {
			s.appendChild(new Option("Rejected", "rejected"));
		});
	</script>';
}
