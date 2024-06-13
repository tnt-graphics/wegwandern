<?php
/**
 * Functions to manage user watchlist
 *
 * @package wegwandern-summit-book
 */

/**
 * When a user logs in to watchlist a hike, add the hike to watchlist
 *
 * @param int $hike_id id of the hike.
 * @param int $user_id id of the user.
 */
function watchlist_hike( $hike_id, $user_id ) {
	$watchlist_user = get_user_by( 'id', $user_id );
	if ( ! in_array( SUMMIT_BOOK_USER_ROLE, $watchlist_user->roles ) ) {
		return;
	}
	if ( ! in_array( $hike_id, get_user_meta( $user_id, 'watchlist' ), true ) ) {
		add_user_meta( $user_id, 'watchlist', $hike_id );
	}
}

add_action( 'wp_ajax_wegwandern_summit_book_watchlist_hike', 'wegwandern_summit_book_watchlist_hike' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_watchlist_hike', 'wegwandern_summit_book_watchlist_hike' );

/**
 * Update a user's watchlist in ajax
 */
function wegwandern_summit_book_watchlist_hike() {
	$request = file_get_contents( 'php://input' );
	parse_str( $request, $post_array );
	if ( isset( $post_array['hikeId'] ) ) {
		global $current_user;
		if ( ! in_array( SUMMIT_BOOK_USER_ROLE, $current_user->roles ) ) {
			$result['result'] = 'failure';
		} else {
			watchlist_hike( $post_array['hikeId'], $current_user->ID );

			/* Sync with hikes Json file */			
			if ( function_exists( 'update_hike_json' ) ) {
				update_hike_json();
			}
			
			$result['result'] = 'success';
		}
	} else {
		$result['result'] = 'failure';
	}
	echo wp_json_encode( $result );
	wp_die();
}

add_action( 'wp_ajax_wegwandern_summit_book_user_watchlists', 'wegwandern_summit_book_user_watchlists' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_user_watchlists', 'wegwandern_summit_book_user_watchlists' );

/**
 * Function to get all the watchlists of user
 */
function wegwandern_summit_book_user_watchlists() {
	global $current_user;
	$watchlists           = get_user_meta( $current_user->ID, 'watchlist' );
	$result['watchlists'] = $watchlists;
	echo wp_json_encode( $result );
	wp_die();
}


