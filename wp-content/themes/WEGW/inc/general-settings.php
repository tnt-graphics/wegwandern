<?php

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Wegwandern
 */

add_post_type_support('wanderung', 'thumbnail');
add_post_type_support('page', 'excerpt');

if (function_exists('add_image_size')) {
	add_image_size('hike-thumbnail', 300, 200, true);
	add_image_size('hike-region', 350, 250, true);
	add_image_size('teaser-twocol', 550, 380, true);
	add_image_size('teaser-twocol-lg-dimension', 600, 400, true);
	add_image_size('region-slider', 350, 350, true);
	add_image_size('teaser-onecol', 1200, 580, true);
	add_image_size('hike-listing', 450, 300, true);
}

if (! post_type_exists('wanderung')) {
	add_action('init', 'wegwandern_custom_post_type', 0);
}

/**
 * Function to register custom post type `Wanderung`
 */
function wegwandern_custom_post_type()
{
	$label_wanderung = array(
		'name'               => _x('Wanderung', 'Post Type General Name', 'wegwandern'),
		'singular_name'      => _x('Wanderung', 'Post Type Singular Name', 'wegwandern'),
		'menu_name'          => __('Wanderung', 'wegwandern'),
		'parent_item_colon'  => __('Wanderung', 'wegwandern'),
		'all_items'          => __('Alle Wanderungen', 'wegwandern'),
		'view_item'          => __('Siehe ', 'wegwandern'),
		'add_new_item'       => __('Neu hinzufügen', 'wegwandern'),
		'add_new'            => __('Neu hinzufügen', 'wegwandern'),
		'edit_item'          => __('Wanderung bearbeiten', 'wegwandern'),
		'update_item'        => __('Update Wanderung', 'wegwandern'),
		'search_items'       => __('Wanderung suchen', 'wegwandern'),
		'not_found'          => __('Nicht gefunden', 'wegwandern'),
		'not_found_in_trash' => __('Nicht im Papierkorb gefunden', 'wegwandern'),
	);

	$arg_wanderung = array(
		'labels'             => $label_wanderung,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array('slug' => 'wanderung'),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array('title', 'editor', 'revisions', 'excerpt', 'thumbnail'),
		'show_in_rest'       => true,

	);

	register_post_type('wanderung', $arg_wanderung);
}

/* Hook into the init action and call wegwandern_custom_taxonomy when it fires */
add_action('init', 'wegwandern_custom_taxonomy', 0);

/**
 *  Create a custom taxonomy for post type: Hiking Locations
 */
