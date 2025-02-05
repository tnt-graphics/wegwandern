<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p id="frm_watch_lookup_<?php echo esc_attr( $field_id . '_' . $row_key ); ?>" class="frm_single_option frm_no_top_margin">
	<select name="field_options[watch_lookup_<?php echo esc_attr( $field_id ); ?>][]">
		<option value=""><?php esc_html_e( '&mdash; Select Field &mdash;', 'formidable-pro' ); ?></option>
		<?php
		foreach ( $lookup_fields as $field_option ) {
			if ( $field_option->id == $field_id ) {
	            continue;
	        }
			$selected = $field_option->id == $selected_field ? ' selected="selected"' : '';
	    ?>
	    <option value="<?php echo esc_attr( $field_option->id ); ?>"<?php
			echo esc_attr( $selected );
			?>><?php
			echo '' == $field_option->name ? esc_html( $field_option->id ) . ' ' . esc_html__( '(no label)', 'formidable-pro' ) : esc_html( $field_option->name );
	    ?></option>
	    <?php } ?>
	</select>
	<a href="javascript:void(0)" class="frm_remove_tag frm-inline-select" data-removeid="frm_watch_lookup_<?php echo esc_attr( $field_id . '_' . $row_key ); ?>" data-fieldid="<?php echo esc_attr( $field_id ); ?>"><?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_minus1_icon' ); ?></a>
</p>
