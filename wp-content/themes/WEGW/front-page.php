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

get_header('home');
global $post;

if ( wp_is_mobile() ) {
	$post_thumb    = get_the_post_thumbnail_url( $post->ID, 'medium' );
} else {
	$post_thumb    = get_the_post_thumbnail_url( $post->ID, 'full' );
}

$container_cls = 'container';
?>

	<main id="primary" class="site-main">

<?php if ( $post_thumb ) { ?>
	<div class="container-fluid container-front-page">
		<img src="<?php echo $post_thumb; ?>" class="feature_banner"/>
		<div class="home-banner-content">
			<h1><?php echo get_the_title( $post->ID ); ?></h1>
			<h2><?php echo esc_attr_x( 'Jetzt gehts los...', 'wegwandern' ); ?></h2>
			<div class="home-search-container">
				<!-- <div class="home-search-wrapper">
					<span class="home_filter_search-icon"></span>
					<input type="text" id="" class="" placeholder="Suche" value="" name="s">
					<span class="home_search_close hide"></span>
				</div> -->
				<form role="search" method="post" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<div class="head_navigation_search search">
					<span class="filter_search-icon"></span>
					<input type="text" id="home-search" class="search-class" placeholder="<?php echo esc_attr_x( 'Suche', 'placeholder', 'wegwandern' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />

					<span class="head_navigation_search_close hide"></span>
					</div>
				</form>
				<?php 
				$tourenportal_page = get_field( 'select_tourenportal_page', 'option' );
				?>
				<a href="<?php echo $tourenportal_page; ?>"><div class="touren_portal"></div></a>
			</div>
		</div>
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
