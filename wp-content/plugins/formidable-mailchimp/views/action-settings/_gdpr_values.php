<?php
// GDPR marketing permissions
?>
<div class="frm_mlcmp_group_select frm6">
<?php

$permissions = array();
if ( isset( $new_field ) ) {
	$permissions = FrmMlcmpAppController::get_marketing_permissions( $list_id );
}

foreach ( $permissions as $p ) {
	?>
	<p class="frm_form_field">
		<label>
			Add marketing preference
			<strong><?php echo esc_html( $p['text'] ); ?></strong>
			when field value is
		</label>
		<span class="frm_show_selected_values_gdpr" class="no_taglist">
        <?php
		// Get selected value
		if ( isset( $list_options['gdpr'] ) && isset( $list_options['gdpr'][ $p['marketing_permission_id'] ] ) ) {
			$val = $list_options['gdpr'][ $p['marketing_permission_id'] ];
		} else {
			$val = '';
		}

		$field_name = $action_control->get_field_name( 'gdpr' ) . '[' . $p['marketing_permission_id'] . ']';

		$selector_args = array(
			'html_name' => $field_name,
			'value'     => $val,
			'source'    => 'form_actions',
		);
		FrmFieldsHelper::display_field_value_selector( $new_field->id, $selector_args );

		?>
		</span>
    </p>
	<?php
	unset( $p );
}
?>
	<div class="clear"></div>
</div>