function wegwandern_custom_taxonomy()
{
	$wander_saison_labels = array(
		'name'          => _x('Wander Saison', 'Taxonomy General Name', 'wegwandern'),
		'singular_name' => _x('Wander Saison', 'Taxonomy Singular Name', 'wegwandern'),
		'search_items'  => __('Suche Wander Saison', 'wegwandern'),
		'all_items'     => __('Alle Wander Saison', 'wegwandern'),
		'edit_item'     => __('Bearbeiten Wander Saison', 'wegwandern'),
		'update_item'   => __('Update Bearbeiten Wander Saison', 'wegwandern'),
		'add_new_item'  => __('Neu hinzufügen', 'wegwandern'),
		'new_item_name' => __('Neue Wander Saison', 'wegwandern'),
		'menu_name'     => __('Wander Saison', 'wegwandern'),
	);

	/* Now register the taxonomy */
	register_taxonomy(
		'wander-saison',
		array('wanderung'),
		array(
			'hierarchical'      => true,
			'labels'            => $wander_saison_labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'sort'              => true,
			'rewrite'           => array('slug' => 'wander-saison'),
		)
	);

	$hiking_aktivitat_labels = array(
		'name'          => _x('Aktivität', 'Taxonomy General Name', 'wegwandern'),
		'singular_name' => _x('Aktivität', 'Taxonomy Singular Name', 'wegwandern'),
		'search_items'  => __('Aktivität suchen', 'wegwandern'),
		'all_items'     => __('Alle Aktivitäten', 'wegwandern'),
		'edit_item'     => __('Aktivität bearbeiten', 'wegwandern'),
		'update_item'   => __('Aktivität aktualisieren', 'wegwandern'),
		'add_new_item'  => __('Neu hinzufügen', 'wegwandern'),
		'new_item_name' => __('Neue Aktivität', 'wegwandern'),
		'menu_name'     => __('Aktivität', 'wegwandern'),
	);

	/* Now register the taxonomy */
	register_taxonomy(
		'aktivitat',
		array('wanderung'),
		array(
			'hierarchical'      => true,
			'labels'            => $hiking_aktivitat_labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'aktivitat'),
		)
	);

	$wanderregionen_labels = array(
		'name'          => _x('Wanderregionen', 'Taxonomy General Name', 'wegwandern'),
		'singular_name' => _x('Wanderregionen', 'Taxonomy Singular Name', 'wegwandern'),
		'search_items'  => __('Wanderregionen suchen', 'wegwandern'),
		'all_items'     => __('Alle Wanderregionen', 'wegwandern'),
		'edit_item'     => __('Wanderregionen bearbeiten', 'wegwandern'),
		'update_item'   => __('Wanderregionen aktualisieren', 'wegwandern'),
		'add_new_item'  => __('Neu hinzufügen', 'wegwandern'),
		'new_item_name' => __('Neue Wanderregionen', 'wegwandern'),
		'menu_name'     => __('Wanderregionen', 'wegwandern'),
	);

	/* Now register the taxonomy */
	register_taxonomy(
		'wanderregionen',
		array('wanderung', 'b2b-werbung'),
		array(
			'hierarchical'      => true,
			'labels'            => $wanderregionen_labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'wanderregionen'),
		)
	);

	$anforderung_labels = array(
		'name'          => _x('Anforderung', 'Taxonomy General Name', 'wegwandern'),
		'singular_name' => _x('Anforderung', 'Taxonomy Singular Name', 'wegwandern'),
		'search_items'  => __('Anforderung suchen', 'wegwandern'),
		'all_items'     => __('Alle Anforderung', 'wegwandern'),
		'edit_item'     => __('Anforderung bearbeiten', 'wegwandern'),
		'update_item'   => __('Anforderung aktualisieren', 'wegwandern'),
		'add_new_item'  => __('Neu hinzufügen', 'wegwandern'),
		'new_item_name' => __('Neue Anforderung', 'wegwandern'),
		'menu_name'     => __('Anforderung', 'wegwandern'),
	);

	/* Now register the taxonomy */
	register_taxonomy(
		'anforderung',
		array('wanderung'),
		array(
			'hierarchical'      => true,
			'labels'            => $anforderung_labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'anforderung'),
		)
	);

	$ausdauer_labels = array(
		'name'          => _x('Ausdauer', 'Taxonomy General Name', 'wegwandern'),
		'singular_name' => _x('Ausdauer', 'Taxonomy Singular Name', 'wegwandern'),
		'search_items'  => __('Ausdauer suchen', 'wegwandern'),
		'all_items'     => __('Alle Ausdauer', 'wegwandern'),
		'edit_item'     => __('Ausdauer bearbeiten', 'wegwandern'),
		'update_item'   => __('Ausdauer aktualisieren', 'wegwandern'),
		'add_new_item'  => __('Neu hinzufügen', 'wegwandern'),
		'new_item_name' => __('Neue Ausdauer', 'wegwandern'),
		'menu_name'     => __('Ausdauer', 'wegwandern'),
	);

	/* Now register the taxonomy */
	register_taxonomy(
		'ausdauer',
		array('wanderung'),
		array(
			'hierarchical'      => true,
			'labels'            => $ausdauer_labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'ausdauer'),
		)
	);

	$angebot_labels = array(
		'name'          => _x('Angebot', 'Taxonomy General Name', 'wegwandern'),
		'singular_name' => _x('Angebot', 'Taxonomy Singular Name', 'wegwandern'),
		'search_items'  => __('Angebot suchen', 'wegwandern'),
		'all_items'     => __('Alle Angebot', 'wegwandern'),
		'edit_item'     => __('Angebot bearbeiten', 'wegwandern'),
		'update_item'   => __('Angebot aktualisieren', 'wegwandern'),
		'add_new_item'  => __('Neu hinzufügen', 'wegwandern'),
		'new_item_name' => __('Neue Angebot', 'wegwandern'),
		'menu_name'     => __('Angebot', 'wegwandern'),
	);

	/* Now register the taxonomy */
	register_taxonomy(
		'angebot',
		array('wanderung'),
		array(
			'hierarchical'      => true,
			'labels'            => $angebot_labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'angebot'),
		)
	);

	$routenverlauf_labels = array(
		'name'          => _x('Routenverlauf', 'Taxonomy General Name', 'wegwandern'),
		'singular_name' => _x('Routenverlauf', 'Taxonomy Singular Name', 'wegwandern'),
		'search_items'  => __('Routenverlauf suchen', 'wegwandern'),
		'all_items'     => __('Alle Routenverlauf', 'wegwandern'),
		'edit_item'     => __('Routenverlauf bearbeiten', 'wegwandern'),
		'update_item'   => __('Routenverlauf aktualisieren', 'wegwandern'),
		'add_new_item'  => __('Neu hinzufügen', 'wegwandern'),
		'new_item_name' => __('Neue Routenverlauf', 'wegwandern'),
		'menu_name'     => __('Routenverlauf', 'wegwandern'),
	);

	/* Now register the taxonomy */
	register_taxonomy(
		'routenverlauf',
		array('wanderung'),
		array(
			'hierarchical'      => true,
			'labels'            => $routenverlauf_labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'routenverlauf'),
		)
	);

	$thema_labels = array(
		'name'          => _x('Thema', 'Taxonomy General Name', 'wegwandern'),
		'singular_name' => _x('Thema', 'Taxonomy Singular Name', 'wegwandern'),
		'search_items'  => __('Thema suchen', 'wegwandern'),
		'all_items'     => __('Alle Thema', 'wegwandern'),
		'edit_item'     => __('Thema bearbeiten', 'wegwandern'),
		'update_item'   => __('Thema aktualisieren', 'wegwandern'),
		'add_new_item'  => __('Neu hinzufügen', 'wegwandern'),
		'new_item_name' => __('Neue Thema', 'wegwandern'),
		'menu_name'     => __('Thema', 'wegwandern'),
	);

	/* Now register the taxonomy */
	register_taxonomy(
		'thema',
		array('wanderung'),
		array(
			'hierarchical'      => true,
			'labels'            => $thema_labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => 'thema'),
		)
	);
}

