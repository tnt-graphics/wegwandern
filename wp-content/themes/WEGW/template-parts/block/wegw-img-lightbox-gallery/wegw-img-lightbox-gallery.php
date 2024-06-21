<?php
/**
 * Template for image lightbox gallery 
 */
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
