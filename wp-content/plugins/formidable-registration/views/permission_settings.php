<h3><?php esc_html_e( 'Permissions', 'frmreg' ); ?></h3>
<table class="form-table">
	<tbody>
		<tr>
			<td colspan="2">
				<?php $id_attr = $this->get_field_id( 'reg_create_users' ); ?>
				<label for="<?php echo esc_attr( $id_attr ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'reg_create_users' ) ); ?>" value="allow" id="<?php echo esc_attr( $id_attr ); ?>" class="frm_reg_create_users" <?php checked( $form_action->post_content['reg_create_users'], 'allow' ); ?> />
					<?php
					esc_html_e( 'Allow logged-in users to create new users with this form', 'frmreg' );
					FrmRegAppController::show_svg_tooltip( __( 'Determine which roles can create new users with this form on the front-end of your site. The selected roles must submit entries on the back-end of your site in order to edit their own profile.', 'frmreg' ) );
					?>
				</label>
			</td>
		</tr>
		<tr class="frm_short_tr" id="reg_create_role_tr" <?php echo $form_action->post_content['reg_create_users'] == 'allow' ? '' : ' style="display:none;"'; ?>>
			<td style="width:275px;padding-top:0;">
				<p class="frm_indent_opt"><?php esc_html_e( 'Role required to create new users:', 'frmreg' ); ?></p>
			</td>
			<td>
				<?php FrmAppHelper::wp_roles_dropdown( $this->get_field_name( 'reg_create_role' ) . '[]', $form_action->post_content['reg_create_role'], 'multiple' ); ?>
			</td>
		</tr>
	</tbody>
</table>
