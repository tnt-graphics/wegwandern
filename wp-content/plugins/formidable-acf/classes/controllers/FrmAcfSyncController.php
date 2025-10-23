<?php
/**
 * Sync controller
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAcfSyncController {

	/**
	 * Keep track of child entry IDs.
	 *
	 * @var array
	 */
	private static $child_entry_ids = array();

	/**
	 * Keep track of new child entry IDs.
	 *
	 * @var array
	 */
	private static $new_child_entry_ids = array();

	/**
	 * Converts Frm value to ACF when getting a meta from ACF.
	 *
	 * @param mixed  $value    Meta value.
	 * @param int    $post_id  Post ID.
	 * @param string $meta_key Meta key. This could be a normal field name, or child of repeater: `repeater_1_sub_field`.
	 * @param bool   $hidden   Is hidden meta or not.
	 * @return mixed
	 */
	public static function acf_get_metadata( $value, $post_id, $meta_key, $hidden ) {
		// Do not process if this is a hidden meta that stores the field key.
		if ( $hidden ) {
			return $value;
		}

		// Only process the post meta.
		$decoded = acf_decode_post_id( $post_id );
		if ( 'post' !== $decoded['type'] ) {
			return $value;
		}

		// Check if the post links with an Frm entry.
		$entry = FrmAcfSyncHelper::get_entry_from_post_id( $post_id );
		if ( ! $entry ) {
			return $value;
		}

		// Get mapping data.
		$mapping = FrmAcfSyncHelper::get_acf_frm_mapping( $entry->form_id );
		if ( ! $mapping ) {
			return $value;
		}

		// Check if the given meta key is a child of ACF repeater.
		$repeater_data = FrmAcfSyncHelper::decode_repeater_item_meta_key( $meta_key, $mapping );

		// If this is a child of ACF repeater, get the whole repeater field to process later.
		if ( $repeater_data ) {
			$meta_key = $repeater_data['repeater_name'];
		}

		if ( ! isset( $mapping[ $meta_key ] ) || ! self::is_valid_mapping( $mapping[ $meta_key ] ) ) {
			return $value;
		}

		$mapping   = $mapping[ $meta_key ];
		$frm_field = FrmField::getOne( $mapping['field_id'] );
		if ( ! $frm_field ) {
			return $value;
		}

		$acf_field = FrmAcfAppHelper::get_acf_field( $repeater_data ? $meta_key : $mapping['acf_field_key'] );
		if ( ! $acf_field ) {
			return $value;
		}

		if ( $meta_key !== $acf_field['name'] && ! $repeater_data ) { // This is child of a group field.
			// This might be replaced with $entry->metas after one of future release of core.
			$frm_value = FrmEntryMeta::get_meta_value( $entry, $frm_field->id );
		} else {
			$frm_value = get_post_meta( $post_id, $meta_key, true );
		}

		$args = array(
			'frm_field' => $frm_field,
			'acf_field' => $acf_field,
			'mapping'   => $mapping,
			'frm_entry' => $entry,
		);

		// Just convert the value if this is not a child of ACF Repeater.
		if ( ! $repeater_data ) {
			return FrmAcfFrmToAcfHelper::convert( $frm_value, $args );
		}

		$repeater_value = FrmAcfFrmToAcfHelper::get_acf_repeater_value( $frm_value, $args );

		return isset( $repeater_value[ $repeater_data['index'] ][ $repeater_data['child_name'] ] ) ? $repeater_value[ $repeater_data['index'] ][ $repeater_data['child_name'] ] : $value;
	}

	/**
	 * Converts ACF value to Frm when updating ACF meta.
	 *
	 * @param mixed $value          Meta value.
	 * @param int   $post_id        Post ID.
	 * @param array $acf_field      ACF field.
	 * @param mixed $original_value The meta original meta value.
	 * @return mixed
	 */
	public static function convert_acf_update_value( $value, $post_id, $acf_field, $original_value ) {
		$entry = FrmAcfSyncHelper::get_entry_from_post_id( $post_id );
		if ( ! $entry ) {
			return $value;
		}

		$mapping = FrmAcfSyncHelper::get_acf_frm_mapping( $entry->form_id );
		if ( ! $mapping || ! isset( $mapping[ $acf_field['name'] ] ) || ! self::is_valid_mapping( $mapping[ $acf_field['name'] ] ) ) {
			return $value;
		}

		// Just check which field type needs to convert to reduce processes.
		if ( ! in_array( $acf_field['type'], array( 'date_picker', 'true_false' ), true ) ) {
			return $value;
		}

		$mapping = $mapping[ $acf_field['name'] ];

		$frm_field = FrmField::getOne( $mapping['field_id'] );
		if ( ! $frm_field ) {
			return $value;
		}

		return FrmAcfAcfToFrmHelper::convert(
			$value,
			array(
				'frm_field' => $frm_field,
				'acf_field' => $acf_field,
				'mapping'   => $mapping,
				'frm_entry' => $entry,
			)
		);
	}

	/**
	 * Updates Frm Repeater when updating an ACF Repeater.
	 *
	 * @param null|mixed $check   If this is not `null`, ACF won't continue to save this meta.
	 * @param int        $post_id Post ID.
	 * @param string     $name    Meta key.
	 * @param mixed      $value   Meta value.
	 * @param bool       $hidden  Is hidden meta or not.
	 * @return null|true
	 */
	public static function update_acf_repeater( $check, $post_id, $name, $value, $hidden ) {
		if ( $hidden ) {
			return $check;
		}

		$decoded = acf_decode_post_id( $post_id );
		if ( 'post' !== $decoded['type'] ) {
			return $check;
		}

		$entry = FrmAcfSyncHelper::get_entry_from_post_id( $post_id );
		if ( ! $entry ) {
			return $check;
		}

		$mapping = FrmAcfSyncHelper::get_acf_frm_mapping( $entry->form_id );
		if ( ! $mapping ) {
			return $check;
		}

		// If this variable is not empty, we're processing the value of repeater item, we will update child entries.
		$repeater_data = FrmAcfSyncHelper::decode_repeater_item_meta_key( $name, $mapping );
		if ( $repeater_data ) {
			if ( ! isset( $mapping[ $repeater_data['repeater_name'] ] ) || ! isset( $mapping[ $repeater_data['repeater_name'] ]['child_mapping'] ) ) {
				return $check;
			}

			$mapping = $mapping[ $repeater_data['repeater_name'] ];

			return self::update_acf_repeater_item( $check, compact( 'entry', 'repeater_data', 'mapping', 'value' ) );
		}

		// We're processing the whole repeater field.
		if ( ! isset( $mapping[ $name ] ) || ! self::is_valid_mapping( $mapping[ $name ] ) ) {
			return $check;
		}

		$mapping   = $mapping[ $name ];
		$acf_field = FrmAcfAppHelper::get_acf_field( $mapping['acf_field_key'] );
		if ( 'repeater' !== $acf_field['type'] ) {
			return $check;
		}

		$frm_field = FrmField::getOne( $mapping['field_id'] );
		if ( ! $frm_field ) {
			return $check;
		}

		// Use the child repeater entries processed above to update the meta.
		$meta_value = isset( self::$new_child_entry_ids[ $entry->id ] ) ? self::$new_child_entry_ids[ $entry->id ] : array();
		update_post_meta( $post_id, $name, $meta_value );

		// Maybe delete child entries which are deleted in ACF.
		self::maybe_delete_child_entries( $entry->id );

		return true;
	}

	/**
	 * Maybe delete child entries. This is used in case an ACF repeater row is deleted.
	 *
	 * @param int $parent_entry_id Parent entry ID.
	 */
	private static function maybe_delete_child_entries( $parent_entry_id ) {
		if ( ! empty( self::$child_entry_ids[ $parent_entry_id ] ) ) {
			foreach ( self::$child_entry_ids[ $parent_entry_id ] as $child_entry_id ) {
				FrmEntry::destroy( $child_entry_id );
			}
		}
	}

	/**
	 * By-pass the duplicate check when creating an entry.
	 *
	 * @return int Return an empty value to by-pass.
	 */
	public static function bypass_frm_duplicate_check() {
		return 0;
	}

	/**
	 * Converts ACF repeater item value and save to Frm.
	 *
	 * @param null|mixed $check Check value.
	 * @param array      $args  {
	 *     The args.
	 *
	 *     @type object $entry         Frm entry.
	 *     @type array  $mapping       Mapping data.
	 *     @type array  $repeater_data Repeater data decoded from the meta key.
	 *     @type mixed  $value         Item value.
	 * }
	 *
	 * @return bool|true
	 */
	private static function update_acf_repeater_item( $check, $args ) {
		// Check if this child field is mapped.
		$frm_child_field_id = FrmAcfSyncHelper::get_frm_field_id_from_mapping( $args['mapping']['child_mapping'], $args['repeater_data']['child_name'] );
		if ( ! $frm_child_field_id ) {
			return $check;
		}

		$frm_child_field = FrmField::getOne( $frm_child_field_id );
		if ( ! $frm_child_field ) {
			return $check;
		}

		$acf_child_field = FrmAcfAppHelper::get_acf_field( $args['repeater_data']['child_name'] );

		$frm_value = FrmAcfAcfToFrmHelper::convert(
			$args['value'],
			array(
				'frm_field' => $frm_child_field,
				'acf_field' => $acf_child_field,
				'mapping'   => $args['mapping'],
				'frm_entry' => $args['entry'],
			)
		);

		/*
		 * Update frm entry.
		 */
		$frm_repeater_id = $args['mapping']['field_id'];
		$entry           = $args['entry'];
		$item_index      = $args['repeater_data']['index'];

		// Get the list of the old child entry IDs, and store in a static variable.
		if ( ! isset( self::$child_entry_ids[ $entry->id ] ) ) {
			// The old child entry IDs are stored in the meta.
			self::$child_entry_ids[ $entry->id ] = get_post_meta( $entry->post_id, $args['mapping']['meta_name'], true );
		}

		// Check if the corresponding entry exists, update its meta, otherwise, create a new entry.
		if ( isset( self::$child_entry_ids[ $entry->id ][ $item_index ] ) ) {
			FrmAcfSyncHelper::add_or_update_frm_meta(
				self::$child_entry_ids[ $entry->id ][ $item_index ],
				$frm_child_field->id,
				$frm_value
			);

			// Track the new child entry ID.
			self::$new_child_entry_ids[ $entry->id ][] = self::$child_entry_ids[ $entry->id ][ $item_index ];
		} else {
			$child_entry_id = self::create_repeater_child_entry( $entry, $frm_child_field, $frm_value );
			if ( $child_entry_id ) {
				// Track the new child entry ID.
				self::$new_child_entry_ids[ $entry->id ][] = $child_entry_id;
			}
		}

		unset( self::$child_entry_ids[ $entry->id ][ $item_index ] );

		return true;
	}

	/**
	 * Creates the Repeater child entry.
	 *
	 * @param object $parent_entry Parent entry object.
	 * @param object $child_field  Child field object.
	 * @param mixed  $child_value  The value of child field.
	 * @return int|bool
	 */
	private static function create_repeater_child_entry( $parent_entry, $child_field, $child_value ) {
		$entry_data = array(
			'name'           => $parent_entry->name,
			'description'    => $parent_entry->description,
			'parent_item_id' => $parent_entry->id,
			'form_id'        => $child_field->form_id,
			'ip'             => $parent_entry->ip,
			'updated_by'     => $parent_entry->updated_by,
			'item_meta'      => array( $child_field->id => $child_value ),
		);

		/**
		 * Filters the Repeater child entry data before creating.
		 *
		 * @param array $entry_data Entry data.
		 * @param array {
		 *     The args.
		 *
		 *     @type object $parent_entry Parent entry object.
		 *     @type object $child_field  Child field object.
		 *     @type mixed  $child_value  The value of child field.
		 * }
		 */
		$entry_data = apply_filters( 'frm_acf_repeater_child_entry_data', $entry_data, compact( 'parent_entry', 'child_field', 'child_value' ) );

		add_filter( 'frm_time_to_check_duplicates', array( __CLASS__, 'bypass_frm_duplicate_check' ) );
		$result = FrmEntry::create( $entry_data );
		remove_filter( 'frm_time_to_check_duplicates', array( __CLASS__, 'bypass_frm_duplicate_check' ) );

		return $result;
	}

	/**
	 * Checks if mapping data is valid.
	 *
	 * @param array $mapping Mapping data.
	 * @return bool
	 */
	private static function is_valid_mapping( $mapping ) {
		return ! empty( $mapping['field_id'] ) && ! empty( $mapping['meta_name'] ) && ! empty( $mapping['acf_field_key'] );
	}

	/**
	 * Updates the meta which stores the ACF field key when creating or updating a post from Formidable Forms.
	 *
	 * The meta key will be `_{meta name}`, the meta value will be the ACF field key.
	 * For each repeater item, the meta key will be `_{repeater meta name}_{index}_{child meta name}`, the meta value
	 * will be the ACF child field key.
	 *
	 * @since 2.x
	 *
	 * @param array $post_data Post data which is passed to the `wp_insert_post()` function.
	 * @param array $args      {
	 *     Args.
	 *
	 *     @type object $form   Form object.
	 *     @type object $entry  Entry object.
	 * }
	 * @return array
	 */
	public static function update_acf_field_key_in_post_meta( $post_data, $args ) {
		$mapping = FrmAcfSyncHelper::get_acf_frm_mapping( $args['form']->id );
		if ( ! $mapping ) {
			return $post_data;
		}

		if ( ! isset( $post_data['meta_input'] ) ) {
			$post_data['meta_input'] = array();
		}

		$no_save_entry = ! empty( $args['form']->options['no_save'] );

		foreach ( $mapping as $meta_key => $mapping_data ) {
			$post_data['meta_input'][ '_' . $meta_key ] = $mapping_data['acf_field_key'];

			$is_repeater = ! empty( $args['entry']->metas[ $mapping_data['field_id'] ] ) && is_array( $args['entry']->metas[ $mapping_data['field_id'] ] );

			if ( ! empty( $mapping_data['child_mapping'] ) && $is_repeater ) {
				if ( $no_save_entry ) {
					self::populate_meta_data_for_repeater_for_no_save_entry( $post_data['meta_input'], $meta_key, $mapping_data, $args );
				} else {
					self::populate_meta_data_for_repeater( $post_data['meta_input'], $meta_key, $mapping_data, $args );
				}
			}
		}

		return $post_data;
	}

	/**
	 * Populates meta data for repeater mapping.
	 *
	 * @since 1.0.2
	 *
	 * @param array  $meta_input   Post meta.
	 * @param string $meta_key     Meta key.
	 * @param array  $mapping_data Mapping data.
	 * @param array  $args         See {@see FrmAcfSyncController::update_acf_field_key_in_post_meta()}.
	 */
	private static function populate_meta_data_for_repeater( &$meta_input, $meta_key, $mapping_data, $args ) {
		foreach ( $mapping_data['child_mapping'] as $child_mapping ) {
			$acf_field = FrmAcfAppHelper::get_acf_field( $child_mapping['meta_name'] );
			if ( $acf_field ) {
				foreach ( $args['entry']->metas[ $mapping_data['field_id'] ] as $item_index => $item_value ) {
					$meta_input[ '_' . $meta_key . '_' . $item_index . '_' . $child_mapping['meta_name'] ] = $acf_field['key'];
				}
			}

			unset( $acf_field );
		}
	}

	/**
	 * Populates meta data for repeater mapping.
	 *
	 * @since 1.0.2
	 *
	 * @param array  $meta_input   Post meta.
	 * @param string $meta_key     Meta key.
	 * @param array  $mapping_data Mapping data.
	 * @param array  $args         See {@see FrmAcfSyncController::update_acf_field_key_in_post_meta()}.
	 */
	private static function populate_meta_data_for_repeater_for_no_save_entry( &$meta_input, $meta_key, $mapping_data, $args ) {
		foreach ( $mapping_data['child_mapping'] as $child_mapping ) {
			$acf_field = FrmAcfAppHelper::get_acf_field( $child_mapping['meta_name'] );
			if ( $acf_field ) {
				foreach ( $args['entry']->metas[ $mapping_data['field_id'] ] as $item_index => $item_value ) {
					$meta_input[ $meta_key . '_' . $item_index . '_' . $child_mapping['meta_name'] ] = FrmEntryMeta::get_entry_meta_by_field( $item_value, $child_mapping['field_id'] );
				}
				$meta_input[ $meta_key ]++;
			}

			unset( $acf_field );
		}

		$meta_input[ $meta_key ] = count( $args['entry']->metas[ $mapping_data['field_id'] ] );
	}
}
