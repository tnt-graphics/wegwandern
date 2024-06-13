<?php
/**
 * Functions for backend map integration into WordPress.
 *
 * @package Wegwandern
 */

/**
 * Add custom meta box to display map in admin backend
 */
add_action( 'add_meta_boxes', 'wegwandern_add_custom_meta_box' );

function wegwandern_add_custom_meta_box() {
	add_meta_box( 'map-meta-box', 'Karte', 'wegwandern_map_custom_meta_box_markup', 'wanderung', 'normal', 'low', null );
}

/**
 * Display Swisstopo map in Admin backend location page
 */
function wegwandern_map_custom_meta_box_markup() {
	wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );

	$current_hike_id                = get_the_ID();
	$gpx_file                       = get_field( 'gpx_file', $current_hike_id );
	$get_activity_taxonomy          = wp_get_post_terms( $current_hike_id, array( 'aktivitat' ) );
	
	if (!empty($get_activity_taxonomy)) {
		$current_hike_activity_taxonomy = $get_activity_taxonomy[0]->slug;
	} else {
		$current_hike_activity_taxonomy = "";
	}

	$json_gpx_data                  = ( $gpx_file ) ? get_field( 'json_gpx_file_data', $current_hike_id ) : 'undefined'; ?>

	<div id="map-view-coordinates-wrapper"></div>
	<br>
	<div id="map" class="map" style="width: 900px; height: 500px;"></div>
	<script type="text/javascript">
		jQuery( document ).ready(function() {
			/* Swisstopo Layer */
			var swisstopo_layer = new ol.layer.Tile({
				source: new ol.source.XYZ({
					url: 'https://wmts.geo.admin.ch/1.0.0/ch.swisstopo.pixelkarte-farbe/default/current/3857/{z}/{x}/{y}.jpeg'
				})
			});

			function deg2rad(deg) {
				return deg * (Math.PI/180);
			}

			function decimal2normalTime(decimalTimeString) {
				var decimalTime = parseFloat(decimalTimeString);
				decimalTime = decimalTime * 60 * 60;
				var hours = Math.floor((decimalTime / (60 * 60)));
				decimalTime = decimalTime - (hours * 60 * 60);
				var minutes = Math.floor((decimalTime / 60));
				decimalTime = decimalTime - (minutes * 60);
				var seconds = Math.round(decimalTime);

				if( minutes < 10 ) {
					minutes = "0" + minutes;
				}

				return hours + "." + minutes;
			}

			/* Get GPX XML file and parsing it to get the longitude and latitude of the `Track Points(trkpt)` */
			var json_gpx_data = <?php echo $json_gpx_data; ?>;

			/* Check if GPX file is uploaded */
			if( json_gpx_data !== undefined ) {
				var gpx_trackpoints = json_gpx_data.trk.trkseg.trkpt;
				var tkpt_length = parseFloat(gpx_trackpoints.length);

				/* Get altitudes and load dynamically to acf fields */
				if (tkpt_length > 0) {
					let allGPXPoints  = gpx_trackpoints.map(function(v) {
						return v.ele;
					});

					var min_altitude = Math.min.apply( null, allGPXPoints );
					var max_altitude = Math.max.apply( null, allGPXPoints );

					var wayTime = 0, sum= 0;

					/* Constants of the formula (schweizmobil) */
					var arrConstants = [
						14.271, 3.6991, 2.5922, -1.4384,
						0.32105, 0.81542, -0.090261, -0.20757,
						0.010192, 0.028588, -0.00057466, -0.0021842,
						1.5176e-5, 8.6894e-5, -1.3584e-7, -1.4026e-6
					];

					for( i=1; i< gpx_trackpoints.length; i++ ) {
						var data = gpx_trackpoints[i];
						var dataBefore = gpx_trackpoints[i - 1];

						/* Distance betwen 2 points */
						var dist = data.distance - dataBefore.distance;
						var distance = dist * 1000;

						if (!distance) {
							continue;
						}

						/* Difference of elevation between 2 points */
						var elevDiff = data.ele - dataBefore.ele;

						/* Slope value between the 2 points */
						var s = (elevDiff * 10.0) / distance;
					
						var minutesPerKilometer = 0;
						if (s > -4 && s < 4) {
							for (var j = 0; j < arrConstants.length; j++) {
								minutesPerKilometer += arrConstants[j] * Math.pow(s, j);
							}
							/* Outside the -40% to +40% range, we use a linear formula */
						} else if (s > 0) {
							minutesPerKilometer = (10 * s);
						} else {
							minutesPerKilometer = (-10 * s);
						}
						wayTime += distance * minutesPerKilometer / 1000;
					}

					var hike_hours = Number( wayTime / 60 );

					if (gpx_trackpoints.length) {
						var sumDown = 0;
						var sumUp = 0;
						for (var i = 0; i < gpx_trackpoints.length - 1; i++) {
							var h1 = Math.round(gpx_trackpoints[i].ele) || 0;
							var h2 = Math.round(gpx_trackpoints[i + 1].ele) || 0;
							var dh = h2 - h1;
							if (dh < 0) {
								sumDown += dh;
							} else if (dh >= 0) {
								sumUp += dh;
							}
						}

						var sumSlopeDist = 0;

						for (var i = 0; i < gpx_trackpoints.length - 1; i++) {
							var h1 = gpx_trackpoints[i].ele || 0;
							var h2 = gpx_trackpoints[i + 1].ele || 0;
							var s1 = gpx_trackpoints[i].dist || 0;
							var s2 = gpx_trackpoints[i + 1].dist || 0;
							var dh = h2 - h1;
							var ds = s2 - s1;
							/* Pythagorean theorem (hypotenuse: the slope/surface distance) */
							sumSlopeDist += Math.sqrt(Math.pow(dh, 2) + Math.pow(ds, 2));
						}

						var totalDistance = gpx_trackpoints[gpx_trackpoints.length - 1].distance;

						var currentActivityTaxonomy = '<?php echo $current_hike_activity_taxonomy; ?>';
						if( currentActivityTaxonomy == 'schneeschuh') {
							hike_hours *= 1.5;
						} else if(currentActivityTaxonomy == 'winterwandern') {
							hike_hours *= 1.25;
						} else {
							
						}

						/* Duration/time taken for hike `Dauer` */
						var dauer_acf_data_key = jQuery("[data-name='dauer']").attr("data-key");
						if( hike_hours != "") {
							var hike_hours_mod = decimal2normalTime( Math.round(hike_hours * 100) / 100 );
							jQuery("#acf-" + dauer_acf_data_key).val(hike_hours_mod);
						} else {
							jQuery("#acf-" + dauer_acf_data_key).val('');
						}

						/* Kilometer taken for hike */
						var total_distance_km_acf_data_key = jQuery("[data-name='km']").attr("data-key");
						jQuery("#acf-" + total_distance_km_acf_data_key).val( totalDistance.toFixed(2) );

						/* Ascent for hike `Aufstieg` */
						var ascent_acf_data_key = jQuery("[data-name='aufstieg']").attr("data-key");
						jQuery("#acf-" + ascent_acf_data_key).val(sumUp);

						/* Descent for hike `Abstieg` */
						var descent_acf_data_key = jQuery("[data-name='abstieg']").attr("data-key");
						jQuery("#acf-" + descent_acf_data_key).val( Math.abs(sumDown) );

						/* Lowest Altitude for hike `Tiefster Punkt` */
						var min_altitude_acf_data_key = jQuery("[data-name='tiefster_punkt']").attr("data-key");
						jQuery("#acf-" + min_altitude_acf_data_key).val( Number(min_altitude).toFixed(2) );

						/* Highest Altitude for hike `Höchster Punkt` */
						var max_altitude_acf_data_key = jQuery("[data-name='hochster_punkt']").attr("data-key");
						jQuery("#acf-" + max_altitude_acf_data_key).val( Number(max_altitude).toFixed(2) );
					}
				}
				
				var gpx_middle_cordinates = parseInt(gpx_trackpoints.length/2);
				var lat = parseFloat(gpx_trackpoints[gpx_middle_cordinates]["@attributes"].lat);
				var lon = parseFloat(gpx_trackpoints[gpx_middle_cordinates]["@attributes"].lon);

				/* Latitude taken for hike */
				var latitude_acf_data_key = jQuery("#hike_latitude_frm_gpx[data-name='latitude']").attr("data-key");
				jQuery("#acf-" + latitude_acf_data_key).val(lat);

				/* Longitude taken for hike */
				var longitude_acf_data_key = jQuery("#hike_longitude_frm_gpx[data-name='longitude']").attr("data-key");
				jQuery("#acf-" + longitude_acf_data_key).val(lon);

				/* Initialise Map */
				var map = new ol.Map({
					target: 'map',
					view: new ol.View({
						zoom: 14,
						center: ol.proj.fromLonLat( [lon, lat] ),
					}),
					layers: [swisstopo_layer]
				});

				/* Get the longitude and latitude of the `Way Points(wpt)` to plot event icons */
				var gpx_waypoints = json_gpx_data.trk.wpt;

				if (gpx_waypoints !== undefined) {
					var wpt_length = parseFloat(gpx_waypoints.length);

					if (wpt_length > 0) {
						for (var i = 0; i < wpt_length; i++) {
							var event_name = gpx_waypoints[i].name;
							var event_icon = "";

							if (gpx_waypoints[i].wptImage) {
								event_icon = gpx_waypoints[i].wptImage;
							} else {
								event_icon = "<?php echo get_template_directory_uri(); ?>/img/icons/home.png";
							}

							var event_icon_markers = new ol.layer.Vector({
								source: new ol.source.Vector(),
								style: new ol.style.Style({
									image: new ol.style.Icon({
										anchor: [0.5, 1],
										src: event_icon,
										scale: 0.5
									})
								})
							});

							map.addLayer(event_icon_markers);
							event_icon_markers.setZIndex(25);

							var wpt_lat = parseFloat(gpx_waypoints[i]["@attributes"].lat);
							var wpt_lon = parseFloat(gpx_waypoints[i]["@attributes"].lon);

							var event_icon_markers_location_plot = new ol.Feature({
								id: "weg_custom_event_icons",
								geometry: new ol.geom.Point(ol.proj.fromLonLat([wpt_lon, wpt_lat]), 'XYZ'),
							});
							event_icon_markers_location_plot.setId('weg_custom_event_icons');
							event_icon_markers.getSource().addFeature(event_icon_markers_location_plot);
						}
					}
				}

				/* Get dynamic GPX file and plot track inside the map */
				var gpx_source = new ol.source.Vector({
					url:  '<?php echo $gpx_file; ?>',
					format: new ol.format.GPX()
				});

				var gpx_path_plot_layer = new ol.layer.Vector({
					source: gpx_source,
					style: new ol.style.Style({
						image: new ol.style.RegularShape({
							radius: 10,
							radius2: 5,
							points: 5,
							fill: new ol.style.Fill({ color: 'yellow' })
						}),
						stroke: new ol.style.Stroke({
							color: [255, 0, 0],
							width: 8
						})
					})
				});
				map.addLayer(gpx_path_plot_layer);

				/* Onclick on map get coordinates and also display an icon on map when clicked */
				var getCordinatesIconLayer;
				map.on('click', evt => {
					map.removeLayer(getCordinatesIconLayer);
					var coords = ol.proj.toLonLat(evt.coordinate);

					var getCordinatesIconStyle = new ol.style.Style({
						image: new ol.style.Icon(({
							anchor: [0.5, 46],
							anchorXUnits: 'fraction',
							anchorYUnits: 'pixels',
							scale: 0.5,
							src: '<?php echo get_template_directory_uri(); ?>/img/icons/red-map-marker.png'
						}))
					});

					getCordinatesIconFeature = new ol.Feature(new ol.geom.Point(evt.coordinate));
					getCordinatesIconFeature.setStyle(getCordinatesIconStyle);
					var getCordinatesIconSource = new ol.source.Vector({
						features: [getCordinatesIconFeature]
					});

					getCordinatesIconLayer = new ol.layer.Vector({
						source: getCordinatesIconSource
					});

					map.addLayer(getCordinatesIconLayer);

					var weg_map_lat = coords[1];
					var weg_map_lon = coords[0];
					var weg_map_loc_coordinates = "<b>Latitude: </b>" + weg_map_lat + " <br><b>Longitude: </b>" + weg_map_lon;
					document.getElementById('map-view-coordinates-wrapper').innerHTML = weg_map_loc_coordinates;
				});

			} else {
				/* Initialise Map without coordinates */
				var map = new ol.Map({
					target: 'map',
					view: new ol.View({	
						zoom: 14,
						center: [900000, 5900000],
					}),
					layers: [swisstopo_layer]
				});

				/**
				 * If GPX field is empty make all field value = null
				 */

				/* Latitude taken for hike */
				var latitude_acf_data_key = jQuery("#hike_latitude_frm_gpx[data-name='latitude']").attr("data-key");
				jQuery("#acf-" + latitude_acf_data_key).val('');

				/* Longitude taken for hike */
				var longitude_acf_data_key = jQuery("#hike_longitude_frm_gpx[data-name='longitude']").attr("data-key");
				jQuery("#acf-" + longitude_acf_data_key).val('');

				/* Duration/time taken for hike - `Dauer` */
				var dauer_acf_data_key = jQuery("[data-name='dauer']").attr("data-key");
				jQuery("#acf-" + dauer_acf_data_key).val('');

				/* Kilometer taken for hike */
				var total_distance_km_acf_data_key = jQuery("[data-name='km']").attr("data-key");
				jQuery("#acf-" + total_distance_km_acf_data_key).val('');

				/* Ascent for hike `Aufstieg` */
				var ascent_acf_data_key = jQuery("[data-name='aufstieg']").attr("data-key");
				jQuery("#acf-" + ascent_acf_data_key).val('');

				/* Descent for hike `Abstieg` */
				var descent_acf_data_key = jQuery("[data-name='abstieg']").attr("data-key");
				jQuery("#acf-" + descent_acf_data_key).val('');

				/* Lowest Altitude for hike `Tiefster Punkt` */
				var min_altitude_acf_data_key = jQuery("[data-name='tiefster_punkt']").attr("data-key");
				jQuery("#acf-" + min_altitude_acf_data_key).val('');

				/* Highest Altitude for hike `Höchster Punkt` */
				var max_altitude_acf_data_key = jQuery("[data-name='hochster_punkt']").attr("data-key");
				jQuery("#acf-" + max_altitude_acf_data_key).val('');
			}

			setTimeout(function () {
				map.updateSize()
			}, 300);
		});
	</script>

	<?php
}

