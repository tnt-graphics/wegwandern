<?php
/**
 * Block for Regionen Content Map & Hike Listing Section
 */

$t              = 0;
$taxquery       = array();
$parent_regions = array();

$wanderung_regionen = get_field( 'map_regionen' );
$map_saison         = get_field( 'map_saison' );
$map_aktivitat      = get_field( 'map_aktivitat' );
$map_anforderung    = get_field( 'map_anforderung' );
$map_ausdauer       = get_field( 'map_ausdauer' );
$map_angebot        = get_field( 'map_angebot' );
$map_routenverlauf  = get_field( 'map_routenverlauf' );
$map_thema          = get_field( 'map_thema' );

/* Hide redion section in filter if choosen from backend */
if ( ! empty( $block['data']['map_regionen'] ) ) {
	echo '<script>
	document.getElementsByClassName("wanderregionen_accordion")[0].classList.add("hide");
	document.getElementsByClassName("wanderregionen_hr")[0].classList.add("hide");
	</script>';
}

?>

<div class="ListHead region_list_Desktop">
	<div class="Listfilter">
	<!--	<div class="karte-btn" onclick="openMap()">
			<span class="karte-btn-icon"></span>
			<span class="karte-btn-text"><?php echo __( 'Karte', 'wegwandern' ); ?></span>
		</div>-->
		<div class="filter-btn region-filter" onclick="openFilter(this)">
			<span class="filter-btn-icon"></span>
			<span class="filter-btn-text"><?php echo __( 'Filtern', 'wegwandern' ); ?></span>
		</div>
		<a  class="filter_reset"><?php echo __( 'Filter zurücksetzen', 'wegwandern' ); ?></a>
	</div>
	<div class="ListSort" onclick="openDropdown(this)">
	<?php echo __( 'Sortieren', 'wegwandern' ); ?>
		<div class="sort_dropdown">
			<div class="sort_padding_dropdown">
			<div class="sort_dropdown_wrapper">
				<label class="check_wrapper_sort"><?php echo __( 'Längste zuerst', 'wegwandern' ); ?><input type="checkbox" name="sort_large" class="sort-largest"/><span class="redmark"></span></label>
				<label class="check_wrapper_sort"><?php echo __( 'Kürzeste zuerst', 'wegwandern' ); ?><input type="checkbox" name="sort_short" class="sort-shortest"/><span class="redmark"></span></label>
			</div>
		</div>
	</div>
