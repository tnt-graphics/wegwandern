<?php
/**
 * Functions for admin backend in Community Beitrag
 *
 * @package wegwandern-summit-book
 */

add_filter( 'manage_community_beitrag_posts_columns', 'community_beitrag_table_head' );

/**
 * Change the columns of community beitrag
 *
 * @param array $defaults array of columns to display in admin listing.
 */
function community_beitrag_table_head( $defaults ) {
	// unset( $defaults['title'] );
	unset( $defaults['date'] );
	// $defaults['titel']  = __( 'Titel', 'wegwandern-summit-book' );
	$defaults['user']   = __( 'User', 'wegwandern-summit-book' );
	$defaults['status'] = __( 'Status', 'wegwandern-summit-book' );
	$defaults['date']   = __( 'Date', 'wegwandern-summit-book' );
	// $defaults['actions'] = __( 'Actions', 'wegwandern-summit-book' );
	return $defaults;
}

add_action( 'manage_community_beitrag_posts_custom_column', 'community_beitrag_table_content', 10, 2 );

/**
 * Add the column contents for community beitrag
 *
 * @param string $column_name name of the column.
 * @param int    $post_id id of the post.
 */
function community_beitrag_table_content( $column_name, $post_id ) {
	if ( 'titel' === $column_name ) {
		$title = get_post_meta( $post_id, 'titel', true );
		echo esc_attr( $title );
	}
	if ( 'status' === $column_name ) {
		$status         = get_post_meta( $post_id, 'article_status', true );
		$status_display = isset( SUMMIT_BOOK_COMMUNITY_BEITRAG_STATUS[ $status ] ) ? SUMMIT_BOOK_COMMUNITY_BEITRAG_STATUS[ $status ] : '';
		echo esc_attr( $status_display );
		$previous_status = get_post_meta( $post_id, 'previous_article_status', true );
		if ( $previous_status && $previous_status === 'rejected' ) {
			echo '<span class="article-reject-status"> (' . __( 'Abgelehnt', 'wegwandern' ) . ')</span>';
		}
	}
	if ( 'user' === $column_name ) {
		$user_id    = get_post_meta( $post_id, 'user', true );
		$first_name = get_user_meta( $user_id, 'first_name', true );
		$last_name  = get_user_meta( $user_id, 'last_name', true );
		echo esc_attr( $first_name . ' ' . $last_name );
	}
}

/**
 * Send reject email when post status changed to rejected
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Old post status.
 * @param WP_Post $post       Post object.
 */
function summit_book_article_rejection_mail( $new_status, $old_status, $post ) {
	if ( $old_status == $new_status || ( $new_status != 'rejected' && $new_status != 'publish' ) ) {
		return;
	}
	if ( $new_status == 'publish' ) {
		update_post_meta( $post->ID, 'article_status', 'published' );
	} else {
		wp_update_post(
			array(
				'ID'          => $post->ID,
				'post_status' => 'draft',
			)
		);
		update_post_meta( $post->ID, 'previous_article_status', 'rejected' );
		// Update entry status.
		update_post_meta( $post->ID, 'article_status', 'saved' );
		// Send article rejected mail to user.
		$author            = get_post_meta( $post->ID, 'user', true );
		$author_user       = get_user_by( 'ID', $author );
		$rejected_subject  = __( 'Dein Wanderbeschrieb wurde abgelehnt', 'wegwandern-summit-book' );
		$rejected_content  = __(
			'Dein Wanderbeschrieb wurde zurückgewiesen. Bitte beachte unsere
		<a href="' . TERMS_OF_USE_URL . '">Nutzungsbedingungen</a>.',
			'wegwandern-summit-book'
		);
		$rejected_from     = __( 'Dein WegWandern.ch-Team', 'wegwandern-summit-book' );
		$rejected_content .= '<br><br>' . $rejected_from;
		wp_mail( $author_user->user_email, $rejected_subject, $rejected_content );
	}
}

add_action( 'transition_post_status', 'summit_book_article_rejection_mail', 10, 3 );
