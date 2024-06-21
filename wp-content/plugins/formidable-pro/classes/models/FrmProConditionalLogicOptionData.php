<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProConditionalLogicOptionData {

	private static $all_fields_by_form_id = array();

	/**
	 * @param int|string $form_id
	 * @return array
	 */
	public static function get_all_fields_for_form( $form_id ) {
		if ( ! isset( self::$all_fields_by_form_id[ $form_id ] ) ) {
			self::$all_fields_by_form_id[ $form_id ] = FrmField::get_all_for_form( $form_id );
		}
		return self::$all_fields_by_form_id[ $form_id ];
	}
}
