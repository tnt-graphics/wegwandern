<?php
/**
 * Form action controller
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmAcfFormActionController
 */
class FrmAcfPostActionController {

	/**
	 * Adds ACF mapping options to the form action.
	 *
	 * @param array $args See hook `frm_pro_post_action_options`.
	 */
	public static function add_acf_options( $args ) {
		include FrmAcfAppHelper::plugin_path() . '/classes/views/post-options.php';
	}

	/**
	 * Filters the post action data before saving.
	 *
	 * @param array $post_content Post content of form action.
	 * @return array
	 */
	public static function filter_saved_value( $post_content ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $post_content['acf'] ) || empty( $post_content['acf_field_group'] ) || empty( $_POST['frm_acf_frm_fields'] ) ) {
			return $post_content;
		}

		$frm_fields     = FrmAppHelper::get_post_param( 'frm_acf_frm_fields' );
		$acf_fields     = FrmAppHelper::get_post_param( 'frm_acf_acf_fields' );
		$frm_sub_fields = FrmAppHelper::get_post_param( 'frm_acf_frm_sub_fields' );
		$acf_sub_fields = FrmAppHelper::get_post_param( 'frm_acf_acf_sub_fields' );
		$acf_field_keys = FrmAppHelper::get_post_param( 'frm_acf_field_keys' );

		if ( ! isset( $post_content['post_custom_fields'] ) ) {
			$post_content['post_custom_fields'] = array();
		}

		foreach ( $frm_fields as $index => $frm_field ) {
			if ( ! $frm_field ) {
				continue;
			}

			$mapping = array(
				'meta_name' => $acf_fields[ $index ],
				'field_id'  => $frm_field,
				'is_acf'    => 1,
			);

			if ( ! empty( $frm_sub_fields[ $frm_field ] ) && ! empty( $acf_sub_fields[ $frm_field ] ) ) {
				$mapping['child_mapping'] = array();
				foreach ( $frm_sub_fields[ $frm_field ] as $sub_index => $frm_sub_field ) {
					if ( ! $frm_sub_field || empty( $acf_sub_fields[ $frm_field ][ $sub_index ] ) ) {
						continue;
					}

					$mapping['child_mapping'][] = array(
						'meta_name' => $acf_sub_fields[ $frm_field ][ $sub_index ],
						'field_id'  => $frm_sub_field,
					);
				}
			}

			if ( ! empty( $acf_field_keys[ $index ] ) ) {
				$mapping['acf_field_key'] = $acf_field_keys[ $index ];
			}

			$post_content['post_custom_fields'][ $acf_fields[ $index ] ] = $mapping;
		}

		return $post_content;
	}

	/**
	 * Loads form action scripts.
	 */
	public static function load_scripts() {
		if ( ! FrmAppHelper::is_form_builder_page() || ! in_array( FrmAppHelper::get_param( 'frm_action' ), array( 'settings', 'update_settings' ) ) ) {
			return;
		}

		$form_id = FrmAppHelper::get_param( 'id', '', 'get', 'intval' );
		if ( ! $form_id ) {
			return;
		}

		wp_enqueue_script(
			'frm-acf',
			FrmAcfAppHelper::plugin_url() . '/js/post-action.js',
			array( 'formidable_admin' ),
			FrmAcfAppHelper::$plug_version,
			true
		);

		wp_localize_script(
			'frm-acf',
			'frmAcfData',
			FrmAcfPostActionHelper::get_global_js_data( $form_id )
		);
	}
}
