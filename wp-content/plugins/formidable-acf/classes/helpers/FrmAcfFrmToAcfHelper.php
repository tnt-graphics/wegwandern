<?php
/**
 * Convert Formidable values to ACF.
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAcfFrmToAcfHelper {

	/**
	 * Keep track of Repeater values when converting.
	 *
	 * @var array
	 */
	private static $acf_repeaters = array();

	/**
	 * Converts Frm value to ACF custom fields array.
	 *
	 * @param mixed $value Frm value.
	 * @param array $args  {
	 *     The args.
	 *
	 *     @type object $frm_field Frm field.
	 *     @type array  $acf_field ACF field.
	 *     @type array  $mapping   Mapping data.
	 *     @type object $frm_entry Frm entry.
	 * }
	 * @return mixed
	 */
	public static function convert( $value, $args ) {
		if ( empty( $args['frm_field'] ) || empty( $args['acf_field'] ) ) {
			return $value;
		}

		$acf_type = $args['acf_field']['type'];
		$frm_type = $args['frm_field']->type;

		if ( method_exists( __CLASS__, $frm_type . '_to_' . $acf_type ) ) {
			$new_value = call_user_func( array( __CLASS__, $frm_type . '_to_' . $acf_type ), $value, $args );
		} else {
			$is_frm_multi_choice = FrmAcfAppHelper::is_multi_choice_field( $args['frm_field'] );
			$is_acf_multi_choice = FrmAcfAppHelper::is_multi_choice_field( $args['acf_field'] );

			if ( $is_frm_multi_choice && ! $is_acf_multi_choice ) {
				$new_value = FrmAcfSyncHelper::convert_multi_to_single_choice( $value );
			} elseif ( ! $is_frm_multi_choice && $is_acf_multi_choice ) {
				$new_value = FrmAcfSyncHelper::convert_single_to_multi_choices( $value );
			} else {
				$new_value = $value;
			}
		}

		$args['old_value'] = $value;

		/**
		 * Filters the value converted from Formidable to ACF.
		 *
		 * @param mixed $new_value The converted value.
		 * @param array $args      The args of {@see FrmAcfFrmToAcfHelper::convert()}, with `old_value` added.
		 */
		$new_value = apply_filters( 'frm_acf_frm_to_acf', $new_value, $args );

		/**
		 * Filters the value converted from a specific Formidable field type to ACF.
		 *
		 * @param mixed $new_value The converted value.
		 * @param array $args      The args of {@see FrmAcfFrmToAcfHelper::convert()}, with `old_value` added.
		 */
		$new_value = apply_filters( 'frm_acf_frm_' . $frm_type . '_to_acf_' . $acf_type, $new_value, $args );

		return $new_value;
	}

	/**
	 * Converts Frm Section value to ACF group.
	 *
	 * @param mixed $value Frm section value.
	 * @param array $args  Mapping args.
	 * @return array|mixed
	 */
	private static function divider_to_group( $value, $args ) {
		if ( empty( $args['mapping']['child_mapping'] ) ) {
			return $value;
		}

		if ( ! is_array( $value ) ) {
			$value = array();
		}

		foreach ( $args['mapping']['child_mapping'] as $mapping ) {
			$frm_field = FrmField::getOne( $mapping['field_id'] );
			if ( ! $frm_field ) {
				continue;
			}

			$acf_field = FrmAcfAppHelper::get_acf_field( $mapping['meta_name'] );
			if ( ! $acf_field ) {
				continue;
			}

			$frm_value = get_post_meta( $args['frm_entry']->post_id, $mapping['meta_name'], true );
			$acf_value = self::convert(
				$frm_value,
				array(
					'frm_field' => $frm_field,
					'acf_field' => $acf_field,
					'mapping'   => $mapping,
				)
			);

			$value[ $acf_field['key'] ] = $acf_value;
		}

		return $value;
	}

	/**
	 * Converts Frm repeater value to ACF. ACF will store the number of items.
	 *
	 * @param array $value Frm repeater value.
	 * @param array $args  Mapping args.
	 * @return int|array
	 */
	private static function divider_to_repeater( $value, $args ) {
		if ( is_array( $value ) ) {
			return count( $value );
		}
		return $value;
	}

	/**
	 * Converts Frm Toggle value to ACF True/False.
	 *
	 * @param string $value Frm Toggle value.
	 * @param array  $args  Mapping args.
	 * @return int
	 */
	private static function toggle_to_true_false( $value, $args ) {
		$on_label = FrmField::get_option( $args['frm_field'], 'toggle_on' );
		$checked  = FrmAppHelper::check_selected( $value, $on_label );

		return $checked ? 1 : 0;
	}

	/**
	 * Gets and converts Frm Repeater value to ACF.
	 *
	 * @param array $value Array of child entry IDs.
	 * @param array $args  Mapping args.
	 * @return array
	 */
	public static function get_acf_repeater_value( $value, $args ) {
		// Cached key.
		$key = $args['frm_entry']->post_id . $args['acf_field']['name'];

		if ( ! isset( self::$acf_repeaters[ $key ] ) ) {
			$value         = array_map( 'intval', $value );
			$child_entries = self::get_child_entries( $args['frm_entry']->id );

			if ( ! $child_entries ) {
				return $value;
			}

			$repeater_data = array();
			foreach ( $child_entries as $child_entry ) {
				if ( ! in_array( intval( $child_entry->id ), $value, true ) ) {
					continue;
				}

				$child_entry     = FrmEntry::get_meta( $child_entry );
				$repeater_data[] = self::get_repeater_item_from_entry( $child_entry, $args['mapping']['child_mapping'] );
			}

			if ( $repeater_data ) {
				self::$acf_repeaters[ $key ] = $repeater_data;
			}
		}

		return self::$acf_repeaters[ $key ];
	}

	/**
	 * Gets child entries.
	 *
	 * @param int $parent_entry_id Parent entry ID.
	 * @return array
	 */
	private static function get_child_entries( $parent_entry_id ) {
		return FrmDb::get_results(
			'frm_items',
			array(
				'parent_item_id' => $parent_entry_id,
			)
		);
	}

	/**
	 * Gets and converts Frm repeater item value to ACF.
	 *
	 * @param object $entry   Child entry object.
	 * @param array  $mapping Child mapping data.
	 * @return array
	 */
	private static function get_repeater_item_from_entry( $entry, $mapping ) {
		$item = array();

		foreach ( $entry->metas as $field_id => $value ) {
			// Not every repeater item is mapped.
			$meta_key = FrmAcfSyncHelper::get_meta_key_from_mapping( $mapping, $field_id );
			if ( ! $meta_key ) {
				continue;
			}

			$frm_field = FrmField::getOne( $field_id );
			if ( ! $frm_field ) {
				continue;
			}

			$acf_field = FrmAcfAppHelper::get_acf_field( $meta_key );
			if ( ! $acf_field ) {
				continue;
			}

			$acf_value = self::convert(
				$value,
				array(
					'frm_field' => $frm_field,
					'acf_field' => $acf_field,
					'frm_entry' => $entry,
				)
			);

			$item[ $acf_field['name'] ] = $acf_value;
		}

		return $item;
	}
}
