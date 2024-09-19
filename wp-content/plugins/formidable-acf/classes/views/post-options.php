<?php
/**
 * Post action options
 *
 * @package FrmAcf
 *
 * @var array $args Args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$acf_enabled = ! empty( $args['form_action']->post_content['acf'] );
$mapping     = ! empty( $args['form_action']->post_content['post_custom_fields'] ) ? (array) $args['form_action']->post_content['post_custom_fields'] : array();
$mapping     = array_filter(
	$mapping,
	function ( $item ) {
		return ! empty( $item['is_acf'] );
	}
);

$child_mapping    = ! empty( $args['form_action']->post_content['acf_child_mapping'] ) ? (array) $args['form_action']->post_content['acf_child_mapping'] : array();
$acf_field_group  = ! empty( $args['form_action']->post_content['acf_field_group'] ) ? $args['form_action']->post_content['acf_field_group'] : '';
$acf_field_groups = FrmAcfAppHelper::get_acf_field_groups();
$js_data          = FrmAcfPostActionHelper::get_form_action_js_data( $args );
?>
<div>
	<?php
	if ( method_exists( 'FrmProHtmlHelper', 'admin_toggle' ) ) {
		$toggle_method = 'admin_toggle';
	} else {
		$toggle_method = 'toggle';
	}

	call_user_func(
		array( 'FrmProHtmlHelper', $toggle_method ),
		'frm_acf',
		$args['action_control']->get_field_name( 'acf' ),
		array(
			'div_class' => 'with_frm_style frm_toggle',
			'checked'   => $acf_enabled,
			'echo'      => true,
			'input_html' => array( 'data-toggleclass' => 'frm_acf_mapping_wrapper' ),
		)
	);
	?>
	<label id="frm_acf_label" for="frm_acf">
		<?php esc_html_e( 'Map form fields to Advanced Custom Fields', 'formidable-acf' ); ?>
	</label>
</div>

<?php
$class = 'frm_acf_mapping_wrapper frm_add_remove';
if ( ! $acf_enabled ) {
	$class .= ' frm_hidden';
} elseif ( $mapping ) {
	$class .= ' frm_acf_mapping_has_field';
}
?>
<div id="frm_acf_mapping_wrapper" class="<?php echo esc_attr( $class ); ?>">
	<p>
		<label for="frm_acf_select_field_group"><?php esc_html_e( 'ACF field group', 'formidable-acf' ); ?></label>
		<select name="<?php echo esc_attr( $args['action_control']->get_field_name( 'acf_field_group' ) ); ?>" id="frm_acf_select_field_group">
			<option value="">&mdash; <?php esc_html_e( 'Select an ACF field group', 'formidable-acf' ); ?> &mdash;</option>
			<?php foreach ( $acf_field_groups as $acf_fg ) : ?>
				<option value="<?php echo esc_attr( $acf_fg['key'] ); ?>" <?php selected( $acf_fg['key'], $acf_field_group ); ?>>
					<?php echo esc_html( $acf_fg['title'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>

	<div id="frm_acf_mapping" class="frm_hidden">
		<div class="frm_grid_container">
			<label class="frm5 frm_first"><?php esc_html_e( 'Formidable Fields', 'formidable-acf' ); ?></label>
			<label class="frm5"><?php esc_html_e( 'Advanced Custom Fields', 'formidable-acf' ); ?></label>
		</div>

		<p id="frm_acf_add_row_wrapper">
			<a href="javascript:void(0)" class="frm_acf_add_row button frm-button-secondary">
				+ <?php esc_html_e( 'Add', 'formidable-acf' ); ?>
			</a>
		</p>
	</div>

	<div id="frm_acf_empty_acf_fields" class="frm_hidden">
		<?php esc_html_e( 'This ACF field group does not contain any fields', 'formidable-acf' ); ?>
	</div>
</div>

<input type="hidden" id="frm_acf_form_action_data" value="<?php echo esc_attr( wp_json_encode( $js_data ) ); ?>" />

<style>
	.frm_icon_font.frm_acf_remove_row:before {
		content: '\e600';
	}
	.frm_icon_font.frm_acf_add_row:before {
		content: '\e602';
	}
	.frm_acf_mapping_has_field #frm_acf_add_row_wrapper {
		display: none;
	}

	.frm_acf_frm_sub_field,
	.frm_acf_acf_sub_field {
		line-height: 32px;
		border-left: 1px solid rgba(40, 47, 54, 0.2);
		position: relative;
		padding-left: 26px;
		margin-left: 10px;
	}

	.frm_acf_frm_sub_field:before,
	.frm_acf_acf_sub_field:before {
		content: '';
		height: 1px;
		width: 16px;
		background-color: rgba(40, 47, 54, 0.2);
		display: block;
		position: absolute;
		left: 0;
		top: 16px;
		margin-right: 10px;
	}
</style>

<script type="text/html" id="tmpl-frm-acf-mapping-row">
	<div class="frm_grid_container frm_acf_mapping_row">
		<div class="frm5 frm_form_field">
			<select name="frm_acf_frm_fields[]" class="frm_acf_select_frm_field frm_single_post_field">
				<option value="">&mdash; <?php esc_html_e( 'Select a Field', 'formidable-acf' ); ?> &mdash;</option>
				<# for ( var i = 0; i < data.frmFields.length; i++ ) { #>
					<option
						value="{{ data.frmFields[ i ].id }}"
						data-type="{{ data.frmFields[ i ].type }}"
						{{ parseInt( data.frmFields[ i ].id ) === parseInt( data.frmFieldId ) ? 'selected' : '' }}
					>
						{{ data.frmFields[ i ].name }}
					</option>
				<# } #>
			</select>

			{{{ data.mapping.child_mapping ? data.showChildFrmFields( data ) : '' }}}
		</div>

		<div class="frm5 frm_form_field">
			<select
				name="frm_acf_acf_fields[]"
				class="frm_custom_field_key"
				{{ ! data.frmFieldId ? 'disabled' : '' }}
			>
				<option value="">{{{ data.frmFieldId ? data.strings.select_field : data.strings.select_frm_first }}}</option>
				<# for ( var i = 0; i < data.acfFields.length; i++ ) { #>
					<option
						value="{{ data.acfFields[ i ].name }}"
						{{ data.acfFields[ i ].name === data.acfFieldName ? 'selected' : '' }}
					>
						{{ data.acfFields[ i ].label }}
					</option>
				<# } #>
			</select>

			<input type="hidden" name="frm_acf_field_keys[]" value="{{ data.mapping.acf_field_key || '' }}" />

			{{{ data.mapping.child_mapping ? data.showChildAcfFields( data ) : '' }}}
		</div>

		<div class="frm2 frm_form_field frm-inline-select">
			<a href="javascript:void(0)" class="frm_acf_remove_row frm_icon_font"></a>
			<a href="javascript:void(0)" class="frm_acf_add_row frm_icon_font"></a>
		</div>
	</div>
</script>
<?php
unset( $class, $acf_enabled, $mapping, $toggle_method );
