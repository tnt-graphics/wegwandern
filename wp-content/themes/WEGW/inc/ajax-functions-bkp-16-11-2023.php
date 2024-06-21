<?php
/**
 * All ajax functions of wegwandern.
 *
 * @package Wegwandern.
 */

add_action( 'wp_ajax_nopriv_wanderung_load_more', 'wanderung_load_more' );
add_action( 'wp_ajax_wanderung_load_more', 'wanderung_load_more' );

/**
 * Ajax function for wegwandern tourenportal map sort option.
 */
add_action( 'wp_ajax_nopriv_get_wanderung_sort_query', 'get_wanderung_sort_query' );
add_action( 'wp_ajax_get_wanderung_sort_query', 'get_wanderung_sort_query' );

function get_wanderung_sort_query() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		die();

	}
	$data_array   = generate_all_data_array();
	$query        = wegw_query_builder( $data_array );
	$allwanderung = get_posts( $query );

	$posts_html = wanderung_listing_fun( $allwanderung );

	/**
	 * For markers update in cluster including - Reset condition
	 */
	$filtered_hike_ids = array_column( $allwanderung, 'ID' );
	if ( ! empty( $allwanderung ) ) {
		$posts_html .= get_wanderung_all_hikes_query( $filtered_hike_ids );
		$ar_posts[]  = $posts_html;

	} else {
		$ar_posts[] = '<script type="text/javascript">var places = {};</script>';
		$ar_posts[] = __( 'Keine Wanderungen gefunden', 'wegwandern' );
	}

	wp_reset_postdata();
	echo json_encode( $ar_posts );
	die();
}

/**
 * Function for building common query
 */
