<?php
/**
 * Custom post types & taxonomies for the plugin.
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if custom post type for B2B Ads, `b2b-werbung` already registered
 */
if ( ! post_type_exists( 'b2b-werbung' ) ) {
	add_action( 'init', 'wegwb_register_custom_post_type_b2b_ads', 0 );

	/* Hook into the init action and call wegwb_register_custom_taxonomy when it fires */
	add_action( 'init', 'wegwb_register_custom_taxonomy', 0 );
	/* Hook into the init action and call wegb_add_custom_post_status when it fires */
	add_action( 'init', 'wegb_add_custom_post_status' );
}

/**
 * Exclude b2b-werbung Post Type from Search
 */
add_action( 'init', 'exclude_b2b_werbung_from_search' );

function exclude_b2b_werbung_from_search() {
	global $wp_post_types;

	if ( post_type_exists( 'b2b-werbung' ) && isset( $wp_post_types['b2b-werbung'] ) ) {
		$wp_post_types['b2b-werbung']->exclude_from_search = true;
	}
}

/**
 * Register custom post type for B2B Ads - `b2b-werbung`
 */
function wegwb_register_custom_post_type_b2b_ads() {
	$label_b2b_ads = array(
		'name'               => _x( 'B2B Werbung', 'wegw-b2b' ),
		'singular_name'      => _x( 'B2B Werbung', 'wegw-b2b' ),
		'menu_name'          => __( 'B2B Werbung', 'wegw-b2b' ),
		'parent_item_colon'  => __( 'B2B Werbung', 'wegw-b2b' ),
		'all_items'          => __( 'Alle Werbung', 'wegw-b2b' ),
		'view_item'          => __( 'Siehe ', 'wegw-b2b' ),
		'add_new_item'       => __( 'Neu hinzufügen', 'wegw-b2b' ),
		'add_new'            => __( 'Neu hinzufügen', 'wegw-b2b' ),
		'edit_item'          => __( 'B2B Werbung bearbeiten', 'wegw-b2b' ),
		'update_item'        => __( 'Update B2B Werbung', 'wegw-b2b' ),
		'search_items'       => __( 'B2B Werbung suchen', 'wegw-b2b' ),
		'not_found'          => __( 'Nicht gefunden', 'wegw-b2b' ),
		'not_found_in_trash' => __( 'Nicht im Papierkorb gefunden', 'wegw-b2b' ),
	);

	/* Set other options for Custom Post Type */
	$arg_b2b_ads = array(
		'labels'             => $label_b2b_ads,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'angebote' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 30,
		'show_in_rest'       => true,
		'supports'           => array( 'title', 'editor', 'revisions', 'excerpt', 'thumbnail', 'author' ),
	);

	register_post_type( 'b2b-werbung', $arg_b2b_ads );
}

/**
 *  Create a custom taxonomies for post type B2B Ads - `b2b-werbung`
 */
function wegwb_register_custom_taxonomy() {
	$label_kategorie = array(
		'name'          => _x( 'Kategorie', 'wegw-b2b' ),
		'singular_name' => _x( 'Kategorie', 'wegw-b2b' ),
		'search_items'  => __( 'Suche Kategorie', 'wegw-b2b' ),
		'all_items'     => __( 'Alle Kategorie', 'wegw-b2b' ),
		'edit_item'     => __( 'Bearbeiten Kategorie', 'wegw-b2b' ),
		'update_item'   => __( 'Update Bearbeiten Kategorie', 'wegw-b2b' ),
		'add_new_item'  => __( 'Neu hinzufügen', 'wegw-b2b' ),
		'new_item_name' => __( 'Neue Wander Saison', 'wegw-b2b' ),
		'menu_name'     => __( 'Kategorie', 'wegw-b2b' ),
	);

	$arg_kategorie = array(
		'hierarchical'      => true,
		'labels'            => $label_kategorie,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'sort'              => true,
		'rewrite'           => array( 'slug' => 'kategorie' ),
	);

	/* Register the taxonomy */
	register_taxonomy( 'kategorie', array( 'b2b-werbung' ), $arg_kategorie );
}

/**
 * Function to add custom columns for post type B2B Ads lisitng - `b2b-werbung`
 */
add_filter( 'manage_b2b-werbung_posts_columns', 'wegwb_b2b_ads_listing_custom_columns_list' );
add_action( 'manage_b2b-werbung_posts_custom_column', 'wegwb_b2b_ads_listing_custom_column', 10, 2 );

