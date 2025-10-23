<?php
/**
 * App helper
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmAcfAppHelper
 */
class FrmAcfAppHelper {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $plug_version = '1.0.3';

	/**
	 * Gets plugin folder name.
	 *
	 * @return string
	 */
	public static function plugin_folder() {
		return basename( self::plugin_path() );
	}

	/**
	 * Gets plugin file path.
	 *
	 * @return string
	 */
	public static function plugin_file() {
		return self::plugin_path() . '/formidable-acf.php';
	}

	/**
	 * Gets plugin path.
	 *
	 * @return string
	 */
	public static function plugin_path() {
		return dirname( dirname( __DIR__ ) );
	}

	/**
	 * Gets plugin URL.
	 *
	 * @return string
	 */
	public static function plugin_url() {
		return plugins_url( '', self::plugin_path() . '/formidable-acf.php' );
	}

	/**
	 * Gets plugin relative URL.
	 *
	 * @return string
	 */
	public static function relative_plugin_url() {
		return str_replace( array( 'https:', 'http:' ), '', self::plugin_url() );
	}

	/**
	 * Converts date format.
	 *
	 * @param string $date The date string.
	 * @param string $source_format Source format.
	 * @param string $return_format Returned format.
	 * @return false|string
	 */
	public static function convert_date_format( $date, $source_format, $return_format = 'Y-m-d' ) {
		$date_obj = DateTime::createFromFormat( $source_format, $date );
		if ( ! $date_obj || $date_obj->format( $source_format ) !== $date ) {
			return false;
		}

		return $date_obj->format( $return_format );
	}

	/**
	 * Gets ACF field from ID.
	 *
	 * @param int $id ACF field ID.
	 * @return array|false
	 */
	public static function get_acf_field( $id ) {
		return acf_get_field( $id );
	}

	/**
	 * Gets all ACF field groups.
	 *
	 * @return array
	 */
	public static function get_acf_field_groups() {
		return acf_get_field_groups();
	}

	/**
	 * Gets ACF field group from ID.
	 *
	 * @param int|string $id The ACF field group ID, key or name.
	 * @return array|false
	 */
	public static function get_acf_field_group( $id ) {
		return acf_get_field_group( $id );
	}

	/**
	 * Gets ACF sub fields.
	 *
	 * @param array $parent_field ACF parent field.
	 * @return array|false
	 */
	public static function get_acf_sub_fields( $parent_field ) {
		if ( ! is_array( $parent_field ) ) {
			$parent_field = self::get_acf_field( $parent_field );
			if ( ! $parent_field ) {
				return false;
			}
		}

		return acf_get_fields( $parent_field );
	}

	/**
	 * Gets ACF fields from field group.
	 *
	 * @param array $field_group ACF field group.
	 * @return array
	 */
	public static function get_acf_fields_from_group( $field_group ) {
		return acf_get_fields( $field_group );
	}

	/**
	 * Gets all ACF fields from post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return array|false
	 */
	public static function get_acf_fields_from_post_id( $post_id ) {
		return get_field_objects( $post_id );
	}

	/**
	 * Checks if given field (ACF or Frm) is a multi choice field.
	 *
	 * @param array|object $field Field data.
	 * @return bool
	 */
	public static function is_multi_choice_field( $field ) {
		$field = (array) $field;
		if ( 'checkbox' === $field['type'] || 'gallery' === $field['type'] ) {
			return true;
		}

		if ( is_array( $field ) && ! empty( $field['key'] ) ) {
			// This is an ACF field.
			return in_array( $field['type'], array( 'select', 'radio' ), true ) && ! empty( $field['multiple'] );
		}

		return in_array( $field['type'], array( 'radio', 'select', 'file' ) ) && intval( FrmField::get_option( $field, 'multiple' ) );
	}

	/**
	 * Gets incompatible messages.
	 *
	 * @return array
	 */
	public static function get_incompatible_messages() {
		$messages = array();

		$version = '5.5.4';
		if ( ! class_exists( 'FrmAppHelper', false ) || version_compare( FrmAppHelper::$plug_version, $version, '<' ) ) {
			$messages[] = sprintf(
				// translators: Formidable Forms version.
				__( 'Formidable ACF requires at least Formidable Forms %s.', 'formidable-acf' ),
				$version
			);
		}

		if ( ! class_exists( 'FrmProDb', false ) || version_compare( FrmProDb::$plug_version, $version, '<' ) ) {
			$messages[] = sprintf(
				// translators: Formidable Forms Pro version.
				__( 'Formidable ACF requires at least Formidable Forms Pro %s.', 'formidable-acf' ),
				$version
			);
		}

		$version = '5.7.11';
		if ( ! function_exists( 'acf_decode_post_id' ) ) {
			$messages[] = sprintf(
				// translators: Advanced Custom Fields version.
				__( 'Formidable ACF requires at least Advanced Custom Fields or Advanced Custom Fields Pro %s.', 'formidable-acf' ),
				$version
			);
		}

		return $messages;
	}
}
