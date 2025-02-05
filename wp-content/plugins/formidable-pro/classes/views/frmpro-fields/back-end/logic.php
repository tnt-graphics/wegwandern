<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<a href="javascript:void(0)" id="logic_<?php echo absint( $field['id'] ); ?>" class="frm_add_logic_row frm_add_logic_link frm-collapsed frm-flex-justify <?php
echo ! empty( $field['hide_field'] ) && ( count( $field['hide_field'] ) > 1 || reset( $field['hide_field'] ) != '' ) ? ' frm_hidden' : '';
?>" aria-expanded="false" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'Collapsible Conditional Logic Settings', 'formidable-pro' ); ?>" aria-controls="collapsible-section">
	<?php esc_html_e( 'Conditional Logic', 'formidable-pro' ); ?>
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon', array( 'aria-hidden' => 'true' ) ); ?>
</a>
<div class="frm_logic_rows frm_add_remove<?php echo ! empty( $field['hide_field'] ) && ( count( $field['hide_field'] ) > 1 || reset( $field['hide_field'] ) != '' ) ? '' : ' frm_hidden'; ?>" id="frm_logic_rows_<?php echo absint( $field['id'] ); ?>">
	<h3 aria-expanded="true" tabindex="0" role="button" aria-label="<?php esc_attr_e( 'Collapsible Conditional Logic Settings', 'formidable-pro' ); ?>" aria-controls="collapsible-section">
		<?php esc_html_e( 'Conditional Logic', 'formidable-pro' ); ?>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon', array( 'aria-hidden' => 'true' ) ); ?>
	</h3>
	<div class="frm-collapse-me" role="group">
	<div id="frm_logic_row_<?php echo absint( $field['id'] ); ?>" class="frm-mb-sm">
		<select name="field_options[show_hide_<?php echo absint( $field['id'] ); ?>]" class="auto_width">
			<?php
			if ( 'submit' === $field['type'] ) {
				?>
				<option value="show" <?php selected( $field['show_hide'], 'show' ); ?>><?php esc_html_e( 'Show this button', 'formidable-pro' ); ?></option>
				<option value="hide" <?php selected( $field['show_hide'], 'hide' ); ?>><?php esc_html_e( 'Hide this button', 'formidable-pro' ); ?></option>
				<option value="enable" <?php selected( $field['show_hide'], 'enable' ); ?>><?php esc_html_e( 'Enable this button', 'formidable-pro' ); ?></option>
				<option value="disable" <?php selected( $field['show_hide'], 'disable' ); ?>><?php esc_html_e( 'Disable this button', 'formidable-pro' ); ?></option>
				<?php
			} else {
				?>
				<option value="show" <?php selected( $field['show_hide'], 'show' ); ?>><?php echo $field['type'] === 'break' ? esc_html__( 'Do not skip next page', 'formidable-pro' ) : esc_html__( 'Show', 'formidable-pro' ); ?></option>
				<option value="hide" <?php selected( $field['show_hide'], 'hide' ); ?>><?php echo $field['type'] === 'break' ? esc_html__( 'Skip next page', 'formidable-pro' ) : esc_html__( 'Hide', 'formidable-pro' ); ?></option>
				<?php
			}
			?>
		</select>

		<?php
		$all_select =
			'<select name="field_options[any_all_' . absint( $field['id'] ) . ']" class="auto_width">' .
			'<option value="any" ' . selected( $field['any_all'], 'any', false ) . '>' . __( 'any', 'formidable-pro' ) . '</option>' .
			'<option value="all" ' . selected( $field['any_all'], 'all', false ) . '>' . __( 'all', 'formidable-pro' ) . '</option>' .
			'</select>';

		printf( esc_html__( 'this field, if %s of the following match:', 'formidable-pro' ), '<br>' . $all_select ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		unset( $all_select );

		if ( ! empty( $field['hide_field'] ) ) {
			foreach ( (array) $field['hide_field'] as $meta_name => $hide_field ) {
				include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/_logic_row.php';
			}
		}
		?>
	</div>
	<a href="javascript:void(0)" class="frm_add_logic_row button frm-button-secondary">
		<?php
		FrmProAppHelper::icon_by_class( 'frmfont frm_plus_icon' );
		echo ' ';
		esc_html_e( 'Add', 'formidable-pro' );
		?>
	</a>
	</div>
</div>
