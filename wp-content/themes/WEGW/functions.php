<?php
/**
 * Wegwandern functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Wegwandern
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.12' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function wegwandern_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on Wegwandern, use a find and replace
		* to change 'wegwandern' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'wegwandern', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'wegwandern' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'wegwandern_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
		show_admin_bar( false );
	}
}
add_action( 'after_setup_theme', 'wegwandern_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function wegwandern_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'wegwandern_content_width', 640 );
}
add_action( 'after_setup_theme', 'wegwandern_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function wegwandern_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'wegwandern' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'wegwandern' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'wegwandern_widgets_init' );

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/general-settings.php';

/**
 * Functions for backend map integration into WordPress.
 */
require get_template_directory() . '/inc/admin-map-widget.php';

/**
 * Functions which enhance the theme by options into WordPress.
 */
require get_template_directory() . '/inc/theme-options.php';

/**
 * Enqueue scripts.
 */
require get_template_directory() . '/inc/wp-scripts.php';

/**
 * Custom widgets initialization.
 */
require get_template_directory() . '/inc/wp-widgets.php';

/**
 * Breadcrumbs initialization.
 */
require get_template_directory() . '/inc/breadcrumb.php';

/**
 * Ajax functions initialization.
 */
require get_template_directory() . '/inc/ajax-functions.php';

/**
 * Gutenberg block initialization.
 */
require get_template_directory() . '/inc/acf-block-elements.php';

/**
 * Gutenberg block patterns initialization.
 */
require get_template_directory() . '/inc/block-patterns.php';
/**
 * Wanderung filter section.
 */

require get_template_directory() . '/inc/wegw-filter.php';

/**
 * Wanderung planen section.
 */
require get_template_directory() . '/inc/wanderung-planen.php';

/**
 * Wanderung region slider.
 */
require get_template_directory() . '/inc/wanderung-region-slider.php';
/**
 * Header main Menu.
 */
require get_template_directory() . '/inc/menu.php';


add_filter(
	'wp_check_filetype_and_ext',
	function( $data, $file, $filename, $mimes ) {

		global $wp_version;
		if ( $wp_version !== '4.7.1' ) {
			return $data;
		}

		$filetype = wp_check_filetype( $filename, $mimes );

		return array(
			'ext'             => $filetype['ext'],
			'type'            => $filetype['type'],
			'proper_filename' => $data['proper_filename'],
		);

	},
	10,
	4
);
/**
 * Function to add svg support.
 */
