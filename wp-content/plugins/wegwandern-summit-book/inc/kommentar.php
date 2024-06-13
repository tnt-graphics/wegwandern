<?php
/**
 * Functions to implement custom comment form in tour detail
 *
 * @package wegwandern-summit-book
 */

/**
 * To insert formidable comment form to tour detail
 */
function wegwandern_summit_book_add_comment_form() {
	ob_start();
	$comment_form_id = FrmForm::get_id_by_key( 'commentsform' );
	// $star_rating_form_id = FrmForm::get_id_by_key( 'star-rating-form' );
	?>
	<div class="container-fluid grey-back community-section-main">
		<div class="comments-section-wrapper">
			<div class='comment-form'>
	<?php
	$community_text                         = __( 'Community', 'wegwandern-summit-book' );
	$registration_link                      = "<a class='create-account' onclick='openRegPoppup(" . 'commentRegMenu' . ")'>Gratis-Account (hier erstellen)</a>";
	$community_desc_not_loggedin_text_first = __(
		'Mit einem ',
		'wegwandern-summit-book'
	);
	$community_desc_not_loggedin_text_last  = __(
		' kannst du Wanderungen bewerten, Fotos hochladen und anderen Wanderinnen und Wandern wertvolle Tipps geben.',
		'wegwandern-summit-book'
	);
	$community_desc_not_loggedin_text       = __(
		"Mit einem $registration_link kannst du Wanderungen bewerten, Fotos hochladen und anderen Wanderinnen und Wandern wertvolle Tipps geben.",
		'wegwandern-summit-book'
	);
	$community_desc_loggedin_text           = __( 'Du bist eingeloggt', 'wegwandern-summit-book' );
	$comment_form_heading                   = __( 'Meine Bewertung', 'wegwandern-summit-book' );
	$comment_list_heading                   = __( 'Bewertungen der Community', 'wegwandern-summit-book' );
	$comment_logged_in_info                 = __( 'Kommentare und Fotos werden vor der Veröffentlichung geprüft. Dies kann einige Tage dauern. An Wochenenden und Feiertagen werden keine Kommentare veroffentlicht publiziert.', 'wegwandern-summit-book' );
	$date_from_text                         = __( 'vor', 'wegwandern-summit-book' );
	$show_full_comment_text                 = __( 'Mehr anzeigen', 'wegwandern-summit-book' );
	global $post;
	?>
	<h2 class='comment-heading'><?php echo esc_attr( $community_text ); ?></h2>
	<div class='comment-section'>
	<div class='comment-desc-section'>
		<?php
		$current_user = wp_get_current_user();
		if ( is_user_logged_in() && in_array( SUMMIT_BOOK_USER_ROLE, $current_user->roles ) ) {
			echo "<p class='comment-desc'>" . $community_desc_loggedin_text . '</p>';
			$form_class = '';
			?>
			<div class='logged-in-user-info'>
				<div class='logged-in-user-avatar-name'>
					<?php
					echo get_user_avatar();
					echo '<h3>' . get_user_display_name() . '</h3>';
					?>
				</div>
				<?php echo "<p class='logged-in-description'>" . $comment_logged_in_info . '</p>'; ?>
			</div>
			<?php
		} elseif ( is_user_logged_in() && in_array( B2B_USER_ROLE, $current_user->roles ) ) {
			?>
			<p class='comment-desc'>
				<?php echo __( 'Ihr Konto verfügt noch nicht über Community-Funktionen.', 'wegwandern-summit-book' ); ?>
				<a><span onclick="openUserRolePopup()"><?php echo __( 'Registrieren Sie sich als Gipfelbuchbenutzer?', 'wegwandern-summit-book' ); ?></span></a>
			</p>
			<?php
			$form_class = 'disabled';
		} else {
			?>
			<p class='comment-desc'>
				<?php echo $community_desc_not_loggedin_text_first; ?>
				<a class='create-account' onclick="openRegPoppup('summitRegMenu')">
				<?php echo __( 'Gratis-Account (hier erstellen)', 'wegwandern-summit-book' ); ?>
				</a>
				<?php echo $community_desc_not_loggedin_text_last; ?>
				<br>
				<?php echo __( 'Sie haben bereits ein Konto?', 'wegwandern-summit-book' ); ?>
				<a class='comment-login' onclick="openSummitBookLoginMenuInKomment()">
				<?php echo __( 'Login', 'wegwandern-summit-book' ); ?>
				</a>
			</p>	
			<?php
			$form_class = 'disabled';
		}
		?>
	</div>
	<div class='comment-form-section <?php echo $form_class; ?>'>
	<h3 class='comment-sub-heading'><?php echo $comment_form_heading; ?></h3>

	<?php // echo do_shortcode( '[formidable id=' . $star_rating_form_id . ']' ); ?>
	<?php echo do_shortcode( '[formidable id=' . $comment_form_id . ']' ); ?>
	
	</div>
	</div>
	</div>
	<?php
	$args         = array(
		'post_id' => $post->ID,
		'status'  => 'approve',
	);
	$all_comments = get_comments( $args );
	if ( ! empty( $all_comments ) ) {
		?>
		<div class='comments-list'><h2 class='comment-list-heading'><?php echo $comment_list_heading; ?></h2>
		<?php
		foreach ( $all_comments as $comment ) {
			?>
			<div class='each-comment'>
			<div class='comment-author-info'>
				<?php
				$rating_post_id = get_user_bewertung( $comment->user_id, $post->ID );
				$rating         = get_post_meta( $rating_post_id, 'rating', true );
				?>
			<div class='comment-author-img'><?php echo get_user_avatar($comment->user_id); ?>
		</div>
			<div class='comment-detail'><div class='comment-author-name'><?php echo get_user_display_name( $comment->user_id ); ?>
			</div>
			<div class='comment-date'><?php echo $date_from_text . ' ' . human_time_diff( strtotime( $comment->comment_date_gmt ) ); ?>
		</div>
			<?php echo show_star_rating( $rating ); ?>
			</div>
			</div>
			<div class='author-comment'>
			<p class='each-comment-inner'>
				<?php
				if ( strlen( $comment->comment_content ) > 480 ) {
					echo "<span class='short-comment-version'>" . substr( $comment->comment_content, 0, 478 ) . " ...<a class='show-full-comment'> $show_full_comment_text</a></span><span class='hide long-comment-version'>" . $comment->comment_content . '</span>';
				} else {
					echo $comment->comment_content;
				}
				?>
			</p>
			<?php echo show_comment_images( $comment->comment_ID ); ?>
			</div>
			</div>
			<?php
		}
		?>
		</div>
		<?php
	}
	$wanderregionen = get_the_terms( $post->ID, 'wanderregionen' );
	if ( ! empty( $wanderregionen ) ) {
		$related_articles = get_articles_of_region( $wanderregionen[0]->term_id );
		if ( ! empty( $related_articles ) ) {
			echo "<div class='community-articles'>";
			$community_article_heading = __( 'Wanderungen der Community', 'wegwandern-summit-book' );
			echo "<h2>$community_article_heading</h2>";
			echo "<div class='each-article-wrapper'>";
			foreach ( $related_articles as $each_article ) {
				$community_article_link = get_permalink( $each_article );
				echo "<div class='each-article'>";
				$teaser_image = get_post_meta( $each_article->ID, 'teaser_image', true );
				$image_url    = wp_get_attachment_image_url( $teaser_image, 'full' );
				echo "<div class='article-img'><a href='$community_article_link'><img src='" . $image_url . "'></a></div>";
				echo "<div class='article-sub-section'>";
				echo "<div class='article-region'>" . $wanderregionen[0]->name . '</div>';
				echo "<div class='article-title'><a href='$community_article_link'>" . get_post_meta( $each_article->ID, 'titel', true ) . '</a></div>';
				echo "<div class='article-author'>" . get_user_avatar($each_article->post_author) . get_user_display_name( $each_article->post_author ) . '</div>';
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
		}
	}
	?>
	</div>
	</div>
	<?php
	ob_flush();
}

add_filter( 'human_time_diff', 'customize_date_as_german', 10, 4 );

/**
 * Replace date words to german language
 *
 * @param string $since date from.
 * @param string $diff date diff.
 * @param string $from date from.
 * @param string $to date to.
 */
function customize_date_as_german( $since, $diff, $from, $to ) {
	$replace = array(
		'second'  => __( 'Sekunde', 'wegwandern-summit-book' ),
		'seconds' => __( 'Sekunden', 'wegwandern-summit-book' ),
		'min'     => __( 'Minute', 'wegwandern-summit-book' ),
		'mins'    => __( 'Minuten', 'wegwandern-summit-book' ),
		'hour'    => __( 'Stunde', 'wegwandern-summit-book' ),
		'hours'   => __( 'Stunden', 'wegwandern-summit-book' ),
		'day'     => __( 'Tag', 'wegwandern-summit-book' ),
		'days'    => __( 'Tagen', 'wegwandern-summit-book' ),
		'week'    => __( 'Woche', 'wegwandern-summit-book' ),
		'weeks'   => __( 'Wochen', 'wegwandern-summit-book' ),
		'month'   => __( 'Monat', 'wegwandern-summit-book' ),
		'months'  => __( 'Monaten', 'wegwandern-summit-book' ),
	);
	return strtr( $since, $replace );
}

/**
 * Get link to tour to display in user dashboard
 *
 * @param int $tour_id id of the tour.
 */
function get_tour_link( $tour_id ) {
	$tour      = get_post( $tour_id );
	$tour_link = get_permalink( $tour );
	return "<a href='$tour_link'>$tour->post_title</a>";
}

/**
 * Display star rating given a rating value
 *
 * @param int $rating rating to be displayed as stars.
 */
function show_star_rating( $rating ) {
	$html  = "<div class='average-star-rating'>";
	$html .= "<div class='frm-star-group'>";
	for ( $i = 0; $i < 5; $i++ ) {
		// $star_on = $i < $rating ? 'star-rating-on' : '';
		$star_on = $i < $rating ? 'fa-star' : 'fa-star-o';
		$html   .= "<i class='star-rating-readonly star-rating fa $star_on '></i>";
	}
	$html .= '</div></div>';
	return $html;
}

/**
 * Display comment images
 *
 * @param int $comment_id id of the comment.
 */
function show_comment_images( $comment_id, $display_location = null ) {
	$comment_imgs = get_comment_meta( $comment_id, 'comment_images', true );
	$html = "";
	if ( $comment_imgs && ! empty( $comment_imgs ) ) {
		$comments_class = 'comment-imgs owl-carousel owl-theme';
		if ( $display_location === 'user-dashboard' ) {
			$comments_class = 'user-comment-imgs';
		}
		$html = "<div class='$comments_class'>";
		foreach ( $comment_imgs as $each_img ) {
			if ( '' !== $each_img ) {
				$image_url = wp_get_attachment_image_url( $each_img, 'full' );
				$html     .= "<div class='each-comment-img'><img src='" . $image_url . "'/></div>";
			}
		}
		$html .= '</div>';
	}
	return $html;
}

add_action( 'delete_user', 'delete_comments_of_user', 10 );

/**
 * Delete community beitrag of user when deleted
 *
 * @param int $user_id id of the user getting deleted.
 */
function delete_comments_of_user( $user_id ) {

	$args     = array(
		'user_id' => $user_id,
	);
	$comments = get_comments( $args );
	if ( ! empty( $comments ) ) {
		foreach ( $comments as $each_comment ) {
			wp_delete_comment( $each_comment->comment_ID );
		}
	}

}

/**
 * Display the average rating in frontend
 *
 * @param integer|null $wanderung_id id of the hike or null.
 */
function get_wanderung_average_rating( $wanderung_id = null ) {
	if ( ! $wanderung_id ) {
		global $post;
		$wanderung_id = $post->ID;
	}
	$args            = array(
		'post_type'   => 'bewertung',
		'meta_key'    => 'rated_wanderung',
		'meta_value'  => $wanderung_id,
		'numberposts' => -1,
	);
	$ratings_of_hike = get_posts( $args );
	$total_rating    = 0;
	$avg_rating      = 0;
	if ( ! empty( $ratings_of_hike ) ) {
		$number_of_ratings = count( $ratings_of_hike );
		foreach ( $ratings_of_hike as $each_rating ) {
			$rating        = get_post_meta( $each_rating->ID, 'rating', true );
			$total_rating += $rating ?? 0;
		}
		$avg_rating = round( $total_rating / $number_of_ratings, 1 );
	}
	return $avg_rating;
}
