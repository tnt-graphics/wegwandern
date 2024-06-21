<?php
/**
 * Common used functions for `Summit Book` plugin
 *
 * @package wegwandern-summit-book
 */

/**
 * Replace the version number of the plugin on each release
 */
if ( ! defined( '_S_VERSION' ) ) {
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Get the name to display in place of nickname
 *
 * @param int $user_id user id optional.
 */
function get_user_display_name( $user_id = null ) {
	if ( $user_id ) {
		$current_user = get_user_by( 'ID', $user_id );
	} else {
		global $current_user;
	}
	if ( get_user_meta( $current_user->ID, 'profile_completion', true ) !== 'yes' ) {
		return '';
	}
	$name_of_user = get_user_meta( $current_user->ID, 'summit_nickname', true );
	if ( ! $name_of_user || '' === $name_of_user ) {
		$first_name_of_user = get_user_meta( $current_user->ID, 'first_name', true );
		$last_name_of_user  = get_user_meta( $current_user->ID, 'last_name', true );
		if ( $first_name_of_user && '' !== $first_name_of_user ) {
			$name_of_user = $first_name_of_user . ' ' . $last_name_of_user;
		} else {
			$name_of_user = '';
		}
	}
	return $name_of_user;
}

/**
 * Get Obfuscate email for `Pinwand` page display
 *
 * @param $email.
 */
function wegwandern_summit_book_obfuscate_email( $email ) {
	$em   = explode( '@', $email );
	$name = implode( '@', array_slice( $em, 0, count( $em ) - 1 ) );
	$len  = floor( strlen( $name ) / 2 );

	return substr( $name, 0, $len ) . str_repeat( '*', $len ) . '@' . end( $em );
}

/*
 * wegwandern_summit_book_register_frm_ajax_load_styles
 *
 * Formidable Summit Book auth section scripts
 */
add_filter( 'frm_ajax_load_styles', 'wegwandern_summit_book_register_frm_ajax_load_styles' );

function wegwandern_summit_book_register_frm_ajax_load_styles( $styles ) {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			/* Hide B2b form if success message comes after registration */
			if ( $("#form_user-registration-summit-book .frm_message span").hasClass("summitbk_reg_success_msg") ) {
				$('#form_user-registration-summit-book .frm_form_fields').addClass(' hide');
			}
		});
	</script>
	<?php
	return $styles;
}

/*
 * wegwandern_summit_book_readonly_acf_load_field
 *
 * ACF `pinwand_status` field make readonly
 */
add_filter( 'acf/load_field/name=pinwand_status', 'wegwandern_summit_book_readonly_acf_load_field' );
add_filter( 'acf/load_field/name=article_status', 'wegwandern_summit_book_readonly_acf_load_field' );

function wegwandern_summit_book_readonly_acf_load_field( $field ) {
	$field['readonly'] = 1;
	return $field;
}


add_action( 'acf/save_post', 'wegwandern_summit_book_ad_title_updater', 20 );

/**
 * Wegwandern_summit_book_ad_title_updater, Update post meta for status in article and ad
 *
 * ACF `pinwand_titel` & post_title connector
 *
 * @param int $post_id id of the post.
 */
function wegwandern_summit_book_ad_title_updater( $post_id ) {
	if ( get_post_type() == 'pinnwand_eintrag' ) {
		$pinwand_post               = array();
		$pinwand_post['post_title'] = get_field( 'pinwand_titel', $post_id );

		$pinwand_post_status = get_post_status( $post_id );
		$pinwand_meta_status = metadata_exists( 'post', $post_id, 'pinwand_status' ) ? get_post_meta( $post_id, 'pinwand_status', true ) : '';

		/* Check if post in `Pending` status */
		if ( $pinwand_post_status == 'publish' && $pinwand_meta_status == 'inVerification' ) {
			$pinwand_post['post_status'] = 'publish';

			/* Update post meta `pinwand_status` */
			update_post_meta( $post_id, 'pinwand_status', 'published' );
		}

		/* Update the post into the database */
		wp_update_post( $pinwand_post );
	} elseif ( get_post_type() == 'community_beitrag' ) {
		$article_post        = array();
		$article_post_status = get_post_status( $post_id );
		$article_meta_status = metadata_exists( 'post', $post_id, 'article_status' ) ? get_post_meta( $post_id, 'article_status', true ) : '';
		if ( $article_post_status == 'publish' && $article_meta_status == 'inVerification' ) {
			$article_post['post_status'] = 'publish';
			update_post_meta( $post_id, 'article_status', 'published' );
		}
		wp_update_post( $article_post );
	}
}

/**
 *
 * Get Summit book profile field values
 */
function get_summit_book_profile_fields() {
	$edit_profile_form_id = FrmForm::get_id_by_key( 'edit-user-profile-summit-book' );
	if ( method_exists( 'FrmProEntriesController', 'entry_link_shortcode' ) ) {

		$gender = FrmProEntriesController::entry_link_shortcode(
			array(
				'id'        => $edit_profile_form_id,
				'field_key' => 'zg19',
			)
		);

		$firstname = FrmProEntriesController::entry_link_shortcode(
			array(
				'id'        => $edit_profile_form_id,
				'field_key' => '9mi9m2',
			)
		);

		$lastname = FrmProEntriesController::entry_link_shortcode(
			array(
				'id'        => $edit_profile_form_id,
				'field_key' => 'uolyi2',
			)
		);

		$email = FrmProEntriesController::entry_link_shortcode(
			array(
				'id'        => $edit_profile_form_id,
				'field_key' => 'tqz0w2',
			)
		);

		return array(
			'gender'    => $gender,
			'firstname' => $firstname,
			'lastname'  => $lastname,
			'email'     => $email,
		);
	} else {
		return array();
	}
}

/**
 * Get the user avatar to display in FE
 */
function get_user_avatar( $user_id = null ) {
	$avatar_field_id = FrmField::get_id_by_key( 'hi9zl2' );
	if ( $user_id ) {
		$avatar_image = do_shortcode( "[frm-field-value field_id='$avatar_field_id' user_id='$user_id' show='avatar' add_link='0']" );
	} else {
		$avatar_image = do_shortcode( "[frm-field-value field_id='$avatar_field_id' user_id='current' show='avatar' add_link='0']" );
	}

	if ( $avatar_image != '' ) {
		return $avatar_image;
	} else {
		return '<img width="132" height="135" src="' . site_url() . '/wp-content/plugins/wegwandern-summit-book/assets/images/avatar_04.svg" class="attachment-thumbnail size-thumbnail">';
	}
}
