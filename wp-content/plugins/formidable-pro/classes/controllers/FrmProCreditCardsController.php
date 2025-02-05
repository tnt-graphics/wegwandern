<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProCreditCardsController extends FrmProComboFieldsController {

	/**
	 * @param array  $field
	 * @param string $field_name
	 * @param array  $atts
	 * @return void
	 */
	public static function show_in_form( $field, $field_name, $atts ) {
		$frm_settings = FrmAppHelper::get_settings();

		$errors  = isset( $atts['errors'] ) ? $atts['errors'] : array();
		$html_id = $atts['html_id'];

		$defaults = self::empty_value_array();
		if ( empty( $field['value'] ) ) {
			$field['value'] = $defaults;
		} else {
			$field['value'] = array_merge( $defaults, (array) $field['value'] );
		}

		if ( $field['default_value'] == $field['value'] ) {
			$field['value'] = $defaults;
		}

		$sub_fields   = self::get_sub_fields( $field );
		$remove_names = ( $field['save_cc'] == -1 );

		include FrmProAppHelper::plugin_path() . '/classes/views/combo-fields/input.php';
	}

	/**
	 * @param array $field
	 * @return array
	 */
	public static function get_sub_fields( $field ) {
		$placeholder = (array) ( isset( $field['placeholder'] ) ? $field['placeholder'] : array() );

		$fields = array(
			'cc'    => array(
				'type'    => 'tel',
				'classes' => 'frm_full frm_cc_number',
				'label'   => 1,
				'atts'    => array(
					'autocomplete'   => 'cc-number',
					'autocorrect'    => 'off',
					'spellcheck'     => 'off',
					'autocapitalize' => 'off',
					'data-name'      => $field['id'] . '-cc',
					'placeholder'    => isset( $placeholder['cc'] ) ? $placeholder['cc'] : '',
				),
			),
			'month' => array(
				'type'        => 'select',
				'classes'     => 'frm_third frm_first frm_cc_exp_month',
				'label'       => 1,
				'options'     => range( 1, 12 ),
				'placeholder' => isset( $placeholder['month'] ) ? $placeholder['month'] : __( 'Month', 'formidable-pro' ),
			),
			'year'  => array(
				'type'        => 'select',
				'classes'     => 'frm_third frm_cc_exp_year',
				'label'       => 1,
				'options'     => range( gmdate( 'Y' ), gmdate( 'Y' ) + 10 ),
				'placeholder' => isset( $placeholder['year'] ) ? $placeholder['year'] : __( 'Year', 'formidable-pro' ),
			),
			'cvc'   => array(
				'type'    => 'tel',
				'classes' => 'frm_third frm_cc_cvc',
				'label'   => 1,
				'atts'    => array(
					'spellcheck'     => 'off',
					'autocapitalize' => 'off',
					'maxlength'      => 4,
					'autocorrect'    => 'off',
					'autocomplete'   => 'off',
					'data-name'      => $field['id'] . '-cvc',
					'placeholder'    => isset( $placeholder['cvc'] ) ? $placeholder['cvc'] : '',
				),
			),
		);

		return $fields;
	}

	/**
	 * @param array $headings
	 * @param array $atts
	 * @return array
	 */
	public static function add_csv_columns( $headings, $atts ) {
		if ( $atts['field']->type === 'credit_card' ) {

			$default_labels          = self::default_labels();
			$default_labels['month'] = __( 'Expiration Month', 'formidable-pro' );
			$default_labels['year']  = __( 'Expiration Year', 'formidable-pro' );

			$values = self::empty_value_array();
			foreach ( $values as $heading => $value ) {
				if ( isset( $default_labels[ $heading ] ) ) {
					$label = $default_labels[ $heading ];
				} else {
					$label = $atts['field']->name;
				}

				$headings[ $atts['field']->id . '_' . $heading ] = strip_tags( $label );
			}
		}

		return $headings;
	}

	private static function empty_value_array() {
		return array(
			'cc'    => '',
			'month' => '',
			'year'  => '',
			'cvc'   => '',
		);
	}

	private static function default_labels() {
		$options = array(
			'cc'  => __( 'Card Number', 'formidable-pro' ),
			'cvc' => __( 'CVC', 'formidable-pro' ),
		);
		return $options;
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function display_value( $value ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmProFieldCreditCard->get_display_value' );
		$field_obj = FrmFieldFactory::get_field_type( 'credit_card' );
		return $field_obj->get_display_value( $value );
	}
}
