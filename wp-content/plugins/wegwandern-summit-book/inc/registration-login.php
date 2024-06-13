<?php
/**
 * All Codes for Registration and Login of summit book
 *
 * @package wegwandern-summit-book
 */

add_action( 'wp_head', 'add_login_registration_forms' );

/**
 * Add login and registration forms
 */
function add_login_registration_forms() {
	$user = wp_get_current_user();
	if ( ! is_user_logged_in() && ! ( is_page( 'b2b-portal' ) )) {
		$error_msg     = '';
		$success_msg   = '';
		$reset_err_msg = '';

		if ( isset( $_GET['frmreg_msg'] ) && str_contains( $_GET['frmreg_msg'], 'clicked' ) ) {
			// Activation complete message.
			$success_msg = esc_html__( 'Die Aktivierung Ihres Kontos ist abgeschlossen! Bitte melden Sie sich hier an!', 'wegwandern-summit-book' );
		}
		if ( ( isset( $_GET['frm_message'] ) && 'complete' === $_GET['frm_message'] ) && ( isset( $_GET['user'] ) && '' != $_GET['user'] ) ) {
			// Activation complete message.
			$success_msg = esc_html__( 'Die Aktivierung Ihres Kontos ist abgeschlossen! Bitte melden Sie sich hier an!', 'wegwandern-summit-book' );
		}
		if ( isset( $_GET['reset-link-sent'] ) && 'true' === $_GET['reset-link-sent'] ) {
			// Password reset mail sent.
			$success_msg = esc_html__( 'Die E-Mail zum Zurücksetzen des Passworts wurde gesendet. Bitte überprüfen Sie Ihren Posteingang.', 'wegwandern-summit-book' );
		}
		if ( isset( $_GET['reset-link-sent'] ) && 'false' === $_GET['reset-link-sent'] ) {
			// No account with this email or username.
			$reset_err_msg = esc_html__( 'Es gibt kein Konto mit diesem Benutzernamen oder dieser E-Mail-Adresse.', 'wegwandern-summit-book' );
		}
		if ( isset( $_GET['password-reset'] ) && 'true' === $_GET['password-reset'] ) {
			// Password is successfully reset.
			$success_msg = esc_html__( 'Ihr Passwort wurde erfolgreich zurückgesetzt. Bitte loggen Sie sich ein..', 'wegwandern-summit-book' );
		}
		if ( isset( $_GET['email-reset'] ) && 'true' === $_GET['email-reset'] ) {
			// Email is successfully reset.
			$success_msg = esc_html__( 'Ihre E-Mail wurde erfolgreich zurückgesetzt. Bitte melden Sie sich an...', 'wegwandern-summit-book' );
		}
		if ( isset( $_GET['password-email-reset'] ) && 'true' === $_GET['password-email-reset'] ) {
			// Email and password reset.
			$success_msg = esc_html__( 'Ihre E-Mail und Ihr Passwort wurden erfolgreich zurückgesetzt. Bitte melden Sie sich erneut an...', 'wegwandern-summit-book' );
		}
		if ( ( isset( $_GET['login'] ) && str_contains( $_GET['login'], 'failed' ) ) || str_contains( $_SERVER['REQUEST_URI'], '?login=failed' ) ) {
			// Username or password is incorrect.
			$error_msg   = esc_html__( 'Der Benutzername oder das Passwort ist falsch. Bitte versuchen Sie es erneut.', 'wegwandern-summit-book' );
			$success_msg = '';
		}
		if ( ( isset( $_GET['authentication'] ) && str_contains( $_GET['authentication'], 'failed' ) ) || str_contains( $_SERVER['REQUEST_URI'], 'authentication=failed' ) ) {
			// User role not summit-book-user.
			$error_msg   = esc_html__( 'Sie sind nicht berechtigt, sich hier einzuloggen.', 'wegwandern-summit-book' );
			$success_msg = '';
		}
		if ( ( isset( $_GET['activation'] ) && str_contains( $_GET['activation'], 'pending' ) ) || str_contains( $_SERVER['REQUEST_URI'], 'activation=pending' ) ) {
			// User role not summit-book-user.
			$error_msg   = esc_html__( 'Dein Benutzerkonto ist noch nicht aktiviert.', 'wegwandern-summit-book' );
			$success_msg = '';
		}
		?>
			<div class="summitLoginMenu summitLoginWindow">
				<div class="login_content_wrapper">
					<div class="summit-error-msg"><?php echo $error_msg; ?></div>
					<div class="summit-success-msg"><?php echo $success_msg; ?></div>
					<div class="login_content_title">
						<h3><?php echo esc_html__( 'Login Gipfelbuch', 'wegwandern-summit-book' ); ?></h3>
						<div class="close_warap">
							<span class="filter_close" onclick="closeSummitLoginContent()"></span>
						</div>     
					</div>
					<?php echo do_shortcode( "[frm-login form_id='summit-book-login' show_labels='0' username_placeholder='E-Mail-Adresse' password_placeholder='Passwort' label_remember='Eingeloggt bleiben' remember='1' show_lost_password='1' label_log_in='Login' class_submit='summit-login-submit' label_lost_password='Passwort vergessen' redirect='" . PROFILE_PAGE_URL . "']" ); ?>
					<h3><?php echo esc_html__( 'Noch kein Gratis-Account?', 'wegwandern-summit-book' ); ?></h3>
					<div class="create_account" onclick="openRegPoppup('summitRegMenu')"><?php echo esc_html__( 'Account erstellen', 'wegwandern-summit-book' ); ?></div>
					<div class="create-account-desc"><p><?php echo esc_html__( 'Privatsphäre und Datenschutz ist uns wichtig. Wir geben keine Informationen weiter.', 'wegwandern-summit-book' ); ?></p></div>

				</div>
			</div>
		<div class="summit-reg">
			<?php
			$summit_book_registration_form = FrmForm::get_id_by_key( 'user-registration-summit-book' );
			?>
			<div class="summitRegMenu regWindow hide">
				<div class="close_warap"><span class="filter_close" onclick="closeReg()"></span></div>
				<h3><?php echo __( 'Erstelle einen Gratis-Account', 'wegwandern-summit-book' ); ?></h3>
				<?php echo do_shortcode( "[formidable id=$summit_book_registration_form]" ); ?>
			</div>
		</div>
		<div class="summitResetMenu resetWindow hide">
			<div class="close_warap"><span class="filter_close" onclick="closeSummitResetReg()"></span></div>
			<div class="error-msg"><?php echo $reset_err_msg; ?></div>

			<?php echo do_shortcode( "[frm-reset-password show_labels='0' lostpass_button='Neues Passwort anfordern' resetpass_button='Passwort zurücksetzen' class='reset-frm']" ); ?>
		</div>
		<?php
	} else {
		if ( in_array( SUMMIT_BOOK_USER_ROLE, (array) $user->roles, true ) ) {
			?>
			<div class="user-avatar-shortcode">
				<?php
				echo get_user_avatar();
				?>
			</div>
			<div class="userNavigationMenu userNavigationWindow">
				<div class="userNavigationContentWrapper">
					<div class="userNavigationContentInnerWrapper">
					<?php
					$name_of_user = get_user_display_name();
					if ( $name_of_user ) {
						echo '<h3>' . $name_of_user . '</h3>';
					}
					echo '<div class="close_warap"><span class="filter_close" onclick="closeNavigationMenu()"></span></div></div>';
					echo do_shortcode( "[display-summit-book-user-menu orientation='vertical']" );
					$logout_link = wp_logout_url( home_url() );
					?>
					<div class='summit-book-logout-menu'>
						<ul>
							<li><a class='link' href='<?php echo $logout_link; ?>'><?php echo __( 'Logout', 'wegwandern-summit-book' ); ?></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="user-confirmation hide">
				<div class="summit-delete-confirm user-confirmation-popup">
					<div class="summit-confirm_wrap"> 
						<h4 class="summit-delete-title">Wanderung löschen?</h4>
						<p class="summit-delete-content">Bist du sicher, dass du diese Wanderung löschen möchtest?</p>
						<div class="summit-confirmDeleteBtn">
							<input type="hidden" class="summit-delete-type">
							<button onclick="summitCloseDeletePopup()" id="confirm-cancel-button"><?php echo __( 'Abbrechen' ); ?></button>
							<button class="summit-delete-edit-submit" data-id="" id="confirm-submit-button"><?php echo __( 'Ja, löschen', 'wegwandern-summit-book' ); ?></button>
							<img id="wegw-confirmation-loader" class="frm_ajax_loading" src="<?php echo get_site_url(); ?>/wp-content/plugins/formidable/images/ajax_loader.gif" alt="Sending">
						</div>
					</div>
				</div>
			</div>
			<?php
		} elseif ( in_array( B2B_USER_ROLE, (array) $user->roles, true ) ) {
			?>
			<div class="userRolePopup userRoleWindow hide">
				<div class="user_role_content_wrapper">
					<div class="user_role_content_title">
						<h3><?php echo esc_html__( 'Login Gipfelbuch', 'wegwandern-summit-book' ); ?></h3>
						<div class="close_warap">
							<span class="filter_close" onclick="closeUserRolePopup()"></span>
						</div>     
					</div>
					<p class="summit-user-role-content"><?php echo __( 'Sie sind registrierter B2B User, möchten Sie sich auch als Gipfelbuch User registrieren?', 'wegwandern-summit-book' ); ?></p>
					<p class="summit-user-role-terms-accept"><?php echo __( 'Indem Sie auf Ja klicken, akzeptieren Sie auch die ', 'wegwandern-summit-book' ) . '<a href="' . TERMS_OF_USE_URL . '" target="_blank">Nutzungsbedingungen</a>'; ?></p>
					<div class="summit-userRoleBtn">
						<button onclick="closeUserRolePopup()"><?php echo __( 'Abbrechen', 'wegwandern-summit-book' ); ?></button>
						<button onclick="summitUserRoleSubmit()" class="summit-user-role-submit" data-id=""><?php echo __( 'Ja', 'wegwandern-summit-book' ); ?></button>
					</div>
				</div>
			</div>
			<?php
		}
	}
}