/**
 * Function to find distance between starting lat/lon & ending lat/lon
 */
function get_haversine_great_circle_distance( $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000 ) {
	/* Convert from degrees to radians */
	$latFrom = deg2rad( $latitudeFrom );
	$lonFrom = deg2rad( $longitudeFrom );
	$latTo   = deg2rad( $latitudeTo );
	$lonTo   = deg2rad( $longitudeTo );

	$latDelta = $latTo - $latFrom;
	$lonDelta = $lonTo - $lonFrom;

	$angle                 = 2 * asin( sqrt( pow( sin( $latDelta / 2 ), 2 ) + cos( $latFrom ) * cos( $latTo ) * pow( sin( $lonDelta / 2 ), 2 ) ) );
	$distance_in_meter     = $angle * $earthRadius;
	$distance_in_kilometer = $distance_in_meter / 1000;
	return $distance_in_kilometer;
}

/**
 * Function to calculate time between each points
 */
function wegwandern_get_time_between_each_points( $hours ) {
	$rhours   = sprintf( '%02d', floor( $hours ) );
	$minutes  = ( $hours - $rhours ) * 60;
	$rminutes = sprintf( '%02d', floor( $minutes ) );
	$seconds  = ( $minutes - $rminutes ) * 60;
	$rseconds = sprintf( '%02d', floor( $seconds ) );

	$gpx_time_format = $rhours . ':' . $rminutes . ':' . $rseconds;
	return $gpx_time_format;
}

