<?php
/**
 * Template for image lightbox gallery 
 */
if ( ! empty( $is_preview ) ) {
	$img_gal = get_field( 'build' );
	$thumbs  = array();
	if ( is_array( $img_gal ) ) {
		foreach ( $img_gal as $gal ) {
			if ( ! empty( $gal['light_gal']['sizes']['thumbnail'] ) ) {
				$thumbs[] = $gal['light_gal']['sizes']['thumbnail'];
			} elseif ( ! empty( $gal['light_gal']['url'] ) ) {
				$thumbs[] = $gal['light_gal']['url'];
			}
		}
	}
	$count = is_array( $img_gal ) ? count( $img_gal ) : 0;
	wegw_acf_block_editor_card(
		__( 'Bildergalerie', 'wegwandern' ),
		array(
			sprintf(
				/* translators: %d: number of images */
				_n( '%d Bild', '%d Bilder', $count, 'wegwandern' ),
				$count
			),
		),
		$thumbs
	);
	return;
}
global $post;
$img_gal = get_field( 'build' );

if ( ! empty( $img_gal ) ) {
	count( $img_gal );
	$cls_wrap = '';
	$counter = '';
	if ( count( $img_gal ) > 1 ){
		$cls_wrap = 'owl-carousel';
		$counter = '<div id="count"></div>';
	}
	
	?>
	<div class="light-box-gallery-wrapper">
		<div class="light-box-inner-wrap">
			<div class="fullscreen_light" onclick="openLightGallery(this)"></div>
			<div class="img-gallery-wrap <?php echo $cls_wrap; ?>">
				<?php foreach ( $img_gal as $gal ) { ?>
					<div class="justified-gallery" data-src="<?php echo $gal['light_gal']['url']; ?>" data-sub-html="<?php echo $gal['light_gal']['caption']; ?>">
						<a>
							<img class="wander-img detail-wander-img" src="<?php echo $gal['light_gal']['sizes']['large']; ?>" />
						</a>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php echo $counter; ?>
		<div class="figcaption"></div>
	</div>
	<?php
}
