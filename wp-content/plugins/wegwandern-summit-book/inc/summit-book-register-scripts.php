<?php
/**
 * Register Styles & Scripts
 *
 * @package wegwandern-summit-book
 */

/**
 * Add scripts and styles for the plugin
 */
add_action( 'wp_enqueue_scripts', 'wegwandern_summit_book_enqueue_script' );

/**
 * Add scripts and styles for summit book
 */
function wegwandern_summit_book_enqueue_script() {
	wp_enqueue_style( 'wegwandern_summit_book_style', SUMMIT_BOOK_PLUGIN_DIR_URL . 'assets/css/style.css', false, _S_VERSION, 'all' );

	if ( is_user_logged_in() ) {
		wp_enqueue_script( 'wegwandern_summit_book_profile_script', SUMMIT_BOOK_PLUGIN_DIR_URL . 'assets/js/profile.js', array( 'jquery' ), _S_VERSION, true );
		$logged_user         = wp_get_current_user();
		$hide_delete_profile = 'no';
		if ( in_array( B2B_USER_ROLE, (array) $logged_user->roles ) ) {
			$hide_delete_profile = 'yes';
		}
		wp_localize_script(
			'wegwandern_summit_book_profile_script',
			'profileObj',
			array(
				'dashboardUrl'     => DASHBOARD_PAGE_URL,
				'confirmPassLabel' => __( 'Neues Kennwort wiederholen', 'wegwandern-summit-book' ),
				'userNickname' => get_user_display_name(),
				'hideDeleteProfile' => $hide_delete_profile,
			)
		);
	}

	wp_enqueue_script( 'summit-book-plugin-ajax', SUMMIT_BOOK_PLUGIN_DIR_URL . 'assets/js/ajax-scripts.js', array(), _S_VERSION, false );
	wp_localize_script(
		'summit-book-plugin-ajax',
		'ajax_object',
		array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'ajax-nonce' ),
		)
	);
	wp_localize_script( 'summit-book-plugin-ajax', 'pinwandObj', array( 'inseratUrl' => INSERAT_ERSTELLEN_URL ) );
	wp_localize_script( 'summit-book-plugin-ajax', 'loginObj', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'wegwandern_summit_book_watchlist_script', SUMMIT_BOOK_PLUGIN_DIR_URL . 'assets/js/watchlist.js', array( 'jquery' ), _S_VERSION, true );
}

/**
 * Add scripts and styles for admin backend
 *
 * @param string $hook hook to identify admin page.
 */
add_action( 'admin_enqueue_scripts', 'wegwandern_summit_book_enqueue_admin_script' );

/**
 * Add scripts and styles for admin
 *
 * @param string $hook the page for which to load scripts and styles.
 */
function wegwandern_summit_book_enqueue_admin_script( $hook ) {
	if ( 'edit.php' !== $hook && 'edit-comments.php' !== $hook ) {
		return false;
	}
	wp_enqueue_style( 'wegwandern_summit_book_admin_style', SUMMIT_BOOK_PLUGIN_DIR_URL . 'assets/css/admin-style.css', false, _S_VERSION, 'all' );

	wp_enqueue_script( 'wegwandern_summit_book_admin_community_beitrag_script', SUMMIT_BOOK_PLUGIN_DIR_URL . 'assets/js/admin-community-beitrag.js', array(), _S_VERSION, true );
	wp_localize_script(
		'wegwandern_summit_book_admin_community_beitrag_script',
		'beitragObj',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'ajax-nonce' ),
		)
	);

	wp_enqueue_script( 'wegwandern_summit_book_admin_pinnwand_beitrag_script', SUMMIT_BOOK_PLUGIN_DIR_URL . 'assets/js/admin-pinwand-eintrag.js', array(), _S_VERSION, true );
}

