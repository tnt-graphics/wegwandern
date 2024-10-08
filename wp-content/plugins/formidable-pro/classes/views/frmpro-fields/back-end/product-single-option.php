<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<li id="frm_delete_field_<?php echo esc_attr( $field['id'] . '-' . $opt_key ); ?>_container" data-optkey="<?php echo esc_attr( $opt_key ); ?>" class="frm_single_option <?php echo $opt_key === '000' ? 'frm_hidden frm_option_template' : ''; ?>">
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_drag_icon frm-drag' ); ?>
	<input type="<?php echo esc_attr( $default_type ); ?>" name="<?php echo esc_attr( $field_name ); ?>" <?php echo ( isset( $checked ) && $checked ? 'checked="checked"' : '' ); ?> value="<?php echo esc_attr( $field_val ); ?>"/>

	<div class="frm_product_price_wrapper">
		<input type="text" name="field_options[options_<?php echo esc_attr( $field['id'] ); ?>][<?php echo esc_attr( $opt_key ); ?>][label]" value="<?php echo esc_attr( $opt ); ?>" class="field_<?php echo esc_attr( $field['id'] ); ?>_option <?php echo esc_attr( $field['separate_value'] ? 'frm_with_key' : '' ); ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>" data-frmchange="trim,updateOption" placeholder="<?php esc_attr_e( 'Product Name', 'formidable-pro' ); ?>" />
		<input type="text" name="field_options[options_<?php echo esc_attr( $field['id'] ); ?>][<?php echo esc_attr( $opt_key ); ?>][price]" value="<?php echo esc_attr( $price ); ?>" class="field_<?php echo esc_attr( $field['id'] ); ?>_option frm_product_price" placeholder="<?php esc_attr_e( 'Price', 'formidable-pro' ); ?>" data-frmchange="trim" />
	</div>

	<a href="javascript:void(0)" class="frm_remove_tag" data-fid="<?php echo esc_attr( $field['id'] ); ?>" data-removeid="frm_delete_field_<?php echo esc_attr( $field['id'] . '-' . $opt_key ); ?>_container" data-removemore="#frm_<?php echo esc_attr( $default_type . '_' . $field['id'] . '-' . $opt_key ); ?>" data-showlast="#frm_add_opt_<?php echo esc_attr( $field['id'] ); ?>"><?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_minus1_icon' ); ?></a>

	<span class="frm_option_key frm-with-right-icon field_<?php echo esc_attr( $field['id'] ); ?>_option_key<?php echo esc_attr( $field['separate_value'] ? '' : ' frm_hidden' ); ?>">
		<input type="<?php echo esc_attr( $default_type ); ?>" class="frm_invisible" />
		<input type="text" name="field_options[options_<?php echo esc_attr( $field['id'] ); ?>][<?php echo esc_attr( $opt_key ); ?>][value]" id="field_key_<?php echo esc_attr( $field['id'] . '-' . $opt_key ); ?>" value="<?php echo esc_attr( $field_val ); ?>" placeholder="<?php esc_attr_e( 'Saved Value', 'formidable-pro' ); ?>" data-frmchange="trim,updateDefault" />
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_save_icon' ); ?>
	</span>
</li>
