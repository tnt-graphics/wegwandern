<?php
/**
 * Block for Regionen Content Map & Hike Listing Section
 */
$t                  = 0;
$taxquery           = array();
$parent_regions 	= array();

$wanderung_regionen = get_field( 'map_regionen' );
if ( ! empty( $wanderung_regionen ) ) {

	foreach( $wanderung_regionen as $wr ) {
		$wr_details = get_term($wr);
		
		if( $wr_details->parent == 0 ) {
			$parent_regions[] = $wr;
		}
	}

	$taxquery[] =
	array(
		'taxonomy'         => 'wanderregionen',
		'field'            => 'term_id',
		'terms'            => $wanderung_regionen,
	);
		
	if( !empty( $parent_regions ) ) {
		$taxquery[0]['include_children'] = true;
		$taxquery[0]['operator'] = 'IN';
	}

	$t++;
}

$map_saison = get_field( 'map_saison' );
if ( ! empty( $map_saison ) ) {
	$taxquery[] =
	array(
		'taxonomy' => 'wander-saison',
		'field'    => 'term_id',
		'terms'    => $map_saison,
		'operator' => 'IN',
	);
	$t++;
}

$map_aktivitat = get_field( 'map_aktivitat' );
if ( ! empty( $map_aktivitat ) ) {
	$taxquery[] =
	array(
		'taxonomy' => 'aktivitat',
		'field'    => 'term_id',
		'terms'    => $map_aktivitat,
		'operator' => 'IN',
	);
	$t++;
}

$map_anforderung = get_field( 'map_anforderung' );
if ( ! empty( $map_anforderung ) ) {
	$taxquery[] =
	array(
		'taxonomy' => 'anforderung',
		'field'    => 'term_id',
		'terms'    => $map_anforderung,
		'operator' => 'IN',
	);
	$t++;
}

$map_ausdauer = get_field( 'map_ausdauer' );
if ( ! empty( $map_ausdauer ) ) {
	$taxquery[] =
	array(
		'taxonomy' => 'ausdauer',
		'field'    => 'term_id',
		'terms'    => $map_ausdauer,
		'operator' => 'IN',
	);
	$t++;
}

$map_angebot = get_field( 'map_angebot' );
if ( ! empty( $map_angebot ) ) {
	$taxquery[] =
	array(
		'taxonomy' => 'angebot',
		'field'    => 'term_id',
		'terms'    => $map_angebot,
		'operator' => 'IN',
	);
	$t++;
}

$map_routenverlauf = get_field( 'map_routenverlauf' );
if ( ! empty( $map_routenverlauf ) ) {
	$taxquery[] =
	array(
		'taxonomy' => 'routenverlauf',
		'field'    => 'term_id',
		'terms'    => $map_routenverlauf,
		'operator' => 'IN',
	);
	$t++;
}

$map_thema = get_field( 'map_thema' );
if ( ! empty( $map_thema ) ) {
	$taxquery[] =
	array(
		'taxonomy' => 'thema',
		'field'    => 'term_id',
		'terms'    => $map_thema,
		'operator' => 'IN',
	);
	$t++;
}

$region_hikes_query = array(
	'posts_per_page' => -1,
	'post_type'      => 'wanderung',
	'post_status'    => 'publish',
);

$region_hikes_listing_query = array(
	'posts_per_page' => 9,
	'post_type'      => 'wanderung',
	'post_status'    => 'publish',
);
if ( ! empty( $taxquery ) ) {
	$region_hikes_query['tax_query'] = $taxquery;
	if ( $t > 1 ) {
		$region_hikes_query['tax_query']['relation'] = 'AND';
	}
	$region_hikes_listing_query['tax_query'] = $taxquery;
	if ( $t > 1 ) {
		$region_hikes_listing_query['tax_query']['relation'] = 'AND';
	}
}
$region_hikes_array         = get_posts( $region_hikes_query );
$region_hikes_array_listing = get_posts( $region_hikes_listing_query );

/* Hide redion section in filter if choosen from backend */
if ( !empty( $block['data']['map_regionen'] ) ) {
	echo '<script>
	document.getElementsByClassName("wanderregionen_accordion")[0].classList.add("hide");
	document.getElementsByClassName("wanderregionen_hr")[0].classList.add("hide");
	</script>';
}

