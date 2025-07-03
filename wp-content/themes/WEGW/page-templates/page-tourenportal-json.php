<?php
/**
 * Template Name: Tourenportal Json Template
 *
 * @package wegwandern
 */

get_header();

global $post;
$post_thumb        = get_the_post_thumbnail_url( $post->ID, 'full' );
$tourenportal_page = get_field( 'select_tourenportal_page', 'option' );
$tourenportal_id   = url_to_postid( $tourenportal_page );
$current_url       = $post->ID;
$container_cls     = 'container';
if ( $current_url === $tourenportal_id ) {
	$container_cls = 'touren_container';
}
$filter_button_text    = get_field( 'filter_button_text' );
$karte_button_text     = get_field( 'karte_button_text' );
$filter_reset_text     = get_field( 'filter_reset_text' );
$load_more_button_text = get_field( 'load_more_button_text' );
$ad_title              = get_field( 'ad_title' );
$ad_placement_pos_mark = get_field( 'ads_position' );
$teaser_1_short_title  = get_field( 'teaser_1_short_title' );
$teaser_1_title        = get_field( 'teaser_1_title' );
$teaser_1_image        = get_field( 'teaser_1_image' );
$teaser_1_redirect_url = get_field( 'teaser_1_redirect_url' );
$teaser_2_short_title  = get_field( 'teaser_2_short_title' );
$teaser_2_title        = get_field( 'teaser_2_title' );
$teaser_2_image        = get_field( 'teaser_2_image' );
$teaser_2_redirect_url = get_field( 'teaser_2_redirect_url' );

// Debug: Check if ACF fields exist and have content
$side_ad_left = get_field('side_ad_left', 'option');
$side_ad_right = get_field('side_ad_right', 'option');
$inside_mobile = get_field('inside_mobile', 'option');

// Debug output (remove this after testing)
if (empty($side_ad_left)) {
    $side_ad_left = '<div style="background: #f0f0f0; padding: 20px; text-align: center; border: 2px dashed #ccc;">Desktop Ad Placeholder (side_ad_left)</div>';
}
if (empty($side_ad_right)) {
    $side_ad_right = '<div style="background: #f0f0f0; padding: 20px; text-align: center; border: 2px dashed #ccc;">Tablet Ad Placeholder (side_ad_right)</div>';
}
if (empty($inside_mobile)) {
    $inside_mobile = '<div style="background: #f0f0f0; padding: 20px; text-align: center; border: 2px dashed #ccc;">Mobile Ad Placeholder (inside_mobile)</div>';
}

$current_logged_in_user = 0;
if ( is_user_logged_in() ) {
	$current_logged_in_user = wp_get_current_user()->ID;
}
?>

