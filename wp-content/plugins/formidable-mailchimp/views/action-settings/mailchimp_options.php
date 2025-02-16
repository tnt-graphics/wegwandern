<div class="mlchp_list frm_grid_container">
	<p class="frm12 frm_form_field">
		<a href="#" class="frm_clear_mailchimp_cache button frm-button-secondary" >
			<?php esc_html_e( 'Clear Cache', 'frmmlcmp' ); ?>
		</a>
		<span style="float:none" class="frm_clear_mailchimp_cache_spinner spinner"></span>
	</p>
	<p class="frm6">
		<?php if ( $lists && isset( $lists['lists'] ) ) { ?>
		<label><?php esc_html_e( 'List', 'frmmlcmp' ); ?> <span class="frm_required">*</span></label>
		<select name="<?php echo esc_attr( $action_control->get_field_name( 'list_id' ) ); ?>">
			<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
			<?php foreach ( $lists['lists'] as $list ) { ?>
			<option value="<?php echo esc_attr( $list['id'] ); ?>" <?php selected( $list_id, $list['id'] ); ?>>
				<?php echo FrmAppHelper::truncate( $list['name'], 40 ); ?>
			</option>
			<?php } ?>
		</select>
			<?php
		} else {
			esc_html_e( 'No Mailchimp mailing lists found', 'frmmlcmp' );
			if ( isset( $lists['error'] ) ) {
				echo '<br/>' . esc_html( $lists['error'] );
			} elseif ( ! empty( $lists ) && ! is_array( $lists ) ) {
				echo '<br/>' . esc_html( $lists );
			}
		}
		?>
	</p>

<?php
include FrmMlcmpAppHelper::plugin_path() . '/views/action-settings/address_action_setting.php';
if ( isset( $list_fields ) && $list_fields ) {
	include __DIR__ . '/_match_fields.php';
} else {
	?>
<div class="frm_mlcmp_fields"></div>
	<?php
}
?>

</div>
