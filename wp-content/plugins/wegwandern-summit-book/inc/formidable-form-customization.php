<?php
/**
 * Custom code on form submission and other formidable form customizations
 *
 * @package wegwandern-summit-book
 */

add_action( 'frm_after_create_entry', 'after_entry_created', 30, 2 );

/**
 * Perform custom code after form submission in formidable
 *
 * @param integer $entry_id id of the entry.
 * @param integer $form_id id of the form.
 */
function after_entry_created( $entry_id, $form_id ) {
	/**
	 * Save comment form content as WordPress comment and rating as a custom post type
	 */
	$comments_form_id = FrmForm::get_id_by_key( 'commentsform' );
	if ( $comments_form_id === $form_id ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$current_user            = wp_get_current_user();
		if ( ! $current_user || 0 === $current_user->ID ) {
			return;
		}

		$post_array              = $_POST;
		$star_rating_field_id    = FrmField::get_id_by_key( '30zuy' );
		$mein_commentar_field_id = FrmField::get_id_by_key( 'ts5o3' );
		$file_upload_field_id    = FrmField::get_id_by_key( 'm7rv1' );
		$wanderung_field_id      = FrmField::get_id_by_key( 'fso1z' );
		$user_field_id           = FrmField::get_id_by_key( 'bk08k' );
		if ( '' === $post_array['item_meta'][ $wanderung_field_id ] ) {
			global $post;
			$post_array['item_meta'][ $wanderung_field_id ] = $post->ID;
		}
		if ( '' !== $post_array['item_meta'][ $mein_commentar_field_id ] || '' !== $post_array['item_meta'][ $file_upload_field_id ] ) {
			$comment_author = trim( $current_user->user_firstname . ' ' . $current_user->user_lastname );
			if ( '' === $comment_author ) {
				$comment_author = $current_user->display_name;
			}

			$values     = array(
				'comment_author'       => $comment_author,
				'comment_author_email' => $current_user->user_email,
				'comment_post_ID'      => $post_array['item_meta'][ $wanderung_field_id ],
				'comment_content'      => $post_array['item_meta'][ $mein_commentar_field_id ],
				'comment_approved'     => 0,
				'user_id'              => $current_user->ID,
			);
			$comment_id = wp_insert_comment( $values );
			add_comment_meta( $comment_id, 'rating', $post_array['item_meta'][ $star_rating_field_id ] );
			add_comment_meta( $comment_id, 'comment_images', $post_array['item_meta'][ $file_upload_field_id ] );
		}
		if ( '' !== $post_array['item_meta'][ $star_rating_field_id ] ) {
			$user_id      = get_current_user_id();
			$tour_id      = $post_array['item_meta'][ $wanderung_field_id ];
			$rating       = $post_array['item_meta'][ $star_rating_field_id ];
			$bewertung_id = get_user_bewertung( $user_id, $tour_id );
			if ( '' !== $bewertung_id ) {
				// If rating exists already, update the rating.
				update_post_meta( $bewertung_id, 'rating', $rating );
			} else {
				// Else insert new rating.
				$rating_values = array(
					'post_status' => 'publish',
					'post_author' => $user_id,
					'post_type'   => 'bewertung',
					'meta_input'  => array(
						'rating'          => $rating,
						'rated_user'      => $user_id,
						'rated_wanderung' => $tour_id,
					),
				);
				wp_insert_post( $rating_values );
			}
		}
	}

	/**
	 * Save Star Rating form content as WordPress rating as a custom post type
	 */
	$star_rating_form_id = FrmForm::get_id_by_key( 'star-rating-form' );
	if ( $star_rating_form_id === $form_id ) {
		$current_user            = wp_get_current_user();
		$post_array              = $_POST;
		$star_rating_field_id    = FrmField::get_id_by_key( '30zuy2' );
		$wanderung_field_id      = FrmField::get_id_by_key( 'fso1z2' );
		$user_field_id           = FrmField::get_id_by_key( 'bk08k2' );

		if ( '' === $post_array['item_meta'][ $wanderung_field_id ] ) {
			global $post;
			$post_array['item_meta'][ $wanderung_field_id ] = $post->ID;
		}

		if ( '' !== $post_array['item_meta'][ $star_rating_field_id ] ) {
			$user_id      = get_current_user_id();
			$tour_id      = $post_array['item_meta'][ $wanderung_field_id ];
			$rating       = $post_array['item_meta'][ $star_rating_field_id ];
			$bewertung_id = get_user_bewertung( $user_id, $tour_id );
			if ( '' !== $bewertung_id ) {
				// If rating exists already, update the rating.
				update_post_meta( $bewertung_id, 'rating', $rating );
			} else {
				// Else insert new rating.
				$rating_values = array(
					'post_status' => 'publish',
					'post_author' => $user_id,
					'post_type'   => 'bewertung',
					'meta_input'  => array(
						'rating'          => $rating,
						'rated_user'      => $user_id,
						'rated_wanderung' => $tour_id,
					),
				);
				wp_insert_post( $rating_values );
			}
		}
	}

	/**
	 * Schedule an event to delete the activation key of a user after 24hrs of registration
	 */
	$registration_form_id = FrmForm::get_id_by_key( 'user-registration-summit-book' );
	if ( $registration_form_id === $form_id ) {
		$post_array          = $_POST;
		$user_email_field_id = FrmField::get_id_by_key( 'qulq12' );
		if ( isset( $post_array['item_meta'][ $user_email_field_id ] ) ) { // get user email id from form.
			wp_schedule_single_event( time() + 86400, 'disable_user_activation_key', array( 'user_email' => $post_array['item_meta'][ $user_email_field_id ] ), true );
		}
	}

	/**
	 * Save article custom post type status according to status set in form
	 */
	$community_beitrag_form_id = FrmForm::get_id_by_key( 'communitybeitragform' );
	if ( $community_beitrag_form_id === $form_id ) {
		update_article_post_type_status( $entry_id );
	}

	/**
	 * Save pinwand ad custom post type status according to status set in form
	 */
	$pinwand_eintrag_form_id = FrmForm::get_id_by_key( 'pinnwandeintragform' );
	if ( $pinwand_eintrag_form_id === $form_id ) {
		update_ad_post_type_status( $entry_id );
	}

	/**
	 * Save the default avatar image for profile avatar
	 */
	$edit_profile_form_id = FrmForm::get_id_by_key( 'edit-user-profile-summit-book' );
	if ( $edit_profile_form_id === $form_id ) {
		$post_array = $_POST;
		update_user_after_profile_edit( $entry_id, $post_array );
	}
}