/**
 * Function to get meta values
 */
function get_meta_values($key = '', $type = 'wanderung', $status = 'publish')
{

	global $wpdb;

	if (empty($key)) {
		return;
	}

	$arr_values = $wpdb->get_col(
		$wpdb->prepare(
			"
        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s 
        AND p.post_status = %s 
        AND p.post_type = %s ORDER BY pm.meta_value ASC
    ",
			$key,
			$status,
			$type
		)
	);

	$sort_array = sort($arr_values);
	if ($sort_array) {
		return $arr_values;
	}
}

/**
 * Retrieves km of wanderungs.
 **/
function get_km_option()
{

	$kms         = get_meta_values('km');
	$km_array    = array();
	$km_option   = '';
	$selected_km = '';
	if (isset($_GET['km'])) {
		$selected_km = $_GET['km'];
	}
	$html = '';
	if (! empty($kms)) {
		foreach ($kms as $km) {
			if (! in_array($km, $km_array) && $km != '') {
				array_push($km_array, $km);
			}
		}

		$first = floor($km_array[array_key_first($km_array)]);
		$last  = ceil($km_array[array_key_last($km_array)]);
		$html  = '<div class="slider km-slider multi-slide" data-notation="km" data-initial="' . $first . '" data-final="' . $last . '" data-first="' . $first . '" data-second="' . $last . '" >
			<input type="text" class="firstVal firstkm" id="distance_start_point" readonly>
		    <input type="text" class="secondVal lastkm" id="distance_end_point" readonly>
			<div class="slider-range"></div>
		</div>';
	}

	return $html;
}

/**
 * Retrieves duration of wanderungs.
 **/
function get_dauer_option()
{
	$dauer          = get_meta_values('dauer');
	$dauer_array    = array();
	$dauer_option   = '';
	$selected_dauer = '';
	if (isset($_GET['dauer'])) {
		$selected_dauer = $_GET['dauer'];
	}

	$html = '';
	if (! empty($dauer)) {
		foreach ($dauer as $dauer) {
			if (! in_array($dauer, $dauer_array) && $dauer != '') {
				array_push($dauer_array, $dauer);
			}
		}

		$first = intval($dauer_array[array_key_first($dauer_array)]);
		$last  = intval($dauer_array[array_key_last($dauer_array)]) + 1;

		$html = '<div class="slider dauer-slider multi-slide" data-notation="h" data-initial="' . $first . '" data-final="' . $last . '" data-first="' . $first . '" data-second="' . $last . '" >
			<input type="text" class="firstVal" id="duration_start_point" readonly >
			<input type="text" class="secondVal" id="duration_end_point" readonly >
	  		<div class="slider-range"></div>
		</div>';
	}

	return $html;
}

/**
 * Retrieves ascent of wanderungs.
 **/
