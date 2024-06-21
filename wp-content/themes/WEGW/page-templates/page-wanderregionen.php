<?php
/**
 * Template Name: Wanderregionen Template 
 */ 

get_header();
global $post;
$post_thumb        = get_the_post_thumbnail_url( $post->ID, 'full' );
?>
	<main id="primary" class="site-main">
		<?php if ( $post_thumb ) { ?>
			<div class="container-fluid region-wander-image-wrapper">
				<img class="region-wander-img" src="<?php echo $post_thumb; ?>" />
				<div class="region-img-content-wrap">
					<?php echo get_breadcrumb(); ?>
					<h1 class='region-title'><?php echo get_the_title( $post->ID ); ?></h1>
				</div>
			</div>
		<?php } ?>

		<div class="container">
			<?php
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content', 'page' );

			endwhile;
			?>
		</div>
	</main>

<?php
get_footer();
