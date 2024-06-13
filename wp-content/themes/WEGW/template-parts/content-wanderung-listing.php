<?php
/**
 * Wanderung Listing.
 */
$filter_button_text    = get_field( 'filter_button_text' );
$karte_button_text     = get_field( 'karte_button_text' );
$filter_reset_text     = get_field( 'filter_reset_text' );
$filter_reset_text     = get_field( 'filter_reset_text' );
$load_more_button_text = get_field( 'load_more_button_text' );

$args = get_wanderung_filter_query();

$serialized_args  = base64_encode( serialize( $args ) );
$wanderung_filter = '<input type="hidden" id="wanderung_filter_query" value="' . $serialized_args . '">';

$allwanderung = get_posts( $args );
$posts_html   = wanderung_listing_fun( $allwanderung );

if ( ! empty( $posts_html ) ) {
	$listing_html = '<div class="single-wander-wrappe">
					' . $posts_html . '
					</div>';
}

$html = '<section class="weg-map-main-wrapper">
		<div id="weg-map-popup" >
		   	<div id="weg-map-popup-inner-wrapper">
		    	<div class="close_map" onclick="closeElement(this)"><span class="close_map_icon"></span></div>
				<div id="cesiumContainer" class="cesiumContainer"></div>
		    	<div class="map_currentLocation"></div><div id="threeD" class="map_3d"></div><div id="map_direction" class="map_direction"></div>
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
			            <div class="accordion">' . __( 'Karteninformationen', 'wegwandern' ) . '</div>
			            <div class="panel">
			               	<div class="fc_check_wrap">
			                	<label class="check_wrapper">' . __( 'ÖV-Haltestellen', 'wegwandern' ) . '<input type="checkbox" name="" id="transport_layer_checkbox" value=""><span class="redmark"></span></label>
			                	 <label class="check_wrapper">' . __( 'Wanderwege', 'wegwandern' ) . '<input type="checkbox" name="" id="hikes_trailing_layer" value=""><span class="redmark"></span></label>
			                    <label class="check_wrapper">' . __( 'Gesperrte Wanderwege', 'wegwandern' ) . '
			                        <input type="checkbox" name="" id="closure_hikes_layer" value=""><span class="redmark"></span></label>
			                    <label class="check_wrapper">' . __( 'Schneehöhe ExoLabs', 'wegwandern' ) . '<input type="checkbox" id="snow_depth_layer" name="" value=""><span class="redmark"></span><div class="info_icon" onclick="infoIconClicked(event,'weg-map-popup-inner-wrapper')"></div></label>
  								<label class="check_wrapper">' . __( 'Schneebedeckung ExoLabs', 'wegwandern' ) . '<input type="checkbox" id="snow_cover_layer" name="" value=""><span class="redmark"></span></label>
			                    <label class="check_wrapper">' . __( 'Hangneigungen über 30°', 'wegwandern' ) . '<input type="checkbox" id="slope_30_layer" name="" value=""><span class="redmark"></span></label>
								<label class="check_wrapper">' . __( 'Wildruhezonen', 'wegwandern' ) . '<input type="checkbox" id="wildlife_layer" name="" value=""><span class="redmark"></span></label>
								<label class="check_wrapper">' . __( 'Wegpunkte WegWandern.ch', 'wegwandern' ) . '<input type="checkbox" id="waypoints_layer" name="" value=""><span class="redmark"></span></label>
			               	</div>
			            </div>
			        </div>
	        	</div>
		   	</div>
			
		   	<div id="detailPgPopup"><div id="detailPgPopupContent"></div></div>
			<div class="elevationGraph"></div>	
		   	<div class="options" id="mapOptions"></div>

			   <div class="snow_info_details hide">
			   <div class="snow_inner_wrapper">
				   <div class="snow_close_wrapper" onclick="infoIconClosed(event,'weg-map-popup-inner-wrapper')"><div class="snow_close"></div></div>
				   <div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>
				   <div class="snow_image"></div>
				   <a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>
			   </div>
		   </div>

		   	<div id="info"></div>
		   	<div class="popover" id="transport-layer-info-popup">
			    <div class="arrow"></div>
			    <div class="popover-title">
			        <div class="popup-title">' . __( 'Objekt Informationen', 'wegwandern' ) . '</div>
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
	
		<div class="mapView hide" id="map-resp" style="width: 100%; height: 500px;">
			<div class="close_map" onclick="closeElementMapResp(this)"><span class="close_map_icon"></span></div>
		    <div class="filter-btn" id="weg-results-filter-btn" onclick="openFilter()">
		        <span class="filter-btn-icon"></span>
		        <span class="filter-btn-text">' . $filter_button_text . '</span>
		    </div>

		    <div class="map_main_search search" style="">
				<div class="map_search_map_wrapper">
    				<span class="filter_search-icon"></span>
    				<input type="text" class="map_search" placeholder="Ort, Region" value="" name="s">
    				<span class="map_main_search_close hide"></span>
				</div>
			</div>

			<div id="cesiumContainerResp" class="cesiumContainer"></div>
		    <div class="map_currentLocation"></div><div id="threeD" class="map_3d"></div><div id="map_direction" class="map_direction"></div>
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
		            <div class="accordion">' . __( 'Karteninformationen', 'wegwandern' ) . '</div>
		            <div class="panel">
		                <div class="fc_check_wrap">
		                    <label class="check_wrapper">' . __( 'ÖV-Haltestellen', 'wegwandern' ) . '<input type="checkbox" name="" id="transport_layer_checkbox" value=""><span class="redmark"></span></label>
							<label class="check_wrapper">' . __( 'Wanderwege', 'wegwandern' ) . '<input type="checkbox" name="" id="hikes_trailing_layer" value=""><span class="redmark"></span></label>
							<label class="check_wrapper">' . __( 'Gesperrte Wanderwege', 'wegwandern' ) . '<input type="checkbox" id="closure_hikes_layer" name="" value=""><span class="redmark"></span></label>
							<label class="check_wrapper">' . __( 'Schneehöhe ExoLabs', 'wegwandern' ) . '<input type="checkbox" id="snow_depth_layer" name="" value=""><span class="redmark"></span><div class="info_icon" onclick="infoIconClicked(event,'map-resp')"></div></label>
							<label class="check_wrapper">' . __( 'Schneebedeckung ExoLabs', 'wegwandern' ) . '<input type="checkbox" id="snow_cover_layer" name="" value=""><span class="redmark"></span></label>
							<label class="check_wrapper">' . __( 'Hangneigungen über 30°', 'wegwandern' ) . '<input type="checkbox" id="slope_30_layer" name="" value=""><span class="redmark"></span></label>
							<label class="check_wrapper ">' . __( 'Wildruhezonen', 'wegwandern' ) . '<input type="checkbox" id="wildlife_layer" name="" value=""><span class="redmark"></span></label>
							<label class="check_wrapper hide">' . __( 'Wegpunkte WegWandern.ch', 'wegwandern' ) . '<input type="checkbox" name="" value=""><span class="redmark"></span></label>
		                </div>
		            </div>
		        </div>
		    </div>
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
			        <div class="popup-title">' . __( 'Objekt Informationen', 'wegwandern' ) . '</div>
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
			' . get_breadcrumb() . '
			<h1 class="page-title">' . get_the_title() . ' </h1>
			<div class="ListHead">
				<div class="Listfilter">
					<div class="karte-btn" onclick="openMap()">
						<span class="karte-btn-icon"></span>
						<span class="karte-btn-text">' . $karte_button_text . '</span>
					</div>
					<div class="filter-btn" onclick="openFilter()">
						<span class="filter-btn-icon"></span>
						<span class="filter-btn-text">' . $filter_button_text . '</span>
					</div>
					<a class="filter_reset">' . $filter_reset_text . '</a>
				</div>
				<div class="ListSort" onclick="openDropdown(this)">
				' . __( 'Sortieren', 'wegwandern' ) . '
					<div class="sort_dropdown">
						<div class="sort_padding_dropdown">
						<div class="sort_dropdown_wrapper">
							<label class="check_wrapper_sort">' . __( 'Längste zuerst', 'wegwandern' ) . '<input type="checkbox" name="sort_large" class="sort-largest"/><span class="redmark"></span></label>
							<label class="check_wrapper_sort">' . __( 'Kürzeste zuerst', 'wegwandern' ) . '<input type="checkbox" name="sort_short" class="sort-shortest"/><span class="redmark"></span></label>
						</div>
					</div>
				</div>
			</div>
		</div>

	<div class="ListHead mob">
  		<div class="Listfilter">
			<div class="karte-btn" onclick="openMap()">
				<span class="karte-btn-icon"></span>
				<span class="karte-btn-text">' . $karte_button_text . '</span>
			</div>
			<div class="filter-btn" onclick="openFilter()">
				<span class="filter-btn-icon"></span>
				<span class="filter-btn-text">' . $filter_button_text . '</span>
			</div>
   		</div>

		<div class=" list_sort_mob">
			<a class="filter_reset">' . $filter_reset_text . '</a>
			<div class="ListSort" onclick="openDropdown(this)">
				' . __( 'Sortieren', 'wegwandern' ) . '
				<div class="sort_dropdown">
					<div class="sort_padding_dropdown">
					<div class="sort_dropdown_wrapper">
						<label class="check_wrapper_sort">' . __( 'Längste zuerst', 'wegwandern' ) . '<input type="checkbox" name="sort_large" class="sort-largest"><span class="redmark"></span></label>
						<label class="check_wrapper_sort">' . __( 'Kürzeste zuerst', 'wegwandern' ) . '<input type="checkbox" name="sort_short" class="sort-shortest"><span class="redmark"></span></label></div>
					</div>
					</div>
				</div>
   			</div>
		</div>

	  	<div class="ListSec">
		' . $listing_html . '
			<div class="LoadMore" id="wanderung-loadmore" data-event="">
			' . $wanderung_filter . '
				<span class="LoadMoreIcon"></span>
				<span class="LoadMoreText">' . $load_more_button_text . '</span>
			</div>
			<div id="loader-icon" class="hide"></div>
	  	</div>
   	</div>

	<div class="mapView" id="map_desktop" style="width: 100%; height: 500px;">
		<div class="close_map_section hide" onclick="closeFullScreen()"><span class="close_map_icon"></span></div>
		<div class="FullScreen" onclick="showFullScreen()"><span class="FullScreenIcon"></span></div>
		<div class="filter-btn hide" id="weg-results-filter-btn" onclick="openFilter()">
			<span class="filter-btn-icon"></span>
			<span class="filter-btn-text">' . $filter_button_text . '</span>
		</div>

		<div class="map_main_search search" style="">
			<div class="map_search_map_wrapper">
				<span class="filter_search-icon"></span>
				<input type="text" class="map_search" placeholder="Ort, Region" value="" name="s">
				<span class="map_main_search_close hide"></span>
			</div>
		</div>

		<div id="cesiumContainerDesktop" class="cesiumContainer"></div>
		<div class="map_currentLocation"></div><div id="threeD" class="map_3d" ></div><div id="map_direction"  class="map_direction" ></div>
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
				<div class="popup-title">' . __( 'Objekt Informationen', 'wegwandern' ) . '</div>
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
			<div class="map_filter"><div class="map_filter_inner_wrapper"><div class="accordion">' . __( 'Karteninformationen', 'wegwandern' ) . '</div><div class="panel"><div class="fc_check_wrap">
			<label class="check_wrapper">' . __( 'ÖV-Haltestellen', 'wegwandern' ) . '<input type="checkbox" name="" id="transport_layer_checkbox" value=""><span class="redmark"></span></label>
			<label class="check_wrapper">' . __( 'Wanderwege', 'wegwandern' ) . '<input type="checkbox" name="" id="hikes_trailing_layer" value=""><span class="redmark"></span></label>
			<label class="check_wrapper">' . __( 'Gesperrte Wanderwege', 'wegwandern' ) . '<input type="checkbox" id="closure_hikes_layer" name="" value=""><span class="redmark"></span></label>
			<label class="check_wrapper">' . __( 'Schneehöhe ExoLabs', 'wegwandern' ) . '<input type="checkbox" id="snow_depth_layer" name="" value=""><span class="redmark"></span><div class="info_icon" onclick="infoIconClicked(event,'map_desktop')"></div></label>
			<label class="check_wrapper">' . __( 'Schneebedeckung ExoLabs', 'wegwandern' ) . '<input type="checkbox" id="snow_cover_layer" name="" value=""><span class="redmark"></span></label>
			<label class="check_wrapper">' . __( 'Hangneigungen über 30°', 'wegwandern' ) . '<input type="checkbox" id="slope_30_layer" name="" value=""><span class="redmark"></span></label>
			<label class="check_wrapper ">' . __( 'Wildruhezonen', 'wegwandern' ) . '<input type="checkbox" id="wildlife_layer" name="" value=""><span class="redmark"></span></label>
			<label class="check_wrapper hide">' . __( 'Wegpunkte WegWandern.ch', 'wegwandern' ) . '<input type="checkbox" name="" value=""><span class="redmark"></span></label></div></div></div></div>
		</div>
		<div id="popup"><div id="popupContent"></div></div>
	</div>

</section>';
echo $html;
