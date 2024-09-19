<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<?php if ( ! empty( $field['slide'] ) || empty( $field['repeat'] ) ) { ?>
<p>
	<label for="frm_slide_field_<?php echo absint( $field['id'] ); ?>" class="frm_inline_label">
		<input type="checkbox" id="frm_slide_field_<?php echo absint( $field['id'] ); ?>" name="field_options[slide_<?php echo absint( $field['id'] ); ?>]" value="1" <?php checked( $field['slide'], 1 ); ?>/>
		<?php esc_html_e( 'Collapsible', 'formidable-pro' ); ?>
		<?php FrmProAppHelper::tooltip_icon( __( 'Collapsible: This section will slide open and closed.', 'formidable-pro' ) ); ?>
	</label>
</p>
<?php } ?>

<input type="hidden" id="frm_repeat_field_<?php echo absint( $field['id'] ); ?>" name="field_options[repeat_<?php echo absint( $field['id'] ); ?>]" class="frm_repeat_field" value="<?php echo esc_attr( $field['repeat'] ); ?>" />