add_action( 'wp_ajax_wegwandern_summit_book_add_b2b_user_role_action', 'wegwandern_summit_book_add_b2b_user_role_action' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_add_b2b_user_role_action', 'wegwandern_summit_book_add_b2b_user_role_action' );

/**
 * Ajax function to bring login or registration form
 */
function wegwandern_summit_book_add_b2b_user_role_action() {
	if ( is_user_logged_in() ) {
		$logged_in_user = wp_get_current_user();
		if ( in_array( B2B_USER_ROLE, $logged_in_user->roles ) ) {
			$logged_in_user->add_role( SUMMIT_BOOK_USER_ROLE );
			update_user_meta( $logged_in_user->ID, 'accept_terms_and_privacy', array( 'accept_terms_and_privacy' ) );
		} elseif ( in_array( SUMMIT_BOOK_USER_ROLE, $logged_in_user->roles ) ) {
			$logged_in_user->add_role( B2B_USER_ROLE );
		}
		$output['result'] = 'roleadded';
	} else {
		$output['result'] = 'notloggedin';
	}
	echo wp_json_encode( $output );
	wp_die();
}

add_action( 'wp_ajax_wegwandern_summit_book_login_action', 'wegwandern_summit_book_login_action' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_login_action', 'wegwandern_summit_book_login_action' );

/**
 * Ajax function to bring login or registration form
 */
function wegwandern_summit_book_login_action() {
	if ( is_user_logged_in() ) {
		$output['result'] = 'loggedin';
	} else {
		$output['result'] = 'notloggedin';
	}
	echo wp_json_encode( $output );
	wp_die();
}

add_action( 'wp_ajax_wegwandern_summit_book_login_authenticate_action', 'wegwandern_summit_book_login_authenticate_action' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_login_authenticate_action', 'wegwandern_summit_book_login_authenticate_action' );

/**
 * Check if the username and passowrd is correct
 */
function wegwandern_summit_book_login_authenticate_action() {
	$request = file_get_contents( 'php://input' );
	parse_str( $request, $post_array );
	$user = get_user_by( 'login', $post_array['userName'] );
	if ( $user && wp_check_password( $post_array['pass'], $user->data->user_pass, $user->ID ) ) {
		$result['result'] = 'success';
	} else {
		$result['result'] = 'failure';
	}
	echo wp_json_encode( $result );
	wp_die();
}

add_filter( 'login_redirect', 'check_profile_completion', 10, 3 );

/**
 * If user's profile is not completed, redirect to edit profile, else redirect to dashboard
 * If user is logging in to watchlist a hike, redirect to requested page after login
 *
 * @param string $redirect_to the original redirect url.
 * @param string $request url the user is coming from.
 * @param object $user logged user's data.
 */
function check_profile_completion( $redirect_to, $request, $user ) {
	if ( isset( $user->roles ) && is_array( $user->roles ) && in_array( SUMMIT_BOOK_USER_ROLE, $user->roles ) ) {
		$profile_completion = get_user_meta( $user->ID, 'profile_completion', true );
		if ( strpos( $request, 'watchlist-hike' ) !== false ) {
			$hike_id = substr( $request, strpos( $request, '=' ) + 1 );
			watchlist_hike( $hike_id, $user->ID );
		}
		if ( 'yes' !== $profile_completion ) {
			$profile_page = PROFILE_PAGE_URL;
			return $profile_page;
		} elseif ( strpos( $request, 'watchlist-hike' ) !== false ) {
			return $redirect_to;
		} elseif ( strpos( $request, 'kommentar-login' ) !== false ) {
			return $redirect_to;
		} else {
			$dashboard_page = DASHBOARD_PAGE_URL;
			return $dashboard_page;
		}
	} else {
		return $redirect_to;
	}
}

/**
 * Hide admin bar for user roles other than admin and contributor
 */
function summit_book_disable_admin_bar() {
	if ( current_user_can( 'administrator' ) || current_user_can( 'contributor' ) ) {
		// user can view admin bar.
		show_admin_bar( true );
	} else {
		// hide admin bar.
		show_admin_bar( false );
	}
}
add_action( 'after_setup_theme', 'summit_book_disable_admin_bar' );

/**
 * Redirect user when not logged in and tries to access dashboard or other pages
 */
function summit_book_not_logged_in_redirect() {
	$user = wp_get_current_user();
	if ( ( is_page( 'gipfelbuch-dashboard' ) || is_page( 'gipfelbuch-profil-bearbeiten' ) || is_page( 'inserat-erstellen' ) || is_page( 'neue-tour-posten' ) ) && ( ! is_user_logged_in() || $user->ID <= 0 || ! in_array( SUMMIT_BOOK_USER_ROLE, $user->roles ) ) ) {
		wp_safe_redirect( home_url() );
		exit();
	}
}
add_action( 'template_redirect', 'summit_book_not_logged_in_redirect' );

/**
 * Display the user icon in header
 */
function display_user_avatar_header() {
	$current_user = wp_get_current_user();
	if ( is_page( 'b2b-portal' ) ) {
		echo '';
	} elseif ( is_user_logged_in() && in_array( SUMMIT_BOOK_USER_ROLE, $current_user->roles ) ) {
		echo '<div class="usr-avatar" onclick="openSummitBookNavigation()"></div>';
	} elseif ( is_user_logged_in() && in_array( B2B_USER_ROLE, $current_user->roles ) ) {
		echo '<div class="login" onclick="openUserRolePopup()"></div>';
	} elseif ( is_user_logged_in() && ! in_array( SUMMIT_BOOK_USER_ROLE, $current_user->roles ) && ! in_array( B2B_USER_ROLE, $current_user->roles ) ) {
		echo '<div class="login"></div>';
	} else {
		echo '<div class="login" onclick="openSummitBookLoginMenu()"></div>';
	}
}

add_action( 'init', 'custom_redirect_after_user_activation' );

/**
 * Redirect user after activation to home page
 */
function custom_redirect_after_user_activation() {
	global $pagenow;
	$frm_message = ( isset( $_GET['frm_message'] ) ) ? $_GET['frm_message'] : '';
	if ( $frm_message == '' ) {
		return false;
	}
	if ( 'wp-login.php' == $pagenow && $frm_message == 'complete' ) {
		$user = get_user_by( 'id', $_GET['user'] );
		if ( $user && in_array( SUMMIT_BOOK_USER_ROLE, $user->roles ) ) {
			wp_safe_redirect( home_url() . '?frm_message=complete&user=' . $_GET['user'] . '&summit_book_user_activation=yes' );
			exit();
		}
	}
}

add_action( 'wp_login_failed', 'check_summit_book_login_failed', 10, 2 );

/**
 * Check if the login is from summit book
 *
 * @param string   $username username.
 * @param WP_Error $error error in login.
 */
function check_summit_book_login_failed( $username, $error ) {
	if ( isset( $_POST['redirect_to'] ) && ( strpos( $_POST['redirect_to'], 'gipfelbuch' ) !== false || strpos( $_POST['redirect_to'], 'watchlist-hikeId' ) !== false ) ) {
		$redirect_from = wp_get_referer();
		if ( strpos( $redirect_from, '?login=failed' ) !== false ) {
			$redirect_from_array = explode( '?', $redirect_from );
			$redirect_from       = $redirect_from_array[0];
		}
		wp_safe_redirect( $redirect_from . '?login=failed&summit_book_login=yes' );
		exit();
	} elseif ( isset( $_POST['redirect_to'] ) && strpos( $_POST['redirect_to'], 'kommentar-login=yes' ) !== false ) {
		$redirect_from = wp_get_referer();
		if ( strpos( $redirect_from, '?login=failed' ) !== false ) {
			$redirect_from_array = explode( '?', $redirect_from );
			$redirect_from       = $redirect_from_array[0];
		}
		wp_safe_redirect( $redirect_from . '?login=failed&summit_book_comment_login=yes' );
		exit();
	}
}

add_action( 'lostpassword_post', 'summit_book_lost_password_validation' );

/**
 * Check if password reset error from summit book
 */
function summit_book_lost_password_validation() {
	if ( strpos( wp_get_referer(), 'b2b' ) !== false ) {
		return;
	}
	if ( isset( $_POST['user_login'] ) ) {
			$mailid      = $_POST['user_login'];
			$mail_exists = email_exists( $mailid );
		if ( $mail_exists ) {
			wp_safe_redirect( site_url() . '/wp-login.php?checkemail=confirm', 301 );
		} else {
			$corrected_url = summit_book_remove_existing_url_param( wp_get_referer() );
			wp_safe_redirect( $corrected_url . '?reset-link-sent=false&summit_book_login=yes', 301 );
			exit;
		}
	}
}

add_action( 'after_password_reset', 'summit_book_reset_password_redirect', 10, 2 );

/**
 * Redirect user after password reset
 *
 * @param WP_User $user user whose password is reset.
 * @param string  $new_pass new password.
 */
function summit_book_reset_password_redirect( $user, $new_pass ) {
	if ( ! in_array( SUMMIT_BOOK_USER_ROLE, $user->roles ) ) {
		return;
	}
	wp_safe_redirect( home_url() . '?password-reset=true&summit_book_login=yes', 301 );
	exit;
}

add_action( 'login_headerurl', 'summit_book_lost_password_redirect' );

/**
 * Redirect to home after password is reset
 */
function summit_book_lost_password_redirect() {
	if ( strpos( wp_get_referer(), 'b2b' ) === false ) {
		$confirm = ( isset( $_GET['checkemail'] ) ? $_GET['checkemail'] : '' );
		if ( $confirm ) {
			$corrected_url = summit_book_remove_existing_url_param( wp_get_referer() );
			wp_safe_redirect( $corrected_url . '?reset-link-sent=true&summit_book_login=yes', 301 );
			exit;
		}
		$frmreg_msg = ( isset( $_GET['frmreg_msg'] ) ? $_GET['frmreg_msg'] : '' );
		if ( 'clicked' === $frmreg_msg ) {
			wp_safe_redirect( home_url() . '?frmreg_msg=clicked&summit_book_login=yes', 301 );
			exit;
		}
	}
}

add_filter( 'authenticate', 'summit_book_role_check_authentication', 10, 3 );

/**
 * Check if the logging in user has summit book role
 *
 * @param WP_User $user user logging in.
 * @param string  $username username.
 * @param string  $password password.
 */
function summit_book_role_check_authentication( $user, $username, $password ) {
	// if ( isset( $_POST['redirect_to'] ) && ( strpos( $_POST['redirect_to'], 'gipfelbuch' ) === false && strpos( $_POST['redirect_to'], 'kommentar' ) === false ) ) {
	// return $user;
	// }
	$user_check = get_user_by( 'email', $username );
	if ( $user_check && wp_check_password( $password, $user_check->user_pass, $user_check->ID ) ) {
		$corrected_url = summit_book_remove_existing_url_param( wp_get_referer() );
		if ( ! in_array( B2B_USER_ROLE, $user_check->roles ) && ! empty( $_POST['redirect_to'] ) && $_POST['redirect_to'] == '/profil/' ) {
			if ( in_array( 'pending', $user_check->roles ) ) {
				wp_safe_redirect( $corrected_url . '?activation=pending', 301 );
				exit;
			}
			wp_safe_redirect( $corrected_url . '?authentication=failed', 301 );
			exit;
		}
		if ( ! in_array( SUMMIT_BOOK_USER_ROLE, $user_check->roles ) ) {
			$popup_parameter   = '?authentication=failed&summit_book_login=yes';
			$comment_parameter = '?authentication=failed&summit_book_comment_login=yes';

			if ( in_array( 'pending', $user_check->roles ) ) {
				$popup_parameter   = '?activation=pending&summit_book_login=yes';
				$comment_parameter = '?activation=pending&summit_book_comment_login=yes';
			}
			if ( strpos( $_POST['redirect_to'], 'gipfelbuch' ) !== false ) {
				wp_safe_redirect( $corrected_url . $popup_parameter, 301 );
				exit;
			} elseif ( strpos( $_POST['redirect_to'], 'kommentar' ) !== false ) {
				wp_safe_redirect( $corrected_url . $comment_parameter, 301 );
				exit;
			}
		}
	} else {
		$popup_parameter   = '?login=failed&summit_book_login=yes';
		$comment_parameter = '?login=failed&summit_book_comment_login=yes';
		$corrected_url     = summit_book_remove_existing_url_param( wp_get_referer() );
		if ( isset( $_POST['redirect_to'] ) && strpos( $_POST['redirect_to'], 'gipfelbuch' ) !== false ) {
			wp_safe_redirect( $corrected_url . $popup_parameter, 301 );
			exit;
		} elseif ( isset( $_POST['redirect_to'] ) && strpos( $_POST['redirect_to'], 'kommentar' ) !== false ) {
			wp_safe_redirect( $corrected_url . $comment_parameter, 301 );
			exit;
		}
	}
	return $user;
}

/**
 * Remove already existing get parameter from url
 *
 * @param string $url url to redirect to.
 */
function summit_book_remove_existing_url_param( $url ) {
	if ( strpos( $url, '?' ) !== false ) {
		$split_url = explode( '?', $url );
		return $split_url[0];
	}
	return $url;
}