function wegw_query_builder( $data_array = array() ) {

	$location = '';
	$sort     = '';
	if ( ! empty( $data_array ) ) {
		$location             = ( isset( $data_array['loc'] ) ) ? $data_array['loc'] : '';
		$sort                 = ( isset( $data_array['sort'] ) ) ? $data_array['sort'] : '';
		$duration_start_point = ( isset( $data_array['duration_start_point'] ) ) ? $data_array['duration_start_point'] : '';
		$duration_end_point   = ( isset( $data_array['duration_end_point'] ) ) ? $data_array['duration_end_point'] : '';
		$distance_start_point = ( isset( $data_array['distance_start_point'] ) ) ? $data_array['distance_start_point'] : '';
		$distance_end_point   = ( isset( $data_array['distance_end_point'] ) ) ? $data_array['distance_end_point'] : '';
		$ascent_start_point   = ( isset( $data_array['ascent_start_point'] ) ) ? $data_array['ascent_start_point'] : '';
		$ascent_end_point     = ( isset( $data_array['ascent_end_point'] ) ) ? $data_array['ascent_end_point'] : '';
		$descent_start_point  = ( isset( $data_array['descent_start_point'] ) ) ? $data_array['descent_start_point'] : '';
		$descent_end_point    = ( isset( $data_array['descent_end_point'] ) ) ? $data_array['descent_end_point'] : '';
		$altitude_start_point = ( isset( $data_array['altitude_start_point'] ) ) ? $data_array['altitude_start_point'] : '';
		$altitude_end_point   = ( isset( $data_array['altitude_end_point'] ) ) ? $data_array['altitude_end_point'] : '';

		$searchbox_filter      = ( isset( $data_array['searchbox_filter'] ) ) ? $data_array['searchbox_filter'] : '';
		$activity_search       = ( isset( $data_array['activity_search'] ) ) ? $data_array['activity_search'] : '';
		$difficulty_search     = ( isset( $data_array['difficulty_search'] ) ) ? $data_array['difficulty_search'] : '';
		$wanderregionen_search = ( isset( $data_array['wanderregionen_search'] ) ) ? $data_array['wanderregionen_search'] : '';
		$angebote_search       = ( isset( $data_array['angebote_search'] ) ) ? $data_array['angebote_search'] : '';
		$thema_search          = ( isset( $data_array['thema_search'] ) ) ? $data_array['thema_search'] : '';
		$routenverlauf_search  = ( isset( $data_array['routenverlauf_search'] ) ) ? $data_array['routenverlauf_search'] : '';
		$ausdauer_search       = ( isset( $data_array['ausdauer_search'] ) ) ? $data_array['ausdauer_search'] : '';
		$wander_saison_search  = ( isset( $data_array['wander_saison_search'] ) ) ? $data_array['wander_saison_search'] : '';

	}
	$wegw_filter_query = array(
		'post_type'      => 'wanderung',
		'posts_per_page' => 20,
		'post_status'    => 'publish',
	);

	$m = 0;

	if ( $distance_start_point != '' || $distance_end_point != '' ) {
		$metaquery[] =
			array(
				'key'     => 'km',
				'value'   => array( $distance_start_point, $distance_end_point ),
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(10,2)',
			);
		$m++;
	}

	if ( $duration_start_point != '' || $duration_end_point != '' ) {
		$metaquery[] =
			array(
				'key'     => 'dauer',
				'value'   => array( $duration_start_point, $duration_end_point ),
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(10,2)',
			);
		$m++;
	}

	if ( $ascent_start_point != '' || $ascent_end_point != '' ) {
		$metaquery[] =
		array(
			'key'     => 'aufstieg',
			'value'   => array( $ascent_start_point, $ascent_end_point ),
			'compare' => 'BETWEEN',
			'type'    => 'DECIMAL(10,2)',
		);
		$m++;
	}

	if ( $descent_start_point != '' || $descent_end_point != '' ) {
		$metaquery[] =
		array(
			'key'     => 'abstieg',
			'value'   => array( $descent_start_point, $descent_end_point ),
			'compare' => 'BETWEEN',
			'type'    => 'DECIMAL(10,2)',
		);
		$m++;
	}

	if ( $altitude_start_point != '' ) {
		$metaquery[] =
		array(
			'key'     => 'tiefster_punkt',
			'value'   => $altitude_start_point,
			'compare' => '>=',
			'type'    => 'DECIMAL(10,2)',
		);
		$m++;
	}

	if ( $altitude_end_point != '' ) {
		$metaquery[] =
		array(
			'key'     => 'hochster_punkt',
			'value'   => $altitude_end_point,
			'compare' => '<=',
			'type'    => 'DECIMAL(10,2)',
		);
		$m++;
	}

	if ( $sort != '' ) {
		$wegw_filter_query['orderby'] = 'km';
		$metaquery[]                  = array(
			'key'  => 'km',
			'type' => 'DECIMAL(10,2)',
		);
	}

	if ( ! empty( $metaquery ) ) {
		$wegw_filter_query['meta_query'] = $metaquery;
		if ( $m > 1 ) {
			$wegw_filter_query['meta_query']['relation'] = 'AND';
		}
	}

	if ( ( $sort == 'large' ) ) {
		$wegw_filter_query['order'] = 'DESC';
	}

	if ( ( $sort == 'short' ) ) {
		$wegw_filter_query['order'] = 'ASC';
	}

	$t = 0;
	if ( $searchbox_filter != '' || $location != '' ) {
		if ( $searchbox_filter == '' ) {
			$searchbox_filter = $location;
		}
		$taxquery[] =
			array(
				'taxonomy' => 'wanderregionen',
				'field'    => 'name',
				'terms'    => $searchbox_filter,
				'compare'  => '%LIKE%',
			);
		$t++;
	}

	if ( $activity_search != '' ) {
		$taxquery[] =
			array(
				'taxonomy' => 'aktivitat',
				'field'    => 'term_id',
				'terms'    => $activity_search,
				'operator' => 'IN',
			);
		$t++;
	}

	if ( $difficulty_search != '' ) {
		$taxquery[] =
			array(
				'taxonomy' => 'anforderung',
				'field'    => 'term_id',
				'terms'    => $difficulty_search,
				'operator' => 'IN',
			);
		$t++;
	}

	if ( $wanderregionen_search != '' ) {
		$taxquery[] =
			array(
				'taxonomy'         => 'wanderregionen',
				'field'            => 'term_id',
				'include_children' => true,
				'terms'            => $wanderregionen_search,
				'operator'         => 'IN',
			);
		$t++;
	}

	if ( $angebote_search != '' ) {
		$taxquery[] =
			array(
				'taxonomy' => 'angebot',
				'field'    => 'term_id',
				'terms'    => $angebote_search,
				'operator' => 'IN',
			);
		$t++;
	}

	if ( $thema_search != '' ) {
		$taxquery[] =
			array(
				'taxonomy' => 'thema',
				'field'    => 'term_id',
				'terms'    => $thema_search,
				'operator' => 'IN',
			);
		$t++;
	}

	if ( $routenverlauf_search != '' ) {
		$taxquery[] =
			array(
				'taxonomy' => 'routenverlauf',
				'field'    => 'term_id',
				'terms'    => $routenverlauf_search,
				'operator' => 'IN',
			);
		$t++;
	}

	if ( $ausdauer_search != '' ) {
		$taxquery[] =
			array(
				'taxonomy' => 'ausdauer',
				'field'    => 'term_id',
				'terms'    => $ausdauer_search,
				'operator' => 'IN',
			);
		$t++;
	}

	if ( $wander_saison_search != '' ) {
		$taxquery[] =
			array(
				'taxonomy' => 'wander-saison',
				'field'    => 'term_id',
				'terms'    => $wander_saison_search,
				'operator' => 'IN',
			);
		$t++;
	}
	if ( ! empty( $taxquery ) ) {
		$wegw_filter_query['tax_query'] = $taxquery;
		if ( $t > 1 ) {
			$wegw_filter_query['tax_query']['relation'] = 'AND';
		}
	}

	if ( ! isset( $wegw_filter_query['orderby'] ) && ! isset( $wegw_filter_query['order'] ) ) {
		$wegw_filter_query['orderby'] = 'ID';
		$wegw_filter_query['order']   = 'DESC';
	}

	return $wegw_filter_query;
}


