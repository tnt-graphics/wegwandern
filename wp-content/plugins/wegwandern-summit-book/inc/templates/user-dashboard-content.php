<?php
/**
 * Template file for User Dashboard page
 *
 * @package wegwandern-summit-book
 */

add_filter( 'page_template', 'wegwandern_summit_book_user_dashboard_page_template' );

/**
 * Add user dashboard page content to page template
 *
 * @param string $page_template template value to be changed for user dashboard page.
 */
function wegwandern_summit_book_user_dashboard_page_template( $page_template ) {
	if ( get_page_template_slug() === 'user-dashboard.php' ) {
		$page_template = SUMMIT_BOOK_PLUGIN_DIR_PATH . 'templates/user-dashboard.php';
	}
	return $page_template;
}

add_filter( 'theme_page_templates', 'wegwandern_summit_book_user_dashboard_template_to_select', 10, 4 );

/**
 * Add user dashboard page content to theme page template
 *
 * @param array    $post_templates array of templates to insert custom page template.
 * @param WP_Theme $wp_theme theme template.
 * @param WP_Post  $post post object.
 * @param string   $post_type type of post.
 */
function wegwandern_summit_book_user_dashboard_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {
	/* Add custom template named neue-tour-posten to select dropdown */
	$post_templates['user-dashboard.php'] = __( 'Gipfelbuch User Dashboard', 'wegwandern-summit-book' );
	return $post_templates;
}
