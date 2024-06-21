<?php
/**
 * Functions for Community Beitrag or hiking article
 *
 * @package wegwandern-summit-book
 */

define(
	'SUMMIT_BOOK_COMMUNITY_BEITRAG_STATUS',
	array(
		'saved'          => __( 'Gespeichert', 'wegwandern-summit-book' ),
		'inVerification' => __( 'In Prüfung', 'wegwandern-summit-book' ),
		'published'      => __( 'Veröffentlicht', 'wegwandern-summit-book' ),
		'rejected'       => __( 'Rejected', 'wegwandern-summit-book' ),
	)
);

add_action( 'wp_ajax_wegwandern_summit_book_region_dropdown', 'wegwandern_summit_book_region_dropdown' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_region_dropdown', 'wegwandern_summit_book_region_dropdown' );

/**
 * Populate the regions of hiking
 */
function wegwandern_summit_book_region_dropdown() {
	$request = file_get_contents( 'php://input' );
	parse_str( $request, $post_array );
	$all_regions = get_regions();
	$regions     = array();
	$subregions  = array();
	foreach ( $all_regions as $each_region ) {
		if ( $each_region->parent > 0 ) {
			$subregions[ $each_region->parent ][ $each_region->term_id ] = $each_region->name;
		} else {
			$regions[ $each_region->term_id ] = $each_region->name;
		}
	}
	$result['regions']    = $regions;
	$result['subregions'] = $subregions;
	$selected_region      = '';
	$selected_subregion   = '';
	if ( isset( $post_array['entryId'] ) ) {
		$entry = FrmEntry::getOne( $post_array['entryId'] );
		if ( $entry ) {
			$entry_post_id      = $entry->post_id;
			$selected_region    = get_post_meta( $entry_post_id, 'region', true );
			$selected_subregion = get_post_meta( $entry_post_id, 'sub_region', true );
		}
	}
	$result['entrySelectedRegion']     = $selected_region;
	$result['entrySelectedSubRegion']  = $selected_subregion;
	$result['entrySelectedRegionText'] = $selected_region !== '' ? $regions[ $selected_region ] : '';
	$selected_sub_region_text          = 'Select';
	if ( $selected_subregion && $selected_subregion != '' && isset( $subregions[ $selected_region ][ $selected_subregion ] ) ) {
		$selected_sub_region_text = $subregions[ $selected_region ][ $selected_subregion ];
	}
	$result['entrySelectedSubRegionText'] = $selected_sub_region_text;
	echo wp_json_encode( $result );
	wp_die();
}

/**
 * Get regions from wanderregionen
 */
function get_regions() {
	$args        = array(
		'taxonomy'   => 'wanderregionen',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);
	$all_regions = get_terms( $args );
	return $all_regions;
}

add_filter( 'acf/load_field/name=region', 'acf_load_region_field_choices' );
add_filter( 'acf/load_field/name=sub_region', 'acf_load_region_field_choices' );

/**
 * Populate region values in acf region and sub region fields
 *
 * @param array $field array of field options.
 */
function acf_load_region_field_choices( $field ) {
	// reset choices.
	$field['choices'] = array();
	$all_regions      = get_regions();
	foreach ( $all_regions as $each_region ) {
		$field['choices'][ $each_region->term_id ] = $each_region->name;
	}
	// return the field.
	return $field;
}

add_action( 'wp_ajax_wegwandern_summit_book_delete_summit_book', 'wegwandern_summit_book_delete_summit_book' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_delete_summit_book', 'wegwandern_summit_book_delete_summit_book' );

/**
 * Delete a community beitrag
 */
function wegwandern_summit_book_delete_summit_book() {
	$request = file_get_contents( 'php://input' );
	parse_str( $request, $post_array );
	$output       = '';
	$process      = '';
	$output_array = array();
	if ( isset( $post_array['entryId'] ) ) {
		$entry_id = $post_array['entryId'];
		if ( isset( $post_array['process'] ) ) {
			$process = $post_array['process'];
			if ( 'confirm' === $process ) {
				$item       = $post_array['item'];
				$yes_button = __( 'Ja, löschen', 'wegwandern-summit-book' );
				$no_button  = __( 'Abbrechen', 'wegwandern-summit-book' );
				if ( 'article' === $item ) {
					$output                 .= '<div class="delete-article-confirmation">';
					$delete_heading          = __( 'Wanderung löschen?', 'wegwandern-summit-book' );
					$delete_msg              = __( 'Bist du sicher, dass du diese Wanderung löschen möchtest?', 'wegwandern-summit-book' );
					$output_array['title']   = $delete_heading;
					$output_array['content'] = $delete_msg;
					$output_array['data_id'] = $entry_id;
					$output_array['type']    = $item;
					$output                 .= "<h2>$delete_heading</h2>";
					$output                 .= "<div class='confirmation-msg'>$delete_msg</div>";
					$output                 .= "<button data-dismiss='modal' class='cancel-delete'>$no_button</button><button class='confirm-delete confirm-delete-article' id='confirm-delete_$entry_id'>$yes_button</button>";
					$output                 .= '</div>';
				} elseif ( 'pinwand-ad' === $item ) {
					$output                 .= '<div class="delete-pinwand-ad-confirmation">';
					$delete_heading          = __( 'Inserat löschen?', 'wegwandern-summit-book' );
					$delete_msg              = __( 'Bist du sicher, dass du dieses Inserat löschen möchtest?', 'wegwandern-summit-book' );
					$output_array['title']   = $delete_heading;
					$output_array['content'] = $delete_msg;
					$output_array['data_id'] = $entry_id;
					$output_array['type']    = $item;
					$output                 .= "<h2>$delete_heading</h2>";
					$output                 .= "<div class='confirmation-msg'>$delete_msg</div>";
					$output                 .= "<button data-dismiss='modal' class='cancel-delete'>$no_button</button><button class='confirm-delete confirm-delete-pinwand-ad' id='confirm-delete_$entry_id'>$yes_button</button>";
					$output                 .= '</div>';
				} elseif ( 'watchlist' === $item ) {
					$output                 .= '<div class="delete-watchlist-confirmation">';
					$delete_heading          = __( 'Wanderung löschen?', 'wegwandern-summit-book' );
					$delete_msg              = __( 'Bist du sicher, dass du diese Wanderung löschen möchtest?', 'wegwandern-summit-book' );
					$output_array['title']   = $delete_heading;
					$output_array['content'] = $delete_msg;
					$output_array['data_id'] = $entry_id;
					$output_array['type']    = $item;
					$output                 .= "<h2>$delete_heading</h2>";
					$output                 .= "<div class='confirmation-msg'>$delete_msg</div>";
					$output                 .= "<button data-dismiss='modal' class='cancel-delete'>$no_button</button><button class='confirm-delete confirm-delete-watchlist' id='confirm-delete_$entry_id'>$yes_button</button>";
					$output                 .= '</div>';
				} elseif ( 'comment' === $item ) {
					$output                 .= '<div class="delete-comment-confirmation">';
					$delete_heading          = __( 'Kommentar löschen?', 'wegwandern-summit-book' );
					$delete_msg              = __( 'Bist du sicher, dass du diesen Kommentar löschen möchtest?', 'wegwandern-summit-book' );
					$output_array['title']   = $delete_heading;
					$output_array['content'] = $delete_msg;
					$output_array['data_id'] = $entry_id;
					$output_array['type']    = $item;
					$output                 .= "<h2>$delete_heading</h2>";
					$output                 .= "<div class='confirmation-msg'>$delete_msg</div>";
					$output                 .= "<button data-dismiss='modal' class='cancel-delete'>$no_button</button><button class='confirm-delete confirm-delete-comment' id='confirm-delete_$entry_id'>$yes_button</button>";
					$output                 .= '</div>';
				}
			} else {
				if ( 'watchlist' === $post_array['item'] ) {
					global $current_user;
					$user_id = $current_user->ID;
					delete_user_meta( $user_id, 'watchlist', $entry_id );

					/* Sync with hikes Json file */			
					if ( function_exists( 'update_hike_json' ) ) {
						update_hike_json();
					}
		
				} elseif ( 'comment' === $post_array['item'] ) {
					wp_delete_comment( $entry_id );
				} else {
					FrmEntry::destroy( $entry_id );
				}
			}
		}
	}
	$result['process']     = $process;
	$result['result']      = $output;
	$result['outputArray'] = $output_array;
	echo wp_json_encode( $result );
	wp_die();
}

add_action( 'delete_user', 'delete_community_beitrags_of_user', 10 );

/**
 * Delete community beitrag of user when deleted
 *
 * @param int $user_id id of the user getting deleted.
 */
function delete_community_beitrags_of_user( $user_id ) {
	/**
	 * Delete the articles of user
	 */
	$args               = array(
		'post_type'   => 'community_beitrag',
		'author'      => $user_id,
		'post_status' => 'any',
		'numberposts' => -1,
	);
	$community_beitrags = get_posts( $args );
	if ( ! empty( $community_beitrags ) ) {
		foreach ( $community_beitrags as $each_article ) {
			$entry_id = FrmDb::get_var( 'frm_items', array( 'post_id' => $each_article->ID ), 'id' );
			FrmEntry::destroy( $entry_id );
			wp_delete_post( $each_article->ID );
		}
	}
	/**
	 * Delete the pinwall ads of user
	 */
	$args2            = array(
		'post_type'   => 'pinnwand_eintrag',
		'author'      => $user_id,
		'post_status' => 'any',
		'numberposts' => -1,
	);
	$pinwand_eintrags = get_posts( $args2 );
	if ( ! empty( $pinwand_eintrags ) ) {
		foreach ( $pinwand_eintrags as $each_pinwand_ad ) {
			$entry_id = FrmDb::get_var( 'frm_items', array( 'post_id' => $each_pinwand_ad->ID ), 'id' );
			FrmEntry::destroy( $entry_id );
			wp_delete_post( $each_pinwand_ad->ID );
		}
	}
}

add_filter( 'single_template', 'override_single_template' );

/**
 * Show template for community beitrag single page
 *
 * @param string $single_template template file.
 */
function override_single_template( $single_template ) {
	global $post;
	$file = SUMMIT_BOOK_PLUGIN_DIR_PATH . '/templates/single-' . $post->post_type . '.php';
	if ( file_exists( $file ) ) {
		$single_template = $file;
	}
	return $single_template;
}

/**
 * Get articles related to a region
 *
 * @param int $region region to find articles for.
 * @param int $number_of_articles number of articles to return.
 */
function get_articles_of_region( $region, $number_of_articles = 2 ) {
	$args     = array(
		'post_type'   => 'community_beitrag',
		'numberposts' => $number_of_articles,
		'meta_query'  => array(
			'relation' => 'OR',
			array(
				'key'   => 'region',
				'value' => $region,
			),
			array(
				'key'   => 'sub_region',
				'value' => $region,
			),
		),
	);
	$articles = get_posts( $args );
	return $articles;
}

add_action( 'wp_ajax_wegwandern_summit_book_article_loadmore', 'wegwandern_summit_book_article_loadmore' );
add_action( 'wp_ajax_nopriv_wegwandern_summit_book_article_loadmore', 'wegwandern_summit_book_article_loadmore' );

/**
 * Load more articles in frontend listing page
 */
function wegwandern_summit_book_article_loadmore() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		die();
	}

	$count = $_POST['count'];
	$args  = array(
		'post_type'      => 'community_beitrag',
		'posts_per_page' => 3,
		'offset'         => $count,
	);

	$article    = '';
	$post_query = get_posts( $args );
	if ( ! empty( $post_query ) ) {
		global $post;
		foreach ( $post_query as $post ) {
			setup_postdata( $post );
			$post_id          = get_the_ID();
			$article_image_id = get_post_meta( $post_id, 'teaser_image', true );
			$article_image    = wp_get_attachment_image_url( $article_image_id, 'large' );
			$article_text     = get_post_meta( $post_id, 'einleitung', true );
			$article_link     = get_permalink( $post_id );
			$article         .= '<div class="blog-wander article-wander">
					<div class="blog-wander-img">
						<a href="' . $article_link . '"><img class="blog-img" src="' . $article_image . '"></a>
					</div>
					<h2><a href="' . $article_link . '">' . get_the_title( $post_id ) . '</a></h2>
					<div class="blog-desc">';
			$article         .= wp_trim_words( $article_text, 110, '...' );
			$article         .= '</div>';
			$article         .= '</div>';
		}
	} else {
		$article .= '<h2 class="noWanderung">' . __( 'Keine Artikel gefunden', 'wegwandern' ) . '</h2>';
	}
	$ar_posts[] = $article;
	wp_reset_postdata();
	echo json_encode( $ar_posts );
	die();
}