function wegw_cc_mime_types( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
  add_filter( 'upload_mimes', 'wegw_cc_mime_types' );

function wegw_fix_svg() {
	echo '<style type="text/css">
		  .attachment-266x266, .thumbnail img {
			   width: 100% !important;
			   height: auto !important;
		  }
		  </style>';
		  $apple_touch_icon_url = get_template_directory_uri() . '/img/favicon/apple-touch-icon.png';
		  $favicon_32           = get_template_directory_uri() . '/img/favicon/favicon-32x32.png';
		  $favicon_16           = get_template_directory_uri() . '/img/favicon/favicon-16x16.png';
		  $favicon_manifest     = get_template_directory_uri() . '/img/favicon/site.webmanifest';
		  $safari_pinned_icon   = get_template_directory_uri() . '/img/favicon/safari-pinned-tab.svg';
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $apple_touch_icon_url . '">';
	echo '<link rel="icon" type="image/png" sizes="32x32" href="' . $favicon_32 . '">';
	echo '<link rel="icon" type="image/png" sizes="16x16" href="' . $favicon_16 . '">';
	echo '<link rel="manifest" href="' . $favicon_manifest . '">';
	echo '<link rel="mask-icon" href="' . $safari_pinned_icon . '" color="#ff0000">
		  <meta name="msapplication-TileColor" content="#ffffff">
		  <meta name="theme-color" content="#ffffff">';
}
  add_action( 'admin_head', 'wegw_fix_svg' );

/*
 * Print data in specified format - for developer convenience
 */
function pre( $data ) {
	echo '<pre>';
	print_r( $data );
	echo '</pre>';
}

/*
 * Ajax function for getting hike GPX file
 */
add_action( 'wp_ajax_wegwandern_get_hike_gpx_file', 'wegwandern_get_hike_gpx_file' );
add_action( 'wp_ajax_nopriv_wegwandern_get_hike_gpx_file', 'wegwandern_get_hike_gpx_file' );

function wegwandern_get_hike_gpx_file() {
	if ( ! empty( $_POST['hike_id'] ) && ! empty( $_POST['hike_id'] ) ) {
		$gpx_file_name = get_field_object( 'gpx_file', $_POST['hike_id'] );
		$gpx_file      = get_field( 'gpx_file', $_POST['hike_id'] );
		$json_gpx_data = get_field( 'json_gpx_file_data', $_POST['hike_id'] );

		$arr = array(
			'gpx_file_name' => $gpx_file_name['value'],
			'json_gpx_data' => $json_gpx_data,
		);
		echo json_encode( $arr );
	}
	die();
}

/**
 * Add custom field body class(es) to the body classes.
 *
 * It accepts values from a per-page custom field, and only outputs when viewing a singular static Page.
 *
 * @param array $classes Existing body classes.
 * @return array Amended body classes.
 */
add_filter( 'body_class', 'custom_body_class' );

function custom_body_class( array $classes ) {
	global $post;

	if ( ! empty( $post ) ) {
		$head_class = get_field( 'head_class', $post->ID );
		$body_cls   = ( $head_class ) ? 'transHead' : '';
		$new_class  = is_singular( 'wanderung' ) ? 'Top' : '';
		// $new_class  = is_singular( 'wanderung' ) ? '' : '';

		if ( $new_class ) {
			$classes[] = $new_class;
		}

		if ( $body_cls ) {
			$classes[] = $body_cls;
		}
		
		if ( is_search() ) {
			$remove_classes = array( 'search' );
			$classes        = array_diff( $classes, $remove_classes );
		}

		return $classes;
	}
}

add_filter( 'nav_menu_css_class', 'special_nav_class', 10, 2 );

function special_nav_class( $classes, $item ) {
	if ( in_array( 'current-menu-item', $classes ) ) {
		$classes[] = 'active ';
	}
	return $classes;
}

add_action(
	'load-post.php',
	function() {
		add_filter( 'wp_terms_checklist_args', 'wpse_terms_checklist_args' );
	}
);

function wpse_terms_checklist_args( $args ) {
	// Target the 'schedule' custom post type edit screen
	if ( 'wanderung' === get_current_screen()->id ) {
		add_filter( 'get_terms_args', 'wpse_terms_args', 10, 2 );
	}
	return $args;
}

function wpse_terms_args( $args, $taxonomies ) {
	// Target the 'all' tab in the 'schedule_day_taxonomy' terms check list
	if (
		   isset( $args['get'] )
		&& 'all' === $args['get']
		&& isset( $taxonomies[0] )
		&& 'wander-saison' === $taxonomies[0]
	) {
		// Modify the term order
		$args['orderby'] = 'ID';  // <-- Adjust this to your needs!
		$args['order']   = 'ASC'; // <-- Adjust this to your needs!

		// House cleaning - Remove the filter callbacks
		remove_filter( current_filter(), __FUNCTION__ );
		remove_filter( 'wp_terms_checklist_args', 'wpse_terms_checklist_args' );
	}
	return $args;
}

/**
 * Function to convert decimal hours to normal hours
 */
function wegwandern_convert_decimal_time( $dec ) {
	// start by converting to seconds
	$seconds = ( $dec * 3600 );
	// we're given hours, so let's get those the easy way
	$hours = floor( $dec );
	// since we've "calculated" hours, let's remove them from the seconds variable
	$seconds -= $hours * 3600;
	// calculate minutes left
	$minutes = floor( $seconds / 60 );
	// return the time formatted H.MM
	return $hours . '.' . lz( $minutes );
}

/**
 * Function to lend zero in convertion of decimal hours to normal hours
 */
function lz( $num ) {
	return ( strlen( $num ) < 2 ) ? "0{$num}" : $num;
}

/**
 * Function to display formated hiking time in hike detail page
 */
function wegwandern_formated_hiking_time_display( $hike_time, $time_separator = '.', $minute_interval = 5 ) {
	$datetimeSet = new DateTime( date( 'Y-m-d' ) );
	$time_split  = explode( $time_separator, $hike_time );

	if ( ! empty( $time_split[1] ) ) {
		$hour   = $time_split[0];
		$minute = $time_split[1];
		$second = '00';

		$dateTime = $datetimeSet->setTime( $hour, $minute, $second );

		$k = $dateTime->setTime(
			$dateTime->format( 'H' ),
			round( $dateTime->format( 'i' ) / $minute_interval ) * $minute_interval,
			0
		);

		// return $k->format( 'g' ) . $time_separator . $k->format( 'i' );
		return date_format( $k, 'H:i' );
	}
}

/**
 * Function to add html in wp_mail
 */
add_filter( 'wp_mail_content_type', 'wegwandern_mail_set_content_type' );

function wegwandern_mail_set_content_type() {
	return 'text/html';
}

/**
 * Function to get all watchlisted hikes
 */
function wegwandern_get_watchlist_hikes_list( $user_id = null ) {
	if ( is_user_logged_in() ) {
		$uid = get_current_user_id();
	} else {
		$uid = $user_id;
	}

	$watchlist_hikes = get_user_meta( $uid, 'watchlist' );
	return (array) $watchlist_hikes;
}

add_action( 'init', 'check_for_json_update' );

/**
 * Check for json update
 */
function check_for_json_update() {
	if ( isset( $_GET['update-json'] ) && $_GET['update-json'] === 'yes' ) {
		update_hike_json();
	}
}

/**
 * Cron function for auto-sync of hike json file
 */
add_action( 'wp_wegwandern_sync_hike_json_file_cron_job', 'update_hike_json' );

/**
 * Function to update the hikes in database to json file
 */
function update_hike_json() {
	$args                   = get_wanderung_filter_query();
	$args['posts_per_page'] = -1;
	$allwanderung           = get_posts( $args );
	$hike_data              = array();
	if ( ! empty( $allwanderung ) ) {
		foreach ( $allwanderung as $wanderung ) {
			$wanderung_data = array();

			/* Filter Aktivitat Section */
			$hike_aktivitat_arr = get_the_terms( $wanderung->ID, 'aktivitat' );
			$hike_aktivitat     = array();
			if ( ! empty( $hike_aktivitat_arr ) ) {
				foreach ( $hike_aktivitat_arr as $ak ) {
					$hike_aktivitat[] = $ak->term_id;
				}
			}

			/* Filter Anforderung Section */
			$hike_level      = get_the_terms( $wanderung->ID, 'anforderung' );
			$hike_level_name = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';
			$hike_level_id     = array();
			if ( ! empty( $hike_level ) ) {
				foreach ( $hike_level as $hl ) {
					$hike_level_id[] = $hl->term_id;
				}
			}

			$wanderregionen      = get_the_terms( $wanderung->ID, 'wanderregionen' );
			$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';
			// $wanderregionen_id   = ( ! empty( $wanderregionen ) ) ? (array) $wanderregionen[0]->term_id : array();
			$wanderregionen_id     = array();
			$wanderregionen_parent_id = 0;
			
			if ( ! empty( $wanderregionen ) ) {
				foreach ( $wanderregionen as $key => $wr ) {

					if ( $wr->parent != 0 ) { // child yes

						$wanderregionen_parent_id = $wr->parent;
						if ( has_term( $wr->parent, 'wanderregionen', $wanderung->ID ) ) {
							$wanderregionen_id[] = $wr->parent;
						} else {
							$wanderregionen_id[] = $wr->term_id;
						}

					} else {
						$wanderregionen_id[] = $wr->term_id;
						$wanderregionen_parent_id = $wr->term_id;
					}
				}
			}

			$wanderregionen_parent_id = (array) $wanderregionen_parent_id;
			$wanderregionen_id = array_unique( $wanderregionen_id );

			/* Filter Angebote Section */
			$hike_angebot_arr = get_the_terms( $wanderung->ID, 'angebot' );
			$hike_angebot     = array();
			if ( ! empty( $hike_angebot_arr ) ) {
				foreach ( $hike_angebot_arr as $ang ) {
					$hike_angebot[] = $ang->term_id;
				}
			}

			/* Filter Thema Section */
			$hike_thema_arr = get_the_terms( $wanderung->ID, 'thema' );
			$hike_thema     = array();
			if ( ! empty( $hike_thema_arr ) ) {
				foreach ( $hike_thema_arr as $thm ) {
					$hike_thema[] = $thm->term_id;
				}
			}

			/* Filter Routenverlauf Section */
			$hike_routenverlauf_arr = get_the_terms( $wanderung->ID, 'routenverlauf' );
			$hike_routenverlauf     = array();
			if ( ! empty( $hike_routenverlauf_arr ) ) {
				foreach ( $hike_routenverlauf_arr as $rt ) {
					$hike_routenverlauf[] = $rt->term_id;
				}
			}

			/* Filter Ausdauer Section */
			$hike_ausdauer_arr = get_the_terms( $wanderung->ID, 'ausdauer' );
			$hike_ausdauer     = array();
			if ( ! empty( $hike_ausdauer_arr ) ) {
				foreach ( $hike_ausdauer_arr as $ad ) {
					$hike_ausdauer[] = $ad->term_id;
				}
			}

			/* Filter Nach Monaten Section */
			$hike_wander_saison_arr = get_the_terms( $wanderung->ID, 'wander-saison' );
			$hike_wander_saison     = array();
			if ( ! empty( $hike_wander_saison_arr ) ) {
				foreach ( $hike_wander_saison_arr as $ws ) {
					$hike_wander_saison[] = $ws->term_id;
				}
			}

			$wander_saison_name = wegw_wandern_saison_name( $wanderung->ID );
			$hike_level_cls     = wegw_wandern_hike_level_class_name( $hike_level_name, $wanderung->ID );

			$hike_time           = ( get_field( 'dauer', $wanderung->ID ) ) ? wegwandern_formated_hiking_time_display( get_field( 'dauer', $wanderung->ID ) ) : '0.00';
			$hike_distance       = ( get_field( 'km', $wanderung->ID ) ) ? get_field( 'km', $wanderung->ID ) : '';
			$hike_ascent         = ( get_field( 'aufstieg', $wanderung->ID ) ) ? get_field( 'aufstieg', $wanderung->ID ) : '';
			$hike_descent        = ( get_field( 'abstieg', $wanderung->ID ) ) ? get_field( 'abstieg', $wanderung->ID ) : '';
			$hike_tiefster_punkt = ( get_field( 'tiefster_punkt', $wanderung->ID ) ) ? get_field( 'tiefster_punkt', $wanderung->ID ) : 0;
			$hike_hochster_punkt = ( get_field( 'hochster_punkt', $wanderung->ID ) ) ? get_field( 'hochster_punkt', $wanderung->ID ) : 0;
			$kurzbeschrieb       = ( get_field( 'kurzbeschrieb', $wanderung->ID ) ) ? get_field( 'kurzbeschrieb', $wanderung->ID ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';

			$latitude      = ( get_post_meta( $wanderung->ID, 'latitude', true ) ) ? get_post_meta( $wanderung->ID, 'latitude', true ) : 0;
			$longitude     = ( get_post_meta( $wanderung->ID, 'longitude', true ) ) ? get_post_meta( $wanderung->ID, 'longitude', true ) : 0;
			$gpx_file      = ( get_field( 'gpx_file', $wanderung->ID ) ) ? get_field( 'gpx_file', $wanderung->ID ) : '';
			$location_link = get_the_permalink( $wanderung->ID );
			$thumbsize     = 'hike-listing';
			$post_thumb    = get_the_post_thumbnail_url( $wanderung->ID, $thumbsize );

			$watchlisted_args            = array(
				'meta_query' => array(
					array(
						'key'     => 'watchlist',
						'value'   => $wanderung->ID,
						'compare' => '=',
					),
				),
				'fields'     => 'ID',
			);
			$watchlisted_user_meta_query = new WP_User_Query( $watchlisted_args );
			$watchlisted_user_meta       = $watchlisted_user_meta_query->get_results();
			$watchlisted_by              = array();
			if ( ! empty( $watchlisted_user_meta ) ) {
				foreach ( $watchlisted_user_meta as $each_watchlist ) {
					$watchlisted_by[] = $each_watchlist;
				}
			}

			$average_rating = 0;
			if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
				$average_rating = get_wanderung_average_rating( $wanderung->ID );
			}

			$wanderung_data = array(
				'longitude'                    => $longitude,
				'latitude'                     => $latitude,
				'location_regionen_name'       => $wanderregionen_name,
				'location_regionen_id'         => $wanderregionen_id,
				'location_regionen_parent_id'  => $wanderregionen_parent_id,
				'location_angebote'            => $hike_angebot,
				'location_thema'               => $hike_thema,
				'location_routenverlauf'       => $hike_routenverlauf,
				'location_ausdauer'            => $hike_ausdauer,
				'location_wander_saison'       => $hike_wander_saison,
				'location_altitude'            => $wanderregionen_name,
				'location_feature_image'       => $post_thumb,
				'location_name'                => $wanderung->post_title,
				'location_desc'                => $kurzbeschrieb,
				'location_level_cls'           => $hike_level_cls,
				'location_level_name'          => $hike_level_name,
				'location_level_id'            => $hike_level_id,
				'location_hike_time'           => $hike_time,
				'location_travel_distance'     => $hike_distance,
				'location_hike_ascent'         => $hike_ascent,
				'location_hike_descent'        => $hike_descent,
				'location_hike_tiefster_punkt' => $hike_tiefster_punkt,
				'location_hike_hochster_punkt' => $hike_hochster_punkt,
				'location_wander_saison_name'  => $wander_saison_name,
				'location_link'                => $location_link,
				'location_id'                  => $wanderung->ID,
				'location_aktivitat'           => $hike_aktivitat,
				'watchlisted_by'               => $watchlisted_by,
				'average_rating'               => $average_rating,
				'gpx_file'                     => $gpx_file,
			);
			$hike_data[]    = $wanderung_data;
		}
	}

	$ad_script_desktop = '';
	$ad_script_tablet  = '';
	$ad_script_mobile  = '';

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

	$all_data          = array();
	$all_data['hikes'] = $hike_data;
	$all_data['ads']   = array(
		'desktop' => $ad_script_desktop,
		'tablet'  => $ad_script_tablet,
		'mobile'  => $ad_script_mobile,
	);
	$hike_json_data = wp_json_encode( $all_data, JSON_PRETTY_PRINT );
	$json_file      = get_template_directory() . '/json-data/hikes.json';
	$lock_file      = $json_file . '.lock';
	$lock_handle    = fopen( $lock_file, 'c' );
	if ( ! $lock_handle || ! flock( $lock_handle, LOCK_EX | LOCK_NB ) ) {
		if ( $lock_handle ) {
			fclose( $lock_handle );
		}
		return;
	}

	$tmp_file = $json_file . '.tmp';
	if ( false !== file_put_contents( $tmp_file, $hike_json_data, LOCK_EX ) ) {
		rename( $tmp_file, $json_file );
	}

	flock( $lock_handle, LOCK_UN );
	fclose( $lock_handle );
	// echo 'Json Written with hikes - ' . count( $allwanderung );
	// die;
}

/* Auto sync Hikes Json files with events - Add, edit, delete wanderung posts */
// add_action( 'save_post', 'wegwandern_auto_sync_hike_json_file' );
// add_action( 'delete_post', 'wegwandern_auto_sync_hike_json_file' );

function wegwandern_auto_sync_hike_json_file( $post_id ) {
	$post_type = get_post_type( $post_id );

	if ( isset( $post_type ) && 'wanderung' === $post_type ) {
		update_hike_json();
	}
}

/* Auto sync Hikes Json files when comment submitted from single hike detail page (Form key: commentsform) */
add_action( 'frm_after_create_entry', 'wegwandern_sync_hike_json_in_comments_form_submit', 30, 2 );

function wegwandern_sync_hike_json_in_comments_form_submit( $entry_id, $form_id ) {
	$form_key = FrmForm::get_key_by_id( $form_id );
	if ( $form_key == 'commentsform' ) {
		/* Sync with hikes Json file */
		if ( function_exists( 'update_hike_json' ) ) {
			update_hike_json();
		}
	}
}


/*
 * Also push the same Formidable newsletter signup to Mailjet (using the Mailjet WP plugin settings).
 * Uses Double Opt-In: confirmation email sent first, only adds to Mailjet after user confirms.
 */
add_action( 'frm_after_create_entry', 'wegwandern_newsletter_doi_step1', 25, 2 );

/**
 * Step 1: On form submit, save token and send confirmation email.
 * Handles:
 * - Form 2 (newsletterabonnieren): Direct newsletter signup → List 10610399
 * - Form 11 (edit-user-profile-summit-book): Summit Book profile with optional newsletter checkbox → List 10610399
 * - Form 4 (edit-user-profile): B2B profile with optional newsletter checkbox → List 10638908
 */
function wegwandern_newsletter_doi_step1( $entry_id, $form_id ) {
	if ( ! class_exists( 'FrmForm' ) || ! class_exists( 'FrmEntry' ) || ! class_exists( 'FrmField' ) ) {
		return;
	}

	$form_key  = FrmForm::get_key_by_id( $form_id );
	
	// Check which form this is
	$is_newsletter_form    = ( 'newsletterabonnieren' === $form_key ) || ( 2 === (int) $form_id );
	$is_profile_form       = ( 'edit-user-profile-summit-book' === $form_key ) || ( 11 === (int) $form_id );
	$is_b2b_profile_form   = ( 'edit-user-profile' === $form_key ) || ( 4 === (int) $form_id );
	
	if ( ! $is_newsletter_form && ! $is_profile_form && ! $is_b2b_profile_form ) {
		return;
	}

	$entry = FrmEntry::getOne( $entry_id, true );
	if ( ! $entry || empty( $entry->metas ) ) {
		return;
	}

	// Extract form fields based on which form it is.
	$email              = '';
	$vorname            = '';
	$nachname           = '';
	$anrede             = '';
	$newsletter_checked = false;
	$fields             = FrmField::get_all_for_form( $form_id );

	foreach ( (array) $fields as $field ) {
		$value = isset( $entry->metas[ $field->id ] ) ? $entry->metas[ $field->id ] : '';
		
		if ( $is_newsletter_form ) {
			// Form 2 field mappings
			if ( 'email' === $field->type && is_email( $value ) && empty( $email ) ) {
				$email = $value;
			}
			if ( 'radio' === $field->type && ( $field->field_key === 'ofpl2' || $field->id == 6 ) ) {
				$anrede = $value;
			}
			if ( 'text' === $field->type && ( $field->field_key === 'fpin6' || $field->id == 8 ) ) {
				$vorname = $value;
			}
			if ( 'text' === $field->type && ( $field->field_key === '7asa1' || $field->id == 9 ) ) {
				$nachname = $value;
			}
		}
		
		if ( $is_profile_form ) {
			// Form 11 field mappings
			if ( 'email' === $field->type && ( $field->field_key === 'tqz0w2' || $field->id == 74 ) && is_email( $value ) ) {
				$email = $value;
			}
			if ( 'select' === $field->type && ( $field->field_key === 'zg19' || $field->id == 70 ) ) {
				$anrede = $value;
			}
			if ( 'text' === $field->type && ( $field->field_key === '9mi9m2' || $field->id == 71 ) ) {
				$vorname = $value;
			}
			if ( 'text' === $field->type && ( $field->field_key === 'uolyi2' || $field->id == 72 ) ) {
				$nachname = $value;
			}
			// Check newsletter subscription checkbox (field 76, key xl1ih2)
			if ( 'checkbox' === $field->type && ( $field->field_key === 'xl1ih2' || $field->id == 76 ) ) {
				// Checkbox value can be array or string containing 'newsletter_subscription'
				if ( is_array( $value ) ) {
					$newsletter_checked = in_array( 'newsletter_subscription', $value, true );
				} else {
					$newsletter_checked = ( strpos( $value, 'newsletter_subscription' ) !== false );
				}
			}
		}
		
		if ( $is_b2b_profile_form ) {
			// Form 4 field mappings (B2B profile)
			if ( 'email' === $field->type && ( $field->field_key === 'tqz0w' || $field->id == 24 ) && is_email( $value ) ) {
				$email = $value;
			}
			if ( 'select' === $field->type && ( $field->field_key === 'b2b_prof_frm_user_designation' || $field->id == 16 ) ) {
				$anrede = $value;
			}
			if ( 'text' === $field->type && ( $field->field_key === 'b2b_prof_frm_user_fname' || $field->id == 17 ) ) {
				$vorname = $value;
			}
			if ( 'text' === $field->type && ( $field->field_key === 'b2b_prof_frm_user_lname' || $field->id == 18 ) ) {
				$nachname = $value;
			}
			// Check newsletter subscription checkbox (field 26, key b2b_prof_frm_newsletter)
			if ( 'checkbox' === $field->type && ( $field->field_key === 'b2b_prof_frm_newsletter' || $field->id == 26 ) ) {
				// Checkbox value can be array or string containing 'newsletter_subscription'
				if ( is_array( $value ) ) {
					$newsletter_checked = in_array( 'newsletter_subscription', $value, true );
				} else {
					$newsletter_checked = ( strpos( $value, 'newsletter_subscription' ) !== false );
				}
			}
		}
	}
	
	// For Form 11 and Form 4: Only proceed if newsletter checkbox is checked
	if ( ( $is_profile_form || $is_b2b_profile_form ) && ! $newsletter_checked ) {
		return;
	}

	if ( empty( $email ) ) {
		return;
	}
	
	// Determine target Mailjet list ID
	// Form 4 (B2B) goes to list 10638908, all others go to 10640994
	$target_list_id = $is_b2b_profile_form ? 10638908 : 10640994;

	// Generate unique confirmation token.
	$token = wp_generate_password( 32, false );
	
	// Store pending signup in database.
	$signup_data = array(
		'email'    => $email,
		'vorname'  => $vorname,
		'nachname' => $nachname,
		'anrede'   => $anrede,
		'entry_id' => $entry_id,
		'list_id'  => $target_list_id,
		'created'  => current_time( 'mysql' ),
	);
	
	update_option( 'wegw_newsletter_pending_' . $token, $signup_data, false );
	
	// Build confirmation URL.
	$confirm_url = add_query_arg( array(
		'newsletter_confirm' => $token,
	), home_url( '/' ) );
	
	// Transform Anrede for email greeting.
	$greeting = '';
	if ( ! empty( $anrede ) ) {
		$anrede_trimmed = trim( $anrede );
		if ( 'Frau' === $anrede_trimmed ) {
			$greeting = 'Liebe ' . $vorname;
		} elseif ( 'Herr' === $anrede_trimmed ) {
			$greeting = 'Lieber ' . $vorname;
		} else {
			$greeting = 'Hallo ' . $vorname;
		}
	} else {
		$greeting = 'Hallo' . ( ! empty( $vorname ) ? ' ' . $vorname : '' );
	}
	
	// Send confirmation email.
	$subject = 'Bitte bestätige deine Newsletter-Anmeldung';
	
	$message = '
	<html>
	<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
		<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
			<p>' . esc_html( $greeting ) . ',</p>
			
			<p>Vielen Dank für deine Anmeldung zum WegWandern Newsletter!</p>
			
			<p>Bitte bestätige deine Anmeldung, indem du auf den folgenden Link klickst:</p>
			
			<p style="text-align: center; margin: 30px 0;">
				<a href="' . esc_url( $confirm_url ) . '" 
				   style="background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
					Newsletter-Anmeldung bestätigen
				</a>
			</p>
			
			<p>Oder kopiere diesen Link in deinen Browser:<br>
			<a href="' . esc_url( $confirm_url ) . '">' . esc_url( $confirm_url ) . '</a></p>
			
			<p>Wenn du diese Anmeldung nicht angefordert hast, kannst du diese E-Mail ignorieren.</p>
			
			<p>Herzliche Grüsse,<br>
			Dein WegWandern Team</p>
		</div>
	</body>
	</html>';
	
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	
	wp_mail( $email, $subject, $message, $headers );
	
	// Log.
	$log_file = WP_CONTENT_DIR . '/mailjet-debug.log';
	$timestamp = date( 'Y-m-d H:i:s' );
	file_put_contents( $log_file, "[{$timestamp}] DOI Step 1: Confirmation email sent to {$email}, token: {$token}\n", FILE_APPEND );
}

/**
 * Step 2: Handle confirmation link click - add to Mailjet.
 */
add_action( 'init', 'wegwandern_newsletter_doi_step2' );

function wegwandern_newsletter_doi_step2() {
	if ( ! isset( $_GET['newsletter_confirm'] ) ) {
		return;
	}
	
	$token = sanitize_text_field( $_GET['newsletter_confirm'] );
	$option_name = 'wegw_newsletter_pending_' . $token;
	$signup_data = get_option( $option_name );
	
	// Log file.
	$log_file = WP_CONTENT_DIR . '/mailjet-debug.log';
	$log = function( $msg ) use ( $log_file ) {
		$timestamp = date( 'Y-m-d H:i:s' );
		file_put_contents( $log_file, "[{$timestamp}] {$msg}\n", FILE_APPEND );
	};
	
	if ( empty( $signup_data ) ) {
		$log( 'DOI Step 2: Invalid or expired token: ' . $token );
		// Redirect to homepage with error.
		wp_redirect( home_url( '/?newsletter=invalid' ) );
		exit;
	}
	
	$email    = $signup_data['email'];
	$vorname  = $signup_data['vorname'];
	$nachname = $signup_data['nachname'];
	$anrede   = $signup_data['anrede'];
	$list_id  = isset( $signup_data['list_id'] ) ? intval( $signup_data['list_id'] ) : 10610399;
	
	$log( 'DOI Step 2: Confirmation received for ' . $email . ' (List: ' . $list_id . ')' );
	
	// Transform Anrede for Mailjet.
	if ( ! empty( $anrede ) ) {
		$anrede_trimmed = trim( $anrede );
		if ( 'Frau' === $anrede_trimmed ) {
			$anrede = 'Liebe';
		} elseif ( 'Herr' === $anrede_trimmed ) {
			$anrede = 'Lieber';
		}
	}
	
	// Build Mailjet properties.
	$properties = array();
	if ( ! empty( $anrede ) ) {
		$properties['anrede'] = $anrede;
	}
	if ( ! empty( $vorname ) ) {
		$properties['vorname'] = $vorname;
		$properties['firstname'] = $vorname;
	}
	if ( ! empty( $nachname ) ) {
		$properties['nachname'] = $nachname;
		$properties['lastname'] = $nachname;
	}
	
	$full_name = trim( $vorname . ' ' . $nachname );
	
	// Now add to Mailjet (confirmed!) using the stored list_id.
	wegwandern_mailjet_add_confirmed_contact( $email, $full_name, $list_id, $properties );
	
	// Delete the pending signup.
	delete_option( $option_name );
	
	$log( 'DOI Step 2: SUCCESS - ' . $email . ' confirmed and added to Mailjet' );
	
	// Redirect to thank you page.
	// Ändere den Slug 'newsletter-bestaetigung' falls deine Seite anders heisst.
	wp_redirect( home_url( '/newsletter-bestaetigung/?newsletter=confirmed' ) );
	exit;
}

/**
 * Add confirmed contact to Mailjet (after DOI confirmation).
 */
function wegwandern_mailjet_add_confirmed_contact( $email, $name, $list_id, $properties = array() ) {
	$log_file = WP_CONTENT_DIR . '/mailjet-debug.log';
	$log = function( $msg ) use ( $log_file ) {
		$timestamp = date( 'Y-m-d H:i:s' );
		file_put_contents( $log_file, "[{$timestamp}] {$msg}\n", FILE_APPEND );
	};

	$log( '=== Mailjet Add Confirmed Contact ===' );
	$log( 'Email: ' . $email . ', List ID: ' . $list_id );

	$creds = wegwandern_get_mailjet_credentials();
	if ( empty( $creds['api_key'] ) || empty( $creds['api_secret'] ) ) {
		$log( 'ERROR: API credentials missing' );
		return;
	}

	$allowed_properties = array( 'anrede', 'vorname', 'firstname', 'nachname', 'lastname' );
	$filtered_properties = array();
	foreach ( $properties as $key => $value ) {
		if ( in_array( $key, $allowed_properties, true ) && ! empty( $value ) ) {
			$filtered_properties[ $key ] = $value;
		}
	}

	$contact_data = array(
		'Email'  => $email,
		'Action' => 'addforce',
	);
	
	if ( ! empty( $filtered_properties ) ) {
		$contact_data['Properties'] = $filtered_properties;
	}
	
	if ( ! empty( $name ) ) {
		$contact_data['Name'] = $name;
	}

	$api_url = 'https://api.mailjet.com/v3/REST/contactslist/' . intval( $list_id ) . '/managecontact';

	$response = wp_remote_post( $api_url, array(
		'method'  => 'POST',
		'timeout' => 30,
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( $creds['api_key'] . ':' . $creds['api_secret'] ),
			'Content-Type'  => 'application/json',
		),
		'body' => wp_json_encode( $contact_data ),
	) );

	if ( is_wp_error( $response ) ) {
		$log( 'ERROR: ' . $response->get_error_message() );
		return;
	}

	$status_code = wp_remote_retrieve_response_code( $response );
	$body        = wp_remote_retrieve_body( $response );

	$log( 'Response status: ' . $status_code );
	$log( 'Response body: ' . $body );
}

