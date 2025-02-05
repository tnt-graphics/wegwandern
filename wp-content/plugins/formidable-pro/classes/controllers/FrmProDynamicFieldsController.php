<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProDynamicFieldsController {

	/**
	 * @since 5.4
	 *
	 * @var string
	 */
	const INCLUDE_DRAFTS = 'include';

	/**
	 * @since 5.4
	 *
	 * @var string
	 */
	const EXCLUDE_DRAFTS = 'exclude';

	/**
	 * @since 5.4
	 *
	 * @var string
	 */
	const DRAFT_ONLY = 'draft_only';

	/**
	 * @since 6.9
	 *
	 * @var string
	 */
	const IN_PROGRESS_ONLY = 'in_progress_only';

	/**
	 * @since 6.9
	 *
	 * @var string
	 */
	const ABANDONED_ONLY = 'abandoned_only';

	/**
	 * @since 6.9
	 *
	 * @var string
	 */
	const INCLUDE_ALL = 'include_all';

	/**
	 * Add options for a Dynamic field
	 *
	 * @since 2.01.0
	 *
	 * @param object $field
	 * @param array  $values
	 * @param array  $atts
	 */
	public static function add_options_for_dynamic_field( $field, &$values, $atts = array() ) {
		if ( self::is_field_independent( $values ) ) {
			$entry_id          = isset( $atts['entry_id'] ) ? $atts['entry_id'] : 0;
			$values['options'] = self::get_independent_options( $values, $field, $entry_id );
		} elseif ( is_numeric( $values['value'] ) ) {
			$values['options'] = array();
			if ( $field->field_options['data_type'] === 'select' ) {
				// add blank option for dropdown
				$values['options'][''] = self::get_placeholder_option( $field );
			}
			$values['options'][ $values['value'] ] = FrmEntryMeta::get_entry_meta_by_field( $values['value'], $values['form_select'] );
		}
	}

	/**
	 * Check if Dynamic field is independent of other Dynamic fields
	 *
	 * @since 2.01.0
	 * @param array $values
	 * @return bool
	 */
	private static function is_field_independent( $values ) {
		$independent = true;
		if ( ! empty( $values['hide_field'] ) && ( ! empty( $values['hide_opt'] ) || ! empty( $values['form_select'] ) ) ) {
			foreach ( $values['hide_field'] as $hkey => $f ) {
				if ( ! empty( $values['hide_opt'][ $hkey ] ) ) {
					continue;
				}
				$f = FrmField::getOne( $f );
				if ( $f && $f->type === 'data' ) {
					$independent = false;
					break;
				}
				unset( $f, $hkey );
			}
		}

		return $independent;
	}

	/**
	 * Get the options for an independent Dynamic field
	 *
	 * @param array    $values
	 * @param object   $field
	 * @param bool|int $entry_id
	 * @return array
	 */
	public static function get_independent_options( $values, $field, $entry_id = false ) {
		global $user_ID, $wpdb;

		$metas          = array();
		$selected_field = FrmField::getOne( $values['form_select'] );

		if ( ! $selected_field ) {
			return array();
		}

		$linked_is_post_field = FrmField::get_option( $selected_field, 'post_field' );
		$linked_posts         = $linked_is_post_field && $linked_is_post_field != '';

		$post_ids = array();

		if ( is_numeric( $values['hide_field'] ) && empty( $values['hide_opt'] ) ) {
			if ( isset( $_POST['item_meta'] ) ) {
				$observed_field_val = isset( $_POST['item_meta'][ $values['hide_field'] ] ) ? $_POST['item_meta'][ $values['hide_field'] ] : '';
			} elseif ( $entry_id ) {
				$observed_field_val = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $values['hide_field'] );
			} else {
				$observed_field_val = '';
			}

			FrmProAppHelper::unserialize_or_decode( $observed_field_val );

			$metas = array();
			FrmProEntryMetaHelper::meta_through_join( $values['hide_field'], $selected_field, $observed_field_val, false, $metas );
		} elseif ( $values['restrict'] && ( $user_ID || self::should_restrict_options_for_logged_out_users( $field ) ) ) {
			$entry_user = FrmProEntryMetaHelper::user_for_dynamic_opts( $user_ID, compact( 'entry_id', 'field' ) );

			if ( isset( $selected_field->form_id ) ) {
				$linked_where = array(
					'form_id' => $selected_field->form_id,
					'user_id' => $entry_user,
				);
				if ( $linked_posts ) {
					$post_ids = FrmDb::get_results( 'frm_items', $linked_where, 'id, post_id' );
				} else {
					$entry_ids = FrmDb::get_col( $wpdb->prefix . 'frm_items', $linked_where, 'id' );
				}
				unset( $linked_where );
			}

			if ( ! empty( $entry_ids ) ) {
				$metas = FrmEntryMeta::getAll(
					array(
						'it.item_id' => $entry_ids,
						'field_id'   => (int) $values['form_select'],
					),
					' ORDER BY meta_value',
					'' 
				);
			}
		} else {
			$limit     = '';
			$meta_args = array();
			if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
				$limit                 = 200;
				$meta_args['limit']    = $limit;
				$meta_args['order_by'] = 'meta_value';
			}

			$metas    = FrmDb::get_results( 'frm_item_metas', array( 'field_id' => $values['form_select'] ), 'item_id, meta_value', $meta_args );
			$post_ids = FrmDb::get_results( 'frm_items', array( 'form_id' => $selected_field->form_id ), 'id, post_id', array( 'limit' => $limit ) );
		}

		if ( $linked_posts && ! empty( $post_ids ) ) {
			foreach ( $post_ids as $entry ) {
				$meta_value = FrmProEntryMetaHelper::get_post_value(
					$entry->post_id,
					$selected_field->field_options['post_field'],
					$selected_field->field_options['custom_field'],
					array(
						'type'    => $selected_field->type,
						'form_id' => $selected_field->form_id,
						'field'   => $selected_field,
					) 
				);
				$metas[]    = array(
					'meta_value' => $meta_value,
					'item_id'    => $entry->id,
				);
			}
		}

		$options           = array();
		$should_strip_tags = $field->field_options['data_type'] === 'select' || FrmAppHelper::is_admin_page( 'formidable' );

		self::maybe_exclude_drafts( $metas, $values );

		foreach ( $metas as $meta ) {
			$meta = (array) $meta;
			if ( $meta['meta_value'] == '' ) {
				continue;
			}

			$new_value = FrmEntriesHelper::display_value(
				$meta['meta_value'],
				$selected_field,
				array(
					'type'          => $selected_field->type,
					'show_icon'     => true,
					'show_filename' => false,
				) 
			);
			if ( $should_strip_tags ) {
				$new_value = strip_tags( $new_value );
			}

			$options[ $meta['item_id'] ] = $new_value;

			unset( $meta );
		}

		$options = apply_filters(
			'frm_data_sort',
			$options,
			array(
				'metas'         => $metas,
				'field'         => $selected_field,
				'dynamic_field' => $values,
			) 
		);

		unset( $metas );

		if ( self::include_blank_option( $options, $field ) ) {
			$options = array( '' => self::get_placeholder_option( $field ) ) + (array) $options;
		}

		return wp_unslash( $options );
	}

	/**
	 * @since 6.8.3
	 *
	 * @param stdClass $field
	 * @return bool
	 */
	private static function should_restrict_options_for_logged_out_users( $field ) {
		$should_restrict_options = true;

		/**
		 * @since 6.8.3
		 *
		 * @param bool     $should_restrict_options
		 * @param stdClass $field
		 */
		return (bool) apply_filters( 'frm_restrict_options_for_logged_out_users', $should_restrict_options, $field );
	}

	/**
	 * Maybe exclude draft items.
	 *
	 * @since 5.4
	 *
	 * @param array $metas  Array of item meta and id.
	 * @param array $values Field array.
	 */
	private static function maybe_exclude_drafts( &$metas, $values ) {
		global $wpdb;

		/**
		 * Allows including or excluding draft items from dynamic data.
		 *
		 * @since 5.4
		 *
		 * @param string $include Accepts `include`, `exclude`, `draft_only`, `in_progress_only`, `abandoned_only`, `include_all`.
		 * @param array  $args    Contains `field` as field array.
		 */
		$include_drafts = apply_filters( 'frm_dynamic_field_include_drafts', self::EXCLUDE_DRAFTS, array( 'field' => $values ) );

		if ( self::INCLUDE_ALL === $include_drafts ) {
			return;
		}

		$item_ids_to_include = FrmDb::get_col(
			"{$wpdb->prefix}frm_items it INNER JOIN {$wpdb->prefix}frm_fields fi ON it.form_id = fi.form_id",
			array(
				'fi.id'    => intval( $values['form_select'] ),
				'is_draft' => self::get_status_to_include( $include_drafts ),
			),
			'it.id'
		);

		if ( ! $item_ids_to_include ) {
			$metas = array();
			return;
		}

		$item_ids_to_include = array_map( 'intval', $item_ids_to_include );

		foreach ( $metas as $index => $meta ) {
			if ( isset( $meta->item_id ) && ! in_array( (int) $meta->item_id, $item_ids_to_include, true ) ) {
				unset( $metas[ $index ] );
			}
		}
	}

	/**
	 * @since 6.9
	 *
	 * @param string $include
	 * @return array|int
	 */
	private static function get_status_to_include( $include ) {
		switch ( $include ) {
			case self::ABANDONED_ONLY:
				return 3;
			case self::IN_PROGRESS_ONLY:
				return 2;
			case self::INCLUDE_DRAFTS:
				return array( 0, 1 );
			case self::DRAFT_ONLY:
				return 1;
			case self::EXCLUDE_DRAFTS:
			default:
				return 0;
		}
	}

	/**
	 * If using Chosen Autocomplete (not Slim Select), don't include the placeholder as an option.
	 *
	 * @since 4.0
	 *
	 * @param stdClass $field
	 * @return string
	 */
	private static function get_placeholder_option( $field ) {
		$placeholder = FrmField::get_option( $field, 'placeholder' );
		if ( FrmField::get_option( $field, 'autocom' ) && FrmProAppHelper::use_chosen_js() ) {
			$placeholder = '';
		}
		return $placeholder;
	}

	/**
	 * A dropdown field should include a blank option if it is not multiselect
	 * unless it autocomplete is also enabled
	 *
	 * @since 2.0
	 *
	 * @param stdClass $field
	 * @return bool
	 */
	public static function include_blank_option( $options, $field ) {
		if ( empty( $options ) || $field->type !== 'data' ) {
			return false;
		}

		if ( ! isset( $field->field_options['data_type'] ) || $field->field_options['data_type'] !== 'select' ) {
			return false;
		}

		return ( ! FrmField::is_multiple_select( $field ) || FrmField::is_option_true( $field, 'autocom' ) );
	}

	/**
	 * @since 6.7.1
	 *
	 * @param array $values
	 * @return array
	 */
	public static function clean_field_options_before_update( $values ) {
		return FrmProFieldsHelper::map_dropdown_data_type_to_select( $values );
	}
}
