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
			$selected_wanderregionen_parent_id     = array();
			
			if ( ! empty( $wanderregionen ) ) {
				/* Get selected parent hikes */
				foreach ( $wanderregionen as $pwr ) {
					if ( $pwr->parent == 0 ) {
						$selected_wanderregionen_parent_id[] = $pwr->term_id;
					}
				}

				foreach ( $wanderregionen as $key => $wr ) {
					/* Check if parent region already selected */
					if ( in_array( $wr->parent, $selected_wanderregionen_parent_id) ) {
						$wanderregionen_id[] = $wr->parent;
					} else {
						$wanderregionen_id[] = $wr->term_id;
					}
				}
			}

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
	$hike_json_data    = wp_json_encode( $all_data, JSON_PRETTY_PRINT );
	$json_file         = get_template_directory() . '/json-data/hikes.json';
	file_put_contents( $json_file, $hike_json_data, JSON_PRETTY_PRINT );
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