function get_aufstieg_option()
{
	$aufstieg          = get_meta_values('aufstieg');
	$aufstieg_array    = array();
	$aufstieg_option   = '';
	$selected_aufstieg = '';
	if (isset($_GET['aufstieg'])) {
		$selected_aufstieg = $_GET['aufstieg'];
	}
	$html = '';
	if (! empty($aufstieg)) {
		foreach ($aufstieg as $aufstieg) {
			if (! in_array($aufstieg, $aufstieg_array) && $aufstieg != '') {
				array_push($aufstieg_array, $aufstieg);
			}
		}
		$first = floor($aufstieg_array[array_key_first($aufstieg_array)]);
		$last  = ceil($aufstieg_array[array_key_last($aufstieg_array)]);

		$html = '<div class="slider aufstieg-slider multi-slide" data-notation="m" data-initial="' . $first . '" data-final="' . $last . '" data-first="' . $first . '" data-second="' . $last . '" >
			<input type="text" class="firstVal" id="ascent_start_point" readonly >
			<input type="text" class="secondVal" id="ascent_end_point" readonly >
			<div class="slider-range"></div>
		</div>';
	}

	return $html;
}

/**
 * Retrieves descent of wanderungs.
 **/
function get_abstieg_option()
{
	$abstieg          = get_meta_values('abstieg');
	$abstieg_array    = array();
	$abstieg_option   = '';
	$selected_abstieg = '';
	if (isset($_GET['abstieg'])) {
		$selected_abstieg = $_GET['abstieg'];
	}
	$html = '';
	if (! empty($abstieg)) {
		foreach ($abstieg as $abstieg) {
			if (! in_array($abstieg, $abstieg_array) && $abstieg != '') {
				array_push($abstieg_array, $abstieg);
			}
		}
		$first = floor($abstieg_array[array_key_first($abstieg_array)]);
		$last  = ceil($abstieg_array[array_key_last($abstieg_array)]);

		$html = '<div class="slider abstieg-slider multi-slide" data-notation="m" data-initial="' . $first . '" data-final="' . $last . '" data-first="' . $first . '" data-second="' . $last . '" >
			<input type="text" class="firstVal" id="descent_start_point" readonly >
			<input type="text" class="secondVal" id="descent_end_point" readonly >
			<div class="slider-range"></div>
  		</div>';
	}

	return $html;
}

/**
 * Retrieves altitude of wanderungs.
 **/
function get_altitude_option()
{
	$lowest_altitude        = get_meta_values('tiefster_punkt');
	$highest_altitude       = get_meta_values('hochster_punkt');
	$lowest_altitude_array  = array();
	$highest_altitude_array = array();

	$html = '';
	if (! empty($lowest_altitude) && ! empty($highest_altitude)) {
		foreach ($lowest_altitude as $low) {
			if (! in_array($low, $lowest_altitude_array) && $low != '') {
				array_push($lowest_altitude_array, $low);
			}
		}

		foreach ($highest_altitude as $high) {
			if (! in_array($high, $highest_altitude_array) && $high != '') {
				array_push($highest_altitude_array, $high);
			}
		}
		$first = floor($lowest_altitude_array[array_key_first($lowest_altitude_array)]);
		$last  = ceil($highest_altitude_array[array_key_last($highest_altitude_array)]);

		$html = '<div class="slider aaltitude-slider multi-slide" data-notation="m" data-initial="' . $first . '" data-final="' . $last . '" data-first="' . $first . '" data-second="' . $last . '" >
			<input type="text" class="firstVal" id="altitude_start_point" readonly >
			<input type="text" class="secondVal" id="altitude_end_point" readonly >
			<div class="slider-range"></div>
  		</div>';
	}

	return $html;
}

/**
 * Function to save filter fields.
 */
add_filter(
	'widget_update_callback',
	function ($instance, $new, $old, $obj) {
		if (str_contains($new['content'], 'field_6412b88271d84')) {
			$arr = (explode(' ', $new['content']));
			foreach ($arr as $val) {
				if (str_contains($val, 'field_6412b88271d84')) {

					$unit         = wegw_get_between_data($val, '[', ']');
					$filterfields = (explode(',', $unit));
					update_option(
						'filterfields',
						$filterfields
					);
				}
			}
			return $instance;
		}
	},
	10,
	4
);

/**
 * Custom function to data in between
 */
function wegw_get_between_data($string, $start, $end)
{
	$pos_string   = stripos($string, $start);
	$substr_data  = substr($string, $pos_string);
	$string_two   = substr($substr_data, strlen($start));
	$second_pos   = stripos($string_two, $end);
	$string_three = substr($string_two, 0, $second_pos);
	$result_unit  = trim($string_three);
	return $result_unit;
}