/**
 * Shortcode für Newsletter-Bestätigungsmeldung.
 * Verwendung: [newsletter_bestaetigung]
 * Zeigt automatisch eine Meldung an, wenn ?newsletter=confirmed in der URL steht.
 */
add_shortcode( 'newsletter_bestaetigung', 'wegwandern_newsletter_confirmation_shortcode' );

function wegwandern_newsletter_confirmation_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'success_title'   => 'Vielen Dank!',
		'success_message' => 'Deine Newsletter-Anmeldung wurde erfolgreich bestätigt. Du erhältst ab sofort unsere neuesten Wandertipps und Angebote.',
		'error_title'     => 'Fehler',
		'error_message'   => 'Der Bestätigungslink ist ungültig oder abgelaufen. Bitte melde dich erneut für den Newsletter an.',
	), $atts, 'newsletter_bestaetigung' );

	$status = isset( $_GET['newsletter'] ) ? sanitize_text_field( $_GET['newsletter'] ) : '';

	if ( empty( $status ) ) {
		return ''; // Kein Parameter = nichts anzeigen.
	}

	$output = '<div class="newsletter-confirmation">';

	if ( $status === 'confirmed' ) {
		$output .= '<div class="newsletter-success">';
		$output .= '<h2>' . esc_html( $atts['success_title'] ) . '</h2>';
		$output .= '<p>' . esc_html( $atts['success_message'] ) . '</p>';
		$output .= '</div>';
	} elseif ( $status === 'error' ) {
		$output .= '<div class="newsletter-error">';
		$output .= '<h2>' . esc_html( $atts['error_title'] ) . '</h2>';
		$output .= '<p>' . esc_html( $atts['error_message'] ) . '</p>';
		$output .= '</div>';
	}

	$output .= '</div>';

	// Inline CSS für einfaches Styling.
	$output .= '<style>
		.newsletter-confirmation { margin: 20px 0; padding: 20px; border-radius: 8px; }
		.newsletter-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
		.newsletter-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
		.newsletter-confirmation h2 { margin-top: 0; }
	</style>';

	return $output;
}

