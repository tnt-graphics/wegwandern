<?php
/**
 * Form action helper
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmAcfFormActionHelper
 */
class FrmAcfPostActionHelper {

	/**
	 * Gets supported field types.
	 *
	 * @return array An array with key as Formidable type and value is an array of ACF types.
	 */
	public static function get_supported_field_types() {
		$field_types = array(
			'text'     => array(
				'text',
				'textarea',
				'password',
				'wysiwyg',
				'select',
				'radio',
				'button_group',
			),
			'textarea' => array(
				'text',
				'textarea',
				'password',
				'wysiwyg',
			),
			'checkbox' => array(
				'checkbox',
				'select',
			),
			'radio' => array(
				'text',
				'password',
				'select',
				'radio',
				'button_group',
			),
			'select' => array(
				'text',
				'password',
				'select',
				'radio',
				'button_group',
			),
			'email' => array(
				'email',
			),
			'url' => array(
				'url',
				'oembed',
			),
			'number' => array(
				'number',
				'range',
			),
			'phone' => array(
				'text',
			),
			'html' => array(
				'textarea',
				'wysiwyg',
			),
			'hidden' => array(
				'text',
				'textarea',
				'password',
				'wysiwyg',
			),
			'file' => array(
				'image',
				'file',
				'gallery',
			),
			'rte'  => array(
				'textarea',
				'wysiwyg',
			),
			'date'  => array(
				'date_picker',
			),
			'time'  => array(
				'time_picker',
			),
			'scale'  => array(
				'number',
				'range',
			),
			'star'  => array(
				'number',
				'range',
			),
			'range'  => array(
				'number',
				'range',
			),
			'toggle'  => array(
				'true_false',
			),
			'nps'  => array(
				'number',
				'range',
			),
			'password' => array(
				'text',
				'password',
			),
			'divider' => array(
				'repeater',
				'group',
			),
		);

		/**
		 * Filters the supported field types list.
		 *
		 * @param array $field_types Supported field types list.
		 */
		return apply_filters( 'frm_acf_supported_field_types', $field_types );
	}

	/**
	 * Gets global JS data.
	 *
	 * @param int $form_id Form ID.
	 * @return array
	 */
	public static function get_global_js_data( $form_id ) {
		$data = array(
			'frm_fields'       => array(),
			'frm_ids_fields'   => array(),
			'acf_fields'       => array(),
			'acf_names_fields' => array(),
			'compatible_types' => self::get_supported_field_types(),
			'strings'          => array(
				'select_field'     => __( '&mdash; Select a field &mdash;', 'formidable-acf' ),
				'select_frm_first' => __( '&mdash; Select a Formidable field first &mdash;', 'formidable-acf' ),
			),
		);

		self::add_frm_fields_data( $form_id, $data );
		self::add_acf_fields_data( $data );

		/**
		 * Filters the global JS data.
		 *
		 * @param array $data JS data.
		 * @param array $args Contains `form_id`.
		 */
		return apply_filters( 'frm_acf_global_js_data', $data, compact( 'form_id' ) );
	}

	/**
	 * Gets form action JS data.
	 *
	 * @param array $args See hook `frm_pro_post_action_options`.
	 * @return array
	 */
	public static function get_form_action_js_data( $args ) {
		$data = array(
			'action_settings' => $args['form_action']->post_content,
			'mapping'         => array(),
		);

		self::add_mapping_data( $args['form_action']->post_content, $data );

		/**
		 * Filters the form action JS data.
		 *
		 * @param array $data JS data.
		 * @param array $args The args of `frm_pro_post_action_options` hook.
		 */
		return apply_filters( 'frm_acf_form_action_js_data', $data, $args );
	}

	/**
	 * Adds mapping data to the global JS data.
	 *
	 * @param array $settings Form action settings.
	 * @param array $data     JS data.
	 */
	private static function add_mapping_data( $settings, &$data ) {
		$data['mapping'] = isset( $settings['post_custom_fields'] ) ? array_values( $settings['post_custom_fields'] ) : array();
	}

	/**
	 * Adds Frm fields data to the global JS data.
	 *
	 * @param int   $form_id Form ID.
	 * @param array $data    JS data.
	 */
	private static function add_frm_fields_data( $form_id, &$data ) {
		$fields = FrmField::get_all_for_form( $form_id );
		$repeater_id_index = array();

		foreach ( $fields as $index => $field ) {
			if ( FrmField::is_repeating_field( $field ) ) {
				$repeater_id_index[ $field->id ] = $index;
			}

			$data['frm_ids_fields'][ $field->id ] = $field;

			unset( $field, $index );
		}

		$skip_field_types = array( 'end_divider' );
		if ( class_exists( 'FrmSubmitHelper' ) ) {
			$skip_field_types[] = FrmSubmitHelper::FIELD_TYPE;
		}

		foreach ( $fields as $index => $field ) {
			if ( in_array( $field->type, $skip_field_types, true ) || ( 'divider' === $field->type && ! FrmField::is_repeating_field( $field ) ) ) {
				unset( $fields[ $index ] );
				continue;
			}

			$repeater_id = FrmField::get_option( $field, 'in_section' );
			if ( $repeater_id && isset( $repeater_id_index[ $repeater_id ] ) ) {
				if ( ! isset( $fields[ $repeater_id_index[ $repeater_id ] ]->child_fields ) ) {
					$fields[ $repeater_id_index[ $repeater_id ] ]->child_fields = array();
				}

				$fields[ $repeater_id_index[ $repeater_id ] ]->child_fields[] = $field;

				unset( $fields[ $index ] );
			}

			unset( $field, $index );
		}

		$data['frm_fields'] = array_values( $fields );
	}

	/**
	 * Adds ACF fields data to the global JS data.
	 *
	 * @param array $data JS data.
	 */
	private static function add_acf_fields_data( &$data ) {
		$field_groups = FrmAcfAppHelper::get_acf_field_groups();

		foreach ( $field_groups as $field_group ) {
			$fields     = FrmAcfAppHelper::get_acf_fields_from_group( $field_group );
			$new_fields = array();

			foreach ( $fields as $field ) {
				// Convert group item meta name, flatten group child fields.
				if ( 'group' === $field['type'] && ! empty( $field['sub_fields'] ) ) {
					foreach ( $field['sub_fields'] as &$sub_field ) {
						$sub_field['original_name'] = $sub_field['name'];
						$sub_field['group_name']    = $field['name'];
						$sub_field['name']          = $field['name'] . '_' . $sub_field['name'];

						$new_fields[] = $sub_field;

						if ( ! isset( $data['acf_names_fields'][ $sub_field['name'] ] ) ) {
							$data['acf_names_fields'][ $sub_field['name'] ] = $sub_field;
						}
					}
					continue;
				}

				$data['acf_names_fields'][ $field['name'] ] = $field;

				$new_fields[] = $field;
				unset( $field );
			}

			$data['acf_fields'][ $field_group['key'] ] = $new_fields;
			unset( $fields, $new_fields );
		}
	}
}
