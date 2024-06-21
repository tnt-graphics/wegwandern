<?php
/**
 * Reusable functions throughout the plugin.
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace the version number of the plugin on each release
 */
if ( ! defined( '_S_VERSION' ) ) {
	define( '_S_VERSION', '1.0.0' );
}

global $wegwb_instances;

/**
 * Initialize placeholders.
 */
$wegwb_instances = array();

/**
 * wegwb_new_instance
 *
 * Creates a new instance of the given class and stores it in the instances data store.
 */
function wegwb_new_instance( $class = '' ) {
	global $wegwb_instances;
	return $wegwb_instances[ $class ] = new $class();
}

/**
 * wegwb_get_path
 *
 * Returns the plugin path to a specified file.
 */
function wegwb_get_path( $filename = '' ) {
	return WEGW_B2B_PATH . ltrim( $filename, '/' );
}

/*
 * wegwb_include
 *
 * Includes a file within the WEGW B2B plugin.
 */
function wegwb_include( $filename = '' ) {
	$file_path = wegwb_get_path( $filename );

	if ( file_exists( $file_path ) ) {
		include_once $file_path;
	}
}

/*
 * wegwb_b2b_user_ads_credits_balance
 *
 * Get user's ads credit balance.
 */
function wegwb_b2b_user_ads_credits_balance( $user_id = null ) {
	$uid = ( isset( $user_id ) && $user_id != '' ) ? $user_id : get_current_user_id();

	$b2b_available_credits = get_user_meta( $uid, 'wegw_b2b_ads_credits_balance', true );
	$b2b_available_credits = ( isset( $b2b_available_credits ) && $b2b_available_credits != '' ) ? $b2b_available_credits : 0;
	return $b2b_available_credits;
}

/*
 * wegwb_b2b_ads_clicks_count
 *
 * Get ads credit balance.
 */