/**
 * Update the default user avatar if no avatar is set
 *
 * @param int   $entry_id id of the entry.
 * @param array $post_array array of post data.
 */
function update_user_after_profile_edit( $entry_id, $post_array ) {
	$entry                        = FrmEntry::getOne( $entry_id );
	$edit_profile_avatar_field_id = FrmField::get_id_by_key( 'hi9zl2' );
	if ( ! isset( $_POST['item_meta'][ $edit_profile_avatar_field_id ] ) || $_POST['item_meta'][ $edit_profile_avatar_field_id ] == '' ) {
		update_user_meta( $entry->user_id, 'avatar', '' );
	}
	$edit_profile_email_field_id = FrmField::get_id_by_key( 'tqz0w2' );
	$edit_profile_pass_field_id  = FrmField::get_id_by_key( 'onp4h2' );
	$current_user                = wp_get_current_user();
	if ( isset( $post_array['item_meta'][ $edit_profile_email_field_id ] ) && $post_array['item_meta'][ $edit_profile_email_field_id ] != $current_user->user_email && isset( $post_array['item_meta'][ $edit_profile_pass_field_id ] ) && $post_array['item_meta'][ $edit_profile_pass_field_id ] != '' ) {
		wp_update_user(
			array(
				'ID'         => $entry->user_id,
				'user_login' => $post_array['item_meta'][ $edit_profile_email_field_id ],
			)
		);
		wp_logout();
		wp_safe_redirect( home_url() . '/?password-email-reset=true&summit_book_login=yes', 301 );
		exit;
	} elseif ( isset( $post_array['item_meta'][ $edit_profile_pass_field_id ] ) && $post_array['item_meta'][ $edit_profile_pass_field_id ] != '' ) {
		wp_logout();
		wp_safe_redirect( home_url() . '/?password-reset=true&summit_book_login=yes', 301 );
		exit;
	} elseif ( isset( $post_array['item_meta'][ $edit_profile_email_field_id ] ) && $post_array['item_meta'][ $edit_profile_email_field_id ] != $current_user->user_email ) {
		wp_update_user(
			array(
				'ID'         => $entry->user_id,
				'user_login' => $post_array['item_meta'][ $edit_profile_email_field_id ],
			)
		);
		wp_logout();
		wp_safe_redirect( home_url() . '/?email-reset=true&summit_book_login=yes', 301 );
		exit;
	}
}

