<?php
/**
 * Sample implementation of the Custom Header feature
 *
 * You can add an optional custom header image to header.php like so ...
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 *
 * @package Wegwandern
 */

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */

if ( function_exists( 'acf_add_options_page' ) ) {

	acf_add_options_page(
		array(
			'page_title' => 'Theme Options',
			'menu_title' => 'Theme Options',
			'menu_slug'  => 'theme-options',
			'capability' => 'edit_posts',
			'redirect'   => false,
		)
	);
}
function wegw_widgets_init() {

	register_sidebar(
		array(
			'name'          => esc_html__( 'Filter Widget', 'wegwandern' ),
			'id'            => 'filter_widget',
			'description'   => esc_html__( 'Add widgets here.', 'wegwandern' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title filt-col-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Widget', 'wegwandern' ),
			'id'            => 'footer_widget',
			'description'   => esc_html__( 'Add widgets here.', 'wegwandern' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title foot-col-title">',
			'after_title'   => '</h2>',
		)
	);

}

add_action( 'widgets_init', 'wegw_widgets_init' );

function get_wanderung_filter_query() {

	$query            = array(
		'post_type'      => 'wanderung',
		'posts_per_page' => 20,
		'post_status'    => 'publish',
	);
	$query['orderby'] = 'ID';
	$query['order']   = 'DESC';
	// print_r($query);
	return $query;
}

/*
 * Gets all published hikes - For cluster markers display
 */
function get_wanderung_all_hikes_query( $filtered_map_ids = null, $page_template = 'tourenportal_page' ) {
	// if ( ! isset( $query['orderby'] ) ) {
	// $query['orderby'] = 'publish_date';
	// }
	// if ( ! isset( $query['order'] ) ) {
	// $query['order']   = 'DESC';
	// }
	if ( isset( $filtered_map_ids ) && $filtered_map_ids != '' ) {
		$query = array(
			'post_type'      => 'wanderung',
			'posts_per_page' => -1,
			'post__in'       => $filtered_map_ids,
			'post_status'    => 'publish',
		);
	} else {
		$query = array(
			'post_type'      => 'wanderung',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
	}

	$map_coordinates        = array();
	$allwanderung           = get_posts( $query );
	$user_watchlisted_hikes = wegwandern_get_watchlist_hikes_list();

	foreach ( $allwanderung as $wanderung ) {
		setup_postdata( $wanderung );

		$wanderregionen = get_the_terms( $wanderung->ID, 'wanderregionen' );
		$hike_level     = get_the_terms( $wanderung->ID, 'anforderung' );
		$wander_saison  = wp_get_post_terms(
			$wanderung->ID,
			'wander-saison',
			array(
				'orderby' => 'term_id',
				'order'   => 'ASC',
			)
		);

		$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';

		$hike_level_name    = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';
		$wander_saison_name = wegw_wandern_saison_name( $wanderung->ID );
		$hike_level_cls     = wegw_wandern_hike_level_class_name( $hike_level_name, $wanderung->ID );

		$hike_time     = ( get_field( 'dauer', $wanderung->ID ) ) ? wegwandern_formated_hiking_time_display( get_field( 'dauer', $wanderung->ID ) ) : '';
		$hike_distance = ( get_field( 'km', $wanderung->ID ) ) ? get_field( 'km', $wanderung->ID ) : '';
		$hike_ascent   = ( get_field( 'aufstieg', $wanderung->ID ) ) ? get_field( 'aufstieg', $wanderung->ID ) : '';
		$hike_descent  = ( get_field( 'abstieg', $wanderung->ID ) ) ? get_field( 'abstieg', $wanderung->ID ) : '';
		$kurzbeschrieb = ( get_field( 'kurzbeschrieb', $wanderung->ID ) ) ? get_field( 'kurzbeschrieb', $wanderung->ID ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';

		/* Cluster updations */
		$latitude      = ( get_post_meta( $wanderung->ID, 'latitude', true ) ) ? get_post_meta( $wanderung->ID, 'latitude', true ) : '';
		$longitude     = ( get_post_meta( $wanderung->ID, 'longitude', true ) ) ? get_post_meta( $wanderung->ID, 'longitude', true ) : '';
		$gpx_file      = ( get_field( 'gpx_file', $wanderung->ID ) ) ? get_field( 'gpx_file', $wanderung->ID ) : '';
		$location_link = get_the_permalink( $wanderung->ID );
		$thumbsize     = ( $page_template == 'tourenportal_page' ) ? 'hike-listing' : 'hike-region';
		$post_thumb    = get_the_post_thumbnail_url( $wanderung->ID, $thumbsize );

		if ( in_array( $wanderung->ID, $user_watchlisted_hikes ) ) {
			$watchlisted = 1;
		} else {
			$watchlisted = 0;
		}

		$average_rating = 0;
		if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
			$average_rating = get_wanderung_average_rating( $wanderung->ID );
		}

		$map_coordinates[] = array(
			'longitude'                   => $longitude,
			'latitude'                    => $latitude,
			'location_regionen_name'      => $wanderregionen_name,
			// 'position_marker' => $position_marker,
			'location_feature_image'      => $post_thumb,
			'location_name'               => $wanderung->post_title,
			'location_desc'               => $kurzbeschrieb,
			'location_level_cls'          => $hike_level_cls,
			'location_level_name'         => $hike_level_name,
			'location_hike_time'          => $hike_time,
			'location_travel_distance'    => $hike_distance,
			'location_hike_ascent'        => $hike_ascent,
			'location_hike_descent'       => $hike_descent,
			'location_wander_saison_name' => $wander_saison_name,
			'location_link'               => $location_link,
			'location_id'                 => $wanderung->ID,
			'watchlisted'                 => $watchlisted,
			'average_rating'              => $average_rating,
		);

		$json_map_coordinates = @json_encode( $map_coordinates, true );
	}

	$posts_html = '<script type="text/javascript">var places =' . $json_map_coordinates . ';</script>';
	return $posts_html;
}

function wanderung_listing_fun( $allwanderung, $page_template = 'tourenportal_page' ) {

	$ad_title              = get_field( 'ad_title' );
	$ad_placement_pos_mark = get_field( 'ads_position' );

	$ad_script_desktop = '';
	$ad_script_tablet  = '';
	$ad_script_mobile  = '';

	$custom_ad_desktop = '';
	$custom_ad_tablet  = '';
	$custom_ad_mobile  = '';

	if ( have_rows( 'manage_ad_scripts', 'option' ) ) :
		while ( have_rows( 'manage_ad_scripts', 'option' ) ) :
			the_row();

			$desktop_ad_scripts = get_sub_field( 'desktop_ad_scripts', 'option' );
			$tablet_ad_scripts  = get_sub_field( 'tablet_ad_scripts', 'option' );
			$mobile_ad_scripts  = get_sub_field( 'mobile_ad_scripts', 'option' );

			foreach ( $desktop_ad_scripts as $desktop_ad ) {
				if ( $desktop_ad['ad_size'] = '300×600' ) {
					$ad_script_desktop = $desktop_ad['ad_script'];
				}
			}

			foreach ( $tablet_ad_scripts as $tablet_ad ) {
				if ( $tablet_ad['ad_size'] = '300×250' ) {
					$ad_script_tablet = $tablet_ad['ad_script'];
				}
			}

			foreach ( $mobile_ad_scripts as $mob_ad ) {
				if ( $mob_ad['ad_size'] = '300×250' ) {
					$ad_script_mobile = $mob_ad['ad_script'];
				}
			}

		endwhile;
	endif;

	$teaser_1_short_title  = get_field( 'teaser_1_short_title' );
	$teaser_1_title        = get_field( 'teaser_1_title' );
	$teaser_1_image        = get_field( 'teaser_1_image' );
	$teaser_1_redirect_url = get_field( 'teaser_1_redirect_url' );
	$teaser_2_short_title  = get_field( 'teaser_2_short_title' );
	$teaser_2_title        = get_field( 'teaser_2_title' );
	$teaser_2_image        = get_field( 'teaser_2_image' );
	$teaser_2_redirect_url = get_field( 'teaser_2_redirect_url' );

	$map_coordinates = array();
	// $position_marker = '//raw.githubusercontent.com/jonataswalker/map-utils/master/images/marker.png';

	$pos        = 1;
	$posts_html = '';

	foreach ( $allwanderung as $wanderung ) {
		setup_postdata( $wanderung );
		$custom_ad = '';

		if ( $pos == $ad_placement_pos_mark ) {

			if ( $ad_script_desktop != '' ) {
				$custom_ad_desktop = '<div class="ad-section-wrap header-ad-desktop-wrapper">
					<p>' . $ad_title . '</p>
					<div class="ad-section"> 
						 
					<!-- 300x250,300x600 / inside-quarter -->
					<div id="div-ad-gds-1280-3">
					<script type="text/javascript">
					gbcallslot1280("div-ad-gds-1280-3", "");
					</script>
					</div>
					
					</div>
				</div>';
			}

			if ( $ad_script_tablet != '' ) {
				$custom_ad_tablet = '<div class="ad-section-wrap header-ad-tablet-wrapper">
					<p>' . $ad_title . '</p>
					<div class="ad-section">tablet </div>
				</div>';
			}

			if ( $ad_script_mobile != '' ) {
				$custom_ad_mobile = '<div class="ad-section-wrap header-ad-mobile-wrapper">
					<p>' . $ad_title . '</p>
					<div class="ad-section">mobile </div>
				</div>';
			}

			$custom_ad = $custom_ad_desktop . $custom_ad_tablet . $custom_ad_mobile . '<div class="promo-section">
								<a href="' . $teaser_1_redirect_url . '" class="teaser-wrap">
								<img src="' . $teaser_1_image['sizes']['region-slider'] . '" class="teaser-section">
									
										<div class="teaser_info">
											<div class="sub_title">' . $teaser_1_short_title . '</div>
											<div class="title">' . $teaser_1_title . '</div>
										</div>
									
								</a>
								<a href="' . $teaser_2_redirect_url . '" class="teaser-wrap">
								<img src="' . $teaser_2_image['sizes']['region-slider'] . '" class="teaser-section">
								
										<div class="teaser_info">
											<div class="sub_title">' . $teaser_2_short_title . '</div>
											<div class="title">' . $teaser_2_title . '</div>
										</div>
									
								</a>
								</div>
								<a href="' . $teaser_1_redirect_url . '" class="teaser-wrap teaser-responsive">
								<img src="' . $teaser_1_image['sizes']['region-slider'] . '" class="teaser-section">
									
										<div class="teaser_info">
											<div class="sub_title">' . $teaser_1_short_title . '</div>
											<div class="title">' . $teaser_1_title . '</div>
										</div>
									
								</a>
								<a href="' . $teaser_2_redirect_url . '" class="teaser-wrap teaser-responsive"> 
								<img src="' . $teaser_2_image['sizes']['region-slider'] . '" class="teaser-section">
								
										<div class="teaser_info">
											<div class="sub_title">' . $teaser_2_short_title . '</div>
											<div class="title">' . $teaser_2_title . '</div>
										</div>
									
								</a>';
		}

		$wanderregionen = get_the_terms( $wanderung->ID, 'wanderregionen' );
		$hike_level     = get_the_terms( $wanderung->ID, 'anforderung' );

		$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';

		$hike_level_name    = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';
		$wander_saison_name = wegw_wandern_saison_name( $wanderung->ID );
		$hike_level_cls     = wegw_wandern_hike_level_class_name( $hike_level_name, $wanderung->ID );

		$hike_time     = ( get_field( 'dauer', $wanderung->ID ) ) ? wegwandern_formated_hiking_time_display( get_field( 'dauer', $wanderung->ID ) ) : '';
		$hike_distance = ( get_field( 'km', $wanderung->ID ) ) ? get_field( 'km', $wanderung->ID ) : '';
		$hike_ascent   = ( get_field( 'aufstieg', $wanderung->ID ) ) ? get_field( 'aufstieg', $wanderung->ID ) : '';
		$hike_descent  = ( get_field( 'abstieg', $wanderung->ID ) ) ? get_field( 'abstieg', $wanderung->ID ) : '';
		$kurzbeschrieb = ( get_field( 'kurzbeschrieb', $wanderung->ID ) ) ? get_field( 'kurzbeschrieb', $wanderung->ID ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';

		/* Cluster updations */
		$latitude      = ( get_post_meta( $wanderung->ID, 'latitude', true ) ) ? get_post_meta( $wanderung->ID, 'latitude', true ) : '';
		$longitude     = ( get_post_meta( $wanderung->ID, 'longitude', true ) ) ? get_post_meta( $wanderung->ID, 'longitude', true ) : '';
		$gpx_file      = ( get_field( 'gpx_file', $wanderung->ID ) ) ? get_field( 'gpx_file', $wanderung->ID ) : '';
		$location_link = get_the_permalink( $wanderung->ID );
		$thumbsize     = ( $page_template == 'tourenportal_page' ) ? 'hike-listing' : 'hike-region';
		$post_thumb    = get_the_post_thumbnail_url( $wanderung->ID, $thumbsize );

		$wander_region_html  = "<div class='single-region-rating'>";
		$wander_region_html .= "<h6 class='single-region'>" . $wanderregionen_name . '</h6>';
		if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
			$average_rating      = get_wanderung_average_rating( $wanderung->ID );
			$wander_region_html .= '<span class="average-rating-display">' . $average_rating . '<i class="fa fa-star"></i></span>';
		}
		$wander_region_html .= '</div>';

		$watchlisted_array   = wegwandern_get_watchlist_hikes_list();
		if ( in_array( $wanderung->ID, $watchlisted_array, false ) ) {
			$watchlisted_class = 'watchlisted';
			$watchlist_on_click = '';
		} else {
			$watchlisted_class   = '';
			$watchlist_on_click = ' onclick="addToWatchlist(this, ' . $wanderung->ID . ')" ';
		}

		$posts_html         .= '<div class="single-wander">
							<div class="single-wander-img">
								<a href="' . get_the_permalink( $wanderung->ID ) . '"><img class="wander-img" src="' . $post_thumb . '"></a>
								<div class="single-wander-heart ' . $watchlisted_class . '" ' . $watchlist_on_click . '></div>
								<div class="single-wander-map" onclick="openPopupMap(this)" data-hikeid="' . $wanderung->ID . '"></div>
							</div>' .
								$wander_region_html .
								'<a href="' . get_the_permalink( $wanderung->ID ) . '" class="wander-redirect"><h2>' . $wanderung->post_title . '</h2></a>
							<div class="wanderung-infobox">
								<div class="hiking_info">
									<div class="hike_level">
									<span class="' . $hike_level_cls . '"></span>
									<p>' . $hike_level_name . '</p>
									</div>
									<div class="hike_time">
									<span class="hike-time-icon"></span>
									<p>' . $hike_time . ' h</p>
									</div>
									<div class="hike_distance">
									<span class="hike-distance-icon"></span>
									<p>' . $hike_distance . ' km</p>
									</div>
									<div class="hike_ascent">
									<span class="hike-ascent-icon"></span>
									<p>' . $hike_ascent . ' m</p>
									</div>
									<div class="hike_descent">
									<span class="hike-descent-icon"></span>
									<p>' . $hike_descent . ' m</p>
									</div>
									<div class="hike_month">
									<span class="hike-month-icon"></span>
									<p>' . $wander_saison_name . '</p>
									</div>
							    </div>
							</div>
						<div class="wanderung-desc">
						' . mb_strimwidth( $kurzbeschrieb, 0, 200, '...' ) . '
					    </div>'.
					    '<div class="weg-map-popup-outter"><div id="weg-map-popup'.$wanderung->ID.'" ><div class="map-fixed-position">'.
          '<div id="weg-map-popup-inner-wrapper'.$wanderung->ID.'">'.
            '<div class="close_map" onclick="closeElement(this)"><span class="close_map_icon"></span></div>'.
            '<div id="cesiumContainer" class="cesiumContainer"></div>'.
            '<div class="map_currentLocation"></div>'.
            '<div id="threeD" class="map_3d"></div>'.
            '<div id="map_direction" class="map_direction"></div>'.
            '<div class="botom_layer_icon">'.
              '<div class="accordion" >'.
                '<div class="weg-layer-wrap layer_head">'.
                  '<div class="weg-layer-text">Hintergrund</div>'.
                '</div>'.
              '</div>'.
              '<div class="panel">'.
                '<div class="weg-layer-wrap activeLayer" id="colormap_view_section">'.
                  '<div class="weg-layer-text">Karte farbig</div>'.
                '</div>'.
                '<div class="weg-layer-wrap" id="aerial_view_section">'.
                  '<div class="weg-layer-text">Luftbild</div>'.
                '</div>'.
                '<div class="weg-layer-wrap" id="grey_view_section">'.
                  '<div class="weg-layer-text">Karte SW</div>'.
                '</div>'.
              '</div>'.
            '</div>'.
            '<div class="copyRight">'.
              '<a target="_blank" href="https://www.swisstopo.admin.ch/de/home.html">© swisstopo</a>'.
            '</div>'.
            '<div class="map_filter">'.
              '<div class="map_filter_inner_wrapper">'.
                '<div class="accordion">Karteninformationen</div>'.
                '<div class="panel">'.
                  '<div class="fc_check_wrap">'.
                    '<label class="check_wrapper">ÖV-Haltestellen'.
                      '<input type="checkbox" name="" id="transport_layer_checkbox" value="">'.
                      '<span class="redmark"></span>'.
                    '</label>'.
                    '<label class="check_wrapper">Wanderwege'.
                      '<input type="checkbox" name="" id="hikes_trailing_layer" value="">'.
                      '<span class="redmark"></span>'.
                    '</label>'.
                    '<label class="check_wrapper">Gesperrte Wanderwege'.
                      '<input type="checkbox" name="" id="closure_hikes_layer" value="">'.
                      '<span class="redmark"></span>'.
                    '</label>'.
                    '<label class="check_wrapper">Schneehöhe ExoLabs'.
                      '<input type="checkbox" name="" id="snow_depth_layer" value="">'.
                      '<span class="redmark"></span>'.
					  					'<div class="info_icon" onclick="infoIconClicked(event,&quot;weg-map-popup'.$wanderung->ID.'&quot;)"></div>'.
                    '</label>'.
                    '<label class="check_wrapper">Schneebedeckung ExoLabs'.
                      '<input type="checkbox" name="" id="snow_cover_layer" value="">'.
                      '<span class="redmark"></span>'.
                    '</label>'.
                    '<label class="check_wrapper">Hangneigungen über 30°'.
                      '<input type="checkbox" id="slope_30_layer" name="" value="">'.
                      '<span class="redmark"></span>'.
                    '</label>'.
                    '<label class="check_wrapper">Wildruhezonen'.
                      '<input type="checkbox" id="wildlife_layer" name="" value="">'.
                      '<span class="redmark"></span>'.
                    '</label>'.
                    '<label class="check_wrapper">Wegpunkte WegWandern.ch'.
                      '<input type="checkbox" id="waypoints_layer" name="" value="">'.
                      '<span class="redmark"></span>'.
                    '</label>'.
                  '</div>'.
                '</div>'.
              '</div>'.
            '</div>'.
          '</div>'.
          '<div id="detailPgPopup"><div id="detailPgPopupContent"></div></div>'.
          '<div class="elevationGraph"></div> '.
          '<div class="options" id="mapOptions"></div>'.
          '<div class="snow_info_details hide">'.
              '<div class="snow_inner_wrapper">'.
                '<div class="snow_close_wrapper" onclick="infoIconClosed(event,&quot;weg-map-popup'.$wanderung->ID.'&quot;)"><div class="snow_close"></div></div>'.
                '<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>'.
                '<div class="snow_image"></div>'.
                '<a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>'.
              '</div>'.
            '</div>'.
          '<div id="info"></div>'.
          '<div class="popover" id="transport-layer-info-popup">'.
            '<div class="arrow"></div>'.
            '<div class="popover-title">'.
              '<div class="popup-title">Objekt Informationen</div>'.
              '<div class="popup-buttons">'.
               
                '<button class="fa fa-remove" title="Close" onclick="closeTransportLayerPopup()"></button>'.
              '</div>'.
            '</div>'.
            '<div class="popover-content">'.
              '<div class="popover-scope">'.
                '<div class="popover-binding">'.
                  '<div class="htmlpopup-container" id="tl-content-area">'.
                  '</div>'.
                '</div>'.
              '</div>'.
            '</div>'.
          '</div>'.
        '</div></div></div>'.
				'</div>';
				$posts_html .= '<script type="text/javascript">
					var gpx_file = "' . $gpx_file . '";
				</script>' . $custom_ad;
			$pos++;
	}

	if ( $page_template == 'tourenportal_page' ) {
		$posts_html .= get_wanderung_all_hikes_query( $filtered_map_ids = null, 'tourenportal_page' );
	}

	return $posts_html;
}


