<?php
/**
 * Table of Contents:
 *
 * 1.0 - Enqeue stylesheets
 * 2.0 - Enqeue JavaScripts
 * ----------------------------------------------------------------------------
 */
function wegwandern_scripts() {
	/**
	 * 1.0 - Enqeue stylesheets
	 * ----------------------------------------------------------------------------
	 */
	wp_enqueue_style( 'custom-css', get_template_directory_uri() . '/css/custom.css', false, _S_VERSION, 'all' );

	// if( is_page('tourenportal') || is_page_template( 'page-templates/page-wanderregionen.php' ) || is_single() ) {
		wp_enqueue_style( 'ol-css', get_template_directory_uri() . '/lib/ol@v7.1.0/ol.css', false, _S_VERSION, 'all' );
		wp_enqueue_style( 'ol-ext-css', get_template_directory_uri() . '/lib/ol-ext/ol-ext.css', false, _S_VERSION, 'all' );

		wp_enqueue_script( 'ol-js', get_template_directory_uri() . '/lib/ol@v7.1.0/ol.js', array(), _S_VERSION, false );
		wp_enqueue_script( 'ol-ext-js', get_template_directory_uri() . '/lib/ol-ext/ol-ext.min.js', array(), _S_VERSION, false );
		wp_enqueue_script( 'ol-proj4-js', get_template_directory_uri() . '/lib/ol-ext/proj4.js', array(), _S_VERSION, false );
	// }

	wp_enqueue_style( 'jquery-ui-css', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css', false, _S_VERSION, 'all' );
	wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array(), _S_VERSION, false );

	wp_enqueue_style( 'owl-carousel-min-css', get_template_directory_uri() . '/css/owl.carousel.min.css', false, _S_VERSION, 'all' );

	/* Owl Carousel 2 css */
	wp_enqueue_style( 'owl-carousel-default-css', get_template_directory_uri() . '/lib/owl-carousel2/owl.theme.default.min.css', false, _S_VERSION, 'all' );
	wp_enqueue_style( 'owl-carousel-min-css', get_template_directory_uri() . '/lib/owl-carousel2/owl.carousel.min.css', false, _S_VERSION, 'all' );

	if( ! is_home() || ! is_front_page() ) {
		wp_enqueue_style( 'lightgallery-bundle-css', get_template_directory_uri() . '/css/lightgallery-bundle.min.css', false, _S_VERSION, 'all' );
	}

	/* Js script for filter section - Tourenportal */
	if( is_page('tourenportal') ) {
		/* Select 2 css */
		wp_enqueue_style( 'select2-style', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css', false, _S_VERSION, 'all' );
		/* Select 2 js */
		wp_enqueue_script( 'select2-script', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js', array(), _S_VERSION, true );
	}
	/**
	 * 2.0 - Enqeue JavaScripts
	 * ----------------------------------------------------------------------------
	
	if ( is_page_template( 'page-templates/page-wanderregionen.php' ) ) {
		wp_enqueue_script( 'admin-script-js', get_template_directory_uri() . '/js/admin.js', array(), _S_VERSION, false );
		wp_localize_script(
			'admin-script-js',
			'ajax_object',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'ajax-nonce' ),

			)
		);
	} */

	wp_enqueue_script( 'filter-script', get_template_directory_uri() . '/js/filter.js', array(), _S_VERSION, true );

	wp_enqueue_script( 'wegw-map-scripts-json-js', get_template_directory_uri() . '/js/wegw-map-scripts-json.js', array(), _S_VERSION, true );
	$localize_args = array(
        'jsonUrl' => get_template_directory_uri() . '/json-data/hikes.json'
    );
    wp_localize_script( 'wegw-map-scripts-json-js', 'wegwMapVars', $localize_args );

	wp_enqueue_script( 'custom-js', get_template_directory_uri() . '/js/custom.js', array(), _S_VERSION, true );

	wp_enqueue_script( 'filesaver-js', 'https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.2/FileSaver.min.js', array(), _S_VERSION, true );

	wp_enqueue_script( 'jspdf-js', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js', array(), _S_VERSION, true );

	wp_enqueue_script( 'jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array(), _S_VERSION, false );

	wp_enqueue_script( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js', array(), _S_VERSION, true );

	wp_enqueue_script( 'jquery-cookie-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js', array(), _S_VERSION, true );

	wp_enqueue_script( 'jquery.ui.touch-punch', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js', array(), _S_VERSION, true );

	wp_enqueue_script( 'owl-carousel-min-js', get_template_directory_uri() . '/js/owl.carousel.min.js', array(), _S_VERSION, true );

	/* Owl Carousel 2 js */
	wp_enqueue_script( 'owl-carousel-js', get_template_directory_uri() . '/lib/owl-carousel2/owl.carousel.min.js', array(), _S_VERSION, true );

	// if( ! is_front_page() ) {
	// 	/* Lightgallery js */
	// 	wp_enqueue_script( 'lg-umd-min-js', get_template_directory_uri() . '/js/lightgallery-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-autoplay-umd-min-js', get_template_directory_uri() . '/js/lg-autoplay-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-fullscreen-umd-min-js', get_template_directory_uri() . '/js/lg-fullscreen-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-video-umd-min-js', get_template_directory_uri() . '/js/lg-video-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-comment-umd-min-js', get_template_directory_uri() . '/js/lg-comment-umd.min.js', array(), _S_VERSION, true );

	// 	/* Lightgallert assets */

	// 	wp_enqueue_style( 'lightgallery-css', get_template_directory_uri() . '/css/lightgallery.min.css', false, _S_VERSION, 'all' );
	// 	wp_enqueue_script( 'lg-thumbnail-umd-min-js', get_template_directory_uri() . '/js/lg-thumbnail-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-zoom-umd-js', get_template_directory_uri() . '/js/lg-zoom-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-hash-umd-min-js', get_template_directory_uri() . '/js/lg-hash-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-medium-zoom-umd-min-js', get_template_directory_uri() . '/js/lg-medium-zoom-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-pager-umd-min-js', get_template_directory_uri() . '/js/lg-pager-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-relative-caption-umd-min-js', get_template_directory_uri() . '/js/lg-relative-caption-umd.min.js', array(), _S_VERSION, true );
	// 	wp_enqueue_script( 'lg-vimeo-thumbnail-umd-min-js', get_template_directory_uri() . '/js/lg-vimeo-thumbnail-umd.min.js', array(), _S_VERSION, true );
	// }
}

add_action( 'wp_enqueue_scripts', 'wegwandern_scripts' );

/**
 * Enqueue jquery for admin logged out.
 */
function wegw_jquery_init() {
	if ( ! is_admin() ) {
		wp_deregister_script( 'jquery' );

		// Load the copy of jQuery that comes with WordPress
		// The last parameter set to TRUE states that it should be loaded
		// in the footer.
		wp_register_script( 'jquery', '/wp-includes/js/jquery/jquery.min.js', false, '3.6.1', false );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'admin-script-js' );
	}
}

// add_action( 'init', 'wegw_jquery_init' );