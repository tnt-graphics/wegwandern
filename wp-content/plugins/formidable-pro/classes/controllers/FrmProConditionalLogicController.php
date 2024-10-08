<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.02.13
 */
class FrmProConditionalLogicController {

	/**
	 * Check if a given field should be present in another field's logic options
	 *
	 * @since 2.02.13
	 *
	 * @param array  $current_field The current field being displayed for editing
	 * @param object $logic_field   The logic field
	 * @param bool   $prefiltered   Pass bool to avoid some filter type checks that have already been applied.
	 *
	 * @return bool
	 */
	public static function is_field_present_in_logic_options( $current_field, $logic_field, $prefiltered = false ) {
		$present = true;

		if ( $logic_field->id == $current_field['id'] ) {
			$present = false;
		} else {
			if ( ! $prefiltered ) {
				if ( FrmField::is_no_save_field( $logic_field->type ) ) {
					$present = false;
				} elseif ( in_array( $logic_field->type, array( 'file', 'date', 'address', 'credit_card' ), true ) ) {
					$present = false;
				} elseif ( FrmProField::is_list_field( $logic_field ) ) {
					$present = false;
				}
			}

			if ( $present ) {
				$parent_form_id = isset( $current_field['parent_form_id'] ) ? $current_field['parent_form_id'] : '0';

				if ( $logic_field->form_id != $current_field['form_id'] && $logic_field->form_id != $parent_form_id ) {
					$present = false;
				} else {
					$in_section_id  = isset( $logic_field->field_options['in_section'] ) ? $logic_field->field_options['in_section'] : '0';
					if ( $in_section_id == $current_field['id'] ) {
						$present = false;
					}
				}
			}
		}

		/**
		 * Allows excluding fields in the conditional logic options.
		 *
		 * @since 5.0
		 *
		 * @param bool  $present Is `true` if field is present in the conditional logic options.
		 * @param array $args    The arguments. Contains `$current_field` and `$logic_field`.
		 */
		return (bool) apply_filters( 'frm_is_field_present_in_logic_options', $present, compact( 'current_field', 'logic_field' ) );
	}
}