/**
 * Function to add custom meta box for Saison
 */
add_action('add_meta_boxes', 'wegwandern_add_custom_meta_box_for_saison');

function wegwandern_add_custom_meta_box_for_saison()
{
	add_meta_box('wandersaison-meta-box', 'Wander Saison', 'wegwandern_saison_taxonomy_meta_box_markup', 'wanderung', 'side', 'high', null);
	add_meta_box('saison-meta-box', 'Beginn und Ende des Monats', 'wegwandern_saison_custom_meta_box_markup', 'wanderung', 'side', 'default', null);
}

function wegwandern_saison_custom_meta_box_markup()
{
	global $post;
	$start_month = get_post_meta($post->ID, 'saison_start_month', true);
	$end_month   = get_post_meta($post->ID, 'saison_end_month', true);
?>
	<label><?php echo esc_html__('Start Monat', 'wegwandern'); ?></label>
	<select name="start_month" id="start_month" class="postbox">
		<option value=""><?php echo esc_html__('Monat auswählen', 'wegwandern'); ?></option>
		<option value="2" <?php selected($start_month, '2'); ?>><?php echo esc_html__('Jan', 'wegwandern'); ?></option>
		<option value="3" <?php selected($start_month, '3'); ?>><?php echo esc_html__('Feb', 'wegwandern'); ?></option>
		<option value="4" <?php selected($start_month, '4'); ?>><?php echo esc_html__('Mar', 'wegwandern'); ?></option>
		<option value="5" <?php selected($start_month, '5'); ?>><?php echo esc_html__('Apr', 'wegwandern'); ?></option>
		<option value="6" <?php selected($start_month, '6'); ?>><?php echo esc_html__('Mai', 'wegwandern'); ?></option>
		<option value="7" <?php selected($start_month, '7'); ?>><?php echo esc_html__('Jun', 'wegwandern'); ?></option>
		<option value="8" <?php selected($start_month, '8'); ?>><?php echo esc_html__('Jul', 'wegwandern'); ?></option>
		<option value="9" <?php selected($start_month, '9'); ?>><?php echo esc_html__('Aug', 'wegwandern'); ?></option>
		<option value="10" <?php selected($start_month, '10'); ?>><?php echo esc_html__('Sep', 'wegwandern'); ?></option>
		<option value="11" <?php selected($start_month, '11'); ?>><?php echo esc_html__('Okt', 'wegwandern'); ?></option>
		<option value="12" <?php selected($start_month, '12'); ?>><?php echo esc_html__('Nov', 'wegwandern'); ?></option>
		<option value="13" <?php selected($start_month, '13'); ?>><?php echo esc_html__('Dez', 'wegwandern'); ?></option>
	</select>

	<label><?php echo esc_html__('Ende Monat', 'wegwandern'); ?></label>
	<select name="end_month" id="end_month" class="postbox">
		<option value=""><?php echo esc_html__('Monat auswählen', 'wegwandern'); ?></option>
		<option value="2" <?php selected($end_month, '2'); ?>><?php echo esc_html__('Jan', 'wegwandern'); ?></option>
		<option value="3" <?php selected($end_month, '3'); ?>><?php echo esc_html__('Feb', 'wegwandern'); ?></option>
		<option value="4" <?php selected($end_month, '4'); ?>><?php echo esc_html__('Mar', 'wegwandern'); ?></option>
		<option value="5" <?php selected($end_month, '5'); ?>><?php echo esc_html__('Apr', 'wegwandern'); ?></option>
		<option value="6" <?php selected($end_month, '6'); ?>><?php echo esc_html__('Mai', 'wegwandern'); ?></option>
		<option value="7" <?php selected($end_month, '7'); ?>><?php echo esc_html__('Jun', 'wegwandern'); ?></option>
		<option value="8" <?php selected($end_month, '8'); ?>><?php echo esc_html__('Jul', 'wegwandern'); ?></option>
		<option value="9" <?php selected($end_month, '9'); ?>><?php echo esc_html__('Aug', 'wegwandern'); ?></option>
		<option value="10" <?php selected($end_month, '10'); ?>><?php echo esc_html__('Sep', 'wegwandern'); ?></option>
		<option value="11" <?php selected($end_month, '11'); ?>><?php echo esc_html__('Okt', 'wegwandern'); ?></option>
		<option value="12" <?php selected($end_month, '12'); ?>><?php echo esc_html__('Nov', 'wegwandern'); ?></option>
		<option value="13" <?php selected($end_month, '13'); ?>><?php echo esc_html__('Dez', 'wegwandern'); ?></option>
	</select>
	<?php
}

