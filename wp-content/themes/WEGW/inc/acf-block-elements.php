<?php
/**
 * Add a custom block category
 */
function wegw_custom_block_category( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'wegwandern-layout-category',
				'title' => __( 'WegWandern Layouts', 'wegwandern-layout-category' ),
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'wegw_custom_block_category', 10, 2 );

/**
 * Add or register ACF blocks
 */
function wegw_register_acf_block_types() {

	acf_register_block_type(
		array(
			'name'            => 'wegw-itinerary',
			'title'           => _x( 'Wanderbeschrieb Accordion', 'wanderung-itinerary', 'wegwandern' ),
			'description'     => __( 'Wanderung Itinerary', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/wanderung-itinerary/wanderung-itinerary.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'wegw-grey-background-section',
			'title'           => _x( 'Grauer Hintergrund mit Symbol', 'wanderung-grey-background-section', 'wegwandern' ),
			'description'     => __( 'Wanderung Grey Background Section', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/wanderung-grey-background-section/wanderung-grey-background-section.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'wegw-merkmale',
			'title'           => _x( 'Wandermerkmale', 'wegw-merkmale', 'wegwandern' ),
			'description'     => __( 'Wandermerkmale', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/wanderung-merkmale/wanderung-merkmale.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'region-menu',
			'title'           => _x( 'Menüliste und Sliderversionen', 'region-menu', 'wegwandern' ),
			'description'     => __( 'Menüliste und Sliderversionen', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/regionen-menu/regionen-menu.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'teaser-box',
			'title'           => _x( 'Teaser box', 'teaser-box', 'wegwandern' ),
			'description'     => __( 'Teaser box', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/teaser-box/teaser-box.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'regionen-content-map-listing',
			'title'           => _x( 'Wander-Karte & List', 'regionen-content-map-listing', 'wegwandern' ),
			'description'     => __( 'Wander-Karte & List', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/regionen-content-map-listing/regionen-content-map-listing.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'wegw-ads',
			'title'           => _x( 'Ad Server', 'wegw-ads', 'wegwandern' ),
			'description'     => __( 'Ad Server', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/wanderung-ads/wanderung-ads.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);
	acf_register_block_type(
		array(
			'name'            => 'wegw-img-lightbox-gallery',
			'title'           => _x( 'Bildergalerie', 'wegw-img-lightbox-gallery', 'wegwandern' ),
			'description'     => __( 'Bildergalerie', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/wegw-img-lightbox-gallery/wegw-img-lightbox-gallery.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);
	acf_register_block_type(
		array(
			'name'            => 'wegw-accordion',
			'title'           => _x( 'Accordion', 'wegw-accordion', 'wegwandern' ),
			'description'     => __( 'Accordion', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/wegw-accordion/wegw-accordion.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'teaser-wanderung',
			'title'           => _x( 'Teaser Wanderung', 'teaser-wanderung', 'wegwandern' ),
			'description'     => __( 'Teaser Wanderung', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/teaser-wanderung/teaser-wanderung.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'teaser-content',
			'title'           => _x( 'Bild Text 2-spaltig', 'teaser-content', 'wegwandern' ),
			'description'     => __( 'Bild Text 2-spaltig', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/teaser-content/teaser-content.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'blog-slider',
			'title'           => _x( 'Blog Slider', 'blog-slider', 'wegwandern' ),
			'description'     => __( 'Blog Slider', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/blog-slider/blog-slider.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);

	acf_register_block_type(
		array(
			'name'            => 'unterkunft-slider',
			'title'           => _x( 'B2B Integration', 'unterkunft-slider', 'wegwandern' ),
			'description'     => __( 'B2B Integration', 'wegwandern' ),
			'render_template' => get_template_directory() . '/template-parts/block/wanderung-unterkunft/wanderung-unterkunft.php',
			'category'        => 'wegwandern-layout-category',
			'icon'            => '',
			'align'           => false,
			'mode'            => 'edit',
		)
	);
}

/**
 * Check if function exists and hook into setup
 */
if ( function_exists( 'acf_register_block_type' ) ) {
	add_action( 'init', 'wegw_register_acf_block_types' );
}
