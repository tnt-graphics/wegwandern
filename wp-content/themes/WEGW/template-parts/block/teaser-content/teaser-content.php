<?php
/**
 * Template for teaser content
 **/
if ( ! empty( $is_preview ) ) {
	$content_teaser = get_field( 'content_teaser' );
	$content_bild   = get_field( 'content_bild' );
	$layout_option  = get_field( 'layout-option' );
	$excerpt        = $content_teaser ? wp_trim_words( wp_strip_all_tags( $content_teaser ), 24 ) : '';
	$thumb          = array();
	if ( is_array( $content_bild ) && ! empty( $content_bild['sizes']['thumbnail'] ) ) {
		$thumb[] = $content_bild['sizes']['thumbnail'];
	} elseif ( is_array( $content_bild ) && ! empty( $content_bild['url'] ) ) {
		$thumb[] = $content_bild['url'];
	}
	wegw_acf_block_editor_card(
		__( 'Bild Text 2-spaltig', 'wegwandern' ),
		array( $layout_option, $excerpt ),
		$thumb
	);
	return;
}
$content_bild     = get_field( 'content_bild' );
$content_bild_img = $content_bild['sizes']['teaser-twocol'];
$content_teaser   = get_field( 'content_teaser' );
$background       = get_field( 'background' );
$fullwidth_sec    = get_field( 'fullwidth_sec' );
$layout_option    = get_field( 'layout-option' );
$fullwidth_sec_cls = '';
if( $fullwidth_sec ) {
	$fullwidth_sec_cls = 'full-width-teaser';
}
$background_class = '';
if ( $background ) {
	$background_class = 'grey-back';
}
?>

<?php if ( $layout_option == 'bild-text' ) { ?>
	<div class="bild-text-wrap-container <?php echo $fullwidth_sec_cls; ?>">
		<div class="bild-text-wrap <?php echo $background_class; ?>">
			<div class="bild-text-img">
			<img src="<?php echo $content_bild_img; ?>">
			</div>
			<div class="bild-text-content">
				<?php echo $content_teaser; ?>
			</div>
		</div>
	</div>
	

<?php } elseif ( $layout_option == 'text-bild' ) { ?>
	<div class="text-bild-wrap-container <?php echo $fullwidth_sec_cls; ?>">
		<div class="text-bild-wrap <?php echo $background_class; ?>">

			<div class="text-bild-content">
				<?php echo $content_teaser; ?>
			</div>
			<div class="text-bild-img">
			<img src="<?php echo $content_bild_img; ?>">
			</div>
		</div>
	</div>
	
<?php } ?>