function wegwb_b2b_ads_listing_custom_columns_list( $columns ) {

	unset( $columns['author'] );
	$columns['end_time']        = __( 'End Time', 'wegw-b2b' );
	$columns['expired_offers']  = __( 'Expired Offers', 'wegw-b2b' );
	$columns['booked_clicks']   = __( 'Booked Clicks', 'wegw-b2b' );
	$columns['billable_clicks'] = __( 'Billable Clicks', 'wegw-b2b' );
	$columns['author_email']    = __( 'Author Email', 'wegw-b2b' );
	$columns['status']          = __( 'Status', 'wegw-b2b' );
	return $columns;
}

function wegwb_b2b_ads_listing_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'end_time':
			if ( ! empty( get_post_meta( $post_id, 'wegw_b2b_ad_end_date', true ) ) ) {
				$b2b_ad_end_date = strtotime( get_post_meta( $post_id, 'wegw_b2b_ad_end_date', true ) );
				$ad_end_date     = date( 'Y-m-d', $b2b_ad_end_date );
			} else {
				$ad_end_date = '—';
			}
			echo $ad_end_date;
			break;
		case 'expired_offers':
			if ( ! empty( get_post_meta( $post_id, 'wegw_b2b_ad_credits_end', true ) ) ) {
				$ad_expired_offers = 'Expired';
			} else {
				$ad_expired_offers = '—';
			}
			echo $ad_expired_offers;
			break;
		case 'booked_clicks':
			if ( ! empty( get_post_meta( $post_id, 'wegw_b2b_credits_booked', true ) ) ) {
				$ad_credits_booked = get_post_meta( $post_id, 'wegw_b2b_credits_booked', true );
			} else {
				$ad_credits_booked = '0';
			}
			echo $ad_credits_booked;
			break;
		case 'billable_clicks':
			$ad_billable_clicks = wegwb_b2b_ads_clicks_count( $post_id );
			echo $ad_billable_clicks;
			break;
		case 'author_email':
			echo get_the_author_meta( 'user_email' );
			break;
		case 'status':
			echo ucfirst( get_post_status( $post_id ) );
			$previous_ad_status = get_post_meta( $post_id, 'ad_previous_status', true );
			if( $previous_ad_status && $previous_ad_status === 'rejected' ) {
				echo "<span class='article-reject-status'>(" . __('Abgelehnt', 'wegw-b2b') . ")</span>";
			}
			break;
	}
}
/**
 * Function to add sorting option for custom columns - B2B Ads lisitng
 */
add_filter( 'manage_edit-b2b-werbung_sortable_columns', 'wegwb_b2b_ads_listing_sortable_custom_columns' );

function wegwb_b2b_ads_listing_sortable_custom_columns( $columns ) {
	$columns['end_time']        = 'end_time';
	$columns['expired_offers']  = 'expired_offers';
	$columns['booked_clicks']   = 'booked_clicks';
	$columns['billable_clicks'] = 'billable_clicks';

	return $columns;
}

/**
 * Function to add `orderby` for custom columns - B2B Ads lisitng
 */
add_filter( 'request', 'wegwb_b2b_ads_listing_sortable_custom_columns_orderby' );

function wegwb_b2b_ads_listing_sortable_custom_columns_orderby( $vars ) {

	if ( isset( $vars['orderby'] ) && 'end_time' === $vars['orderby'] ) {
		$vars = array_merge(
			$vars,
			array(
				'meta_key' => 'wegw_b2b_ad_end_date',
				'orderby'  => 'wegw_b2b_ad_end_date',
			)
		);
	}

	if ( isset( $vars['orderby'] ) && 'expired_offers' === $vars['orderby'] ) {
		$vars = array_merge(
			$vars,
			array(
				'meta_key' => 'wegw_b2b_ad_credits_end',
				'orderby'  => 'wegw_b2b_ad_credits_end',
			)
		);
	}

	if ( isset( $vars['orderby'] ) && 'booked_clicks' === $vars['orderby'] ) {
		$vars = array_merge(
			$vars,
			array(
				'meta_key' => 'wegw_b2b_credits_booked',
				'orderby'  => 'meta_value_num',
			)
		);
	}

	if ( isset( $vars['orderby'] ) && 'billable_clicks' === $vars['orderby'] ) {
		$vars = array_merge(
			$vars,
			array(
				'meta_key' => 'wegw_b2b_ad_clicks',
				'orderby'  => 'meta_value_num',
			)
		);
	}

	return $vars;
}

function wegb_add_custom_post_status() {
	register_post_status(
		'rejected',
		array(
			'label'                     => _x( 'Rejected', 'b2b-werbung' ),
			'public'                    => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Rejected<span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>' ),
		)
	);
}

