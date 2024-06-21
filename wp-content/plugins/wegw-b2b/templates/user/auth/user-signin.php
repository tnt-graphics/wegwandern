<?php
get_header('home');
global $post;
$post_thumb          = get_the_post_thumbnail_url( $post->ID, 'full' );
$b2b_login_sub_title = get_field( 'b2b_login_sub_title' );
$b2b_login_title     = get_field( 'b2b_login_title' );
?>

<div class="container-fluid">
	<div class="b2b-teaser-wrap ">
		<img src="<?php echo $post_thumb; ?>" class="b2b-teaser-section">
		<div class="region-img-content-wrap b2b-teaser_info">
			<?php echo get_breadcrumb(); ?>
			<!-- <div class="b2b-sub_title"><?php //echo $b2b_login_sub_title; ?></div> -->
			<h1 class="b2b-title"><?php echo $b2b_login_title; ?></h1>
		</div>
	</div>
</div>
<div class="container">
	<div class="b2b-portal-section">
		<div class="b2b-portal-content-section">
		<?php 
			if ( have_rows( 'page_content__user_not_logged_in' ) ) : 
				while ( have_rows( 'page_content__user_not_logged_in' ) ) :
						the_row();
						$section_1   = get_sub_field( 'content_section_1' );
						$section_2   = get_sub_field( 'content_section_2' ); ?>
						<div class="b2b-portal-content-1">
							<p> <?php echo $section_1; ?> </p>
						</div><?php 
						 echo $section_2;
				endwhile;
			endif; ?>
		</div>
		<div class="b2b-portal-login-section">
			<!-- B2B Login Form Start-->
			<?php
				$error_msg     = '';
				$success_msg   = '';
				$reset_err_msg = '';

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

			<div class="loginMenu fullwidthB2BLogin">
				<div class="login_content_wrapper">
					<div class="error-msg"><?php echo $error_msg; ?></div>
					<div class="success-msg"><?php echo $success_msg; ?></div>
					<div class="login_content_title">
						<h3><?php echo esc_html__( 'Login B2B-Portal', 'wegw-b2b' ); ?></h3>
					</div>

					<?php echo do_shortcode( "[frm-login show_labels='0' username_placeholder='E-Mail-Adresse' password_placeholder='Passwort' label_remember='Eingeloggt bleiben' remember='1' label_log_in='Login'  show_lost_password='1' label_lost_password='Passwort vergessen' class_submit='b2b-login-submit' redirect='/profil/']" ); ?>

					<h3 class="create_account_title"><?php echo esc_html__( 'Noch kein Konto?', 'wegw-b2b' ); ?></h3>
					<div class="create_account" onclick="openRegPoppup('regMenu')">
						<?php echo esc_html__( 'Konto erstellen', 'wegw-b2b' ); ?>
					</div>
					<div class="create-account-desc">
						<p><?php echo esc_html__( 'Privatsphäre und Datenschutz ist uns wichtig. Wir geben keine Informationen weiter.', 'wegw-b2b' ); ?></p>
					</div>
				</div>
			</div>

			<?php if ( is_user_logged_in() ) { ?>
				<div class="logoutMenu logoutWindow">
					<div class="logout_content_wrapper">
						<p><a href="<?php echo wp_logout_url( site_url( WEGW_B2B_LOGIN_PAGE ) ); ?>"><?php echo esc_html__( 'Logout', 'wegwandern' ); ?></a></p>
					</div>
				</div>
			<?php } ?>
			<!-- B2B Login Form End-->

			<div class="regMenu regWindow hide">
				<div class="close_warap"><span class="filter_close" onclick="closeReg()"></span></div>
				<?php
				$b2b_registration_form_id = FrmForm::get_id_by_key( 'user-registration' );
				echo do_shortcode( "[formidable id=$b2b_registration_form_id]" );
				?>
			</div>

			<div class="resetMenu resetWindow hide">
				<div class="close_warap"><span class="filter_close" onclick="closeResetReg()"></span></div>
				<div class="error-msg"><?php echo $reset_err_msg; ?></div>
				<?php echo do_shortcode( "[frm-reset-password show_labels='0' lostpass_button='Neues Passwort anfordern' resetpass_button='Passwort zurücksetzen' class='reset-frm' ]" ); ?>
			</div>
		</div>
	</div>
</div>
<div class="container">
<?php if ( get_field( 'b2b_holiday', 'option' ) ) :
	$b2b_holiday_content_title       = esc_html( get_field( 'b2b_holiday_content_title', 'option' ) );
	$b2b_holiday_content_description = get_field( 'b2b_holiday_content_description', 'option' );
	?>
<div class="holiday_info">
	<div class="holiday_info_title"><?php echo $b2b_holiday_content_title; ?></div>
	<div class="holiday_info_content"> <?php echo $b2b_holiday_content_description; ?></div>
</div>
<?php
	endif; ?>
	</div>
	<?php
	/*
	<div class="container">
	<div class="ppc_container">
		<div class="pay_per_click_wrapper">
			<?php
			if ( have_rows( 'section_1_left' ) ) :
				while ( have_rows( 'section_1_left' ) ) :
					the_row();
					$section_1_left_title   = get_sub_field( 'section_1_left_title' );
					$section_1_left_content = get_sub_field( 'section_1_left_content' );
					?>
					<h3><?php echo $section_1_left_title; ?></h3>
					<div class="ppc_content">
						<?php echo $section_1_left_content; ?>
						<?php
						if ( have_rows( 'credits_price_settings', 'option' ) ) :
							//Loop through rows.
							$i = 1;
							?>

							<div class="klicks_list">
								Preisbeispiele:
								<ul>
								<?php
								while ( have_rows( 'credits_price_settings', 'option' ) ) :
									the_row();
									$b2b_ad_clicks_count = get_sub_field( 'b2b_ad_clicks_count' );
									$b2b_ad_clicks_price = get_sub_field( 'b2b_ad_clicks_price' );
									$min_text            = ( $i == 1 ) ? '(Mindestbuchung)' : '';
									?>

									<li><?php echo $b2b_ad_clicks_count; ?> Klicks = CHF <?php echo $b2b_ad_clicks_price; ?><?php echo $min_text; ?></li>

									<?php
									$i++;
								endwhile;
								?>
								</ul>
							</div>

						<?php endif; ?>
					</div>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>

		<div class="ppc_right_side_wrapper">
			<?php
			if ( have_rows( 'section_1_right' ) ) :
				while ( have_rows( 'section_1_right' ) ) :
					  the_row();
					  $section_1_right_title   = get_sub_field( 'section_1_right_title' );
					  $section_1_right_content = get_sub_field( 'section_1_right_content' );
					?>
					<div class="ppc_right_side_wrapper">
						<h3><?php echo $section_1_right_title; ?></h3>
						<div class="werbeplatzierungen_content"><?php echo $section_1_right_content; ?></div>
					</div>
				<?php endwhile; ?>
			<?php endif; ?>

			<?php
			if ( have_rows( 'section_2_left' ) ) :
				while ( have_rows( 'section_2_left' ) ) :
					the_row();
					$section_2_left_title   = get_sub_field( 'section_2_left_title' );
					$section_2_left_content = get_sub_field( 'section_2_left_content' );
					?>
					<h3><?php echo $section_2_left_title; ?></h3>
					<div class="hinweis_content"><?php echo $section_2_left_content; ?></div>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</div>
	</div>*/
	?>
<?php get_footer(); ?>
