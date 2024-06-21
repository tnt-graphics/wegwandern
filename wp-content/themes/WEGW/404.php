<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Wegwandern
 */

get_header();
$args       = array(
	'post_type'      => 'attachment',
	'post_title'     => 'Wanderwege Signalisation',
	'name'           => 'wanderwege_signalisation',
	'posts_per_page' => 1,
	'post_status'    => 'inherit',
);
$post_thumb = null;
$image_post = get_posts( $args );
if ( ! empty( $image_post ) ) {
	$post_thumb = wp_get_attachment_image_url( $image_post[0]->ID, 'full' );
}
?>

<main id="primary" class="site-main">
	<?php if ( $post_thumb ) { ?>
		<div class="container-fluid">
			<img class="region-wander-img" src="<?php echo $post_thumb; ?>" />
		</div>
	<?php } ?>
	<div class="container">
		<div class="404-content-wrap">
			<h1 class='404-title'><?php echo __( 'Uuuppps… Hier bist du auf dem Holzweg', 'wegwandern' ); ?></h1>
		</div>
		<div class="404-search-section">
			<h2 class='404-search-title'><?php echo __( 'Vielleicht findest du hier den richtigen Weg:', 'wegwandern' ); ?></h2>
			<div class="searchinputFieldWrapper">
				<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<div class="searchinputField">
						<span class="searchResult_filter_search-icon"></span>
						<input type="text" class="" placeholder="<?php echo esc_attr_x( 'Suche', 'placeholder', 'wegwandern' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
						<span class="searchResult_search_close hide"></span>
					</div>
				</form>
			</div>
		</div>
	</div>
</main><!-- #main -->

<?php
get_footer();