/**
 * Function to save custom meta box for Saison
 */
function wegwandern_saison_save_postdata($post_id)
{
	if (array_key_exists('start_month', $_POST)) {
		update_post_meta(
			$post_id,
			'saison_start_month',
			$_POST['start_month']
		);
	}

	if (array_key_exists('end_month', $_POST)) {
		update_post_meta(
			$post_id,
			'saison_end_month',
			$_POST['end_month']
		);
	}

	if (array_key_exists('start_month', $_POST) && array_key_exists('end_month', $_POST)) {
		$start_month = $_POST['start_month'];
		$end_month   = $_POST['end_month'];
		$all_saison  = array();
		$month_array = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13);
		$start_index = array_search($start_month, $month_array);
		$end_index   = array_search($end_month, $month_array);
		if ($end_index < $start_index) {
			$end_index = array_search($end_month, array_reverse($month_array, true));
		}
		$diff        = ($end_index - $start_index) + 1;
		$terms_array = array_slice($month_array, $start_index, $diff);
		$res         = wp_set_post_terms($post_id, $terms_array, 'wander-saison');
	}
}

add_action('save_post', 'wegwandern_saison_save_postdata');

/**
 * Function to enqueue custom admin scripts
 */
// add_action( 'admin_enqueue_scripts', 'wpdocs_my_admin_scripts' );

function wpdocs_my_admin_scripts($hook)
{
	wp_enqueue_script('wpdocs-my-editor-script', get_template_directory_uri() . '/js/my-editor-script.js', array(), _S_VERSION, true);
	wp_enqueue_style('admin-styles', get_template_directory_uri() . '/css/admin.css');
	$data = array(
		'hook' => $hook,
	);

	wp_localize_script('wpdocs-my-editor-script', 'my_editor_script', $data);
}

/**
 * Function to add meta box for taxonomy Saison
 */
function wegwandern_saison_taxonomy_meta_box_markup()
{
	global $post;
	$seas                = array();
	$assignwander_saison = wp_get_post_terms($post->ID, 'wander-saison');
	foreach ($assignwander_saison as $season) {
		array_push($seas, $season->name);
	}

	$wander_saison_args = array(
		'taxonomy'         => array('wander-saison'),
		'hide_empty'       => false,
		'orderby'          => '',
		'parent'           => 0,
		'suppress_filters' => false,
	);

	$wander_saison = get_terms($wander_saison_args);
	foreach ($wander_saison as $saison) {
		$check = '';
		if (in_array($saison->name, $seas)) {
			$check = 'checked';
		}
		echo '<input type="checkbox" name="season" ' . $check . ' value="">' . $saison->name . '<br/>';
	}
}

/**
 * Function check failed logins
 */
add_action('wp_login_failed', 'wegw_end_login_fail');

function wegw_end_login_fail($username)
{
	/* Check from where did the post submission come from? */
	$referrer = $_SERVER['HTTP_REFERER'];
	/* If there's a valid referrer, and it's not the default log-in screen */
	if (! empty($referrer) && ! strstr($referrer, 'wp-login') && ! strstr($referrer, 'wp-admin')) {
		/* Let's append some information (login=failed) to the URL for the theme to use */
		wp_redirect($referrer . '?login=failed');
		exit;
	}
}

/**
 * Function to get Wndern Saison name
 *
 *  @param int $wanderung_id wanderung id.
 */
function wegw_wandern_saison_name($wanderung_id)
{
	$wander_saison_name = '';
	$start_month        = get_post_meta($wanderung_id, 'saison_start_month', true);
	$end_month          = get_post_meta($wanderung_id, 'saison_end_month', true);

	if ($start_month && $end_month) {
		$start_month_name   = get_term($start_month)->name;
		$end_month_name     = get_term($end_month)->name;
		$saison_range       = ($start_month_name == $end_month_name) ? $start_month_name : $start_month_name . '-' . $end_month_name;
		$wander_saison_name = $saison_range;
	}

	return $wander_saison_name;
}

/**
 * Function to get Wndern hike level class name
 *
 *  @param string $hike_level_name hike_level_name.
 */
