<?php
/**
 * Wanderung Grey Background Section
 */
$wegw_grey_background_section_content = get_field( 'wegw_grey_background_section_content' );
$wegw_grey_background_section_icon    = get_field( 'wegw_grey_background_section_icon' );
$wegw_grey_background_section_color   = get_field( 'wegw_grey_background_section_color' );

$wegw_grey_background_section_icon_url = "";
if ( ! empty( $wegw_grey_background_section_icon ) && is_array( $wegw_grey_background_section_icon ) ) {
	$wegw_grey_background_section_icon_url = $wegw_grey_background_section_icon['url'];
}
?>

<div class="hightlight-wrapper-container">
	<div class='hightlight-wrapper <?php echo $wegw_grey_background_section_color; ?>'>
		<?php echo $wegw_grey_background_section_content; ?>
		<img src="<?php echo $wegw_grey_background_section_icon_url; ?>" />
	</div>
</div>