/*
 * Filter hike results via main sidebar
 */
add_action( 'wp_ajax_nopriv_get_wanderung_sidebar_filter_query', 'get_wanderung_sidebar_filter_query' );
add_action( 'wp_ajax_get_wanderung_sidebar_filter_query', 'get_wanderung_sidebar_filter_query' );

function get_wanderung_sidebar_filter_query() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		die();

	}
	/* Check event triggered say: 'Reset' to consider all total hikes */
	$event = ( isset( $_POST['event'] ) && $_POST['event'] != '' ) ? $_POST['event'] : '';

	/* Check map section pagename */
	$page_type = $_POST['map_page'];

	/*
	 Check event type
	 * if event_type == `hover` update the filter button and display total number of hikes available
	 * if event_type == `btnClick` update search results with hikes count 20 per page
	 */
	$event_type = ( isset( $_POST['event_type'] ) && $_POST['event_type'] != '' ) ? $_POST['event_type'] : '';
	if ( $event_type == 'hover' ) {
		$post_count = -1;
	} else {
		if ( $event == 'total_hikes_filter' ) {
			$post_count = -1;
		}

		/*
		 Check page type for loading hikes:
		 * If page == `tourenportal` load 20 hikes.
		 * If page == `region` load 9 hikes.
		 */
		if ( isset( $page_type ) && $page_type == 'region' ) {
			$post_count = 9;
		} else {
			$post_count = 20;
		}
	}

	$data_array                          = generate_all_data_array();
	$data_array['post_count']            = $post_count;
	$wegw_filter_query                   = wegw_query_builder( $data_array );
	$wegw_filter_query['posts_per_page'] = $post_count;

	if ( $event_type == 'hover' ) {
		$allwanderung = get_posts( $wegw_filter_query );
		$ar_posts     = count( $allwanderung );
	} else {
		$allwanderung = get_posts( $wegw_filter_query );
		$query_pass   = base64_encode( serialize( $wegw_filter_query ) );
		$posts_html   = wanderung_listing_fun( $allwanderung );

		/* For markers update in cluster to consider all total hikes including - Reset condition */
		if ( $event == 'total_hikes_filter' ) {
			$all_count_query                   = $wegw_filter_query;
			$all_count_query['posts_per_page'] = -1;

			$all_hike          = get_posts( $all_count_query );
			$all_count         = count( $all_hike );
			$filtered_hike_ids = array_column( $all_hike, 'ID' );
		} else {
			$filtered_hike_ids = array_column( $allwanderung, 'ID' );
		}

		if ( ! empty( $allwanderung ) ) {
			$posts_html       .= get_wanderung_all_hikes_query( $filtered_hike_ids );
			$ar_posts[]        = $posts_html;
			$ar_posts['count'] = $all_count;

		} else {
			$ar_posts[]        = '<script type="text/javascript">var places = {};</script>';
			$ar_posts[]        = __( 'Keine Wanderungen gefunden', 'wegwandern' );
			$ar_posts['count'] = 0;
		}
	}

	echo json_encode( $ar_posts );
	die();
}