// Legacy function kept for compatibility (now unused).
function wegwandern_mailjet_subscribe_from_formidable( $entry_id, $form_id ) {
	// This function is replaced by the DOI flow above.
	return;
}

/**
 * Get Mailjet API credentials from api-wegwandern.txt file.
 */
function wegwandern_get_mailjet_credentials() {
	static $credentials;
	
	if ( null !== $credentials ) {
		return $credentials;
	}
	
	$file_path = get_template_directory() . '/api-wegwandern.txt';
	if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
		return array( 'api_key' => '', 'api_secret' => '' );
	}
	
	$lines = file( $file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	$api_key = '';
	$api_secret = '';
	
	if ( ! empty( $lines[0] ) ) {
		$api_key = trim( $lines[0] );
	}
	if ( ! empty( $lines[1] ) ) {
		$api_secret = trim( $lines[1] );
	}
	
	$credentials = array(
		'api_key'    => $api_key,
		'api_secret' => $api_secret,
	);
	
	return $credentials;
}

/*
 * Add custom click functionality to ACF field in theme options.
 * Click to sync hikes json file
 */
add_action( 'acf/input/admin_footer', 'wegwandern_acf_input_admin_footer' );

function wegwandern_acf_input_admin_footer() {
	$url = site_url();
	?>
	<script type="text/javascript">
		(function($) {
			$('#hikes_json_update_btn').click(function(){
				window.open('<?php echo $url; ?>/?update-json=yes', '_blank');
			});
		})(jQuery); 
	</script>
	<?php
}