<main id="primary" class="site-main">
	<div class="touren_container">
		<section class="weg-map-main-wrapper">

		<div class="mapView hide" id="map-resp" style="width: 100%; height: 500px;">
				<div class="close_map" onclick="closeElementMapResp(this)">
					<span class="close_map_icon"></span>
				</div>
				<div class="filter-btn" id="weg-results-filter-btn" onclick="openFilter()">
					<span class="filter-btn-icon"></span>
					<span class="filter-btn-text"><?php echo $filter_button_text; ?></span>
				</div>
				<div class="map_main_search search" style="">
					<div class="map_search_map_wrapper">
						<span class="filter_search-icon"></span>
						<input type="text" class="map_search" placeholder="Ort, Region" value="" name="s">
						<span class="map_main_search_close hide"></span>
					</div>
				</div>
				<div id="cesiumContainerResp" class="cesiumContainer"></div>
				<div class="map_currentLocation"></div>
				<div id="threeD" class="map_3d"></div>
				<div id="map_direction" class="map_direction"></div>
				<div class="botom_layer_icon">
					<div class="accordion" >
						<div class="weg-layer-wrap layer_head">
							<div class="weg-layer-text">Hintergrund</div>
						</div>
					</div>
					<div class="panel">
						<div class="weg-layer-wrap activeLayer" id="colormap_view_section">
							<div class="weg-layer-text">Karte farbig</div>
						</div>
						<div class="weg-layer-wrap" id="aerial_view_section">
							<div class="weg-layer-text">Luftbild</div>
						</div>
						<div class="weg-layer-wrap" id="grey_view_section">
							<div class="weg-layer-text">Karte SW</div>
						</div>
					</div>
				</div>
				<div class="copyRight">
					<a target="_blank" href="https://www.swisstopo.admin.ch/de/home.html">© swisstopo</a>
				</div>
				<div class="map_filter">
					<div class="map_filter_inner_wrapper">
						<div class="accordion"><?php echo __( 'Karteninformationen', 'wegwandern' ); ?></div>
						<div class="panel">
							<div class="fc_check_wrap">
								<label class="check_wrapper"><?php echo __( 'ÖV-Haltestellen', 'wegwandern' ); ?>
									<input type="checkbox" name="" id="transport_layer_checkbox" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper"><?php echo __( 'Wanderwege', 'wegwandern' ); ?>
											<input type="checkbox" name="" id="hikes_trailing_layer" value="">
											<span class="redmark"></span>
										</label>
								<label class="check_wrapper"><?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
									<input type="checkbox" id="closure_hikes_layer" name="" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper"><?php echo __( 'Schneehöhe ExoLabs', 'wegwandern' ); ?>
									<input type="checkbox" id="snow_depth_layer" name="" value="">
									<span class="redmark"></span>
									<div class="info_icon" onclick="infoIconClicked(event,'map-resp')"></div>
								</label>
				  				<label class="check_wrapper"><?php echo __( 'Schneebedeckung ExoLabs', 'wegwandern' ); ?>
									<input type="checkbox" id="snow_cover_layer" name="" value="">
									<span class="redmark"></span>
								</label>
				  
								<label class="check_wrapper"><?php echo __( 'Hangneigungen über 30°', 'wegwandern' ); ?>
									<input type="checkbox" id="slope_30_layer" name="" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper "><?php echo __( 'Wildruhezonen', 'wegwandern' ); ?>
									<input type="checkbox" id="wildlife_layer" name="" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper hide">
									<?php echo __( 'Wegpunkte WegWandern.ch', 'wegwandern' ); ?>
									<input type="checkbox" name="" value="">
									<span class="redmark"></span>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div id="info"></div>
				<div class="snow_info_details hide">
					<div class="snow_inner_wrapper">
						<div class="snow_close_wrapper" onclick="infoIconClosed(event,'map-resp')"><div class="snow_close"></div></div>
						<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>
						<div class="snow_image"></div>
						<a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>
					</div>
				</div>
				<div class="popover" id="transport-layer-info-popup">
					<div class="arrow"></div>
					<div class="popover-title">
						<div class="popup-title"><?php echo __( 'Objekt Informationen', 'wegwandern' ); ?></div>
						<div class="popup-buttons">
							<!-- <button class="fa-print" title="Print"></button> -->
							<!-- <button class="fa-minus" title="Minimize"></button> -->
							<button class="fa fa-remove" title="Close" onclick="closeTransportLayerPopup()"></button>
						</div>
					</div>
					<div class="popover-content">
						<div class="popover-scope">
							<div class="popover-binding">
								<div class="htmlpopup-container" id="tl-content-area">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="container">
				<div class="ListView">
					<?php echo get_breadcrumb(); ?>
					<h1 class="page-title"><?php echo get_the_title(); ?></h1>
					<div class="ListHead">
						<div class="Listfilter">
							<div class="karte-btn" onclick="openMap()">
								<span class="karte-btn-icon"></span>
								<span class="karte-btn-text"><?php echo $karte_button_text; ?></span>
							</div>
							<div class="filter-btn" onclick="openFilter()">
								<span class="filter-btn-icon"></span>
								<span class="filter-btn-text"><?php echo $filter_button_text; ?></span>
							</div>
							<a class="filter_reset"><?php echo $filter_reset_text; ?></a>
						</div>
						<div class="ListSort" onclick="openDropdown(this)">
							<?php echo __( 'Sortieren', 'wegwandern' ); ?>
							<div class="sort_dropdown">
								<div class="sort_padding_dropdown">
									<div class="sort_dropdown_wrapper">
										<label class="check_wrapper_sort">
											<?php echo __( 'Längste zuerst', 'wegwandern' ); ?>
											<input type="checkbox" name="sort_large" class="sort-largest"/>
											<span class="redmark"></span>
										</label>
										<label class="check_wrapper_sort"><?php echo __( 'Kürzeste zuerst', 'wegwandern' ); ?>
											<input type="checkbox" name="sort_short" class="sort-shortest"/>
											<span class="redmark"></span>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ListHead mob">
						<div class="Listfilter">
							<div class="karte-btn" onclick="openMap()">
								<span class="karte-btn-icon"></span>
								<span class="karte-btn-text"><?php echo $karte_button_text; ?></span>
							</div>
							<div class="filter-btn" onclick="openFilter()">
								<span class="filter-btn-icon"></span>
								<span class="filter-btn-text"><?php echo $filter_button_text; ?></span>
							</div>
						</div>
						<div class=" list_sort_mob">
							<a class="filter_reset"><?php echo $filter_reset_text; ?></a>
							<div class="ListSort" onclick="openDropdown(this)">
								<?php echo __( 'Sortieren', 'wegwandern' ); ?>
								<div class="sort_dropdown">
									<div class="sort_padding_dropdown">
										<div class="sort_dropdown_wrapper">
											<label class="check_wrapper_sort">
												<?php echo __( 'Längste zuerst', 'wegwandern' ); ?>
												<input type="checkbox" name="sort_large" class="sort-largest">
												<span class="redmark"></span>
											</label>
											<label class="check_wrapper_sort">
												<?php echo __( 'Kürzeste zuerst', 'wegwandern' ); ?>
												<input type="checkbox" name="sort_short" class="sort-shortest">
												<span class="redmark"></span>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="ListSec">
						<div class="single-wander-wrappe-json" data-ad-title="<?php echo esc_attr( $ad_title ); ?>" data-ad-position="<?php echo esc_attr( $ad_placement_pos_mark ); ?>" data-logged-user="<?php echo $current_logged_in_user; ?>">
							<div class="ad-section-wrap header-ad-desktop-wrapper" style="display: none;">
								<p><?php echo $ad_title; ?></p>
								<div class="ad-section"></div>
							</div>
							<div class="ad-section-wrap header-ad-tablet-wrapper" style="display: none;">
								<p><?php echo $ad_title; ?></p>
								<div class="ad-section"></div>
							</div>
							<div class="ad-section-wrap header-ad-mobile-wrapper" style="display: none;">
								<p><?php echo $ad_title; ?></p>
								<div class="ad-section"></div>
							</div>
							
							<script>
							// Define a function to execute on load and resize
							function loadAndResizeFunction() {
								var windowWidth = $(window).width();
								
								// Hide all ad wrappers first
								$('.ad-section-wrap').hide();
								
								// Clear all ad sections
								$('.ad-section-wrap .ad-section').empty();
								
								if (windowWidth > 1200) {
									// Desktop - load side_ad_left
									$('.ad-section-wrap.header-ad-desktop-wrapper').show();
									<?php 
									echo "$('.ad-section-wrap.header-ad-desktop-wrapper .ad-section').html(`" . $side_ad_left . "`);";
									?>
								} else if (windowWidth >= 900 && windowWidth <= 1199) {
									// Tablet - load side_ad_right
									$('.ad-section-wrap.header-ad-tablet-wrapper').show();
									<?php 
									echo "$('.ad-section-wrap.header-ad-tablet-wrapper .ad-section').html(`" . $side_ad_right . "`);";
									?>
								} else if (windowWidth < 900) {
									// Mobile - load inside_mobile
									$('.ad-section-wrap.header-ad-mobile-wrapper').show();
									<?php 
									echo "$('.ad-section-wrap.header-ad-mobile-wrapper .ad-section').html(`" . $inside_mobile . "`);";
									?>
								}
							}

							// Execute the function on page load
							jQuery(document).ready(function() {
								loadAndResizeFunction();
							});

							// Execute the function on window resize
							jQuery(window).on('resize', function() {
								loadAndResizeFunction();
							});
							</script>
							<div class="promo-section">
								<a href="<?php echo $teaser_1_redirect_url; ?>" class="teaser-wrap">
									<img src="<?php echo $teaser_1_image['sizes']['region-slider']; ?>" class="teaser-section">
									<div class="teaser_info">
										<div class="sub_title"><?php echo $teaser_1_short_title; ?></div>
										<div class="title"><?php echo $teaser_1_title; ?></div>
									</div>
								</a>
								<a href="<?php echo $teaser_2_redirect_url; ?>" class="teaser-wrap">
									<img src="<?php echo $teaser_2_image['sizes']['region-slider']; ?>" class="teaser-section">
									<div class="teaser_info">
										<div class="sub_title"><?php echo $teaser_2_short_title; ?></div>
										<div class="title"><?php echo $teaser_2_title; ?></div>
									</div>
								</a>
							</div>
							<a href="<?php echo $teaser_1_redirect_url; ?>" class="teaser-wrap teaser-responsive">
								<img src="<?php echo $teaser_1_image['sizes']['region-slider']; ?>" class="teaser-section">
								<div class="teaser_info">
									<div class="sub_title"><?php echo $teaser_1_short_title; ?></div>
									<div class="title"><?php echo $teaser_1_title; ?></div>
								</div>
							</a>
							<a href="<?php echo $teaser_2_redirect_url; ?>" class="teaser-wrap teaser-responsive"> 
								<img src="<?php echo $teaser_2_image['sizes']['region-slider']; ?>" class="teaser-section">
								<div class="teaser_info">
									<div class="sub_title"><?php echo $teaser_2_short_title; ?></div>
									<div class="title"><?php echo $teaser_2_title; ?></div>
								</div>
							</a>
							</div>
						</div>
						<div class="LoadMore" id="wanderung-loadmore" data-event="">
							<!-- @todo filter -->
							<span class="LoadMoreIcon"></span>
							<span class="LoadMoreText"><?php echo $load_more_button_text; ?></span>
						</div>
						<div id="loader-icon" class="hide"></div>
					</div>

					<div class="mapView" id="map_desktop" style="width: 100%; height: 500px;">
					<div class="close_map_section hide" onclick="closeFullScreen()">
						<span class="close_map_icon"></span>
					</div>
					<div class="FullScreen" onclick="showFullScreen()">
						<span class="FullScreenIcon"></span>
					</div>
					<div class="filter-btn hide" id="weg-results-filter-btn" onclick="openFilter()">
						<span class="filter-btn-icon"></span>
						<span class="filter-btn-text"><?php echo $filter_button_text; ?></span>
					</div>
					<div class="map_main_search search" style="">
						<div class="map_search_map_wrapper">
							<span class="filter_search-icon"></span>
							<input type="text" class="map_search" placeholder="Ort, Region" value="" name="s">
							<span class="map_main_search_close hide"></span>
						</div>
					</div>
					<div id="cesiumContainerDesktop" class="cesiumContainer"></div>
					<div class="map_currentLocation"></div>
					<div id="threeD" class="map_3d" ></div>
					<div id="map_direction"  class="map_direction" ></div>
					<div id="info"></div>
					<div class="snow_info_details hide">
					<div class="snow_inner_wrapper">
						<div class="snow_close_wrapper" onclick="infoIconClosed(event,'map_desktop')"><div class="snow_close"></div></div>
						<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>
						<div class="snow_image"></div>
						<a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>
					</div>
				</div>
					<div class="popover" id="transport-layer-info-popup">
						<div class="arrow"></div>
						<div class="popover-title">
							<div class="popup-title"><?php echo __( 'Objekt Informationen', 'wegwandern' ); ?></div>
							<div class="popup-buttons">
								<!-- <button class="fa-print" title="Print"></button> -->
								<!-- <button class="fa-minus" title="Minimize"></button> -->
								<button class="fa fa-remove" title="Close" onclick="closeTransportLayerPopup()"></button>
							</div>
						</div>
						<div class="popover-content">
							<div class="popover-scope">
								<div class="popover-binding">
									<div class="htmlpopup-container" id="tl-content-area">
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="botom_layer_icon">
						<div class="accordion" >
							<div class="weg-layer-wrap layer_head">
								<div class="weg-layer-text">Hintergrund</div>
							</div>
						</div>
						<div class="panel">
							<div class="weg-layer-wrap activeLayer" id="colormap_view_section">
								<div class="weg-layer-text">Karte farbig</div>
							</div>
							<div class="weg-layer-wrap" id="aerial_view_section">
								<div class="weg-layer-text">Luftbild</div>
							</div>
							<div class="weg-layer-wrap" id="grey_view_section">
								<div class="weg-layer-text">Karte SW</div>
							</div>
						</div>
						</div>
						<div class="copyRight">
							<a target="_blank" href="https://www.swisstopo.admin.ch/de/home.html">© swisstopo</a>
						</div>
						<div class="map_filter">
							<div class="map_filter_inner_wrapper">
								<div class="accordion"><?php echo __( 'Karteninformationen', 'wegwandern' ); ?></div>
								<div class="panel">
									<div class="fc_check_wrap">
										<label class="check_wrapper"><?php echo __( 'ÖV-Haltestellen', 'wegwandern' ); ?>
											<input type="checkbox" name="" id="transport_layer_checkbox" value="">
											<span class="redmark"></span>
										</label>
										<label class="check_wrapper"><?php echo __( 'Wanderwege', 'wegwandern' ); ?>
											<input type="checkbox" name="" id="hikes_trailing_layer" value="">
											<span class="redmark"></span>
										</label>
										<label class="check_wrapper"><?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
											<input type="checkbox" name="" id="closure_hikes_layer" value="">
											<span class="redmark"></span>
										</label>
										<label class="check_wrapper"><?php echo __( 'Schneehöhe ExoLabs', 'wegwandern' ); ?>
											<input type="checkbox" id="snow_depth_layer" name="" value="">
											<span class="redmark"></span>
											<div class="info_icon" onclick="infoIconClicked(event,'map_desktop')"></div>
										</label>
										<label class="check_wrapper"><?php echo __( 'Schneebedeckung ExoLabs', 'wegwandern' ); ?>
											<input type="checkbox" id="snow_cover_layer" name="" value="">
											<span class="redmark"></span>
										</label>
										<label class="check_wrapper"><?php echo __( 'Hangneigungen über 30°', 'wegwandern' ); ?>
											<input type="checkbox" id="slope_30_layer" name="" value="">
											<span class="redmark"></span>
										</label>
										<label class="check_wrapper"><?php echo __( 'Wildruhezonen', 'wegwandern' ); ?>
											<input type="checkbox" id="wildlife_layer" name="" value="">
											<span class="redmark"></span>
										</label>
										<label class="check_wrapper hide"><?php echo __( 'Wegpunkte WegWandern.ch', 'wegwandern' ); ?>
											<input type="checkbox" name="" value="">
											<span class="redmark"></span>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="popup"><div id="popupContent"></div></div>
				</div>
		</section>
	</div>
</main>

<?php
// get_sidebar();
get_footer();