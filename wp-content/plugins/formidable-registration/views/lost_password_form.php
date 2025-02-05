<?php
/**
 * @since 2.0
 * @var FrmRegLostPWForm $form
 */
?>

<div id="<?php echo esc_attr( $form->get_html_id() ); ?>" class="<?php echo esc_attr( $form->get_class() ); ?>">

	<form id="lostpasswordform_<?php echo esc_attr( $form->get_form_number() ); ?>" action="<?php echo esc_url( site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post" class="frm-show-form">

		<?php if ( count( $form->get_errors() ) > 0 ) : ?>
			<div class="frm_error_style">
				<?php foreach ( $form->get_errors() as $error ) : ?>
					<?php echo FrmAppHelper::kses( $error, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="frm_form_fields">
			<fieldset>

				<div class="frm_description">
					<p><?php echo FrmAppHelper::kses( $form->get_description(), 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				</div>

				<div class="frm_form_field form-field <?php echo esc_attr( $this->label_class ); ?>">
					<label for="<?php echo esc_attr( $form->get_field_id() ); ?>" class="frm_primary_label"><?php esc_html_e( 'Username or Email Address', 'frmreg' ); ?>
						<span class="frm_required">*</span>
					</label>
					<input type="text" id="<?php echo esc_attr( $form->get_field_id() ); ?>" name="user_login">
				</div>

				<?php do_action( 'lostpassword_form' ); ?>

				<div class="frm_submit">
					<input value="<?php echo esc_attr( $form->get_submit_text() ); ?>" type="submit">
				</div>

			</fieldset>
		</div>
	</form>
</div>