function wegwb_b2b_ads_clicks_count( $ad_ID, $ad_date = null ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'b2b_ad_clicks';

	if ( ( $ad_date ) && $ad_date != '' ) {
		$search_ad_date        = date( 'Y-m-d', strtotime( $ad_date ) );
		$search_ad_date_after  = date( 'Y-m-d', strtotime( $search_ad_date . ' +1 day' ) );
		$include_ad_date_query = " AND (click_date > '{$search_ad_date}' AND click_date < '{$search_ad_date_after}') ";
	} else {
		$include_ad_date_query = '';
	}

	$clicks_count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}` WHERE `ad_id` = '{$ad_ID}' AND `status` = 1 " . $include_ad_date_query );
	return $clicks_count;
}

/*
 * wegwb_register_session
 *
 * Check if session already started
 */
function wegwb_register_session() {
	if ( ! session_id() ) {
		session_start();
	}
}

// add_action( 'init', 'wegwb_register_session' );

add_filter( 'frm_field_classes', 'wegw_add_input_class', 10, 2 );
/*
 * wegw_add_input_class
 *
 * Add class to form fields
 */
function wegw_add_input_class( $classes, $field ) {
	$edit_profile_password_field_id = FrmField::get_id_by_key( 'b2b_prof_frm_user_pwd' );
	$registration_password_field_id = FrmField::get_id_by_key( '1abwj' );
	if ( $field['id'] == $edit_profile_password_field_id || $field['id'] == $registration_password_field_id ) {
		$classes .= 'fld-paswd';
	}

	return $classes;
}

/*
 * wegb_function_new_user
 * wegb_function_check_login_redirect
 *
 * Redirection to profile for initial login, after first login redirection to angebote
 */
add_action( 'user_register', 'wegb_function_new_user' );
add_action( 'wp_login', 'wegb_function_check_login_redirect', 10, 2 );

function wegb_function_new_user( $user_id ) {
	add_user_meta( $user_id, '_new_user', '1' );
}


function wegb_function_check_login_redirect( $user_login, $user ) {
	$logincontrol = get_user_meta( $user->ID, '_new_user', 'TRUE' );

	if ( in_array( 'summit-book-user', (array) $user->roles ) && strpos( $_POST['redirect_to'], 'gipfelbuch' ) !== false ) {
		// home_url( 'gipfelbuch-profil-bearbeiten' )
		// wp_redirect( site_url( 'gipfelbuch-profil-bearbeiten' ), 302 );
		// exit;
		return true;
	}

	if ( $logincontrol == '2' ) {
		// set the user to old
		// update_user_meta( $user->ID, '_new_user', '0' );

		// Do the redirects or whatever you need to do for the first login
		// wp_redirect( site_url() . '/angebote/', 302 ); angebote changed into b2b-portal-dashboard
		wp_redirect( site_url( WEGW_B2B_DASHBOARD ), 302 );
		exit;
	} elseif ( $logincontrol == '1' ) {
		update_user_meta( $user->ID, '_new_user', '2' );
	}

}

 /**
  * Get B2B profile field values
  */
function get_b2b_profile_fields() {
	$edit_profile_form_id = FrmForm::get_id_by_key( 'edit-user-profile' );
	$gender               = FrmProEntriesController::entry_link_shortcode(
		array(
			'id'        => $edit_profile_form_id,
			'field_key' => 'b2b_prof_frm_user_designation',
		)
	);
	$firstname            = FrmProEntriesController::entry_link_shortcode(
		array(
			'id'        => $edit_profile_form_id,
			'field_key' => 'b2b_prof_frm_user_fname',
		)
	);
	$lastname             = FrmProEntriesController::entry_link_shortcode(
		array(
			'id'        => $edit_profile_form_id,
			'field_key' => 'b2b_prof_frm_user_lname',
		)
	);
	$address              = FrmProEntriesController::entry_link_shortcode(
		array(
			'id'        => $edit_profile_form_id,
			'field_key' => 'b2b_prof_frm_user_addr',
		)
	);
	$ort                  = FrmProEntriesController::entry_link_shortcode(
		array(
			'id'        => $edit_profile_form_id,
			'field_key' => 'b2b_prof_frm_user_ort',
		)
	);
	$plz                  = FrmProEntriesController::entry_link_shortcode(
		array(
			'id'        => $edit_profile_form_id,
			'field_key' => 'b2b_prof_frm_user_plz',
		)
	);
	$phonenumber          = FrmProEntriesController::entry_link_shortcode(
		array(
			'id'        => $edit_profile_form_id,
			'field_key' => 'b2b_prof_frm_user_tele',
		)
	);
	$email                = FrmProEntriesController::entry_link_shortcode(
		array(
			'id'        => $edit_profile_form_id,
			'field_key' => 'tqz0w',
		)
	);

	return array(
		'gender'      => $gender,
		'firstname'   => $firstname,
		'lastname'    => $lastname,
		'address'     => $address,
		'ort'         => $ort,
		'plz'         => $plz,
		'phonenumber' => $phonenumber,
		'email'       => $email,
	);
}


/*
 * wegb_redirect_to_specific_page
 *
 * Redirection for not logged users
 */
add_action( 'template_redirect', 'wegb_redirect_to_specific_page' );

function wegb_redirect_to_specific_page() {

	if ( ( is_page( WEGW_B2B_AD_LISTING ) || is_page( WEGW_B2B_DASHBOARD ) || is_page( WEGW_B2B_AD_CREATE ) || is_page( WEGW_B2B_PROFILE ) || is_page( WEGW_B2B_CREDIT_PURCHASE_PAGE ) ) && ! is_user_logged_in() ) {

		// wp_safe_redirect( site_url() . '/b2b-portal/?msg=login', 301 );
		wp_safe_redirect( site_url( WEGW_B2B_LOGIN_PAGE ), 301 );
		exit;
	}

	/* If user logged in and visiting 'WEGW_B2B_LOGIN_PAGE' redirect to Dashboard*/
	if ( is_page( WEGW_B2B_LOGIN_PAGE ) && is_user_logged_in() ) {
		wp_safe_redirect( site_url( WEGW_B2B_DASHBOARD ), 301 );
		exit;
	}

	/* If user not completed profile,  redirect to profile page*/

	$b2b_profile_fields = get_b2b_profile_fields();

	if ( empty( $b2b_profile_fields['gender'] ) || empty( $b2b_profile_fields['firstname'] ) || empty( $b2b_profile_fields['lastname'] ) || empty( $b2b_profile_fields['address'] ) || empty( $b2b_profile_fields['ort'] ) || empty( $b2b_profile_fields['plz'] ) || empty( $b2b_profile_fields['phonenumber'] ) || empty( $b2b_profile_fields['email'] ) ) {
		if ( is_page( 'angebote-erfassen' ) || is_page( 'status-angebote' ) || is_page( 'weitere-werbemoglichkeiten' ) || is_page( 'credits-kaufen' ) ) {
			wp_safe_redirect( site_url() . '/profil', 301 );
			exit;
		}
	}
}

/*
 * wegb_lost_password_redirect
 *
 * Redirection after forgot password
 */
add_action( 'login_headerurl', 'wegb_lost_password_redirect' );

function wegb_lost_password_redirect() {
	if ( strpos( wp_get_referer(), 'b2b' ) !== false ) {
		// Check if have submitted
		$confirm = ( isset( $_GET['checkemail'] ) ? $_GET['checkemail'] : '' );

		if ( $confirm ) {
			// wp_safe_redirect( site_url() . '/b2b-login/?reset-link-sent=true&', 301 );
			wp_safe_redirect( site_url( WEGW_B2B_LOGIN_PAGE . '/?reset-link-sent=true&' ), 301 );
			exit;
		}
		$frmreg_msg = ( isset( $_GET['frmreg_msg'] ) ? $_GET['frmreg_msg'] : '' );
		if ( 'clicked' === $frmreg_msg ) {
			// wp_safe_redirect( site_url() . '/b2b-login/?frmreg_msg=clicked', 301 );
			wp_safe_redirect( site_url( WEGW_B2B_LOGIN_PAGE . '/?frmreg_msg=clicked' ), 301 );
			exit;
		}
	}
}

add_action( 'lostpassword_post', 'wegb_lost_password_validation' );

function wegb_lost_password_validation( $errors ) {
	if ( strpos( wp_get_referer(), 'b2b' ) !== false ) {
		if ( isset( $_POST['user_login'] ) ) {
				$mailid      = $_POST['user_login'];
				$mail_exists = email_exists( $mailid );
				// $send_flag   = ( $mail_exists ) ? 'true' : 'false';
			// wp_safe_redirect( site_url() . '/b2b-login/?reset-link-sent=' . $send_flag . '&', 301 );
			if ( $mail_exists ) {
				wp_safe_redirect( site_url() . '/wp-login.php?checkemail=confirm', 301 );
			} else {
				// wp_safe_redirect( site_url() . '/b2b-login/?reset-link-sent=false', 301 );
				wp_safe_redirect( site_url( WEGW_B2B_LOGIN_PAGE . '/?reset-link-sent=false' ), 301 );
				exit;
			}
		}
	}
}

/*
 * wegb_reset_password_redirect
 *
 * Redirection after password reset
 */
add_action( 'after_password_reset', 'wegb_reset_password_redirect', 10, 2 );

function wegb_reset_password_redirect( $user, $new_pass ) {
	if ( in_array( 'summit-book-user', $user->roles ) ) {
		return;
	}
	// wp_safe_redirect( site_url() . '/b2b-login/?password-reset=true', 301 );
	wp_safe_redirect( site_url( WEGW_B2B_LOGIN_PAGE . '/?password-reset=true' ), 301 );
	exit;
}

/*
 * wegwb_get_user_ip
 *
 * Get user IP
 */
function wegwb_get_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		/* Check ip from share internet */
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		/* To check ip is pass from proxy */
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/*
 * wegwb_b2b_holiday_datepicker_customizations
 *
 * ACF Holiday date settings - Date picker customizations
 */
add_action( 'acf/input/admin_footer', 'wegwb_b2b_holiday_datepicker_customizations' );

function wegwb_b2b_holiday_datepicker_customizations() { ?>
	<script type="text/javascript">
		(function($) {
			var arrDisabledDates = {};
			arrDisabledDates[new Date()] = new Date();
			acf.add_filter('date_picker_args', function( args, $field ){
				args['minDate']             = '0';
				return args;
			});
		})(jQuery);
	</script>
	<?php
}

/*
 * wegwb_profile_edit_submit
 *
 * Avatar image update
 */
add_action( 'frm_after_create_entry', 'wegwb_profile_edit_submit', 30, 2 );

add_action( 'frm_after_update_entry', 'wegwb_profile_edit_submit', 30, 2 );

function wegwb_profile_edit_submit( $entry_id, $form_id ) {
	$edit_profile_form_id = FrmForm::get_id_by_key( 'edit-user-profile' );
	if ( $form_id == $edit_profile_form_id ) { // replace 5 with the id of the form

		$user_ID                            = get_current_user_id();
		$current_user                       = get_user_by( 'id', $user_ID );
		$edit_profile_password_field_id     = FrmField::get_id_by_key( 'b2b_prof_frm_user_pwd' );
		$edit_profile_hidden_email_field_id = FrmField::get_id_by_key( 'hidden_email' );
		$edit_profile_email_field_id        = FrmField::get_id_by_key( 'b2b_prof_frm_user_email' );
		if ( ( isset( $_POST['item_meta'][ $edit_profile_password_field_id ] ) && $_POST['item_meta'][ $edit_profile_password_field_id ] != '' ) && ( isset( $_POST['item_meta'][ $edit_profile_hidden_email_field_id ] ) && $_POST['item_meta'][ $edit_profile_hidden_email_field_id ] != '' ) ) {
			wp_update_user(
				array(
					'ID'         => $user_ID,
					'user_login' => $_POST['item_meta'][ $edit_profile_email_field_id ],
				)
			);
			wp_logout();
			// wp_safe_redirect( site_url() . '/b2b-login/?password-email-reset=true', 301 );
			wp_safe_redirect( site_url( WEGW_B2B_LOGIN_PAGE ) . '/?password-email-reset=true', 301 );
			exit;

		}
		if ( isset( $_POST['item_meta'][ $edit_profile_password_field_id ] ) && $_POST['item_meta'][ $edit_profile_password_field_id ] != '' ) {

			wp_logout();
			wp_safe_redirect( site_url( WEGW_B2B_LOGIN_PAGE ) . '/?password-reset=true', 301 );
			exit;

		}
		if ( isset( $_POST['item_meta'][ $edit_profile_hidden_email_field_id ] ) && $_POST['item_meta'][ $edit_profile_hidden_email_field_id ] != '') {
			wp_update_user(
				array(
					'ID'         => $user_ID,
					'user_login' => $_POST['item_meta'][ $edit_profile_email_field_id ],
				)
			);
			wp_logout();
			// wp_safe_redirect( site_url() . '/b2b-login/?email-reset=true', 301 );
			wp_safe_redirect( site_url( WEGW_B2B_LOGIN_PAGE ) . '/?email-reset=true', 301 );
			exit;
		}
		$edit_profile_avatar_field_id = FrmField::get_id_by_key( 'hi9zl' );
		if ( isset( $_POST['item_meta'][ $edit_profile_avatar_field_id ] ) && $_POST['item_meta'][ $edit_profile_avatar_field_id ] == '' ) {
			update_user_meta( $user_ID, 'frm_avatar_id', 5698 );
		}
	}
}

function send_email_ad_expiry( $user_email, $message ) {
	$name  = 'test';
	$email = get_option( 'admin_email' );
	// $message = 'Your ad is going to expire';

	// php mailer variables
	$to      = $user_email;
	$subject = __( 'Ad expiry notification', 'wegwandern' );
	$headers = 'From: ' . $email . "\r\n" .
	'Reply-To: ' . $email . "\r\n";

	 $sent = wp_mail( $to, $subject, strip_tags( $message ), $headers );

	if ( $sent ) {
		// message sent!
		echo 'sent';
	} else {
		// message wasn't sent
		echo 'not sent';
	}
}

/*
 * Filter to change `Password Hint` for Forgot password section (Formidable)
 */
add_filter(
	'password_hint',
	function( $hint ) {
		return __( 'Hinweis: Das Passwort sollte mindestens zwölf Zeichen lang sein. Um es stärker zu machen, verwenden Sie Gross - und Kleinbuchstaben, Zahlen und Symbole wie ! " ? $ % ^ & ).' );
	}
);

/*
 * wegwb_b2b_add_custom_role_to_authors
 *
 * Add custom user role 'B2B User' to authors dropdown
 */
add_filter( 'rest_user_query', 'wegwb_b2b_add_custom_role_to_authors', 10, 2 );

function wegwb_b2b_add_custom_role_to_authors( $prepared_args, $request ) {
	$prepared_args['who'] = array( 'b2b-user' );
	return $prepared_args;
}

/*
 * wegwb_b2b_check_user_role_access
 *
 * Check if the logged in user have access/user is in role 'B2B User'
 */
function wegwb_b2b_check_user_role_access() {
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();

		/*
		  * Check if 'Admin' or 'B2B User' role
		*/
		if ( user_can( $current_user, 'administrator' ) || in_array( 'b2b-user', (array) $current_user->roles ) ) {
			return true;
		} else {
			echo '<main class="b2b-user-role-section"><div class="b2b-user-role-container container">';
			echo '<h3>' . __('Login B2B Portal', 'wegwandern-summit-book') . '</h3>';
			echo '<p>' . __( 'Sie sind registrierter Summit-Book-Nutzer und möchten sich auch als B2B-Nutzer registrieren?', 'wegw-b2b' ) . '</p>';
			echo '<div class="b2b-userRoleBtn">';
			echo '<a class="b2b-user-role-cancel" href="' . home_url() . '">';
			echo __( 'Abbrechen', 'wegwandern-summit-book' );
			echo '</a>';
			echo '<button onclick="summitUserRoleSubmit()" class="b2b-user-role-submit">';
			echo __( 'Ja', 'wegwandern-summit-book' );
			echo '</button>';
			echo '</div>';
			echo '</div></main>';
			get_footer();
			die();
		}
	}
}

/*
 * wegwb_b2b_register_frm_ajax_load_styles
 *
 * Formidable B2B auth section scripts
 */
add_filter( 'frm_ajax_load_styles', 'wegwb_b2b_register_frm_ajax_load_styles' );

function wegwb_b2b_register_frm_ajax_load_styles( $styles ) {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			/* Hide B2b form if success message comes after registration */
			if ( $("#form_user-registration .frm_message span").hasClass("b2b_reg_success_msg") ) {
				$('#form_user-registration .frm_form_fields').addClass(' hide');
			}
		});
	</script>
	<?php
	return $styles;
}

add_filter( 'body_class', 'wegw_b2b_add_custom_body_class' );

/**
 * Add custom class to b2b portal page
 *
 * @param array $classes array of classes.
 */
function wegw_b2b_add_custom_body_class( $classes ) {
	if ( is_page( 'b2b-portal' ) ) {
		$classes[] = 'home';
		if ( in_array( 'TopAd', $classes ) ) {
			$key = array_search( 'TopAd', $classes );
			if ( $key != false ) {
				unset( $classes[ $key ] );
			}
		}
	}
	return $classes;
}

add_filter( 'document_title_parts', 'wegw_b2b_custom_title' );

function wegw_b2b_custom_title( $title ) {
	if ( is_post_type_archive( 'b2b-werbung' ) ) {
		$title['title'] = __( 'Angebote', 'wegwandern' );
	}
	return $title;
}

/* Add featured image size for B2B slider */
if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'b2b-slider-listing', 450, 300, true );
}
