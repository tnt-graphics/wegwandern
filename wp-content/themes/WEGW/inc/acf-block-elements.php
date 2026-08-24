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
			'name'                         => 'wegw-itinerary',
			'title'                        => _x( 'Wanderbeschrieb Accordion', 'wanderung-itinerary', 'wegwandern' ),
			'description'                  => __( 'Wanderung Itinerary', 'wegwandern' ),
			'render_template'              => get_template_directory() . '/template-parts/block/wanderung-itinerary/wanderung-itinerary.php',
			'category'                     => 'wegwandern-layout-category',
			'icon'                         => 'list-view',
			'align'                        => false,
			'mode'                         => 'preview',
			'acf_block_version'            => 3,
			'api_version'                  => 3,
			'hide_fields_in_sidebar'       => false,
			'expanded_editor_buttons'      => true,
			'expanded_editor_button_text'  => __( 'Felder bearbeiten', 'wegwandern' ),
			'supports'                     => array(
				'align' => false,
				'mode'  => false,
				'jsx'   => false,
			),
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

/**
 * Stack itinerary repeaters as boxes in WP 7.1 (table layout is clipped in the iframe sidebar).
 */
function wegw_itinerary_repeater_editor_layout( $field ) {
	if ( is_admin() ) {
		$field['layout'] = 'block';
	}
	return $field;
}
add_filter( 'acf/load_field/name=itinerary_details', 'wegw_itinerary_repeater_editor_layout' );
add_filter( 'acf/load_field/name=itinerary_icons', 'wegw_itinerary_repeater_editor_layout' );

/**
 * WP 7.1 always iframes the canvas. ACF still styles `.edit-post-sidebar`, which
 * no longer wraps the inspector — fields overflow and get clipped.
 */
function wegw_itinerary_block_editor_assets() {
	$css = <<<'CSS'
.editor-sidebar,
.editor-sidebar__panel,
.interface-complementary-area,
.interface-interface-skeleton__sidebar {
	overflow-x: auto;
}
.editor-sidebar .acf-block-panel,
.editor-sidebar .acf-fields,
.interface-complementary-area .acf-block-panel,
.interface-complementary-area .acf-fields,
.edit-post-sidebar .acf-block-panel,
.edit-post-sidebar .acf-fields {
	max-width: 100%;
	min-width: 0;
	overflow-x: auto;
	box-sizing: border-box;
}
.editor-sidebar .acf-fields > .acf-field,
.interface-complementary-area .acf-fields > .acf-field {
	width: auto !important;
	float: none !important;
	min-width: 0;
}
.editor-sidebar .acf-repeater,
.editor-sidebar .acf-table,
.interface-complementary-area .acf-repeater,
.interface-complementary-area .acf-table,
.acf-block-form-modal .acf-repeater,
.acf-block-form-modal .acf-table {
	width: 100% !important;
	max-width: 100%;
	table-layout: auto;
}
.editor-sidebar .acf-repeater .acf-row-handle,
.interface-complementary-area .acf-repeater .acf-row-handle {
	width: 28px;
}
.acf-block-form-modal .components-modal__content {
	overflow: auto;
}
.acf-block-form-modal .acf-modal-block-form-container,
.acf-block-form-modal .acf-fields {
	max-width: 100%;
}
.wegw-itinerary-editor-preview {
	padding: 16px;
	border: 1px solid #dcdcde;
	background: #fff;
	border-radius: 2px;
}
.wegw-itinerary-editor-preview p { margin: 0 0 8px; }
.wegw-itinerary-editor-preview ol { margin: 0; padding-left: 1.25em; }
CSS;

	wp_register_style( 'wegw-acf-block-editor', false, array(), _S_VERSION );
	wp_enqueue_style( 'wegw-acf-block-editor' );
	wp_add_inline_style( 'wegw-acf-block-editor', $css );

	$script_handle = ( wp_script_is( 'acf-blocks', 'registered' ) || wp_script_is( 'acf-blocks', 'enqueued' ) ) ? 'acf-blocks' : 'wp-edit-post';
	if ( wp_script_is( $script_handle, 'registered' ) || wp_script_is( $script_handle, 'enqueued' ) ) {
		wp_add_inline_script(
			$script_handle,
			'(function(){if(!window.wp||!wp.data){return;}var last="";function findBtn(){var b=document.querySelector(".acf-blocks-open-expanded-editor-btn");if(b){return b;}var buttons=document.querySelectorAll(".block-editor-block-toolbar button");for(var i=0;i<buttons.length;i++){if(buttons[i].querySelector(".dashicons-edit, .dashicon.dashicons-edit")){return buttons[i];}}return null;}function openEditor(){if(document.querySelector(".acf-block-form-modal")){return true;}var btn=findBtn();if(btn){btn.click();return true;}return false;}wp.data.subscribe(function(){try{var b=wp.data.select("core/block-editor").getSelectedBlock();var id=b&&b.clientId?b.clientId:"";if(id===last){return;}last=id;if(!b||b.name!=="acf/wegw-itinerary"){return;}var tries=0;var t=setInterval(function(){tries++;if(openEditor()||tries>15){clearInterval(t);}},150);}catch(e){}});})();',
			'after'
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'wegw_itinerary_block_editor_assets', 20 );