/**
 * Update article post type status as pending
 *
 * @param int $entry_id id of the entry.
 */
function update_article_post_type_status( $entry_id ) {
	$entry                   = FrmEntry::getOne( $entry_id );
	$article_status_field_id = FrmField::get_id_by_key( 'tobcx' );
	if ( $entry->post_id && isset( $_POST['item_meta'][ $article_status_field_id ] ) ) {
		$article_status = $_POST['item_meta'][ $article_status_field_id ];
		if ( 'inVerification' === $article_status ) {
			$save_post_data = array(
				'ID'          => $entry->post_id,
				'post_status' => 'pending',
			);
			wp_update_post( $save_post_data );
			update_post_meta( $entry->post_id, 'previous_article_status', '' );
		}
	}
}

/**
 * Update ad post type status as pending
 *
 * @param int $entry_id id of the entry.
 */
function update_ad_post_type_status( $entry_id ) {
	$entry              = FrmEntry::getOne( $entry_id );
	$ad_status_field_id = FrmField::get_id_by_key( 'q2dk0' );
	if ( $entry->post_id && isset( $_POST['item_meta'][ $ad_status_field_id ] ) ) {
		$article_status = $_POST['item_meta'][ $ad_status_field_id ];
		if ( 'inVerification' === $article_status ) {
			$save_post_data = array(
				'ID'          => $entry->post_id,
				'post_status' => 'pending',
			);
			wp_update_post( $save_post_data );
		}
	}
}

add_action( 'frm_after_update_entry', 'after_entry_updated', 10, 2 );

/**
 * Perform actions when form is updated
 *
 * @param int $entry_id id of the entry being updated.
 * @param int $form_id id of the form being updated.
 */
function after_entry_updated( $entry_id, $form_id ) {
	$community_beitrag_form_id = FrmForm::get_id_by_key( 'communitybeitragform' );
	$pinwand_eintrag_form_id   = FrmForm::get_id_by_key( 'pinnwandeintragform' );
	if ( $community_beitrag_form_id === $form_id ) {
		update_article_post_type_status( $entry_id );
	}
	if ( $pinwand_eintrag_form_id === $form_id ) {
		update_ad_post_type_status( $entry_id );
	}
	$edit_profile_form_id = FrmForm::get_id_by_key( 'edit-user-profile-summit-book' );
	if ( $edit_profile_form_id === $form_id ) {
		$post_array = $_POST;
		update_user_after_profile_edit( $entry_id, $post_array );
	}
}

add_action( 'disable_user_activation_key', 'disable_user_activation_key_action' );

/**
 * Disable user activation key after 24hrs
 *
 * @param string $user_email email of user from scheduled event.
 */
function disable_user_activation_key_action( $user_email ) {
	$registered_user = get_user_by( 'email', $user_email );
	if ( $registered_user ) {
		wp_update_user(
			array(
				'ID'                  => $registered_user->ID,
				'user_activation_key' => '',
			)
		);
	}
}

add_filter( 'frm_redirect_url', 'article_form_redirection', 1, 3 );

/**
 * Change the redirect url based on field data in community beitrag form
 *
 * @param string $url url which is originally set for redirection.
 * @param object $form form object with data.
 * @param array  $params array of parameters.
 */
function article_form_redirection( $url, $form, $params ) {
	$community_beitrag_form_id = FrmForm::get_id_by_key( 'communitybeitragform' );
	if ( $community_beitrag_form_id == $form->id ) {
		$field_id   = FrmField::get_id_by_key( 'tobcx' );
		$post_array = $_POST;
		if ( isset( $post_array['item_meta'][ $field_id ] ) && 'inVerification' === $post_array['item_meta'][ $field_id ] ) {
			$url = DASHBOARD_PAGE_URL;
		}
	}
	$pinwand_eintrag_form_id = FrmForm::get_id_by_key( 'pinnwandeintragform' );
	if ( $pinwand_eintrag_form_id == $form->id ) {
		$field_id   = FrmField::get_id_by_key( 'q2dk0' );
		$post_array = $_POST;
		if ( isset( $post_array['item_meta'][ $field_id ] ) && 'inVerification' === $post_array['item_meta'][ $field_id ] ) {
			$url = DASHBOARD_PAGE_URL;
		}
	}
	return $url;
}