/**
 * Hook function to save json formatted gpx data from uploaded GPX file to database post_meta `json_gpx_file_data`
 */
add_action( 'acf/save_post', 'wegw_gpx_json_data_update_on_import' );

function wegw_gpx_json_data_update_on_import( $post_id ) {
	$posttype = get_post_type( $post_id );
	$gpx_file = get_field( 'gpx_file', $post_id );
	$dauer = "";

	if ( 'wanderung' == $posttype && ! empty( $gpx_file ) ) {
		$gpx_data     = file_get_contents( $gpx_file );
		$gpx_data_arr = json_decode( json_encode( simplexml_load_string( $gpx_data ) ), true );

		$way_time        = $way_time_minute = $distance_m = $s = $calculated_time = 0;
		$trkpoints_count = $total_distance_calculated_upto_point = 0;
		if ( $gpx_data_arr['trk']['trkseg']['trkpt'] ) {
			$trkpoints_count = count( $gpx_data_arr['trk']['trkseg']['trkpt'] );
		}

		if ( $trkpoints_count > 0 ) {
			/* Looping to get distance & time between each points */
			for ( $i = 1; $i < $trkpoints_count; $i++ ) {
				$j             = $i - 1;
				$latitudeFrom  = $gpx_data_arr['trk']['trkseg']['trkpt'][ $j ]['@attributes']['lat'];
				$longitudeFrom = $gpx_data_arr['trk']['trkseg']['trkpt'][ $j ]['@attributes']['lon'];
				$latitudeTo    = $gpx_data_arr['trk']['trkseg']['trkpt'][ $i ]['@attributes']['lat'];
				$longitudeTo   = $gpx_data_arr['trk']['trkseg']['trkpt'][ $i ]['@attributes']['lon'];

				$points_distance                       = get_haversine_great_circle_distance( $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo );
				$total_distance_calculated_upto_point += $points_distance;

				$gpx_data_arr['trk']['trkseg']['trkpt'][ $i ]['distance'] = $total_distance_calculated_upto_point;
				$gpx_prev_point_data                                      = $gpx_data_arr['trk']['trkseg']['trkpt'][ $j ];

				/* Constants of the formula (schweizmobil) */
				$array_constants = array(
					14.271,
					3.6991,
					2.5922,
					-1.4384,
					0.32105,
					0.81542,
					-0.090261,
					-0.20757,
					0.010192,
					0.028588,
					-0.00057466,
					-0.0021842,
					1.5176e-5,
					8.6894e-5,
					-1.3584e-7,
					-1.4026e-6,
				);

				if ( isset( $gpx_prev_point_data['distance'] ) && $gpx_prev_point_data['distance'] != '' ) {
					/* Distance betwen 2 points */
					$distance = $total_distance_calculated_upto_point - $gpx_prev_point_data['distance'];

					/* Convert distance to meters */
					$distance_m = $distance * 1000;
				}

				/* Difference of elevation between 2 points */
				$elevation_difference = $gpx_data_arr['trk']['trkseg']['trkpt'][ $i ]['ele'] - $gpx_prev_point_data['ele'];

				/* Slope value between the 2 points */
				if ( $distance_m > 0 ) {
					$s = ( $elevation_difference * 10 ) / $distance_m;
				}

				$minutes_per_kilometer = 0;
				if ( $s > -4 && $s < 4 ) {
					for ( $k = 0; $k < count( $array_constants ); $k++ ) {
						$minutes_per_kilometer += $array_constants[ $k ] * pow( $s, $k );
					}
					/* outside the -40% to +40% range, we use a linear formula */
				} elseif ( $s > 0 ) {
					$minutes_per_kilometer = ( 10 * $s );
				} else {
					$minutes_per_kilometer = ( -10 * $s );
				}

				$way_time        = $distance_m * $minutes_per_kilometer / 1000;
				$way_time_minute = $way_time / 60;

				/*
				 * $way_time_minute == Dauer
				 *
				 * Update for change in duration of hiking in both season - Schneeschuh(1.5 hrs) & Winterwandern(1.25 hrs)
				 *
				 * Get `Aktivitat` taxonomy currently associated with the specific hike
				 **/
				$get_activity_taxonomy = wp_get_post_terms( $post_id, array( 'aktivitat' ) );
				if ( $get_activity_taxonomy ) {
					$current_hike_activity_taxonomy = $get_activity_taxonomy[0]->slug;

					if ( $current_hike_activity_taxonomy == 'schneeschuh' ) {
						$way_time_minute *= 1.5;
					} elseif ( $current_hike_activity_taxonomy == 'winterwandern' ) {
						$way_time_minute *= 1.25;
					}
				}

				/* Get total `Time taken/Dauer` to complete the hike */
				$dauer += $way_time_minute;

				$time_between_each_points = wegwandern_get_time_between_each_points( $way_time_minute );
				$current_date_time        = date( 'Y/m/d' ) . $time_between_each_points;
				$str_current_date_time    = strtotime( $current_date_time );

				/* Calculate current point time + time taken till that point */
				$calculated_time += $str_current_date_time;
				$gpx_time_set_hr  = date( 'H', $calculated_time );
				$gpx_time_set_min = date( 'i', $calculated_time );
				$gpx_time_set     = $gpx_time_set_hr . ':' . $gpx_time_set_min . ' h';
				$gpx_data_arr['trk']['trkseg']['trkpt'][ $i ]['timeSet'] = $gpx_time_set;
			}
		}

		if ( have_rows( 'wpt-coordinates' ) ) :
			$wpt_coordinates = array();
			while ( have_rows( 'wpt-coordinates' ) ) :
				the_row();
				$wpt_latitude  = get_sub_field( 'latitude' );
				$wpt_longitude = get_sub_field( 'longitude' );
				$wpt_info      = get_sub_field( 'wegpunkt_info' );
				// $wpt_elevation     = get_sub_field( 'elevation' );
				$wpt_icon          = get_sub_field( 'icon' );
				$wpt_image         = isset( $wpt_icon ) ? $wpt_icon : '';
				$wpt_coordinates[] = array(
					'@attributes' => array(
						'lat' => $wpt_latitude,
						'lon' => $wpt_longitude,
					),
					// "ele" => $wpt_elevation,
					'name'        => '',
					'wptImage'    => $wpt_image,
					'wpt_info'    => $wpt_info,
				);

			endwhile;
		endif;

		if ( isset( $wpt_coordinates ) && $wpt_coordinates != '' ) {
			/* set empty array $gpx_data_arr['trk']['wpt'] */
			$gpx_data_arr['trk']['wpt'] = array();
			foreach ( $wpt_coordinates as $wptc ) {
				if ( isset( $gpx_data_arr['trk']['wpt'] ) && $gpx_data_arr['trk']['wpt'] != '' ) {
					array_push( $gpx_data_arr['trk']['wpt'], $wptc );
				} elseif ( isset( $gpx_data_arr['wpt'] ) && $gpx_data_arr['wpt'] != '' ) {
					$already_exist_wpt = $gpx_data_arr['wpt'];
					unset( $gpx_data_arr['wpt'] );
					$gpx_data_arr['trk']['wpt'][] = $wptc;
					array_push( $gpx_data_arr['trk']['wpt'], $already_exist_wpt );
				} else {
					array_push( $gpx_data_arr['trk']['wpt'], $wptc );
				}
			}
		}

		$dauer = number_format( $dauer, 2 );
		$dauer_converted = wegwandern_convert_decimal_time( $dauer );
		update_field( 'dauer', $dauer_converted, $post_id );

		$json_gpx_data = json_encode( $gpx_data_arr, JSON_UNESCAPED_UNICODE );
		update_field( 'json_gpx_file_data', $json_gpx_data, $post_id );

		/* Run function to Update gpx post-meta fields */
		wegw_gpx_fields_update_on_import( $post_id );
	}
}

