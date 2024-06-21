<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProDisplaysController {

	public static $post_type = 'frm_display'; // this is here for backward compatibility, but use FrmViewsDisplaysController::$post_type instead

	private static $first_version_without_views = '4.09'; // used by deprecated_function

	/**
	 * Handle a deprecated views function
	 *
	 * @param string $method generally passed as __METHOD__
	 * @param string $replacement the new function in formidable-views
	 */
	public static function deprecated_function( $method, $replacement = '', ...$params ) {
		if ( ! self::silently_handle_deprecation( $method ) ) {
			_deprecated_function( $method, self::$first_version_without_views, $replacement );
		}

		if ( $replacement && is_callable( $replacement ) ) {
			return $replacement( ...$params );
		}
	}

	private static function silently_handle_deprecation( $method ) {
		return in_array( $method, self::silent_deprecation_methods(), true );
	}

	private static function silent_deprecation_methods() {
		return array(
			'FrmProDisplay::getOne',
			'FrmProDisplay::getAll',
			'FrmProDisplay::get_auto_custom_display',
			'FrmProDisplaysController::get_shortcode',
		);
	}

	/**
	 * @deprecated 4.09
	 */
	public static function get_shortcode( $atts ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::get_shortcode', $atts );
	}

	/**
	 * @deprecated 4.09 But deprecated messages were not being logged.
	 * @since 6.9.1 Deprecated messages are no longer silenced.
	 */
	public static function get_before_content_for_listing_page( $view, $args ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::get_before_content_for_listing_page', $view, $args );
	}

	/**
	 * @deprecated 4.09 But deprecated messages were not being logged.
	 * @since 6.9.1 Deprecated messages are no longer silenced.
	 */
	public static function get_inner_content_for_listing_page( $view, $args ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::get_inner_content_for_listing_page', $view, $args );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function highlight_menu() {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::highlight_menu' );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function switch_form_box() {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::switch_form_box' );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function filter_forms( $query ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::filter_forms', $query );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function add_form_nav( $views ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::add_form_nav', $views );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function add_form_nav_edit( $post ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::add_form_nav_edit', $post );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function publish_button() {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::publish_button' );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function add_new_button( $form_id = 0 ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::add_new_button', $form_id );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function post_row_actions( $actions, $post ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::post_row_actions', $actions, $post );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function create_from_template( $path ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::create_from_template', $path );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function manage_columns( $columns ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::manage_columns', $columns );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function sortable_columns( $columns ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::sortable_columns', $columns );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function hidden_columns( $result ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::hidden_columns', $result );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function manage_custom_columns( $column_name, $id ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::manage_custom_columns', $column_name, $id );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function submitbox_actions() {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::submitbox_actions' );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function default_content( $content, $post ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::default_content', $content, $post );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function default_title( $title, $post ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::default_title', $title, $post );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function default_excerpt( $excerpt, $post ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::default_excerpt', $excerpt, $post );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function add_meta_boxes( $post_type ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::add_meta_boxes', $post_type );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function save_post( $post_id ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::save_post', $post_id );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function before_delete_post( $post_id ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::before_delete_post', $post_id );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function get_content( $content ) {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::get_content', $content );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function get_display_data( $view, $content = '', $entry_id = false, $extra_atts = array() ) {
		if ( ! $entry_id && ! empty( $extra_atts['return_entry_ids'] ) && is_callable( 'FrmViewsDisplaysController::get_view_entry_ids' ) ) {
			return FrmViewsDisplaysController::get_view_entry_ids( $view, $content );
		}
		return self::deprecated_function( __METHOD__ );
	}

	/**
	 * @deprecated 4.09
	 */
	public static function get_post_content() {
		return self::deprecated_function( __METHOD__, 'FrmViewsDisplaysController::get_post_content' );
	}
}