</div>
</div>
<div class="ListHead mob region_list_Resp">
	<div class="Listfilter">
		<div class="karte-btn" onclick="openMap()">
			<span class="karte-btn-icon"></span>
			<span class="karte-btn-text"><?php echo __( 'Karte', 'wegwandern' ); ?></span>
		</div>
		<div class="filter-btn region-filter" onclick="openFilter(this)">
			<span class="filter-btn-icon"></span>
			<span class="filter-btn-text"><?php echo __( 'Filtern', 'wegwandern' ); ?></span>
		</div>
	</div>
	<div class=" list_sort_mob">
		<a class="filter_reset"><?php echo __( 'Filter zurücksetzen', 'wegwandern' ); ?></a>
		<div class="ListSort" onclick="openDropdown(this)">
		<?php echo __( 'Sortieren', 'wegwandern' ); ?>
			<div class="sort_dropdown">
				<div class="sort_padding_dropdown">
				<div class="sort_dropdown_wrapper">
					<label class="check_wrapper_sort"><?php echo __( 'Längste zuerst', 'wegwandern' ); ?><input type="checkbox" name="sort_large" class="sort-largest"><span class="redmark"></span></label>
					<label class="check_wrapper_sort"><?php echo __( 'Kürzeste zuerst', 'wegwandern' ); ?><input type="checkbox" name="sort_short" class="sort-shortest"><span class="redmark"></span></label></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="map-region-container">

	<!-- Responsive map section start -->
	<div class="mapView hide" id="map-resp" style="width: 100%; height: 500px;">
		<div class="close_map" onclick="closeElementMapResp(this)"><span class="close_map_icon"></span></div>
		<div id="cesiumContainerResp" class="cesiumContainer"></div>
		<div class="map_currentLocation"></div>
		<div id="threeD" class="map_3d"></div>
		<div id="map_direction" class="map_direction"></div>
		<div class="botom_layer_icon">
			<div class="accordion">
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
						<label class="check_wrapper">
							<?php echo __( 'ÖV-Haltestellen', 'wegwandern' ); ?>
							<input type="checkbox" name="" id="transport_layer_checkbox" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Wanderwege', 'wegwandern' ); ?>
							<input type="checkbox" name="" id="hikes_trailing_layer" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
							<input type="checkbox" name="" id="closure_hikes_layer" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Schneehöhe Exolabs', 'wegwandern' ); ?>
							<input type="checkbox" id="snow_depth_layer" name="" value="">
							<span class="redmark"></span>
							<div class="info_icon" onclick="infoIconClicked(event,'map-resp')"></div>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Schneebedeckung ExoLabs', 'wegwandern' ); ?>
							<input type="checkbox" id="snow_cover_layer" name="" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Hangneigungen über 30°', 'wegwandern' ); ?>
							<input type="checkbox" id="slope_30_layer" name="" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Wildruhezonen', 'wegwandern' ); ?>
							<input type="checkbox" id="wildlife_layer" name="" value="">
							<span class="redmark"></span>
						</label>
					</div>
				</div>
			</div>
		</div>

		<!-- Hike Info Marker Popup start -->
		<div id="popup"><div id="popupContent"></div></div>
		<!-- Hike Info Marker Popup end -->
		<div class="snow_info_details hide">
		<div class="snow_inner_wrapper">
			<div class="snow_close_wrapper" onclick="infoIconClosed(event,'map-resp')"><div class="snow_close"></div></div>
			<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>
			<div class="snow_image"></div>
			<a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>
		</div>
	</div>
		<div id="info"></div>
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
	<!-- Responsive map section end -->

	<!--<div class="karte-btn region-karte-btn" onclick="openMap()">
		<span class="karte-btn-icon"></span>
		<span class="karte-btn-text">Karte</span>
	</div> -->
	<div class="map_region" id="map_desktop" style="width: 100%; height: 500px;">
		<div class="close_map_section hide" onclick="closeFullScreen()"><span class="close_map_icon"></span></div>
		<div class="FullScreen" onclick="showFullScreen()"><span class="FullScreenIcon"></span></div>
		<div id="cesiumContainerDesktop" class="cesiumContainer"></div>
		<div class="map_currentLocation"></div>
		<div id="threeD" class="map_3d"></div>
		<div id="map_direction" class="map_direction"></div>
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
			<div class="accordion">
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
						<label class="check_wrapper">
							<?php echo __( 'ÖV-Haltestellen', 'wegwandern' ); ?>
							<input type="checkbox" name="" id="transport_layer_checkbox" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Wanderwege', 'wegwandern' ); ?>
							<input type="checkbox" name="" id="hikes_trailing_layer" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
							<input type="checkbox" name="" id="closure_hikes_layer" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Schneehöhe Exolabs', 'wegwandern' ); ?>
							<input type="checkbox" id="snow_depth_layer" name="" value="">
							<span class="redmark"></span>
							<div class="info_icon" onclick="infoIconClicked(event,'map_desktop')"></div>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Schneebedeckung ExoLabs', 'wegwandern' ); ?>
							<input type="checkbox" id="snow_cover_layer" name="" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Hangneigungen über 30°', 'wegwandern' ); ?>
							<input type="checkbox" id="slope_30_layer" name="" value="">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">
							<?php echo __( 'Wildruhezonen', 'wegwandern' ); ?>
							<input type="checkbox" id="wildlife_layer" name="" value="">
							<span class="redmark"></span>
						</label>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="region-hike-list-container">

	<div class="ListSec">
		<?php
		$posts_html             = '';
		$current_logged_in_user = 0;
		if ( is_user_logged_in() ) {
			$current_logged_in_user = wp_get_current_user()->ID;
		}
		echo '<div class="region-single-wander-wrappe" data-logged-user="' . $current_logged_in_user . '">' . $posts_html . '</div>';

		?>
		<div class="LoadMore" id="wanderung-loadmore" data-event="regionenMap">
			<input type="hidden" id="wanderung_filter_query" value="">
			<input type="hidden" id="regionen_id" value="<?php echo wp_json_encode( $wanderung_regionen ); ?>" 
			data-region="<?php echo wp_json_encode( $wanderung_regionen ); ?>"
			data-saison="<?php echo wp_json_encode( $map_saison ); ?>" 
			data-aktivitat="<?php echo wp_json_encode( $map_aktivitat ); ?>"
			data-anforderung="<?php echo wp_json_encode( $map_anforderung ); ?>"
			data-ausdauer="<?php echo wp_json_encode( $map_ausdauer ); ?>"
			data-angebot="<?php echo wp_json_encode( $map_angebot ); ?>"
			data-routenverlauf="<?php echo wp_json_encode( $map_routenverlauf ); ?>"
			data-thema="<?php echo wp_json_encode( $map_thema ); ?>"/>
			<span class="LoadMoreIcon"></span>
			<span class="LoadMoreText"><?php echo __( 'Weitere Wanderungen', 'wegwandern' ); ?></span>
		</div>
		<div id="loader-icon" class="hide"></div>
	</div>
</div>
