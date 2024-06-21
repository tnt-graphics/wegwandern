<?php
/**
 * Custom shortcodes for summit book
 *
 * @package wegwandern-summit-book
 */

add_action( 'init', 'summit_book_add_custom_shortcode' );

/**
 * Shortcodes of summit book
 */
function summit_book_add_custom_shortcode() {
	add_shortcode( 'display-summit-book-user-menu', 'display_summit_book_user_menu_content' );
	add_shortcode( 'display-hike-average-rating', 'display_hike_average_rating' );
}

/**
 * Display navigation for user menu
 *
 * @param array $atts shortcode attributes.
 */
function display_summit_book_user_menu_content( $atts ) {
	$atts = shortcode_atts(
		array(
			'orientation' => 'horizontal',
		),
		$atts,
		'display-summit-book-user-menu'
	);
	ob_start();
	global $current_user;
	if ( ! $current_user || ! $current_user->ID ) {
		return ob_get_clean();
	}

	$profile_fields = get_summit_book_profile_fields();
	$profile_completion = get_user_meta( $current_user->ID, 'profile_completion', true );

	//echo "<pre>"; print_r( $profile_fields ); echo "</pre>";

	if( 'yes' !== $profile_completion ){
		if( !empty( $profile_fields['gender'] ) && !empty( $profile_fields['firstname'] ) && !empty( $profile_fields['lastname'] ) && !empty( $profile_fields['email'] ) ){
			update_user_meta( $current_user->ID, 'profile_completion', 'yes' );
			$profile_completion  = 'yes';
		}
	}

	if ( ! $profile_completion || 'yes' !== $profile_completion ) {
		
		//var_dump($profile_completion);?>
	
		<div class="user-navigation <?php echo esc_attr( $atts['orientation'] ); ?>"><?php
			$menu_array = array(
				'menu'       => wp_get_nav_menu_object( 'Summit Book User Profile Not Completed' ),
				'menu_class' => $atts['orientation'] === 'horizontal' ? 'owl-carousel' : 'menu',
			);
			//echo "<pre>"; print_r(  $menu_array ); echo "</pre>";
			echo wp_nav_menu( $menu_array );
			?>
		</div><?php

		return ob_get_clean();
	}
	?>
	<div class="user-navigation <?php echo esc_attr( $atts['orientation'] ); ?>">
		<?php
		$menu_array = array(
			'menu'       => wp_get_nav_menu_object( SUMMIT_BOOK_USER_MENU_NAME ),
			'menu_class' => $atts['orientation'] === 'horizontal' ? 'owl-carousel' : 'menu',
		);
		echo wp_nav_menu( $menu_array );
		?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Display the average rating of a hike
 *
 * @param array $atts shortcode attributes.
 */
function display_hike_average_rating( $atts ) {
	$atts = shortcode_atts(
		array(
			'wanderung_post_id' => '',
		),
		$atts,
		'display-hike-average-rating'
	);
	if ( $atts['wanderung_post_id'] !== '' ) {
		$wanderung_id = $atts['wanderung_post_id'];
	} else {
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
	echo esc_attr( $avg_rating );
}
