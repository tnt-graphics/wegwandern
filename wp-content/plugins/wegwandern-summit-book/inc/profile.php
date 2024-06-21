<?php
/**
 * Profile functions for summit book
 *
 * @package wegwandern-summit-book
 */

add_action( 'wp_ajax_wegwandern_summit_book_delete_user_action', 'wegwandern_summit_book_delete_user_action' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_delete_user_action', 'wegwandern_summit_book_delete_user_action' );

/**
 * Check if the username and passowrd is correct
 */
function wegwandern_summit_book_delete_user_action() {
	global $current_user;

	$post_query = new WP_Query( 
		array(
			'post_type' 	 => 'community_beitrag',
			'posts_per_page' => -1,
			'author'         =>  $current_user->ID,
		) 
   );

   if ( $post_query->have_posts() ) {
		while ( $post_query->have_posts() ) {
			$post_query->the_post();
			$post_id    = get_the_ID();
			wp_delete_post( $post_id, true );
		}
   }


	if ( wp_delete_user( $current_user->ID ) ) {
		$result['result']   = 'success';
		$result['redirect'] = home_url();
	} else {
		$result['result']   = 'failure';
		$result['redirect'] = DASHBOARD_PAGE_URL;
	}
	echo wp_json_encode( $result );
	wp_die();
}

/**
 * Move description to upside for password confirmation of edit profile form
 *
 * @param string $default_html the default html of the field.
 * @param string $field_type the type of field.
 */
function frm_move_field_description( $default_html, $field_type ) {
	if ( 'password' !== $field_type ) {
		return $default_html;
	}
	$start_description     = '[if description]';
	$end_description       = '[/if description]';
	$description_start_pos = strpos( $default_html, $start_description );
	$description_end_pos   = strpos( $default_html, $end_description );
	if ( false === $description_start_pos || false === $description_end_pos ) {
		return $default_html;
	}

	$description_length = $description_end_pos - $description_start_pos + strlen( $end_description );
	$description_string = substr( $default_html, $description_start_pos, $description_length );
	$default_html       = str_replace( $description_string, '', $default_html );
	$default_html       = str_replace( '/label>', '/label>' . $description_string, $default_html );

	return $default_html;
}

add_filter( 'frm_load_dropzone', 'stop_dropzone' );

/**
 * Do not show dropzone in profile edit
 *
 * @param bool $load_it whether to load dropzone or not.
 */
function stop_dropzone( $load_it ) {
	if ( 'profil-bearbeiten' === basename( get_permalink() ) ) { // set the page or other conditions here.
		$load_it = false;
	}
	return $load_it;
}

