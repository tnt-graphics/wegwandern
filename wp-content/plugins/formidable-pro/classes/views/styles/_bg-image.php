<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_image_preview_wrapper">
	<input type="hidden" class="frm_image_id" name="<?php echo esc_attr( $frm_style->get_field_name( $image_id_input_name ) ); ?>" value="<?php echo esc_attr( $bg_image_id ); ?>" />
	<div class="frm_image_preview_frame <?php echo 0 === $bg_image_id ? 'frm_hidden' : ''; ?>">
		<div class="frm_image_styling_frame">
			<?php echo $bg_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="frm_image_data">
				<div class="frm_image_preview_title"><?php echo esc_html( $bg_image_filename ); ?></div>
				<div class="frm_remove_image_option" title="<?php esc_attr_e( 'Remove image', 'formidable-pro' ); ?>">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_delete_icon' ); ?>
					<?php esc_attr_e( 'Delete', 'formidable-pro' ); ?>
				</div>
			</div>
		</div>
	</div>
	<button type="button" class="frm_choose_image_box frm-flex-center frm_button frm_no_style_button<?php echo 0 === $bg_image_id ? '' : ' frm_hidden'; ?>">
		<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-upload-icon-2024' ); ?>
		<?php esc_attr_e( 'Upload background image', 'formidable-pro' ); ?>
	</button>
</div>
