<?php
/**
 * The template for displaying blog slider
 */

$select_post          = get_field( 'select_post' );
$all_blog_count       = count( $select_post );
$blogs                = get_field( 'blog' );
$darstellungsart_blog = get_field( 'darstellungsart_blog' );
$section_titel        = get_field( 'section_titel' );
$section_sub_titel    = get_field( 'section_sub_titel' );


?>

<?php if ( $select_post && 'slider' == $darstellungsart_blog ) { ?>
<div class="container-fluid full-width-slider grey-back">
	<div class="wander-in-region-wrapper ">
	<div class="wander-in-region">
		<h3 class="full-width-slider-title"><?php echo __( 'Aus unserem Blog', 'wegwandern' ); ?><span class="counter-in-region"><?php echo $all_blog_count; ?></span></h3>
		</div>
	<div class="owl-carousel owl-theme wander-in-region-carousel">
		<?php
		foreach ( $select_post as $blog ) {
				$post_thumb = get_the_post_thumbnail_url( $blog['blog']->ID, 'teaser-twocol' );
			?>
		<div class="single-wander-block">
			<div class="single-wander-img">
			<a href="<?php echo get_the_permalink( $blog['blog']->ID ); ?>">
				<img decoding="async" class="wander-img" src="<?php echo $post_thumb; ?>">
			</a>
			</div>
			<h6><?php echo category_html( $blog['blog']->ID ); ?></h6>
			<h3><a href="<?php echo get_the_permalink( $blog['blog']->ID ); ?>"><?php echo $blog['blog']->post_title; ?></a></h3>
			
			</div>
		
		<?php } ?>
		</div>
		</div>
	<a href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>"><div class="wander-in-region-btn region-desktop"><?php echo __( 'Zur Blog Übersicht', 'wegwandern' ); ?></div></a>
	<a href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>"><div class="wander-in-region-btn region-tab"><?php echo __( 'Zur Blog Übersicht', 'wegwandern' ); ?></div></a>
	<a href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>"><div class="wander-in-region-btn region-mob"><?php echo __( 'Zur Blog Übersicht', 'wegwandern' ); ?></div></a>
	</div>

</div>
<?php } ?>
<?php if ( 'four_blog' == $darstellungsart_blog ) { ?>
<div class="wanderblog-bg-wrapper grey-back">
	<div class="wanderblog-wrap">
   <div class="col1_wrap ">
		<h3 class="natur_title"> <?php echo $section_titel; ?></h3>
		<h6 class="natur_sub__title"> <?php echo $section_sub_titel; ?></h6>

		<?php
		  $post_id          = $select_post[0]['blog']->ID;
				$post_thumb = get_the_post_thumbnail_url( $post_id, 'teaser-onecol' );
		?>
		<a class="blog-wrap" href="<?php echo get_the_permalink( $post_id ); ?>" target="_blank">
		<img decoding="async" class="teaser_img" src="<?php echo $post_thumb; ?>">
		</a>
			<div class="natur-img-content-wrap">
				<p><?php echo category_html( $post_id ); ?></p>
				<a class="blog-wrap" href="<?php echo get_the_permalink( $post_id ); ?>" target="_blank">
				<h3 class="natur-title"><?php echo $select_post[0]['blog']->post_title; ?></h3>
				</a>
			</div>
				
</div>
<div class="col3_wrap ">

	
		<div class="col3_natur">
			<?php
			for ( $i = 1; $i <= 3; $i++ ) {
				$post_id       = $select_post[ $i ]['blog']->ID;
				$post_thumb    = get_the_post_thumbnail_url( $post_id, 'hike-region' );
				// $cat_html      = '';
				// $category_name = '';
				?>
				<div class="single_natur">			
					<a href="<?php echo get_the_permalink( $post_id ); ?>" target="_blank">
						<img decoding="async" class="teaser_img" src="<?php echo $post_thumb; ?>">
					</a>
					
					<div class="cat-tit-wrap">
						<h6><?php echo category_html( $post_id ); ?></h6>
						<a class="region-link-wrap" href="<?php echo get_the_permalink( $post_id ); ?>" target="_blank">
						<h3><?php echo $select_post[ $i ]['blog']->post_title; ?></h3>
						</a>
					</div>		
				</div>
			<?php } ?>
			</div>
			<a href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>"><button ><?php echo __( 'Zur Blog Übersicht', 'wegwandern' ); ?></button></a>
		
</div>
</div>
</div>
	<?php
}
?>
