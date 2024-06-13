<?php
/* B2B login*/
function wegw_b2b_login_callback() {
	$error_msg   = '';
	$success_msg = '';
	// 'failed' === $_GET['login']

	if ( isset( $_GET['frmreg_msg'] ) && str_contains( $_GET['frmreg_msg'], 'clicked' ) ) {

		$success_msg = esc_html__( 'Die Aktivierung Ihres Kontos ist abgeschlossen! Bitte melden Sie sich hier an!', 'wegw-b2b' );

	}
	if ( ( isset( $_GET['frm_message'] ) && 'complete' === $_GET['frm_message'] ) && ( isset( $_GET['user'] ) && '' != $_GET['user'] ) ) {
		$success_msg = esc_html__( 'Die Aktivierung Ihres Kontos ist abgeschlossen! Bitte melden Sie sich hier an!', 'wegw-b2b' );

	}

	if ( isset( $_GET['reset-link-sent'] ) && 'true' === $_GET['reset-link-sent'] ) {
		$success_msg = esc_html__( 'Die E-Mail zum Zurücksetzen des Passworts wurde gesendet. Bitte überprüfen Sie Ihren Posteingang.', 'wegw-b2b' );

	}
	if ( isset( $_GET['reset-link-sent'] ) && 'false' === $_GET['reset-link-sent'] ) {
		$reset_err_msg = esc_html__( 'Es gibt kein Konto mit diesem Benutzernamen oder dieser E-Mail-Adresse.', 'wegw-b2b' );

	}
	if ( isset( $_GET['password-reset'] ) && 'true' === $_GET['password-reset'] ) {
		$success_msg = esc_html__( 'Ihr Passwort wurde erfolgreich zurückgesetzt. Bitte loggen Sie sich ein..', 'wegw-b2b' );

	}
	if ( isset( $_GET['email-reset'] ) && 'true' === $_GET['email-reset'] ) {
		$success_msg = esc_html__( 'Ihre E-Mail wurde erfolgreich zurückgesetzt. Bitte melden Sie sich an...', 'wegw-b2b' );

	}
	if ( isset( $_GET['password-email-reset'] ) && 'true' === $_GET['password-email-reset'] ) {
		$success_msg = esc_html__( 'Ihre E-Mail und Ihr Passwort wurden erfolgreich zurückgesetzt. Bitte melden Sie sich erneut an...', 'wegw-b2b' );

	}
	if ( ( isset( $_GET['login'] ) && str_contains( $_GET['login'], 'failed' ) ) || str_contains( $_SERVER['REQUEST_URI'], '?login=failed' ) ) {

		$error_msg   = esc_html__( 'Der Benutzername oder das Passwort ist falsch. Bitte versuchen Sie es erneut.', 'wegw-b2b' );
		$success_msg = '';
	}
	?>

<div class="overlay hide"></div>

<?php }

add_action( 'b2b_login_reg_init', 'wegw_b2b_login_callback' );

function wegw_b2b_user_avatar_callback() {

	$user_ID      = get_current_user_id();
	$avatar_array = get_user_meta( $user_ID, 'frm_avatar_id' );

	if ( $user_ID ) {
		echo '<div class="usr-avatar default-avatar" onclick="openLogoutMenu()"></div>';
	} else {
		echo '<div class="login" onclick=""></div>';
	}
}

add_action( 'b2b_user_avatar', 'wegw_b2b_user_avatar_callback' );
?>