/*
 * Hook function to update hike postmeta fields while all in one import GPX data
 */
// add_action( 'pmxi_saved_post', 'wegw_gpx_data_update' );

// function wegw_gpx_data_update( $post_id ) {
// 	/* Remove action filter 'acf/save_post' on import data */
// 	remove_filter( 'acf/save_post', 'wegw_gpx_json_data_update_on_import' );

// 	/* Update hidden GPX json data */
	// wegw_gpx_json_data_update_on_import( $post_id );

// 	/* Update postmeeta fields from GPX data */
// 	wegw_gpx_fields_update_on_import( $post_id );
// }

function wegw_gpx_fields_update_on_import( $post_id ) {
	$posttype = get_post_type( $post_id );
	$gpx_file = get_field( 'gpx_file', $post_id );

	if ( 'wanderung' == $posttype && ! empty( $gpx_file ) ) {
		$gpx_data     = get_field( 'json_gpx_file_data', $post_id );
		$gpx_data_arr = json_decode( $gpx_data, true );

		$way_time        = $way_time_minute = $calculated_time = $gpx_trackpoints_count = $total_distance_calculated_upto_point = 0;
		$gpx_trackpoints = $gpx_data_arr['trk']['trkseg']['trkpt'];
		if ( $gpx_trackpoints ) {
			$gpx_trackpoints_count = count( $gpx_data_arr['trk']['trkseg']['trkpt'] );
		}

		/*
		 * Getting values for `Latitude` and `Longitude` respectively.
		 * Getting values for `Tiefster Punkt`(Min Altitude) and `Höchster Punkt`(Max Altitude) respectively.
		 */
		if ( $gpx_trackpoints_count > 0 ) {

			$gpx_middle_cordinates = (int) ( $gpx_trackpoints_count / 2 );
			$lat                   = (float) $gpx_trackpoints[ $gpx_middle_cordinates ]['@attributes']['lat'];
			$lon                   = (float) $gpx_trackpoints[ $gpx_middle_cordinates ]['@attributes']['lon'];

			$start_altitude = $gpx_trackpoints[0]['ele'];
			$min_altitude   = $start_altitude;
			$max_altitude   = $start_altitude;

			for ( $i = 0; $i < $gpx_trackpoints_count; $i++ ) {
				if ( $gpx_trackpoints[ $i ]['ele'] < $min_altitude ) {
					$min_altitude = $gpx_trackpoints[ $i ]['ele'];
				}

				if ( $gpx_trackpoints[ $i ]['ele'] > $max_altitude ) {
					$max_altitude = $gpx_trackpoints[ $i ]['ele'];
				}
			}

			/*
			 * Getting values for `Aufstieg`(Ascent) and `Abstieg`(Descent) respectively.
			 */
			$sumDown = $sumUp = $s = $sumSlopeDist = 0;
			for ( $i = 0; $i < ( $gpx_trackpoints_count - 1 ); $i++ ) {
				$sh1 = ( isset( $gpx_trackpoints[ $i ]['ele'] ) && $gpx_trackpoints[ $i ]['ele'] != '' ) ? round( $gpx_trackpoints[ $i ]['ele'] ) : 0;
				$sh2 = ( isset( $gpx_trackpoints[ $i + 1 ]['ele'] ) && $gpx_trackpoints[ $i + 1 ]['ele'] != '' ) ? round( $gpx_trackpoints[ $i + 1 ]['ele'] ) : 0;
				$sdh = $sh2 - $sh1;

				if ( $sdh < 0 ) {
					$sumDown += $sdh;
				} elseif ( $sdh >= 0 ) {
					$sumUp += $sdh;
				}
			}

			/*
			 * Getting values for `Dauer`(Hours of hiking) and `KM`(Kilometers) respectively.
			 */
			$wayTime = $sum = 0;

			// Constants of the formula (schweizmobil)
			$arrConstants = array(
				14.271,
				3.6991,
				2.5922,
				-1.4384,
				0.32105,
				0.81542,
				-0.090261,
				-0.20757,
				0.010192,
				0.028588,
				-0.00057466,
				-0.0021842,
				1.5176e-5,
				8.6894e-5,
				-1.3584e-7,
				-1.4026e-6,
			);

			for ( $i = 1; $i < $gpx_trackpoints_count; $i++ ) {
				$data       = $gpx_trackpoints[ $i ];
				$dataBefore = $gpx_trackpoints[ $i - 1 ];

				// Distance betwen 2 points
				if ( isset( $dataBefore['distance'] ) && $dataBefore['distance'] != '' ) {
					$dist = $data['distance'] - $dataBefore['distance'];
				}

				$distance = @$dist * 1000;
				if ( ! $distance ) {
					continue;
				}

				// Difference of elevation between 2 points
				$elevDiff = $data['ele'] - $dataBefore['ele'];

				// Slope value between the 2 points
				$s = ( $elevDiff * 10.0 ) / $distance;

				$minutesPerKilometer = 0;
				if ( $s > -4 && $s < 4 ) {
					for ( $j = 0; $j < count( $arrConstants ); $j++ ) {
						$minutesPerKilometer += $arrConstants[ $j ] * pow( $s, $j );
					}
				} elseif ( $s > 0 ) {
					$minutesPerKilometer = ( 10 * $s );
				} else {
					$minutesPerKilometer = ( -10 * $s );
				}
				$wayTime += $distance * $minutesPerKilometer / 1000;
			}

			// $hike_hours    = number_format( floor( round( $wayTime ) ) / 60, 2, '.', '' );
			$hike_hours    = number_format( $wayTime / 60, 2, '.', '' );
			$totalDistance = $gpx_trackpoints[ $gpx_trackpoints_count - 1 ]['distance'];
		}

		$latitude       = $lat;
		$longitude      = $lon;
		$dauer          = wegwandern_convert_decimal_time( $hike_hours );
		$km             = round( $totalDistance, 2 );
		$aufstieg       = $sumUp;
		$abstieg        = abs( $sumDown );
		$tiefster_punkt = round( $min_altitude, 2 );
		$hochster_punkt = round( $max_altitude, 2 );

		if ( isset( $latitude ) && $latitude != '' ) {
			update_field( 'latitude', $latitude, $post_id );
		}

		if ( isset( $longitude ) && $longitude != '' ) {
			update_field( 'longitude', $longitude, $post_id );
		}

		// if ( isset( $dauer ) && $dauer != '' ) {
		// 	update_field( 'dauer', $dauer, $post_id );
		// }

		if ( isset( $km ) && $km != '' ) {
			update_field( 'km', $km, $post_id );
		}

		if ( isset( $aufstieg ) && $aufstieg != '' ) {
			update_field( 'aufstieg', $aufstieg, $post_id );
		}

		if ( isset( $abstieg ) && $abstieg != '' ) {
			update_field( 'abstieg', $abstieg, $post_id );
		}

		if ( isset( $tiefster_punkt ) && $tiefster_punkt != '' ) {
			update_field( 'tiefster_punkt', $tiefster_punkt, $post_id );
		}

		if ( isset( $hochster_punkt ) && $hochster_punkt != '' ) {
			update_field( 'hochster_punkt', $hochster_punkt, $post_id );
		}
	}
}
