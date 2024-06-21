<?php
/**
 * Sync helper
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAcfSyncHelper {

	/**
	 * Keep track of post ID - entry mapping.
	 *
	 * @var array
	 */
	private static $entries_by_post_id = array();

	/**
	 * Keep track of form ID - ACF mapping option.
	 *
	 * @var array
	 */
	private static $acf_frm_mappings = array();

	/**
	 * Format:
	 * array(
	 *     {form_id} => array(
	 *         {group_name}_{child_name} => {child_name}
	 *     )
	 * )
	 *
	 * @var array
	 */
	private static $acf_group_name_mapping = array();

	/**
	 * Gets entry from post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return object|false
	 */
	public static function get_entry_from_post_id( $post_id ) {
		if ( isset( self::$entries_by_post_id[ $post_id ] ) ) {
			return self::$entries_by_post_id[ $post_id ];
		}

		$entry = FrmDb::get_row(
			'frm_items',
			array(
				'post_id' => $post_id,
			)
		);

		self::$entries_by_post_id[ $post_id ] = $entry;
		return $entry;
	}

	/**
	 * Gets ACF mapping from form ID.
	 *
	 * @param int $form_id Form ID.
	 * @return array|false
	 */
	public static function get_acf_frm_mapping( $form_id ) {
		if ( isset( self::$acf_frm_mappings[ $form_id ] ) ) {
			return self::$acf_frm_mappings[ $form_id ];
		}

		$post_action = FrmFormAction::get_action_for_form(
			$form_id,
			'wppost',
			array(
				'post_status' => 'publish',
				'limit'       => 1,
			)
		);

		if ( ! $post_action ) {
			self::$acf_frm_mappings[ $form_id ] = false;
			return false;
		}

		$mapping = array();

		if ( ! empty( $post_action->post_content['acf'] ) && ! empty( $post_action->post_content['post_custom_fields'] ) ) {
			foreach ( $post_action->post_content['post_custom_fields'] as $custom_field ) {
				if ( empty( $custom_field['is_acf'] ) ) {
					continue;
				}

				unset( $custom_field['is_acf'] );
				$mapping[ $custom_field['meta_name'] ] = $custom_field;
			}
		}

		self::$acf_frm_mappings[ $form_id ] = $mapping;
		return $mapping;
	}

	/**
	 * Gets Frm field ID from given meta key and mapping data.
	 *
	 * @param array  $mapping  Mapping data.
	 * @param string $meta_key Meta key.
	 * @return int|false
	 */
	public static function get_frm_field_id_from_mapping( $mapping, $meta_key ) {
		foreach ( $mapping as $item ) {
			if ( $item['meta_name'] === $meta_key ) {
				return $item['field_id'];
			}
		}

		return false;
	}

	/**
	 * Gets meta key from given Frm field ID and mapping data.
	 *
	 * @param array $mapping  Mapping data.
	 * @param int   $field_id Frm field ID.
	 * @return string|false
	 */
	public static function get_meta_key_from_mapping( $mapping, $field_id ) {
		foreach ( $mapping as $item ) {
			if ( intval( $item['field_id'] ) === intval( $field_id ) ) {
				return $item['meta_name'];
			}
		}

		return false;
	}

	/**
	 * Decodes the ACF repeater item meta key into the repeater name, index and child field name.
	 *
	 * @param string $meta_key Meta key.
	 * @param array  $mapping  Mapping data.
	 * @return array|false
	 */
	public static function decode_repeater_item_meta_key( $meta_key, $mapping ) {
		foreach ( $mapping as $item ) {
			if ( empty( $item['child_mapping'] ) ) {
				continue;
			}

			preg_match_all( '/(' . $item['meta_name'] . ')_([0-9]+)_(.+)/m', $meta_key, $matches, PREG_SET_ORDER );
			if ( empty( $matches ) ) {
				continue;
			}

			return array(
				'repeater_name' => $matches[0][1],
				'index'         => intval( $matches[0][2] ),
				'child_name'    => $matches[0][3],
			);
		}

		return false;
	}

	/**
	 * Converts multi choice to single choice value.
	 *
	 * @param mixed $value Multi choice value.
	 * @return mixed
	 */
	public static function convert_multi_to_single_choice( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		if ( ! $value ) {
			return '';
		}

		return reset( $value );
	}

	/**
	 * Converts single choice to multi choice value.
	 *
	 * @param mixed $value Single choice value.
	 * @return array
	 */
	public static function convert_single_to_multi_choices( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( ! $value ) {
			return array();
		}

		return array( $value );
	}

	/**
	 * Checks if Frm meta exists.
	 *
	 * @param int $entry_id Entry ID.
	 * @param int $field_id Field ID.
	 * @return string|null Return the meta value or `null`.
	 */
	public static function frm_meta_exists( $entry_id, $field_id ) {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d AND field_id = %d",
				intval( $entry_id ),
				intval( $field_id )
			)
		);
	}

	/**
	 * Gets ACF group data from child meta name and mapping data.
	 *
	 * @param string $meta_name Group child meta name. It has format `{group_name}_{child_name}`.
	 * @param array  $mapping   Mapping data.
	 * @return array|false
	 */
	public static function get_group_data_from_child_meta_name( $meta_name, $mapping ) {
		foreach ( $mapping as $item ) {
			if ( empty( $item['child_mapping'] ) ) {
				continue;
			}

			foreach ( $item['child_mapping'] as $child_item ) {
				if ( $meta_name === $child_item['meta_name'] ) {
					return array(
						'mapping'        => $child_item,
						'acf_name'       => preg_replace( '/^(' . $item['meta_name'] . '_)/', '', $meta_name ),
						'acf_group_name' => $item['meta_name'],
					);
				}
			}
		}

		return false;
	}

	/**
	 * Adds or updates frm entry meta.
	 *
	 * @param int   $entry_id Entry ID.
	 * @param int   $field_id Field ID.
	 * @param mixed $value    The value.
	 */
	public static function add_or_update_frm_meta( $entry_id, $field_id, $value ) {
		if ( self::frm_meta_exists( $entry_id, $field_id ) ) {
			FrmEntryMeta::update_entry_meta( $entry_id, $field_id, null, $value );
		} else {
			FrmEntryMeta::add_entry_meta( $entry_id, $field_id, null, $value );
		}
	}
}