/*
 * Function to change user activation mail subject
 */
add_filter( 'user_activation_notification_title', 'wegwandern_user_activation_subject_update', 10, 4 );

function wegwandern_user_activation_subject_update( $text ) {
	$form_id = isset( $_POST['form_id'] ) && $_POST['form_id'] != "" ? $_POST['form_id'] : 0;
	$b2b_reg_form_id = FrmForm::get_id_by_key( 'user-registration' );
	$summit_book_reg_form_id = FrmForm::get_id_by_key( 'user-registration-summit-book' );

	if ( $form_id == $b2b_reg_form_id ) {
		$activation_mail_subject = "Bitte aktivieren Sie Ihr B2B-Konto";
	} elseif( $form_id == $summit_book_reg_form_id ) {
		$activation_mail_subject = "Bitte aktivieren Sie Ihr Gipfelbuch-Konto";
	} else {
		$activation_mail_subject = "Aktiviere deinen Account";
	}

	return $activation_mail_subject;
}

/*
 * Function to change user activation mail content
 */
add_filter( 'user_activation_notification_message', 'wegwandern_user_activation_message_update', 10, 4 );

function wegwandern_user_activation_message_update( $message, $activation_url ) {
	$form_id = isset( $_POST['form_id'] ) && $_POST['form_id'] != "" ? $_POST['form_id'] : 0;
	$b2b_reg_form_id = FrmForm::get_id_by_key( 'user-registration' );
	$summit_book_reg_form_id = FrmForm::get_id_by_key( 'user-registration-summit-book' );

	if ( $form_id == $b2b_reg_form_id ) {
		$message = "Vielen Dank für Ihre Registrierung für das B2B Portal. Bitte verifizieren Sie Ihre Anmeldung indem Sie auf diesen Link klicken: " . $activation_url;
	} elseif( $form_id == $summit_book_reg_form_id ) {
		$message = "Vielen Dank für Ihre Registrierung für das «Gipfelbuch». Bitte verifizieren Sie Ihre Anmeldung indem Sie auf diesen Link klicken: " . $activation_url;
	} else {
		$message = "Vielen Dank für Ihre Registrierung bei WegWandern.ch! Um die Aktivierung Ihres Kontos abzuschließen, klicken Sie bitte auf den folgenden Link: " . $activation_url;
	}

	return $message;
}

/*
 * Function to change user activation mail 'From Email Address'
 */
add_filter( 'wp_mail_from', 'wegwandern_user_activation_from_address_update');

function wegwandern_user_activation_from_address_update() {
	return 'info@wegwandern.ch';
}

add_filter( 'wp_mail_from_name', 'wegwandern_user_activation_from_address_name_update');

function wegwandern_user_activation_from_address_name_update() {
	return 'Wegwandern';
}

/*
 * Function to treat empty body classes - `wegw-body-wrapper`
 */
add_filter( 'body_class', 'wegwandern_body_class' );

function wegwandern_body_class( $css_class ) {
	$css_class[] = 'wegw-body-wrapper';
	return $css_class;
}