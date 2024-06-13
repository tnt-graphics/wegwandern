<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Wegwandern
 */

get_header();
global $post;
$post_thumb      = get_the_post_thumbnail_url( $post->ID, 'full' );
$container_cls   = 'container';
?>

<main id="primary" class="site-main">

	<?php if ( $post_thumb ) { ?>
	<div class="container-fluid">
		<img class="region-wander-img master-img" src="<?php echo $post_thumb; ?>" />
		<h5 class="master-category"><?php echo category_html(  $post->ID ); ?></h5>
		<h1 class='master-head-title'><?php echo get_the_title( $post->ID ); ?></h1>
		<?php
		if ( has_excerpt() ) {
			echo '<h3 class="master-sub-head">' . get_the_excerpt( $post->ID ) . '</h3>';
		}
		?>
		<span class="master-date"><?php echo get_the_date( 'd. F Y' ); ?></span>
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
get_footer();