/**
 * Function for getting all data values
 */
function generate_all_data_array() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		die();

	}
	$data_array                         = array();
	$data_array['duration_start_point'] = ( isset( $_POST['duration_start_point'] ) && $_POST['duration_start_point'] != '' ) ? $_POST['duration_start_point'] : '';
	$data_array['duration_end_point']   = ( isset( $_POST['duration_end_point'] ) && $_POST['duration_end_point'] != '' ) ? $_POST['duration_end_point'] : '';
	$data_array['distance_start_point'] = ( isset( $_POST['distance_start_point'] ) && $_POST['distance_start_point'] != '' ) ? $_POST['distance_start_point'] : '';
	$data_array['distance_end_point']   = ( isset( $_POST['distance_end_point'] ) && $_POST['distance_end_point'] != '' ) ? $_POST['distance_end_point'] : '';
	$data_array['ascent_start_point']   = ( isset( $_POST['ascent_start_point'] ) && $_POST['ascent_start_point'] != '' ) ? $_POST['ascent_start_point'] : '';
	$data_array['ascent_end_point']     = ( isset( $_POST['ascent_end_point'] ) && $_POST['ascent_end_point'] != '' ) ? $_POST['ascent_end_point'] : '';
	$data_array['descent_start_point']  = ( isset( $_POST['descent_start_point'] ) && $_POST['descent_start_point'] != '' ) ? $_POST['descent_start_point'] : '';
	$data_array['descent_end_point']    = ( isset( $_POST['descent_end_point'] ) && $_POST['descent_end_point'] != '' ) ? $_POST['descent_end_point'] : '';
	$data_array['altitude_start_point'] = ( isset( $_POST['altitude_start_point'] ) && $_POST['altitude_start_point'] != '' ) ? $_POST['altitude_start_point'] : '';
	$data_array['altitude_end_point']   = ( isset( $_POST['altitude_end_point'] ) && $_POST['altitude_end_point'] != '' ) ? $_POST['altitude_end_point'] : '';

	$data_array['searchbox_filter']      = ( isset( $_POST['searchbox_filter'] ) && $_POST['searchbox_filter'] != '' ) ? $_POST['searchbox_filter'] : '';
	$data_array['activity_search']       = ( isset( $_POST['activity_search'] ) && $_POST['activity_search'] != '' ) ? $_POST['activity_search'] : '';
	$data_array['difficulty_search']     = ( isset( $_POST['difficulty_search'] ) && $_POST['difficulty_search'] != '' ) ? $_POST['difficulty_search'] : '';
	$data_array['wanderregionen_search'] = ( isset( $_POST['wanderregionen_search'] ) && $_POST['wanderregionen_search'] != '' ) ? $_POST['wanderregionen_search'] : '';
	$data_array['angebote_search']       = ( isset( $_POST['angebote_search'] ) && $_POST['angebote_search'] != '' ) ? $_POST['angebote_search'] : '';
	$data_array['thema_search']          = ( isset( $_POST['thema_search'] ) && $_POST['thema_search'] != '' ) ? $_POST['thema_search'] : '';
	$data_array['routenverlauf_search']  = ( isset( $_POST['routenverlauf_search'] ) && $_POST['routenverlauf_search'] != '' ) ? $_POST['routenverlauf_search'] : '';
	$data_array['ausdauer_search']       = ( isset( $_POST['ausdauer_search'] ) && $_POST['ausdauer_search'] != '' ) ? $_POST['ausdauer_search'] : '';
	$data_array['wander_saison_search']  = ( isset( $_POST['wander_saison_search'] ) && $_POST['wander_saison_search'] != '' ) ? $_POST['wander_saison_search'] : '';

	$data_array['loc']  = ( isset( $_POST['loc'] ) ) ? $_POST['loc'] : '';
	$data_array['sort'] = ( isset( $_POST['sort'] ) ) ? $_POST['sort'] : '';

	return $data_array;
}

/*
 * Ajax request for sorting hikes according to drag/zoom of map
 */
add_action( 'wp_ajax_wanderung_drag_map_hikes_filter', 'wanderung_drag_map_hikes_filter' );
add_action( 'wp_ajax_nopriv_wanderung_drag_map_hikes_filter', 'wanderung_drag_map_hikes_filter' );

