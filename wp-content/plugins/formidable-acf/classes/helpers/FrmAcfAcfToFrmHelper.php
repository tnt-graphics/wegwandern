<?php
/**
 * Convert ACF values to Formidable.
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAcfAcfToFrmHelper {

	/**
	 * Converts ACF value to Frm.
	 *
	 * @param mixed $value ACF value.
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

		if ( method_exists( __CLASS__, $acf_type . '_to_' . $frm_type ) ) {
			$new_value = call_user_func( array( __CLASS__, $acf_type . '_to_' . $frm_type ), $value, $args );
		} else {
			$is_acf_multi_choice = FrmAcfAppHelper::is_multi_choice_field( $args['acf_field'] );
			$is_frm_multi_choice = FrmAcfAppHelper::is_multi_choice_field( $args['frm_field'] );

			if ( $is_acf_multi_choice && ! $is_frm_multi_choice ) {
				$new_value = FrmAcfSyncHelper::convert_multi_to_single_choice( $value );
			} elseif ( ! $is_acf_multi_choice && $is_frm_multi_choice ) {
				$new_value = FrmAcfSyncHelper::convert_single_to_multi_choices( $value );
			} else {
				$new_value = $value;
			}
		}

		$args['old_value'] = $value;

		/**
		 * Filters the value converted from ACF to Formidable.
		 *
		 * @param mixed $new_value The converted value.
		 * @param array $args      The args of {@see FrmAcfAcfToFrmHelper::convert()}, with `old_value` added.
		 */
		$new_value = apply_filters( 'frm_acf_acf_to_frm', $new_value, $args );

		/**
		 * Filters the value converted from a specific ACF field type to Formidable.
		 *
		 * @param mixed $new_value The converted value.
		 * @param array $args      The args of {@see FrmAcfAcfToFrmHelper::convert()}, with `old_value` added.
		 */
		$new_value = apply_filters( 'frm_acf_acf_' . $acf_type . '_to_frm_' . $frm_type, $new_value, $args );

		return $new_value;
	}

	/**
	 * Converts ACF date picker value to Frm.
	 *
	 * @param string $value ACF date picker.
	 * @param array  $args  Convert args.
	 * @return string|false
	 */
	private static function date_picker_to_date( $value, $args ) {
		return FrmAcfAppHelper::convert_date_format( $value, 'Ymd' );
	}

	/**
	 * Converts ACF True/False value to Frm Toggle.
	 *
	 * @param int|string $value ACF True/False value. This is `0` or `1`.
	 * @param array      $args  Mapping args.
	 * @return string
	 */
	private static function true_false_to_toggle( $value, $args ) {
		$on_label  = FrmField::get_option( $args['frm_field'], 'toggle_on' );
		$off_label = FrmField::get_option( $args['frm_field'], 'toggle_off' );

		return intval( $value ) ? $on_label : $off_label;
	}
}
