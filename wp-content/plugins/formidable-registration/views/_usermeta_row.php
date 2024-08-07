<div id="frm_user_meta_<?php echo esc_attr( $meta_key ); ?>" class="frm_user_meta_row frm_grid_container">
	<div class="frm4 frm_form_field">
		<label class="screen-reader-text">
			<?php esc_html_e( 'Name', 'formidable' ); ?>
		</label>
		<input type="text" value="<?php echo esc_attr( ( isset( $echo ) && $echo ) ? $meta_name : '' ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'reg_usermeta' ) ); ?>[<?php echo esc_attr( $meta_key ); ?>][meta_name]"/>
	</div>

	<div class="frm7 frm_form_field">
		<label class="screen-reader-text">
			<?php esc_html_e( 'Value', 'formidable' ); ?>
		</label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'reg_usermeta' ) ); ?>[<?php echo esc_attr( $meta_key ); ?>][field_id]">
			<option value="">- <?php esc_html_e( 'Select Field', 'frmreg' ); ?> -</option>
			<?php
			if ( isset( $fields ) && is_array( $fields ) ) {
				foreach ( $fields as $field ) {
					if ( ! FrmField::is_no_save_field( $field->type ) && FrmRegActionHelper::include_in_user_meta( $field, $field_id ) ) { ?>
						<option value="<?php echo esc_attr( $field->id ); ?>" <?php selected( $field_id, $field->id ); ?>><?php echo esc_html( FrmAppHelper::truncate( $field->name, 50 ) ); ?></option>
						<?php
					}
				}
			}
			?>
		</select>
	</div>
	<div class="frm1 frm_form_field frm-inline-select">
			<a class="frm_remove_tag reg_remove_user_meta_row frm_icon_font" data-removeid="frm_user_meta_<?php echo esc_attr( $meta_key ); ?>" data-showlast="#frm_user_meta_add" data-hidelast="#frm_user_meta_table"></a>
			<a class="frm_add_tag frm_icon_font reg_add_user_meta_row" href="javascript:void(0)"></a>
	</div>
</div>
