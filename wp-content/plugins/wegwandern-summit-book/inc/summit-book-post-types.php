<?php
/**
 * Custom Post types of summit book
 *
 * @package wegwandern-summit-book
 */

/**
 * Register the article custom post type
 */
function wegwandern_summit_book_setup_post_type() {

	register_post_type(
		'community_beitrag',
		array(
			'labels'             => array(
				'name'          => __( 'Community-Beiträge', 'wegwandern-summit-book' ),
				'singular_name' => __( 'Community-Beitrag', 'wegwandern-summit-book' ),
			),
			'public'             => true,
			'has_archive'        => true,
			// 'supports'    => false,
			'supports'           => array( 'title' ),
			'rewrite'            => array(
				'slug'       => 'wanderung-community',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'pinnwand_eintrag',
		array(
			'labels'              => array(
				'name'               => __( 'Pinnwand-Einträge', 'wegwandern-summit-book' ),
				'singular_name'      => __( 'Pinnwand-Eintrag', 'wegwandern-summit-book' ),
				'menu_name'          => __( 'Pinnwand-Eintrag', 'wegwandern-summit-book' ),
				'parent_item_colon'  => __( 'Pinnwand-Eintrag', 'wegwandern-summit-book' ),
				'all_items'          => __( 'Alle Einträge', 'wegwandern-summit-book' ),
				'view_item'          => __( 'Siehe ', 'wegwandern-summit-book' ),
				'add_new_item'       => __( 'Neu hinzufügen', 'wegwandern-summit-book' ),
				'add_new'            => __( 'Neu hinzufügen', 'wegwandern-summit-book' ),
				'edit_item'          => __( 'Pinnwand-Eintrag bearbeiten', 'wegwandern-summit-book' ),
				'update_item'        => __( 'Update Pinnwand-Eintrag', 'wegwandern-summit-book' ),
				'search_items'       => __( 'Pinnwand-Eintrag suchen', 'wegwandern-summit-book' ),
				'not_found'          => __( 'Nicht gefunden', 'wegwandern-summit-book' ),
				'not_found_in_trash' => __( 'Nicht im Papierkorb gefunden', 'wegwandern-summit-book' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'query_var'           => true,
			'show_in_rest'        => true,
			'show_in_nav_menus'   => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'supports'            => array( 'title' ),
		)
	);

	register_post_type(
		'bewertung',
		array(
			'labels'              => array(
				'name'          => __( 'Bewertungen', 'wegwandern-summit-book' ),
				'singular_name' => __( 'Bewertung', 'wegwandern-summit-book' ),
			),
			'public'              => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'supports'            => false,
		)
	);

	/**
	 * Add 'rejected' post status.
	 */
	register_post_status(
		'rejected',
		array(
			'label'                     => __( 'Rejected', 'wegwandern-summit-book' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			// %s would have the value of the number of posts in the status.
			'label_count'               => _n_noop( 'Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>', 'wegwandern-summit-book' ),
		)
	);

	/**
	 * Add 'expired' post status.
	 */
	register_post_status(
		'expired',
		array(
			'label'                     => __( 'Expired', 'wegwandern-summit-book' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'wegwandern-summit-book' ),
		)
	);

	add_role(
		'summit-book-user',
		'Summit-Book-User',
		array(
			'read'              => true,
			'create_posts'      => true,
			'edit_posts'        => true,
			'edit_others_posts' => true,
			'publish_posts'     => true,
			'manage_categories' => true,
		)
	);

	if ( function_exists( 'acf_register_block_type' ) ) {
		acf_register_block_type(
			array(
				'name'            => 'community-teaser',
				'title'           => _x( 'Community Teaser', 'community-teaser', 'wegwandern' ),
				'description'     => __( 'Community Teaser', 'wegwandern' ),
				'render_template' => get_template_directory() . '/template-parts/block/community-teaser/community-teaser.php',
				'category'        => 'wegwandern-layout-category',
				'icon'            => '',
				'align'           => false,
				'mode'            => 'edit',
			)
		);
	}
}
add_action( 'init', 'wegwandern_summit_book_setup_post_type' );

/**
 * Activate the plugin.
 */
function wegwandern_summit_book_activate() {
	// Add custom post types.
	wegwandern_summit_book_setup_post_type();
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wegwandern_summit_book_activate' );

/**
 * Deactivate the plugin.
 */
function wegwandern_summit_book_deactivate() {
	// remove scheduled crons.
	wp_clear_scheduled_hook( 'wegwandern_summit_book_pinnwand_eintrag_daily_cron' );
}
register_deactivation_hook( __FILE__, 'wegwandern_summit_book_deactivate' );
