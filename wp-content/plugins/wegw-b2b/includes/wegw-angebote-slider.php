<?php
function angebote_slider_display() {
	global $post;

	$wanderung_id        = $post->ID;
	$wanderregionen      = get_the_terms( $wanderung_id, 'wanderregionen' );
	$wanderregionen_id   = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->term_id : 'Region';
	$ad_counter          = 1;
	$ads_title           = '';
	$single_ad_loop      = '';
	$html                = '';
	$subtitle_ads_slider = '';
	$cat_array           = array();
	$args                = array(
		'post_type'      => 'b2b-werbung',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish' ),
		'orderby'        => 'rand',
		'tax_query'      => array(
			array(
				'taxonomy' => 'wanderregionen',
				'field'    => 'term_id',
				'terms'    => $wanderregionen_id,
			),
		),
	);
	$check_ad_exists     = get_posts( $args );

	if ( empty( $check_ad_exists ) ) {
		$args = array(
			'post_type'      => 'b2b-werbung',
			'post_status'    => array( 'publish' ),
			'orderby'        => 'rand',
			'posts_per_page' => 12,
		);
	}
	$b2b_ads_listing_loop = new WP_Query( $args );

	?>
   
	<?php
	if ( $b2b_ads_listing_loop->have_posts() ) :
		$angebote_count = $b2b_ads_listing_loop->found_posts;

		switch ( true ) {
			case ( 2 === $angebote_count ):
				$outer_div   = 'angebote_2col_wrapper';
				$wrap_div    = 'angebote_2col';
				$single_wrap = 'angebote_2col_inner';
				break;
			case ( 1 === $angebote_count ):
				$outer_div = 'angebote_wrapper_5_5';
				break;
			case ( 3 === $angebote_count ):
				$outer_div   = 'angebote_3col_wrapper';
				$wrap_div    = 'angebote_3col';
				$single_wrap = 'angebote_3col_inner';
				break;
			case ( 4 <= $angebote_count ):
				$single_wrap = 'angebote_slide_inner';
			default:
				break;
		}

		while ( $b2b_ads_listing_loop->have_posts() ) :
			$b2b_ads_listing_loop->the_post();

			$ad_ID               = get_the_ID();
			$b2b_ad_title        = get_the_title();
			$current_post_status = get_post_status();
			$post_date           = get_the_time( 'd.m.Y', $ad_ID );
			$b2b_category        = get_the_terms( $ad_ID, 'kategorie' );
			$ads_title           = 'Angebote';

			if ( ! empty( $b2b_category ) ) {
				if ( count( $b2b_category ) == 1 && ! in_array( $b2b_category[0]->name, $cat_array ) ) {
					array_push( $cat_array, $b2b_category[0]->name );
				}

				if ( count( $cat_array ) > 1 || ( count( $b2b_category ) > 1 ) ) {
					$ads_title = 'Angebote';
				} else {
					$ads_title = $b2b_category[0]->nam;
				}
			}

			$get_b2b_ad_image = get_post_meta( $ad_ID, 'wegw_b2b_ad_image', true );

			// $get_wegw_b2b_ad_main_title  = get_post_meta( $ad_ID, 'wegw_b2b_ad_main_title', true );
			$get_wegw_b2b_ad_bold_text   = get_post_meta( $ad_ID, 'wegw_b2b_ad_bold_text', true );
			$get_wegw_b2b_ad_link        = get_post_meta( $ad_ID, 'wegw_b2b_ad_link', true );
			$get_wegw_b2b_ad_credits_end = get_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end', true );

			$b2b_ad_image = isset( $get_b2b_ad_image ) ? $get_b2b_ad_image : '';
			// $b2b_ad_main_title = isset( $get_wegw_b2b_ad_main_title ) ? $get_wegw_b2b_ad_main_title : '';
			$b2b_ad_bold_text = isset( $get_wegw_b2b_ad_bold_text ) ? $get_wegw_b2b_ad_bold_text : '';
			$b2b_ad_link      = isset( $get_wegw_b2b_ad_link ) ? $get_wegw_b2b_ad_link : '';

			$b2b_ad_bold_html = ( '' != $b2b_ad_bold_text ) ? '<b>' . wp_trim_words( $b2b_ad_bold_text, 50, '...' ) . '</b>' : '';

			if ( 1 === $angebote_count ) {
				$single_ad_loop .= '<div class="angebote_image_wrapper">
					<img class="" src="' . $b2b_ad_image . '">
					</div>
					<div class="angebote_content_wrapper">
						<h6>' . $b2b_category[0]->name . '</h6>
						<h3>' . $b2b_ad_title . '</h3>
						<p>' . wp_trim_words( get_the_content(), 110, '...' ) . $b2b_ad_bold_html . ' </p>
							<a href="' . $b2b_ad_link . '" target="_blank" onclick="b2b_ad_click_calculate(' . $ad_ID . ')"><span></span>zum Angebot</a>
				</div>';
			} else {
				if ( $ad_counter > 12 ) {
					break;
				}

				$single_ad_loop .= '<div class="' . $single_wrap . '">
					<img class="" src="' . $b2b_ad_image . '">
					<h6>' . $b2b_ad_title . '</h6>
					<p>' . wp_trim_words( get_the_content(), 110, '...' ) . $b2b_ad_bold_html . ' </p>
					<a href="' . $b2b_ad_link . '" target="_blank" onclick="b2b_ad_click_calculate(' . $ad_ID . ')"><span></span>zum Angebot</a>
				</div>';
			}

			$ad_counter++;
		endwhile;

		wp_reset_postdata();

		if ( ( 2 === (int) $angebote_count ) || ( 3 === (int) $angebote_count ) ) {
			$ads_title_upd = ( $ads_title != '' ) ? $ads_title : 'Angebote';
			$html         .= '<div class="' . $outer_div . '">
				<h3>' . $ads_title_upd . '</h3>
				<h6>' . $subtitle_ads_slider . '</h6>
				<div class="' . $wrap_div . '">

				' . $single_ad_loop . '
				</div>
			</div>';
		}

		if ( 3 < $angebote_count ) {
			$btn = '';

			if ( 12 < $angebote_count ) {
				$btn = '<a href="/angebote/"><div class="wander-in-region-btn">' . esc_html__( 'Alle Angebote', 'wegwandern' ) . '</div></a>';

			}
			$html .= '<div class="container-fuild grey-back pad40 full-width-slider">
				<div class="angebote_slider_wrapper">
					<div class="angebote-head">
						<h3 class="full-width-slider-title">' . $ads_title . '<span class="counter-in-angebote">' . $angebote_count . '</span></h3>
						<h6>' . $subtitle_ads_slider . '</h6>
					</div>
						<div class="angebote_slider owl-theme owl-carousel">  
						' . $single_ad_loop . '
						</div>
				</div>' . $btn . '
			</div>';
		}

		if ( 1 === $angebote_count ) {
			$html .= '<div class="' . $outer_div . '">' . $single_ad_loop . '</div>';
		}

		echo $html;
	endif;
}
