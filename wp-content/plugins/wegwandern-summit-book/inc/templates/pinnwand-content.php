<?php
/**
 * Template file for pinnwand page
 *
 * @package wegwandern-summit-book
 */

add_filter( 'page_template', 'wegwandern_summit_book_pinnwand_page_template' );

/**
 * Add pinnwand page content to page template
 *
 * @param string $page_template template value to be changed for pinnwand page.
 */
function wegwandern_summit_book_pinnwand_page_template( $page_template ) {
	if ( get_page_template_slug() === 'pinnwand.php' ) {
		$page_template = SUMMIT_BOOK_PLUGIN_DIR_PATH . 'templates/pinnwand.php';
	}
	return $page_template;
}

add_filter( 'theme_page_templates', 'wegwandern_summit_book_add_pinnwand_page_template_to_select', 10, 4 );

/**
 * Add pinnwand page content to theme page template
 *
 * @param array    $post_templates array of templates to insert custom page template.
 * @param WP_Theme $wp_theme theme template.
 * @param WP_Post  $post post object.
 * @param string   $post_type type of post.
 */
function wegwandern_summit_book_add_pinnwand_page_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
	/* Add custom template named pinnwand to select dropdown */
	$post_templates['pinnwand.php'] = __( 'Gipfelbuch Pinwand', 'wegwandern-summit-book' );
	return $post_templates;
}