foreach ( $region_hikes_array as $wanderung ) {

	$wanderregionen      = get_the_terms( $wanderung->ID, 'wanderregionen' );
	$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';

	$wander_saison = wp_get_post_terms(
		$wanderung->ID,
		'wander-saison',
		array(
			'orderby' => 'term_id',
			'order'   => 'ASC',
		)
	);
	if ( ! empty( $wander_saison ) ) {
		$wander_saison_name = array_column( $wander_saison, 'name' );
		if ( count( $wander_saison_name ) > 1 ) {
			$saison_range       = $wander_saison_name[0] . '-' . end( $wander_saison_name );
			$wander_saison_name = $saison_range;
		} else {
			$wander_saison_name = $wander_saison_name[0];
		}
	} else {
		$wander_saison_name = '';
	}

	$hike_level      = get_the_terms( $wanderung->ID, 'anforderung' );
	$hike_level_name = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';
	$hike_level_cls  = wegw_wandern_hike_level_class_name( $hike_level_name, $wanderung->ID );

	$hike_time     = ( get_field( 'dauer', $wanderung->ID ) ) ? get_field( 'dauer', $wanderung->ID ) : '';
	$hike_distance = ( get_field( 'km', $wanderung->ID ) ) ? get_field( 'km', $wanderung->ID ) : '';
	$hike_ascent   = ( get_field( 'aufstieg', $wanderung->ID ) ) ? get_field( 'aufstieg', $wanderung->ID ) : '';
	$hike_descent  = ( get_field( 'abstieg', $wanderung->ID ) ) ? get_field( 'abstieg', $wanderung->ID ) : '';
	$kurzbeschrieb = ( get_field( 'kurzbeschrieb', $wanderung->ID ) ) ? get_field( 'kurzbeschrieb', $wanderung->ID ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';

	/* Cluster updations */
	$latitude      = ( get_post_meta( $wanderung->ID, 'latitude', true ) ) ? get_post_meta( $wanderung->ID, 'latitude', true ) : '';
	$longitude     = ( get_post_meta( $wanderung->ID, 'longitude', true ) ) ? get_post_meta( $wanderung->ID, 'longitude', true ) : '';
	$gpx_file      = ( get_field( 'gpx_file', $wanderung->ID ) ) ? get_field( 'gpx_file', $wanderung->ID ) : '';
	$location_link = get_the_permalink( $wanderung->ID );
	$post_thumb    = get_the_post_thumbnail_url( $wanderung->ID, 'hike-thumbnail' );

	$user_watchlisted_hikes = wegwandern_get_watchlist_hikes_list();
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
} ?>

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
							<?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
							<input type="checkbox" name="" id="closure_hikes_layer" value="">
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
							<?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
							<input type="checkbox" name="" id="closure_hikes_layer" value="">
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

	<?php
	if ( isset( $json_map_coordinates ) && $json_map_coordinates != '' ) {
		echo '<script type="text/javascript">var places =' . $json_map_coordinates . ';</script>';
	} else {
		echo '<script type="text/javascript">var places;</script>';
	}
	?>
</div>

<div class="region-hike-list-container">

	<!-- Map Elevation Popup start -->
	<div id="weg-map-popup">
		<div id="weg-map-popup-inner-wrapper">
			<div class="close_map" onclick="closeElement(this)"><span class="close_map_icon"></span></div>
			<div id="cesiumContainer" class="cesiumContainer"></div>
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
								<?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
								<input type="checkbox" name="" id="closure_hikes_layer" value="">
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
							<label class="check_wrapper">
								<?php echo __( 'Wegpunkte WegWandern.ch', 'wegwandern' ); ?>
								<input type="checkbox" id="waypoints_layer" name="" value="">
								<span class="redmark"></span>
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="detailPgPopup">
			<div id="detailPgPopupContent"></div>
		</div>
		<div class="elevationGraph"></div>
		<div class="options" id="mapOptions"></div>
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
	<!-- Map Elevation Popup end -->

	<div class="ListSec">
		<?php
		$posts_html         = wanderung_listing_fun( $region_hikes_array_listing, 'region_detail_page' );
		$loadMore_hideClass = '';
		// $empty_result_mg_hide = "hide";
		if ( ! empty( $posts_html ) ) {
			echo '<div class="region-single-wander-wrappe">' . $posts_html . '</div>';
		} else {
			// $loadMore_hideClass = "hide";
			echo '<div class="region-single-wander-wrappe"></div><h2 class="noWanderung">Keine Wanderungen gefunden</h2>';
		}
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