function wanderung_drag_map_hikes_filter() {

	/*
	 Check page type for loading hikes:
	 * If page == `tourenportal` load 20 hikes.
	 * If page == `region` load 9 hikes.
	 */
	$page_type = $_POST['map_page'];
	if ( isset( $page_type ) && $page_type == 'region' ) {
		$postsPerPage = 9;
	} else {
		$postsPerPage = 20;
	}

	$filtered_map_ids = ( isset( $_POST['location_id'] ) && $_POST['location_id'] != '' ) ? $_POST['location_id'] : array( 0 );

	if ( isset( $filtered_map_ids ) && count( $filtered_map_ids ) > 0 ) {

		$query = array(
			'post_type'        => 'wanderung',
			'posts_per_page'   => $postsPerPage,
			'post__in'         => $filtered_map_ids,
			'post_status'      => 'publish',
			'suppress_filters' => false,
		);

		$map_coordinates = array();
		$allwanderung    = get_posts( $query );

		foreach ( $allwanderung as $wanderung ) {
			setup_postdata( $wanderung );
			$map_coordinates[] = wanderung_get_hike_details( $wanderung, $map_json = true );
		}
	}

	echo json_encode( $map_coordinates );
	wp_die();
}

/**
 * Ajax request for wegwandern map load more option.
 */
add_action( 'wp_ajax_nopriv_wanderung_drag_map_hikes_load_more', 'wanderung_drag_map_hikes_load_more' );
add_action( 'wp_ajax_wanderung_drag_map_hikes_load_more', 'wanderung_drag_map_hikes_load_more' );

/**
 * Function for getting hikes `Load more` results
 */
function wanderung_drag_map_hikes_load_more() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		die();
	}

	$count                  = $_POST['count'];
	$wanderung_filter_query = $_POST['wanderung_filter_query'];
	$filtered_map_ids       = $_POST['filtered_map_ids'];
	$page_type              = $_POST['map_page'];
	$data_array             = generate_all_data_array();

	/*
	 Check page type for loading hikes:
	 * If page == `tourenportal` load 20 hikes.
	 * If page == `region` load 9 hikes.
	 */
	if ( isset( $page_type ) && $page_type == 'region' ) {
		$postsPerPage = 9;
	} else {
		$postsPerPage = 20;
	}

	if ( $wanderung_filter_query == '1' ) {

		$args = array(
			'post_type'        => 'wanderung',
			'post_status'      => 'publish',
			'offset'           => $count,
			'posts_per_page'   => $postsPerPage,
			'suppress_filters' => false,
		);
	} else {

		$wanderung_filter_query                     = wegw_query_builder( $data_array );
		$wanderung_filter_query['offset']           = $count;
		$wanderung_filter_query['posts_per_page']   = $postsPerPage;
		$wanderung_filter_query['suppress_filters'] = false;
		$args                                       = $wanderung_filter_query;
	}

	if ( ! empty( $filtered_map_ids ) ) {
		$filtered_map_ids = explode( ',', $filtered_map_ids );
		$args['post__in'] = $filtered_map_ids;
	}

	$wanderung_posts = '';
	$wanderungs      = get_posts( $args );

	if ( ! empty( $wanderungs ) ) {

		foreach ( $wanderungs as $wanderung ) :
			setup_postdata( $wanderung );
			$wanderung_posts .= wanderung_get_hike_details( $wanderung );
		endforeach;

	} else {
		$wanderung_posts = '<h2 class="noWanderung">' . __( 'Keine Wanderungen gefunden', 'wegwandern' ) . '</h2>';
	}

	$ar_posts[] = $wanderung_posts;
	wp_reset_postdata();
	echo json_encode( $ar_posts );
	die();
}

/**
 * Ajax request for wegwandern drag map sort option.
 */
add_action( 'wp_ajax_nopriv_wanderung_drag_map_hikes_sort_query', 'wanderung_drag_map_hikes_sort_query' );
add_action( 'wp_ajax_wanderung_drag_map_hikes_sort_query', 'wanderung_drag_map_hikes_sort_query' );

/**
 * Function for getting sort result of wegwandern drag map.
 */
