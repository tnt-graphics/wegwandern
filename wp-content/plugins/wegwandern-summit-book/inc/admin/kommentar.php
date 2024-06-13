<?php
/**
 * Functions for admin interface of kommentar
 *
 * @package wegwandern-summit-book
 */

add_action( 'manage_comments_custom_column', 'comments_column_with_images', 10, 2 );

/**
 * Add the column contents for community beitrag
 *
 * @param string $column_name name of the column.
 * @param int    $comment_id id of the comment.
 */
function comments_column_with_images( $column_name, $comment_id ) {
	if ( 'kommentar' === $column_name ) {
		echo nl2br( esc_attr( get_comment( $comment_id )->comment_content ) );
		$comment_imgs = get_comment_meta( $comment_id, 'comment_images', true );
		if ( $comment_imgs ) {
			echo "<div class='comment-imgs-admin'>";
			foreach ( $comment_imgs as $each_img ) {
				if ( '' !== $each_img ) {
					$image_url = wp_get_attachment_image_url( $each_img, 'full' );
					echo "<div class='each-comment-img'><img src='" . $image_url . "'/></div>";
				}
			}
			echo '</div>';
		}
	}
}

/**
 * Add komment column in admin backend
 *
 * @param array $columns array of columns.
 */
function summit_book_comments_columns( $columns ) {
	unset( $columns['comment'] );
	$columns['kommentar'] = __( 'Kommentar' );
	return $columns;
}
add_filter( 'manage_edit-comments_columns', 'summit_book_comments_columns' );

add_filter( 'manage_bewertung_posts_columns', 'bewertung_table_head' );

/**
 * Change the columns of community beitrag
 *
 * @param array $defaults array of columns to display in admin listing.
 */
function bewertung_table_head( $defaults ) {
	unset( $defaults['title'] );
	$defaults['tour']   = __( 'Tour', 'wegwandern-summit-book' );
	$defaults['rating'] = __( 'Bewertung', 'wegwandern-summit-book' );
	$defaults['user']   = __( 'User', 'wegwandern-summit-book' );
	return $defaults;
}

add_action( 'manage_bewertung_posts_custom_column', 'bewertung_table_content', 10, 2 );

/**
 * Add the column contents for community beitrag
 *
 * @param string $column_name name of the column.
 * @param int    $post_id id of the post.
 */
function bewertung_table_content( $column_name, $post_id ) {
	if ( 'tour' === $column_name ) {
		$tour = get_post_meta( $post_id, 'rated_wanderung', true );
		echo esc_attr( get_post( $tour )->post_title );
	}
	if ( 'rating' === $column_name ) {
		$rating = get_post_meta( $post_id, 'rating', true );
		echo esc_attr( $rating );
	}
	if ( 'user' === $column_name ) {
		$user_id    = get_post_meta( $post_id, 'rated_user', true );
		$first_name = get_user_meta( $user_id, 'first_name', true );
		$last_name  = get_user_meta( $user_id, 'last_name', true );
		echo esc_attr( $first_name . ' ' . $last_name ) . '<br>';
	}
}

add_action( 'transition_comment_status', 'notify_user_on_comment_reject', 10, 3 );

/**
 * Reject comment notification to user
 *
 * @param int|string $new_status new status of the comment.
 * @param int|string $old_status old status of the comment.
 * @param WP_Comment $comment comment object.
 */
function notify_user_on_comment_reject( $new_status, $old_status, $comment ) {
	if ( 'spam' === $new_status ) {
		$comment_user      = get_user_by( 'ID', $comment->user_id );
		$rejected_subject  = __( 'Dein Kommentar wurde abgelehnt', 'wegwandern-summit-book' );
		$rejected_content  = __(
			'Dein Kommentar wurde zurückgewiesen. Bitte beachte unsere
		<a href="' . TERMS_OF_USE_URL . '">Nutzungsbedingungen</a>.',
			'wegwandern-summit-book'
		);
		$rejected_from     = __( 'Dein WegWandern.ch-Team', 'wegwandern-summit-book' );
		$rejected_content .= '<br><br>' . $rejected_from;
		wp_mail( $comment_user->user_email, $rejected_subject, $rejected_content );
	}
}

/**
 * Set cron job to delete all spam comments older than 4 weeks
 */
function wegwandern_summit_book_old_spam_delete() {
	// Comments marked spam and older than 4 weeks.
	$args     = array(
		'date_query' => array(
			array(
				'before'    => '4 weeks ago',
				'inclusive' => true,
			),
		),
		'status'     => 'spam',
	);
	$comments = get_comments( $args );
	foreach ( $comments as $comment ) {
		wp_delete_comment( $comment->comment_ID, true );
	}
}

add_action( 'wegwandern_summit_book_spam_comment_daily_cron', 'wegwandern_summit_book_old_spam_delete' );

if ( ! wp_next_scheduled( 'wegwandern_summit_book_spam_comment_daily_cron' ) ) {
	wp_schedule_event( time(), 'daily', 'wegwandern_summit_book_spam_comment_daily_cron' );
}