function wegw_wandern_hike_level_class_name($hike_level_name, $wand_id = '')
{
	$hike_level_cls = '';
	if ('T1' === $hike_level_name) {
		$hike_level_cls = 'hike-yellow';
	} elseif ('T2' === $hike_level_name || 'T3' === $hike_level_name) {
		$hike_level_cls = 'hike-red';
	} elseif ('T4' === $hike_level_name || 'T5' === $hike_level_name || 'T6' === $hike_level_name) {
		$hike_level_cls = 'hike-blue';
	} elseif ('WT1' === $hike_level_name || 'WT2' === $hike_level_name || 'WT3' === $hike_level_name || 'WT4' === $hike_level_name || 'WT5' === $hike_level_name || 'WT6' === $hike_level_name) {
		$hike_level_cls = 'hike-pink';
	} else {
		$aktivitat = get_the_terms($wand_id, 'aktivitat');
		if (! empty($aktivitat)) {
			foreach ($aktivitat as $aktivitat) {
				$aktivitat_name = $aktivitat->name;
			}
			$hike_level_cls = ($aktivitat_name == 'Winterwandern') ? 'hike-pink' : '';
		}
	}
	return $hike_level_cls;
}

add_action('admin_init', 'wegw_block_wp_admin');

function wegw_block_wp_admin()
{
	if (is_admin() && ! current_user_can('administrator') && ! (defined('DOING_AJAX') && DOING_AJAX)) {
		//wp_safe_redirect( site_url() . '/angebote/', 301 ); angebote changed into b2b-portal-dashboard
		wp_safe_redirect(site_url() . '/b2b-portal-dashboard/', 301);
	}
}

/**
 * Function to get ad section in templates
 *
 *  @param string $position
 */
function wegwandern_ad_section_display($position = 'center-between-contents', $desktop = false, $tab = false, $mob = false)
{
	$html_ad = '';
	if (have_rows('manage_ad_scripts', 'option')) :
		while (have_rows('manage_ad_scripts', 'option')) :
			the_row();

			/* Header ads scripts */
			$ad_script_desktop_header = get_sub_field('ad_script_for_desktop_header', 'option');
			$ad_script_tablet_header  = get_sub_field('ad_script_for_tab_header', 'option');
			$ad_script_mobile_header  = get_sub_field('ad_script_for_mobile_header', 'option');

			$desktop_ad_scripts        = get_sub_field('desktop_ad_scripts', 'option');
			$ad_script_desktop_300x600 = '';
			$ad_script_desktop_994x500 = '';

			foreach ($desktop_ad_scripts as $desktop_ad) {
				if ($desktop_ad['ad_size'] == '300x600') {
					$ad_script_desktop_300x600 = $desktop_ad['ad_script'];
				}
				if ($desktop_ad['ad_size'] == '994x500') {
					$ad_script_desktop_994x500 = $desktop_ad['ad_script'];
				}
			}

			$tablet_ad_scripts        = get_sub_field('tablet_ad_scripts', 'option');
			$ad_script_tablet_300x250 = '';
			$ad_script_tablet_300x600 = '';

			foreach ($tablet_ad_scripts as $tablet_ad) {
				if ($tablet_ad['ad_size'] == '300x250') {
					$ad_script_tablet_300x250 = $tablet_ad['ad_script'];
				}
				if ($tablet_ad['ad_size'] == '300x600') {
					$ad_script_tablet_300x600 = $tablet_ad['ad_script'];
				}
			}

			$mobile_ad_scripts = get_sub_field('mobile_ad_scripts', 'option');
			$ad_script_mobile  = '';
			foreach ($mobile_ad_scripts as $mob_ad) {
				if ($mob_ad['ad_size'] == '300x250') {
					$ad_script_mobile = $mob_ad['ad_script'];
				}
			}

		endwhile;
	endif;

	/* Ad position */
	if ($position == 'header') {
		/* If ad position is `Header` section */
		if (isset($ad_script_desktop_header) && $ad_script_desktop_header != '') {
	?>
			<div class='ad-above-header-container header-ad-desktop-wrapper'>
				<div class="ad-section"><?php echo $ad_script_desktop_header; ?></div>

				<!-- <div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div> -->
				<div class="ad-scroll-btn">
					<div class="ad-scroll-btn-icon"></div>
					<div class="ad-scroll-btn-text" onclick="adScrollToHeader()">Scrolle zu WegWandern.ch</div>
				</div>
			</div>
		<?php
		}

		if (isset($ad_script_tablet_header) && $ad_script_tablet_header != '') {
		?>
			<div class='ad-above-header-container header-ad-tablet-wrapper'>
				<div class="ad-section"><?php echo $ad_script_tablet_header; ?></div>

				<!-- <div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div> -->
				<div class="ad-scroll-btn">
					<div class="ad-scroll-btn-icon"></div>
					<div class="ad-scroll-btn-text" onclick="adScrollToHeader()">Scrolle zu WegWandern.ch</div>
				</div>
			</div>
<?php
		}

		if (isset($ad_script_mobile_header) && $ad_script_mobile_header != '') {
			echo '<div class="ad-above-header-container header-ad-mobile-wrapper"><div class="ad-section">' . $ad_script_mobile_header . '</div></div>';
		}
	} elseif ($position == 'center-between-contents') {
		/* If ad position is `Center` of contents */
		if ($ad_script_desktop_994x500 != '' && $desktop) {
			$html_ad .= '<div class="ad-section-wrap ad-block-content-desktop-wrapper full-width"><p>Anzeige</p><div class="ad-section">' . $ad_script_desktop_994x500 . '</div></div>';
		}

		if ($ad_script_tablet_300x600 != '' && $tab) {
			$html_ad .= '<div class="ad-section-wrap ad-block-content-tablet-wrapper full-width"><p>Anzeige</p><div class="ad-section">' . $ad_script_tablet_300x600 . '</div></div>';
		}

		if ($ad_script_mobile != '' && $mob) {
			$html_ad .= '<div class="ad-section-wrap ad-block-content-mobile-wrapper full-width"><p>Anzeige</p><div class="ad-section">' . $ad_script_mobile . '</div></div>';
		}
		echo $html_ad;
	} elseif ($position == 'right') {
		/* If ad position is `Right side` of contents */
		if ($ad_script_desktop_300x600 != '' && $desktop) {
			$html_ad .= '<div class="ad-section-wrap ad-block-content-desktop-wrapper"><p>Anzeige</p><div class="ad-section">' . $ad_script_desktop_300x600 . '</div></div>';
		}

		if ($ad_script_tablet_300x250 != '' && $tab) {
			$html_ad .= '<div class="ad-section-wrap ad-block-content-tablet-wrapper"><p>Anzeige</p><div class="ad-section">' . $ad_script_tablet_300x250 . '</div></div>';
		}

		if ($ad_script_mobile != '' && $mob) {
			$html_ad .= '<div class="ad-section-wrap ad-block-content-mobile-wrapper"><p>Anzeige</p><div class="ad-section">' . $ad_script_mobile . '</div></div>';
		}
		echo $html_ad;
	}
}

