<?php
/**
 * Template file for profile page
 *
 * @package wegwandern-summit-book
 */

// include file for template header and footer.
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
add_filter( 'page_template', 'wegwandern_summit_book_profile_page_template' );

/**
 * Add profile page content to page template
 *
 * @param string $page_template template value to be changed for profile page.
 */
function wegwandern_summit_book_profile_page_template( $page_template ) {
	if ( get_page_template_slug() === 'summit-book-user-profile.php' ) {
		$page_template = SUMMIT_BOOK_PLUGIN_DIR_PATH . 'templates/summit-book-user-profile.php';
	}
	return $page_template;
}

add_filter( 'theme_page_templates', 'wegwandern_summit_book_add_profile_page_template_to_select', 10, 4 );

/**
 * Add profile page content to theme page template
 *
 * @param array    $post_templates array of templates to insert custom page template.
 * @param WP_Theme $wp_theme theme template.
 * @param WP_Post  $post post object.
 * @param string   $post_type type of post.
 */
function wegwandern_summit_book_add_profile_page_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
	/* Add custom template named summit-book-user-profile to select dropdown */
	$post_templates['summit-book-user-profile.php'] = __( 'Gipfelbuch User Profile', 'wegwandern-summit-book' );
	return $post_templates;
}