add_role(
	'b2b-user',
	'B2B-User',
	array(
		'read'              => true,
		'create_posts'      => true,
		'edit_posts'        => true,
		'edit_others_posts' => true,
		'publish_posts'     => true,
		'manage_categories' => true,
	)
);

/**
 * Function to send mail to Ad author if publish/schedule done from post edit page.
 */
add_action( 'transition_post_status', 'wegwb_b2b_ads_admin_transition_post_status', 10, 3 );

function wegwb_b2b_ads_admin_transition_post_status( $new_status, $old_status, $post ) {
	$ad_ID             = $post->ID;
	$ad_title          = esc_html( get_the_title( $ad_ID ) );
	$post_author_ID    = get_post_field( 'post_author', $ad_ID );
	$post_author_email = get_the_author_meta( 'user_email', $post_author_ID );

	/* Check if the post type is 'b2b-werbung' */
	if ( $post->post_type == 'b2b-werbung' ) {
		/* Check if the post status is changed to 'Rejected' */
		if ( $old_status == 'pending' && $new_status == 'rejected' ) {
			if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
				$args = array(
					'ID'          => $ad_ID,
					'post_status' => 'draft',
				);

				wp_update_post( $args );

				update_post_meta( $ad_ID, 'ad_previous_status', 'rejected' );

				/* Send mail to user - 'Rejection' */
				$headers  = array( 'Content-Type: text/html; charset=UTF-8' );
				$message  = 'Ihr Inserat "' . $ad_title . '" auf ' . "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . ' mussten wir leider ablehnen und in den Entwurfsmodus zurücksetzen. Bitte überprüfen Sie, ob Ihre Anzeige unseren Allgemeinen Geschäftsbedingungen (AGB) sowie unseren Nutzungs- und Datenschutzbestimmungen entspricht.' . '<br /><br />';
				$message .= 'Bei Fragen stehen wir Ihnen gerne zur Verfügung.' . '<br /><br />';
				$message .= 'Beste Grüsse' . '<br />' . 'Yvonne Zürrer und Claudia Ruf' . '<br />' . 'Ihr ' . "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . ' Team' . '<br /><br />';
				$message .= "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . '<br />' . 'Marchwartstrasse 72' . '<br />' . '8038 Zürich' . '<br /><a href="mailto:info@wegwandern.ch">' . 'info@wegwandern.ch' . '</a><br />' . '+41 43 537 70 58' . '<br />';

				wp_mail(
					$post_author_email,
					'Ihr Inserat auf WegWandern.ch wurde abgelehnt',
					__( $message, 'wegw-b2b' ),
					$headers
				);
			} else {
				echo wp_send_json_error( 'Access Denied.' );
			}
		}

		/* Check if the post status is changed to 'Publish' || 'Future' */
		if ( $old_status == 'pending' && ( $new_status == 'publish' || $new_status == 'future' ) ) {
			if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {

				/* Send mail to user - 'Publish' */
				$headers  = array( 'Content-Type: text/html; charset=UTF-8' );
				$message  = 'Ihr Inserat "' . $ad_title . '" auf ' . "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . ' wurde genehmigt und ist jetzt online.' . '<br /><br />';
				$message .= 'Beste Grüsse' . '<br />' . 'Yvonne Zürrer und Claudia Ruf' . '<br />' . 'Ihr ' . "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . ' Team' . '<br /><br />';
				$message .= "<a href='https://wegwandern.ch/'>" . 'WegWandern.ch' . '</a>' . '<br />' . 'Marchwartstrasse 72' . '<br />' . '8038 Zürich' . '<br /><a href="mailto:info@wegwandern.ch">' . 'info@wegwandern.ch' . '</a><br />' . '+41 43 537 70 58' . '<br />';

				wp_mail(
					$post_author_email,
					'Ihr Inserat auf WegWandern.ch wurde genehmigt',
					__( $message, 'wegw-b2b' ),
					$headers
				);
			} else {
				echo wp_send_json_error( 'Access Denied.' );
			}
		}
	}
}

/**
 * Function to not update Ad `Author ID` when publishing
 */
add_filter( 'wp_insert_post_data', 'wegwb_b2b_ads_change_author', '99', 2 );

function wegwb_b2b_ads_change_author( $data, $post ) {
	if ( $data['post_type'] != 'b2b-werbung' ) {
		return $data;
	}

	$ad_ID = $post['ID'];
	if ( $ad_ID != 0 ) {
		$post_author_ID      = get_post_field( 'post_author', $ad_ID );
		$data['post_author'] = $post_author_ID;
	}

	return $data;
}
