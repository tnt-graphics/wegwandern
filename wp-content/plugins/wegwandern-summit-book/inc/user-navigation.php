<?php
/**
 * Create a custom navigation menu for subscriber user
 *
 * @package wegwandern-summit-book
 */

add_action( 'init', 'create_summit_book_user_navigation' );

/**
 * Create the navigation for summit book user
 */
function create_summit_book_user_navigation() {
	$menu_name     = SUMMIT_BOOK_USER_MENU_NAME;
	$menu_location = 'summitbookusermenu';
	if ( ! wp_get_nav_menu_object( $menu_name ) ) {
		$menu_id          = wp_create_nav_menu( $menu_name );
		$nav_items_to_add = array(
			'profil_bearbeiten' => array(
				'title' => __( 'Profil bearbeiten', 'wegwandern-summit-book' ),
				'path'  => PROFILE_PAGE_URL,
			),
			'dashboard'         => array(
				'title' => __( 'Dashboard', 'wegwandern-summit-book' ),
				'path'  => DASHBOARD_PAGE_URL,
			),
			'inserat_erstellen' => array(
				'title' => __( 'Inserat erstellen', 'wegwandern-summit-book' ),
				'path'  => INSERAT_ERSTELLEN_URL,
			),
			'neue_tour_posten'  => array(
				'title' => __( 'Neue Tour posten', 'wegwandern-summit-book' ),
				'path'  => NEUE_TOUR_POSTEN_PAGE_URL,
			),
		);
		foreach ( $nav_items_to_add as $nav_item ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'   => $nav_item['title'],
					'menu-item-url'     => $nav_item['path'],
					'menu-item-status'  => 'publish',
					'menu-item-classes' => 'summit-book-user-menu-item',
				)
			);
		}
		if ( ! has_nav_menu( $menu_location ) ) {
			$locations                   = get_theme_mod( 'nav_menu_locations' );
			$locations[ $menu_location ] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}
}