function wanderung_drag_map_hikes_sort_query() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		die();

	}
	$data_array = generate_all_data_array();
	$query      = wegw_query_builder( $data_array );

	/*
	 Check page type for loading hikes:
	 * If page == `tourenportal` load 20 hikes.
	 * If page == `region` load 9 hikes.
	 */
	$page_type = $_POST['map_page'];

	if ( isset( $page_type ) && $page_type == 'region' ) {
		$postsPerPage = 9;
	} else {
		$postsPerPage = 20;
	}

	$query['posts_per_page'] = $postsPerPage;

	$filtered_map_ids = $_POST['filtered_map_ids'];

	if ( ! empty( $filtered_map_ids ) ) {
		$filtered_map_ids  = explode( ',', $filtered_map_ids );
		$query['post__in'] = $filtered_map_ids;
	}

	$wanderung_posts = '';
	$wanderungs      = get_posts( $query );
	if ( ! empty( $wanderungs ) ) {

		foreach ( $wanderungs as $wanderung ) :
			setup_postdata( $wanderung );
			$wanderung_posts .= wanderung_get_hike_details( $wanderung );
		endforeach;

	} else {
		$wanderung_posts = '<h2 class="noWanderung">' . __( 'Keine Wanderungen gefunden', 'wegwandern' ) . '</h2>';
	}

	$ar_posts[] = $wanderung_posts;
	wp_reset_postdata();
	echo json_encode( $ar_posts );
	die();
}

/**
 * All ajax functions of wegwandern Regionen hike listing.
 */
add_action( 'wp_ajax_nopriv_wanderung_regionen_map_load_more', 'wanderung_regionen_map_load_more' );
add_action( 'wp_ajax_wanderung_regionen_map_load_more', 'wanderung_regionen_map_load_more' );

/**
 * Function for getting loadmore result for regionen page hike listing
 */
function wanderung_regionen_map_load_more() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		die();

	}
	$count       = $_POST['count'];
	$regionen_id = $_POST['regionen_id'];

	if ( isset( $regionen_id ) && $regionen_id != '' ) {
		$args = array(
			'post_type'        => 'wanderung',
			'post_status'      => 'publish',
			'offset'           => $count,
			'posts_per_page'   => 9,
			'suppress_filters' => false,
			'tax_query'        => array(
				array(
					'taxonomy' => 'wanderregionen',
					'field'    => 'term_id',
					'terms'    => $regionen_id,
				),
			),
		);

		$wanderung_posts = '';
		$wanderungs      = get_posts( $args );
	}

	if ( ! empty( $wanderungs ) ) {

		foreach ( $wanderungs as $wanderung ) :
			setup_postdata( $wanderung );
			$wanderung_posts .= wanderung_get_hike_details( $wanderung );
		endforeach;

	} else {
		$wanderung_posts = '<h2 class="noWanderung">' . __( 'Keine Wanderungen gefunden', 'wegwandern' ) . '</h2>';
	}

	$ar_posts[] = $wanderung_posts;
	wp_reset_postdata();
	echo json_encode( $ar_posts );
	die();
}

/**
 * Common function for getting hike listing
 *
 * If `map_json` == false, hike listing html is passed in return.
 *
 * If `map_json` == true, instead of hike listing html map json is passed in return.
 * Default value of `map_json` is false
 */
