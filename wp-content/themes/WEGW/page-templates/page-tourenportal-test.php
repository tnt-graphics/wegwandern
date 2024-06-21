<?php
/**
 * Template Name: Tourenportal Test Template
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
	<div class="touren_container">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content-wanderung-listing-test', 'page' );
		endwhile;
		?>
	</div>
</main>

<?php
// get_sidebar();
get_footer();
