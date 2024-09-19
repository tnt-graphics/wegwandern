<?php
/**
 * Hooks controller
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmAcfHooksController
 */
class FrmAcfHooksController {

	/**
	 * Adds this class to hook controllers list.
	 *
	 * @param array $controllers Hooks controllers.
	 * @return array
	 */
	public static function add_hooks_controller( $controllers ) {
		if ( FrmAcfAppHelper::get_incompatible_messages() ) {
			self::load_incompatible_hooks();
			return $controllers;
		}

		$controllers[] = __CLASS__;
		return $controllers;
	}

	/**
	 * Loads hooks when this plugin isn't safe to run.
	 */
	private static function load_incompatible_hooks() {
		self::load_translation();

		add_action( 'admin_notices', array( 'FrmAcfAppController', 'show_incompatible_notice' ) );
		add_filter( 'frm_message_list', array( 'FrmAcfAppController', 'add_incompatible_notice_to_message_list' ) );
	}

	/**
	 * Loads translation.
	 */
	private static function load_translation() {
		add_action( 'plugins_loaded', array( 'FrmAcfAppController', 'init_translation' ) );
	}

	/**
	 * Loads plugin hooks.
	 */
	public static function load_hooks() {
		self::load_translation();

		add_filter( 'acf/pre_load_metadata', array( 'FrmAcfSyncController', 'acf_get_metadata' ), 10, 4 );
		add_filter( 'acf/update_value', array( 'FrmAcfSyncController', 'convert_acf_update_value' ), 10, 4 );
		add_filter( 'acf/pre_update_metadata', array( 'FrmAcfSyncController', 'update_acf_repeater' ), 10, 5 );
		add_filter( 'frm_before_create_post', array( 'FrmAcfSyncController', 'update_acf_field_key_in_post_meta' ), 10, 2 );
	}

	/**
	 * These hooks are only needed for front-end forms.
	 */
	public static function load_form_hooks() {
	}

	/**
	 * These hooks only load during ajax request.
	 */
	public static function load_ajax_hooks() {
	}

	/**
	 * These hooks only load in the admin area.
	 */
	public static function load_admin_hooks() {
		add_action( 'admin_init', array( 'FrmAcfAppController', 'include_updater' ) );

		add_action( 'admin_enqueue_scripts', array( 'FrmAcfPostActionController', 'load_scripts' ) );
		add_action( 'frm_pro_post_action_options', array( 'FrmAcfPostActionController', 'add_acf_options' ) );
		add_filter( 'frm_before_save_wppost_action', array( 'FrmAcfPostActionController', 'filter_saved_value' ), 15 );

		add_filter(
			'acf/pre_render_fields',
			function ( $fields ) {
				remove_action( 'pre_get_posts', 'FrmProFileField::filter_media_library', 99 );
				return $fields;
			}
		);

		add_action(
			'acf/render_fields',
			function () {
				add_action( 'pre_get_posts', 'FrmProFileField::filter_media_library', 99 );
			}
		);
	}
}