function wanderung_get_hike_details( $wanderung, $map_json = false ) {
	// pre($wanderung);
	// exit;
	$latitude  = ( get_post_meta( $wanderung->ID, 'latitude', true ) ) ? get_post_meta( $wanderung->ID, 'latitude', true ) : '';
	$longitude = ( get_post_meta( $wanderung->ID, 'longitude', true ) ) ? get_post_meta( $wanderung->ID, 'longitude', true ) : '';

	$hike_title    = get_the_title( $wanderung->ID );
	$post_thumb    = get_the_post_thumbnail_url( $wanderung->ID, 'hike-listing' );
	$location_link = get_the_permalink( $wanderung->ID );

	$wanderregionen      = get_the_terms( $wanderung->ID, 'wanderregionen' );
	$wanderregionen_name = ( ! empty( $wanderung->ID ) ) ? $wanderregionen[0]->name : 'Region';

	$hike_level      = get_the_terms( $wanderung->ID, 'anforderung' );
	$hike_level_name = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';
	$hike_level_cls  = wegw_wandern_hike_level_class_name( $hike_level_name, $wanderung->ID );

	$wander_saison_name = wegw_wandern_saison_name( $wanderung->ID );

	$hike_time     = ( get_field( 'dauer', $wanderung->ID ) ) ? wegwandern_formated_hiking_time_display( get_field( 'dauer', $wanderung->ID ) ) : '';
	$hike_distance = ( get_field( 'km', $wanderung->ID ) ) ? get_field( 'km', $wanderung->ID ) : '';
	$hike_ascent   = ( get_field( 'aufstieg', $wanderung->ID ) ) ? get_field( 'aufstieg', $wanderung->ID ) : '';
	$hike_descent  = ( get_field( 'abstieg', $wanderung->ID ) ) ? get_field( 'abstieg', $wanderung->ID ) : '';
	$kurzbeschrieb = ( get_field( 'kurzbeschrieb', $wanderung->ID ) ) ? get_field( 'kurzbeschrieb', $wanderung->ID ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';

	$post_thumb = get_the_post_thumbnail_url( $wanderung->ID, 'hike-listing' );

	$watchlisted_array = wegwandern_get_watchlist_hikes_list();
	if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
		$average_rating = get_wanderung_average_rating( $wanderung->ID );
	}
	if ( $map_json ) {
		$data = array(
			'longitude'                   => $longitude,
			'latitude'                    => $latitude,
			'location_regionen_name'      => $wanderregionen_name,
			'location_feature_image'      => $post_thumb,
			'location_name'               => $hike_title,
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
			'watchlisted'                 => in_array( $wanderung->ID, $watchlisted_array, false ) ? 1 : 0,
			'average_rating'              => $average_rating,
		);

	} else {
		$wander_region_html  = "<div class='single-region-rating'>";
		$wander_region_html .= "<h6 class='single-region'>" . $wanderregionen_name . '</h6>';
		if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
			$wander_region_html .= '<span class="average-rating-display">' . $average_rating . '<i class="fa fa-star"></i></span>';
		}
		$wander_region_html .= '</div>';
		if ( in_array( $wanderung->ID, $watchlisted_array, false ) ) {
			$watchlisted_class  = 'watchlisted';
			$watchlist_on_click = '';
		} else {
			$watchlisted_class  = '';
			$watchlist_on_click = ' onclick="addToWatchlist(this, ' . $wanderung->ID . ')" ';
		}
		$data = '<div class="single-wander">
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
						<p>' . $hike_time . ' h </p>
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
			</div>
		</div>';
	}

	return $data;
}

/**
 * Ajax request for wegwandern blog load more option.
 */
add_action( 'wp_ajax_nopriv_wanderung_blogs_load_more', 'wanderung_blogs_load_more' );
add_action( 'wp_ajax_wanderung_blogs_load_more', 'wanderung_blogs_load_more' );

/**
 * Function for getting loadmore result for blog
 */
function wanderung_blogs_load_more() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		die();
	}

	$count     = $_POST['count'];
	$page_type = $_POST['page_type'];
	if ( isset( $page_type ) && $page_type != '' ) {
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'offset'         => $count,
		);
		if ( 'page' != $page_type ) {
			$args['cat'] = $page_type;
		}
		// print_r($args);

		$blog_posts = '';
		//$post_query = new WP_Query( $args );
		$post_query = get_posts( $args );
		if ( ! empty( $post_query ) ) {
			global $post;
			foreach ( $post_query as $post ) {
				setup_postdata( $post );
				$id = $post->ID;
				$post_id    = get_the_ID();
				$post_thumb = get_the_post_thumbnail_url( $post_id, 'teaser-twocol' );
				$post_link  = get_permalink( $post_id );

				$blog_posts .= '<div class="blog-wander">
					<div class="blog-wander-img">
						<a href="' . $post_link . '">
							<img class="blog-img" src="' . $post_thumb . '">
						</a>
					</div>
					<h6>' . category_html( $post_id ) . '</h6>
					<h2><a href="' . $post_link . '">' . get_the_title( $post_id ) . '</a></h2>
					<div class="blog-desc">';
				if ( has_excerpt( $post_id ) ) {
					$blog_posts .= wp_trim_words( get_the_excerpt( $post_id ), 20, '...' );
				} else {
					$blog_posts .= wp_trim_words( get_the_content( $post_id ), 20, '...' );
				}
				$blog_posts .= '</div>
				</div>';

			}
		}else {
			$blog_posts .= '<h2 class="noWanderung">' . __( 'Keine Blogs gefunden', 'wegwandern' ) . '</h2>';
		}
		
		$ar_posts[] = $blog_posts;
		wp_reset_postdata();
		echo json_encode( $ar_posts );
		die();
	}

}

