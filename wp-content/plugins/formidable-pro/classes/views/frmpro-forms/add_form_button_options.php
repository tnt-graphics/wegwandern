<?php
/**
 * @deprecated 6.16.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

_deprecated_file( __FILE__, '6.16.3' );

$start_over = isset( $values['start_over'] ) ? $values['start_over'] : '';

if ( $page_field ) {
	?>
	<tr>
		<td colspan="2">
			<?php esc_html_e( 'Previous Button Text setting was moved into the Page break field settings', 'formidable-pro' ); ?>
		</td>
	</tr>
	<?php
}

if ( ! FrmProSubmitHelper::is_available() ) {
	?>
	<tr>
		<td>
			<label for="frm_update_button_text"><?php esc_html_e( 'Update Button Text', 'formidable-pro' ); ?></label>
		</td>
		<td>
			<input id="frm_update_button_text" type="text" name="options[edit_value]" value="<?php echo esc_attr( $values['edit_value'] ); ?>" />
		</td>
	</tr>

	<tr>
		<td>
			<label for="frm_submit_button_alignment"><?php esc_html_e( 'Submit Button Position', 'formidable-pro' ); ?></label>
		</td>
		<td>
			<select id="frm_submit_button_alignment" name="options[submit_align]">
				<option value=""><?php esc_html_e( 'Default', 'formidable-pro' ); ?></option>
				<option value="center" <?php selected( $values['submit_align'], 'center' ); ?>>
					<?php esc_html_e( 'Center', 'formidable-pro' ); ?>
				</option>
				<option value="full" <?php selected( $values['submit_align'], 'full' ); ?>>
					<?php esc_html_e( 'Full Width', 'formidable-pro' ); ?>
				</option>
				<option value="inline" <?php selected( $values['submit_align'], 'inline' ); ?>>
					<?php esc_html_e( 'Inline', 'formidable-pro' ); ?>
				</option>
				<option value="none" <?php selected( $values['submit_align'], 'none' ); ?>>
					<?php esc_html_e( 'None', 'formidable-pro' ); ?>
				</option>
			</select>
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<label class="frm-inline-select">
				<input type="checkbox" id="logic_link_submit" <?php
				echo ! empty( $submit_conditions['hide_field'] ) && ( count( $submit_conditions['hide_field'] ) > 1 || reset( $submit_conditions['hide_field'] ) != '' ) ? ' checked="checked"' : '';
				?> />
				<?php esc_html_e( 'Add conditional logic to submit button', 'formidable-pro' ); ?>
			</label>
			<?php include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/_submit_conditional.php'; ?>
		</td>
	</tr>

	<?php if ( method_exists( 'FrmFormsHelper', 'get_start_over_shortcode' ) ) : ?>
		<tr>
			<td colspan="2">
				<label class="frm-inline-select">
					<input
						type="checkbox"
						id="start_over"
						name="options[start_over]"
						value="1"
						<?php checked( $start_over, 1 ); ?>
						data-toggleclass="frm_start_over_label_wrapper"
					/>
					<?php esc_html_e( 'Add Start over button', 'formidable-pro' ); ?>
				</label>
			</td>
		</tr>

		<tr id="frm_start_over_label_wrapper" class="frm_start_over_label_wrapper <?php echo $start_over ? '' : 'frm_hidden'; ?>">
			<td>
				<label for="frm_start_over_label"><?php esc_html_e( 'Start Over Button Text', 'formidable-pro' ); ?></label>
			</td>
			<td>
				<input id="frm_start_over_label" type="text" name="options[start_over_label]" value="<?php echo esc_attr( empty( $values['start_over_label'] ) ? __( 'Start Over', 'formidable-pro' ) : $values['start_over_label'] ); ?>" />
			</td>
		</tr>
	<?php endif; ?>
	<?php
} else {
	?>
	<tr>
		<td colspan="2">
			<?php
			FrmProFormsHelper::array_to_hidden_inputs(
				array(
					'submit_conditions' => $submit_conditions,
				),
				'options'
			);
			?>
		</td>
	</tr>
	<?php
}