/**
 * Function to remove child regions from Region page filter selection backend block
 *
add_filter('acf/fields/taxonomy/query', 'wegwandern_block_hide_child_taxonomies_wanderregionen', 10, 2);

function wegwandern_block_hide_child_taxonomies_wanderregionen( $args, $field ) {
	if( 'map_regionen' === $field['_name'] ) {
		$args['parent'] = 0;
	}

	return $args;
}
 */
/*
 * Display category in blog slider
 * 
 **/
function category_html($post_id)
{
	$category_object = get_the_category($post_id);
	$cat_count       = count($category_object);
	// $cat_name_arr    = array();
	$category_name = '';
	$category_link = '';
	$cat_html      = '';

	if ($cat_count == 1) {
		$category_name = $category_object[0]->name;
		$category_id   = $category_object[0]->term_id;

		/* Add Red color to special category 'Paid Post' */
		if ($category_name === 'Paid Post') {
			$category_paid_post_spl_class = 'paid_post_spl_cat';
			$category_link                = '';
		} else {
			$category_paid_post_spl_class = '';
			// $category_link                = get_category_link( $category_id );
			$category_link                = ' href="' . get_category_link($category_id) . '" ';
		}

		$cat_html = '<a class="' . $category_paid_post_spl_class . '"' . $category_link . '>' . $category_name . '</a>';
	} elseif ($cat_count > 1) {
		$cat_html = '';
		foreach ($category_object as $cat) {
			// array_push( $cat_name_arr, $cat->name );

			/* Add Red color to special category 'Paid Post' */
			if ($cat->name === 'Paid Post') {
				$category_paid_post_spl_class = 'paid_post_spl_cat';
				$category_link                = '';
			} else {
				$category_paid_post_spl_class = '';
				$category_link                = ' href="' . get_category_link($cat->term_id) . '" ';
			}

			$cat_html .= '<a class="' . $category_paid_post_spl_class . '"' . $category_link . '>' . $cat->name . '</a>';
		}
		// $cat_html .= '';
		// $category_name   = implode( ",", $cat_name_arr );
	}

	return $cat_html;
}