/**
 * Ajax request for wegwandern search load more option.
 */
add_action( 'wp_ajax_nopriv_wanderung_search_load_more', 'wanderung_search_load_more' );
add_action( 'wp_ajax_wanderung_search_load_more', 'wanderung_search_load_more' );

/**
 * Function for getting loadmore result for search
 */
function wanderung_search_load_more() {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['search_nonce'] ) ), 'search_nonce' ) ) {
		die();
	}

	$offset         = sanitize_text_field( wp_unslash( $_POST['offset'] ) );
	$count          = sanitize_text_field( wp_unslash( $_POST['count'] ) );
	$search_query   = sanitize_text_field( wp_unslash( $_POST['search_query'] ) );
	$post_type      = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );
	$posts_per_page = 9;

	if ( ( $count >= $offset ) && ! empty( $search_query ) ) {

		if ( ! empty( $post_type ) && $post_type != '' ) {
			$searchable_post_types = $post_type;
		} else {
			$searchable_post_types = get_post_types( array( 'exclude_from_search' => false ) );
		}

		$posts = get_posts(
			array(
				's'              => $search_query,
				'post_type'      => $searchable_post_types,
				'posts_per_page' => $posts_per_page,
				'offset'         => $offset,
			)
		);

		$count = 13;
		if ( ! empty( $posts ) ) {
			ob_start();
			global $post;
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				$id = $post->ID;

				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				get_template_part(
					'template-parts/content',
					'search',
					array(
						'count' => $count,
						'id'    => $id,
					)
				);
				$count++;

			}
			$search_content = ob_get_contents();
			ob_end_clean();
			wp_reset_postdata();
		}
	} else {
		$search_content .= '<h2 class="noWanderung">' . __( 'Keine Blogs gefunden', 'wegwandern' ) . '</h2>';
	}
	wp_reset_postdata();
	wp_send_json_success( $search_content );
}



/**
 * Ajax request for wegwandern taxonomy load more option.
 */
add_action( 'wp_ajax_nopriv_wanderung_taxonomy_load_more', 'wanderung_taxonomy_load_more' );
add_action( 'wp_ajax_wanderung_taxonomy_load_more', 'wanderung_taxonomy_load_more' );

/**
 * Function for getting loadmore result for taxonomy
 */
function wanderung_taxonomy_load_more() {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['taxonomy_nonce'] ) ), 'taxonomy_nonce' ) ) {
		die();
	}

	global $wpdb;

	$offset       = sanitize_text_field( wp_unslash( $_POST['offset'] ) );
	$count        = sanitize_text_field( wp_unslash( $_POST['count'] ) );
	$search_query = sanitize_text_field( wp_unslash( $_POST['search_query'] ) );
	$post_type    = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );
	$taxonomy     = sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) );
	$term_id      = sanitize_text_field( wp_unslash( $_POST['term_id'] ) );
	$query        = '';
	$limit        = 9;

	if ( ( $count >= $offset ) ) {

		$query .= " SELECT post_title, post_excerpt, ID FROM $wpdb->posts
					 LEFT JOIN  $wpdb->term_relationships as t
					 ON ID = t.object_id
					 WHERE post_type ='" . $post_type . "'
					 AND post_status = 'publish'
					 AND t.term_taxonomy_id = '" . $term_id . "'
					 GROUP BY ID
					 ORDER BY post_date DESC
					 LIMIT " . $limit . ' OFFSET ' . $offset;

		$taxonomy = $wpdb->get_results( $query );

		$count = 13;
		if ( ! empty( $taxonomy ) ) {
			ob_start();
			global $post;
			foreach ( $taxonomy as $post ) {

				$id   = $post->ID;
				$post = get_post( $id );
				setup_postdata( $post );

				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				get_template_part(
					'template-parts/content',
					'search',
					array(
						'count' => $count,
						'id'    => $id,
					)
				);

				$count++;
			}
			$search_content = ob_get_contents();
			ob_end_clean();
			wp_reset_postdata();
		} else {
			$search_content .= '<h2 class="noWanderung">' . __( 'Keine Blogs gefunden', 'wegwandern' ) . '</h2>';
		}
		wp_send_json_success( $search_content );
	}
}
