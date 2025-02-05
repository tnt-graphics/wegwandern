<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( is_callable( array( 'FrmFieldsHelper', 'show_radio_display_format' ) ) ) {
	FrmFieldsHelper::show_radio_display_format( $field );
}
?>
<?php if ( ! isset( $field['image_sizes'] ) || ! empty( $field['image_sizes'] ) ) : ?>
	<p class="frm_form_field frm_image_size_container frm_image_size_<?php echo absint( $field['id'] ); ?> <?php echo esc_attr( empty( $field['image_options'] ) ? ' frm_hidden ' : '' ); ?> ">
		<label for="field_options_image_size_<?php echo absint( $field['id'] ); ?>">
			<?php esc_html_e( 'Image Size', 'formidable-pro' ); ?>
		</label>
		<select name="field_options[image_size_<?php echo absint( $field['id'] ); ?>]" id="field_options_image_size_<?php echo absint( $field['id'] ); ?>" class="frm_field_options_image_size">
			<?php foreach ( $columns as $col => $col_label ) { ?>
				<option value="<?php echo esc_attr( $col ); ?>" <?php selected( $field['image_size'], $col ); ?>>
					<?php echo esc_html( $col_label ); ?>
				</option>
			<?php } ?>
		</select>
	</p>
<?php endif; ?>

<?php if ( ! isset( $field['hide_option_labels'] ) || ! empty( $field['hide_option_labels'] ) ) : ?>
	<p class="frm6 frm_form_field frm_label_with_image_radio frm_toggle_image_options_<?php echo absint( $field['id'] ); ?> <?php echo empty( $field['image_options'] ) ? ' frm_hidden ' : ''; ?>">
		<label for="hide_image_text_<?php echo absint( $field['id'] ); ?>">
			<input type="checkbox" name="field_options[hide_image_text_<?php echo absint( $field['id'] ); ?>]" id="hide_image_text_<?php echo absint( $field['id'] ); ?>" value="1" class="frm_hide_image_text" <?php isset( $field['hide_image_text'] ) ? checked( $field['hide_image_text'], 1 ) : 0; ?> />
			<?php esc_html_e( 'Hide option labels', 'formidable-pro' ); ?>
		</label>
	</p>
<?php endif; ?>
