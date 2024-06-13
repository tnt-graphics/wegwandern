<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Wegwandern
 */

get_header();
global $post;
$post_thumb        = get_the_post_thumbnail_url( $post->ID, 'full' );
$tourenportal_page = get_field( 'select_tourenportal_page', 'option' );
$tourenportal_id   = url_to_postid( $tourenportal_page );
$current_url       = $post->ID;
$container_cls     = 'container';
if ( $current_url === $tourenportal_id ) {
	$container_cls = 'touren_container';
}
?>

	<main id="primary" class="site-main">

<?php if ( $post_thumb ) { ?>
	<div class="container-fluid">
	<img class="region-wander-img master-img" src="<?php echo $post_thumb; ?>" />
		<h1 class='master-head-title'><?php echo get_the_title( $post->ID ); ?></h1>
	</div>
	<?php } ?>
	<div class="<?php echo $container_cls; ?>">
		<?php
		while ( have_posts() ) :
			the_post();
			
			get_template_part( 'template-parts/content', 'page' );

		endwhile; // End of the loop.
		?>
</div>
	</main><!-- #main -->

<?php
// get_sidebar();
get_footer();
