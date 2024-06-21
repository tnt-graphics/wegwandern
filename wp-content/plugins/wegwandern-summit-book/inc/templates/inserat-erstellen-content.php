<?php
/**
 * Template file for Create Pinnwand-Eintrag (Pinwall Ad) page
 *
 * @package wegwandern-summit-book
 */

add_filter( 'page_template', 'wegwandern_summit_book_inserat_erstellen_page_template' );

/**
 * Add Inserat Erstellen page content to page template
 *
 * @param string $page_template template value to be changed for Inserat Erstellen page.
 */
function wegwandern_summit_book_inserat_erstellen_page_template( $page_template ) {
	if ( get_page_template_slug() === 'inserat-erstellen.php' ) {
		$page_template = SUMMIT_BOOK_PLUGIN_DIR_PATH . 'templates/inserat-erstellen.php';
	}
	return $page_template;
}

add_filter( 'theme_page_templates', 'wegwandern_summit_book_inserat_erstellen_template_to_select', 10, 4 );

/**
 * Add Inserat Erstellen page content to theme page template
 *
 * @param array    $post_templates array of templates to insert custom page template.
 * @param WP_Theme $wp_theme theme template.
 * @param WP_Post  $post post object.
 * @param string   $post_type type of post.
 */
function wegwandern_summit_book_inserat_erstellen_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
	/* Add custom template named inserat-erstellen to select dropdown */
	$post_templates['inserat-erstellen.php'] = __( 'Gipfelbuch Inserat Erstellen', 'wegwandern-summit-book' );
	return $post_templates;
}
