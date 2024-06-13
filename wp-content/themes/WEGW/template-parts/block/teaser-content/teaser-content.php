<?php
/**
 * Template for teaser content
 **/
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
