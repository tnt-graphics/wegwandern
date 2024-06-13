<?php if ( is_plugin_active( 'wegw-b2b/wegw-b2b.php' ) ) {
	$select_category    = get_field( 'select_category' );
	$select_hike_region =  get_field( 'select_wanderregion' );
	$select_ad          = get_field( 'select_ad' );
	$single_ad_layout   = get_field( 'single_ad_layout' );
	$display_google_ads = get_field( 'display_google_ads' );
	
	
	$angebote_slider_ad_script = '';
	$ad_counter                = 1;
	$ads_title                 = '';
	$single_ad_loop            = '';
	$html                      = '';
	$subtitle_ads_slider       = get_field( 'subtitle_ads_slider' );
	if ( is_array( $select_category ) && 1 == count( $select_category ) ) {
		$adterm    = get_term( $select_category[0] );
		$ads_title = $adterm->name;
	}
	if ( have_rows( 'manage_ad_scripts', 'option' ) ) :
		while ( have_rows( 'manage_ad_scripts', 'option' ) ) :
			the_row();
			$angebote_slider_ad_script = get_sub_field( 'angebote_slider_ad_script', 'option' );
	endwhile;
	endif;
	$ads_title = ( '' != $ads_title ) ? $ads_title : 'Angebote';
	if ( '' != $select_ad ) {
		$args = array(
			'post_type'      => 'b2b-werbung',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish' ),
			'p'              => $select_ad, // ID of a page, post, or custom type , 'pending', 'draft', 'future'
		);
	} 
	// args in case there is a selected region in gutenberg Block as slider
	else if ( $select_hike_region ) {
		$args = array(
			'post_type'      => 'b2b-werbung',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish' ),
			'tax_query'      => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'kategorie',
					'terms'    => $select_category,
					'field'    => 'id',
					'operator' => 'IN',
				),
				array(
					'taxonomy' => 'wanderregionen',
					'terms'    => $select_hike_region,
					'field'    => 'id',
					'operator' => 'IN',
				)
			),
		);
	} else {
		$args = array(
			'post_type'      => 'b2b-werbung',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish' ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'kategorie',
					'terms'    => $select_category,
					'field'    => 'id',
					// 'include_children' => true,
					'operator' => 'IN',
				)
			),
		);
	}
	$args['orderby'] = 'rand';
	// print_r( get_posts($args));
	$b2b_ads_listing_loop = new WP_Query( $args );

	if ( $b2b_ads_listing_loop->have_posts() ) :
		 $angebote_count = $b2b_ads_listing_loop->found_posts;

		switch ( true ) {
			case ( 2 === $angebote_count ):
				$outer_div   = 'angebote_2col_wrapper';
				$wrap_div    = 'angebote_2col';
				$single_wrap = 'angebote_2col_inner';
				break;
			case ( 1 === $angebote_count ):
				$outer_div = ( $select_ad == '' ) ? 'angebote_wrapper_5_5' : ( ( 'small' === $single_ad_layout ) ? 'angebote_wrapper_5_5' : 'angebote_wrapper_7_3' );
				break;
			case ( 3 === $angebote_count ):
				$outer_div   = 'angebote_3col_wrapper';
				$wrap_div    = 'angebote_3col';
				$single_wrap = 'angebote_3col_inner';
				break;
			case ( $angebote_count >= 4 ):
				$single_wrap = 'angebote_slide_inner';
			default:
				break;
		}

		?>
		<?php
		while ( $b2b_ads_listing_loop->have_posts() ) :
			$b2b_ads_listing_loop->the_post();
			$ad_ID               = get_the_ID();
			$current_post_status = get_post_status();
			$post_date           = get_the_time( 'd.m.Y', $ad_ID );

			$get_b2b_ad_image = get_post_meta( $ad_ID, 'wegw_b2b_ad_image', true );
			// echo attachment_url_to_postid( $get_b2b_ad_image );
			// $get_wegw_b2b_ad_main_title  = get_post_meta( $ad_ID, 'wegw_b2b_ad_main_title', true );
			$get_wegw_b2b_ad_bold_text   = get_post_meta( $ad_ID, 'wegw_b2b_ad_bold_text', true );
			$get_wegw_b2b_ad_link        = get_post_meta( $ad_ID, 'wegw_b2b_ad_link', true );
			$get_wegw_b2b_ad_credits_end = get_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end', true );

			if ( has_post_thumbnail( $ad_ID ) ) {
				// $b2b_ad_image      = isset( $get_b2b_ad_image ) ? $get_b2b_ad_image : '';
				if ( wp_is_mobile() ) {
					$b2b_ad_image      = get_the_post_thumbnail_url( $ad_ID, 'hike-region' );
				} else {
					$b2b_ad_image      = get_the_post_thumbnail_url( $ad_ID, 'b2b-slider-listing' );
				}
			} else {
				$b2b_ad_image = "";
			}
			
			$b2b_ad_main_title = get_the_title();
			$b2b_ad_bold_text  = isset( $get_wegw_b2b_ad_bold_text ) ? $get_wegw_b2b_ad_bold_text : '';
			$b2b_ad_link       = isset( $get_wegw_b2b_ad_link ) ? $get_wegw_b2b_ad_link : '';

			$b2b_ad_bold_html = ( '' != $b2b_ad_bold_text ) ? '<b>' . wp_trim_words( $b2b_ad_bold_text, 50, '...' ) . '</b>' : '';
			$ads_html         = '';
			if ( $angebote_slider_ad_script != '' && $display_google_ads && $ad_counter == 2 && 3 < $angebote_count ) {
				$ads_html = '<div class="angebote_slide_inner"><div class="slider-ad"></div>' . $angebote_slider_ad_script . '</div>';
			}
			if ( 1 === $angebote_count ) {
				$single_ad_loop .= '<div class="angebote_image_wrapper">
					<img class="" src="' . $b2b_ad_image . '">
					</div>
					<div class="angebote_content_wrapper">
						<h6>' . $ads_title . '</h6>
						<h3>' . $b2b_ad_main_title . '</h3>
						<p>' . wp_trim_words( get_the_content(), 110, '...' ) . $b2b_ad_bold_html . ' </p>
							<a href="' . $b2b_ad_link . '" target="_blank" onclick="b2b_ad_click_calculate(' . $ad_ID . ')"><span></span>zum Angebot</a>
				</div>';
			} else {
				if ( $ad_counter > 12 ) {
					break;
				}

				$single_ad_loop .= '<div class="' . $single_wrap . '">
					<img class="" src="' . $b2b_ad_image . '">
					<h6>' . $b2b_ad_main_title . '</h6>
					<p>' . wp_trim_words( get_the_content(), 110, '...' ) . $b2b_ad_bold_html . ' </p>
					<a href="' . $b2b_ad_link . '" target="_blank" onclick="b2b_ad_click_calculate(' . $ad_ID . ')"><span></span>zum Angebot</a>
				</div>' . $ads_html;
			}
			$ad_counter++;
	endwhile;
		wp_reset_postdata();

		if ( 2 === $angebote_count || 3 === $angebote_count ) {
			$html .= '<div class="' . $outer_div . '">
				<h3>' . $ads_title . '</h3>
				<h6>' . $subtitle_ads_slider . '</h6>
				<div class="' . $wrap_div . '">

				' . $single_ad_loop . '
				</div>
			</div>';
		}
		if ( 3 < $angebote_count ) {
			$btn = '';
			$alle_angebote_link = get_field( 'alle_angebote_link' );
			if ( ( 12 < $angebote_count ) && '' != $alle_angebote_link ) {
				
				$btn = '<a href="'. $alle_angebote_link .'"><div class="wander-in-region-btn">' . esc_html__( 'Alle Angebote', 'wegwandern' ) . '</div></a>';

			}
			$html .= '<div class="container-fuild grey-back pad40 full-width-slider">
				<div class="angebote_slider_wrapper">
					<div class="angebote-head">
						<h3 class="full-width-slider-title">' . $ads_title . '<span class="counter-in-angebote">' . $angebote_count . '</span></h3>
						<h6>' . $subtitle_ads_slider . '</h6>
					</div>
						<div class="angebote_slider owl-carousel owl-theme">  
						' . $single_ad_loop . '
						</div>
				</div>' . $btn . '
			</div>';

		}
		if ( 1 === $angebote_count ) {
			$html .= '<div class="' . $outer_div . '">' . $single_ad_loop . '</div>';
		}
		echo $html;
		?>
	
		<?php
endif;
}