add_filter( 'frm_skip_form_action', 'stop_multiple_actions', 20, 2 );

/**
 * Stop sending of multiple emails
 *
 * @param bool  $skip_this_action whether to skip this action or not.
 * @param array $args array of form values.
 */
function stop_multiple_actions( $skip_this_action, $args ) {
	if ( 2088 === $args['action']->ID ) {
		$entry_id = $args['entry'];
		if ( is_object( $args['entry'] ) ) {
			$entry_id = $args['entry']->id;
		}
		$option_key    = 'frm_triggered_' . $args['action']->ID . '_' . $entry_id;
		$was_triggered = get_option( $option_key );
		if ( $was_triggered ) {
			$skip_this_action = true;
		}
	}
	return $skip_this_action;
}

add_action( 'frm_trigger_email_action', 'frm_set_action_triggered', 30, 2 );

/**
 * Update the action as triggered
 *
 * @param object $action action object.
 * @param object $entry entry object.
 */
function frm_set_action_triggered( $action, $entry ) {
	if ( 2088 === $action->ID ) {
		$option_key = 'frm_triggered_' . $action->ID . '_' . $entry->id;
		update_option( $option_key, true );
	}
}

add_filter( 'frm_get_default_value', 'wegwandern_summit_book_set_default_value', 10, 2 );

/**
 * Set a dynamic default value for a field in formidable form
 *
 * @param string $new_value value to be set in form field.
 * @param object $field field object.
 */
function wegwandern_summit_book_set_default_value( $new_value, $field ) {
	/**
	 * Set the previous rating of the user for the tour
	 */
	$star_rating_field_id = FrmField::get_id_by_key( '30zuy' );
	if ( $field->id == $star_rating_field_id && is_user_logged_in() ) {
		global $post;
		global $current_user;
		$bewertung_id = get_user_bewertung( $current_user->ID, $post->ID );
		if ( '' !== $bewertung_id ) {
			$new_value = get_post_meta( $bewertung_id, 'rating', true );
			$new_value = $new_value ? intval( $new_value ) : 0;
		}
	}
	$wanderung_field_id = FrmField::get_id_by_key( 'fso1z' );
	if ( $field->id == $wanderung_field_id ) {
		global $post;
		$new_value = $post->ID;
	}
	return $new_value;
}

/**
 * Check if a rating value exists for the user in a tour
 *
 * @param int $user_id id of the user.
 * @param int $tour_id id of the tour.
 */
function get_user_bewertung( $user_id, $tour_id ) {
	$args         = array(
		'meta_key'   => 'rated_wanderung',
		'meta_value' => $tour_id,
		'post_type'  => 'bewertung',
		'author'     => $user_id,
	);
	$bewertung    = get_posts( $args );
	$bewertung_id = ! empty( $bewertung ) && isset( $bewertung[0] ) ? $bewertung[0]->ID : '';
	return $bewertung_id;
}

add_filter( 'frm_field_classes', 'summit_book_add_input_class', 10, 2 );

/**
 * Add class for registration and profile password fields for password strength
 *
 * Add class to form fields
 *
 * @param string $classes string containing all the classes for field.
 * @param array  $field array of field parameters.
 */
function summit_book_add_input_class( $classes, $field ) {
	$field_id_profile = FrmField::get_id_by_key( 'onp4h2' );
	$field_id_reg     = FrmField::get_id_by_key( '1abwj2' );
	if ( $field['id'] == $field_id_profile || $field['id'] == $field_id_reg ) {
		$classes .= ' fld-paswd';
	}

	return $classes;
}

add_filter(
	'frmreg_activation_success_msg',
	function( $message ) {
		if ( __( 'Your account has been activated. You may now log in.', 'frmreg' ) === $message ) {
			return null;
		}
		return $message;
	}
);

// add_filter( 'frm_load_dropzone', '__return_false' );
