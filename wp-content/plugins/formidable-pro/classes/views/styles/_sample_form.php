<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmAppHelper::include_svg();
$svg_args = array(
	'width'  => '1em',
	'height' => '1em',
);
?>
<div class="frm_form_field frm_section_heading form-field frm_half frm_first">
	<h3 class="frm_pos_top frm_section_spacing"><?php esc_html_e( 'Repeatable Section', 'formidable-pro' ); ?></h3>
    <div>
        <div class="frm_repeat_sec">

			<div class="frm_form_field form-field <?php echo esc_attr( $pos_class ); ?>">
				<label for="field_aq7w5ep" class="frm_primary_label"><?php esc_html_e( 'Text Area', 'formidable-pro' ); ?></label>
				<textarea id="field_aq7w5ep"></textarea>
				<div class="frm_description"><?php esc_html_e( 'Another field with a description', 'formidable-pro' ); ?></div>
			</div>

			<div class="frm_form_field form-field frm_half frm_first <?php echo esc_attr( $pos_class ); ?>">
				<label class="frm_primary_label"><?php esc_html_e( 'Radio Buttons', 'formidable-pro' ); ?></label>
				<div class="frm_opt_container">
					<div class="frm_radio">
						<label><input type="radio" name="item_meta[1029]" />
						<?php esc_html_e( 'Option 1', 'formidable-pro' ); ?></label>
					</div>
					<div class="frm_radio">
						<label><input type="radio" name="item_meta[1029]" />
						<?php esc_html_e( 'Option 2', 'formidable-pro' ); ?></label>
					</div>
				</div>
			</div>

			<div class="frm_form_field form-field frm_half <?php echo esc_attr( $pos_class ); ?>">
				<label class="frm_primary_label"><?php esc_html_e( 'Check Boxes', 'formidable-pro' ); ?></label>
				<div class="frm_opt_container">
					<div class="frm_checkbox"><label><input type="checkbox" />
						<?php esc_html_e( 'Option 1', 'formidable-pro' ); ?>
					</label></div>
					<div class="frm_checkbox"><label><input type="checkbox" />
						<?php esc_html_e( 'Option 2', 'formidable-pro' ); ?>
					</label></div>
				</div>
			</div>
            <div class="frm_form_field frm_repeat_buttons frm_hidden_container">
				<a href="javascript:void(0)" class="frm_button frm_add_form_row">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus1_icon', $svg_args ); ?>
					<?php esc_html_e( 'Add', 'formidable-pro' ); ?>
				</a>
				<a href="javascript:void(0)" class="frm_button frm_remove_form_row">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_minus1_icon', $svg_args ); ?>
					<?php esc_html_e( 'Remove', 'formidable-pro' ); ?>
				</a>
            </div>
        </div>
    </div>
</div>

<div class="frm_form_field frm_section_heading form-field frm_half">
	<h3 class="frm_pos_top frm_trigger active frm_section_spacing">
		<i class="frm_icon_font frm_arrow_icon frm_before_collapse"></i>
		<?php esc_html_e( 'Collapsible Section', 'formidable-pro' ); ?>
		<i class="frm_icon_font frm_arrow_icon frm_after_collapse"></i>
	</h3>

    <div class="frm_toggle_container">
		<div class="frm_form_field form-field">
			<div id="datepicker_sample" class="frm-datepicker" style="margin-bottom:<?php echo esc_attr( $style->post_content['field_margin'] ); ?>;"></div>
		</div>

    </div>
</div>

<div class="frm_form_field frm_section_heading form-field">
    <h3 class="frm_pos_top frm_section_spacing"> </h3>
</div>

<div class="frm_form_field form-field frm_first frm_third <?php echo esc_attr( $pos_class ); ?>">
	<label for="field_toggle" class="frm_primary_label">
		<?php esc_html_e( 'Toggle', 'formidable-pro' ); ?>
	</label>
	<div>
		<?php
		FrmProHtmlHelper::toggle(
			'sample-toggle',
			'',
			array(
				'off_label'   => 'No',
				'on_label'    => 'Yes',
				'show_labels' => true,
				'echo'        => true,
			)
		);
		?>
	</div>
</div>

<div class="frm_form_field form-field frm_two_thirds <?php echo esc_attr( $pos_class ); ?>">
	<label for="field_slider" class="frm_primary_label">
		<?php esc_html_e( 'Slider', 'formidable-pro' ); ?>
	</label>
	<div class="frm_range_container">
		<span class="frm_range_value">150</span>
		<input type="range" data-frmrange id="field_slider" value="150" min="100" max="200" step="1" />
		<div class="frm_description">
			<span class="frm_range_min">100</span>
			<span class="frm_range_max">200</span>
		</div>
	</div>
</div>

<div class="frm_rootline_group">
	<ul class="frm_page_bar frm_progress_line frm_show_lines">
		<li class="frm_rootline_single">
			<span class="frm_rootline_title">Step 1</span>
			<input type="button" value="1" class="frm_page_back" disabled="disabled"  />
		</li>
		<li class="frm_rootline_single frm_current_page">
			<span class="frm_rootline_title">Step 2</span>
			<input type="button" value="2" class="frm_page_skip" disabled="disabled"  />
		</li>
		<li class="frm_rootline_single">
			<span class="frm_rootline_title">Step 3</span>
			<input type="button" value="3" class="frm_page_skip" disabled="disabled" />
		</li>
	</ul>
	<div class="frm_percent_complete"><?php echo esc_html( sprintf( __( '%s Complete', 'formidable-pro' ), '33%' ) ); ?></div>
	<div class="frm_pages_complete"><?php echo esc_html( sprintf( __( '%1$d of %2$d', 'formidable-pro' ), 2, 3 ) ); ?></div>
	<div class="frm_clearfix"></div>
</div>

<div class="frm_rootline_group">
	<ul class="frm_page_bar frm_rootline frm_show_lines">
		<li class="frm_rootline_single">
			<input type="button" value="1" class="frm_page_back" disabled="disabled"  />
			<span class="frm_rootline_title">Step 1</span>
		</li>
		<li class="frm_rootline_single frm_current_page">
			<input type="button" value="2" class="frm_page_skip" disabled="disabled"  />
			<span class="frm_rootline_title">Step 2</span>
		</li>
		<li class="frm_rootline_single">
			<input type="button" value="3" class="frm_page_skip" disabled="disabled" />
			<span class="frm_rootline_title">Step 3</span>
		</li>
	</ul>
	<div class="frm_clearfix"></div>
</div>
