<?php
/**
 * Template Name: Sitemap Template
 */

get_header();
global $post;
$post_thumb = get_the_post_thumbnail_url( $post->ID, 'full' );
?>

<main id="primary" class="site-main">
	<?php if ( $post_thumb ) { ?>
		<div class="container-fluid region-wander-image-wrapper">
			<img class="region-wander-img" src="<?php echo $post_thumb; ?>" />
		</div>
	<?php } ?>
	<div class="container">
		<div class="sitemap-content-wrap">
			<h1 class='sitemap-title'><?php echo get_the_title( $post->ID ); ?></h1>
		</div>
		<div class="wegwandern-sitemap">
		<?php
		if( function_exists( 'aioseo_html_sitemap' ) ) aioseo_html_sitemap(); ?>
		</div>
	</div>
</main><!-- #main -->
<?php
// get_sidebar();
get_footer();
