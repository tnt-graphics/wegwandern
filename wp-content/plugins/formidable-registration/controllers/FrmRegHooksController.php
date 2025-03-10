<?php

class FrmRegHooksController {

	// TODO: break this up so hooks are loaded on specific pages
	public static function load_hooks() {
		if ( ! FrmRegAppHelper::is_formidable_compatible() ) {
			self::load_basic_admin_hooks();

			return;
		}

		// FrmRegAppController
		// This action triggers only when CSS is saved on Formidable Styles page
		add_action( 'frm_include_front_css', 'FrmRegAppController::add_login_form_css' );

		// FrmRegActionController
		add_action( 'frm_registered_form_actions', 'FrmRegActionController::register_actions' );
		add_filter( 'frm_action_triggers', 'FrmRegActionController::add_registration_trigger' );
		add_filter( 'frm_email_action_options', 'FrmRegActionController::add_trigger_to_action' );
		add_filter( 'frm_twilio_action_options', 'FrmRegActionController::add_trigger_to_action' );
		add_filter( 'frm_mailchimp_action_options', 'FrmRegActionController::add_trigger_to_action' );
		add_filter( 'frm_api_action_options', 'FrmRegActionController::add_trigger_to_action' );
		add_filter( 'frm_pro_repeater_action_support', 'FrmRegActionController::add_repeater_actions_support' );

		// FrmRegUserController
		add_action( 'frm_trigger_register_action', 'FrmRegUserController::register_user', 10, 3 );
		add_action( 'wp_ajax_frm_payments_paypal_ipn', 'FrmRegUserController::set_allow_edit', 5 );
		add_action( 'wp_ajax_nopriv_frm_payments_paypal_ipn', 'FrmRegUserController::set_allow_edit', 5 );
		add_action( 'frm_after_update_field', 'FrmRegUserController::update_user_metas' );

		// FrmRegProfileController
		add_action( 'show_user_profile', 'FrmRegProfileController::show_user_meta', 200 );
		add_action( 'edit_user_profile', 'FrmRegProfileController::show_user_meta', 200 );

		// FrmRegAvatarController
		add_filter( 'get_avatar', 'FrmRegAvatarController::get_avatar', 10, 6 );

		// FrmRegEntry
		add_action( 'frm_show_new_entry_page', 'FrmRegEntry::maybe_force_new_entry', 20, 2 );
		add_filter( 'frm_get_default_value', 'FrmRegEntry::reset_user_id_for_user_creation', 10, 2 );
		add_filter( 'frm_setup_new_fields_vars', 'FrmRegEntry::reset_user_id_for_back_user_creation', 20, 2 );
		add_filter( 'frm_setup_edit_fields_vars', 'FrmRegEntry::check_updated_user_meta', 10, 3 );
		add_action( 'frm_after_create_entry', 'FrmRegEntry::maybe_hash_password', 40, 2 );
		add_action( 'frm_after_create_entry', 'FrmRegEntry::maybe_update_entry_user_id', 40 );
		add_action( 'frm_after_update_entry', 'FrmRegEntry::maybe_hash_password', 40, 2 );
		add_action( 'frm_after_update_entry', 'FrmRegEntry::maybe_update_entry_user_id', 40 );

		new FrmRegEntryController();

		// FrmRegModerationController
		add_filter( 'the_content', 'FrmRegModerationController::print_activation_messages', 21 );

		// FrmRegWidgetController
		add_action( 'widgets_init', 'FrmRegWidgetController::register_widgets' );
		add_filter( 'widget_text', 'do_shortcode' );

		// FrmRegRegistrationPageController
		add_action( 'login_form_register', 'FrmRegRegistrationPageController::redirect_to_custom_registration_page' );

		// FrmRegLoginController
		add_action( 'login_form_login', 'FrmRegLoginController::redirect_to_custom_login' );
		add_filter( 'authenticate', 'FrmRegLoginController::redirect_at_authenticate_when_error', 999, 1 );
		//add_filter( 'login_redirect', 'FrmRegLoginController::redirect_after_login', 10, 3 );
		add_filter( 'login_message', 'FrmRegLoginController::print_login_messages', 20 );
		add_filter( 'wp_authenticate_user', 'FrmRegLoginController::prevent_pending_login', 10, 2 );

		// FrmRegResetPasswordController
		add_action( 'login_form_lostpassword', 'FrmRegResetPasswordController::redirect_to_custom_lost_password' );
		add_action( 'login_form_rp', 'FrmRegResetPasswordController::redirect_to_custom_reset_password' );
		add_action( 'login_form_resetpass', 'FrmRegResetPasswordController::redirect_to_custom_reset_password' );
		add_action( 'login_form_lostpassword', 'FrmRegResetPasswordController::do_lost_password' );
		add_action( 'login_form_rp', 'FrmRegResetPasswordController::do_reset_password' );
		add_action( 'login_form_resetpass', 'FrmRegResetPasswordController::do_reset_password' );
		add_filter( 'allow_password_reset', 'FrmRegResetPasswordController::prevent_password_reset', 10, 2 );

		add_action( 'wp', 'FrmRegSessionErrorController::maybe_start_session' );

		// Shortcodes
		add_shortcode( 'frm-login', 'FrmRegShortcodesController::do_login_form_shortcode' );
		add_shortcode( 'frm-reset-password', 'FrmRegShortcodesController::do_reset_password_shortcode' );
		add_shortcode( 'frm-primary-blog', 'FrmRegShortcodesController::do_primary_blog_shortcode' );

		// Only support frm-set-password-link in email actions.
		// This is for security. Contributors can use this shortcode in posts/pages otherwise.
		add_action( 'frm_trigger_email_action', function () {
			add_shortcode( 'frm-set-password-link', 'FrmRegShortcodesController::set_password_link' );
		}, 1 );
		add_action( 'frm_trigger_email_action', function () {
			remove_shortcode( 'frm-set-password-link' );
		}, 99 );

		self::load_admin_hooks();
	}

	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		self::load_basic_admin_hooks();

		add_action( 'admin_init', 'FrmRegAppController::initialize', 0 );
		add_action( 'admin_init', 'FrmRegAppHelper::enqueue_admin_js', 1 );
		add_action( 'admin_init', 'FrmRegAppHelper::enqueue_global_js', 11 );

		new FrmRegGlobalSettingsController();

		// Ajax functions
		add_action( 'wp_ajax_frmreg_install', 'FrmRegAppController::initialize' );
		add_action( 'wp_ajax_resend_activation_link', 'FrmRegEmailController::resend_activation_email' );
		add_action( 'wp_ajax_nopriv_resend_activation_link', 'FrmRegEmailController::resend_activation_email' );
		add_action( 'wp_ajax_frm_activate_user', 'FrmRegModerationController::do_activation_link' );
		add_action( 'wp_ajax_nopriv_frm_activate_user', 'FrmRegModerationController::do_activation_link' );

		// FrmRegActionController
		add_filter( 'frm_form_email_action_settings', 'FrmRegActionController::customize_new_email_action' );
		add_action( 'wp_ajax_frm_add_user_meta_row', 'FrmRegActionController::add_user_meta_row' );
		add_filter( 'frm_form_options_before_update', 'FrmRegActionController::before_update_form', 15, 2 );
		add_action( 'frm_after_import_view', 'FrmRegActionController::migrate_action_after_import', 10, 2 );

		// FrmRegActionHelper
		add_filter( 'frm_before_save_register_action', 'FrmRegActionHelper::filter_user_meta', 10, 1 );

		// FrmRegShortcodesController
		add_filter( 'frm_popup_shortcodes', 'FrmRegShortcodesController::add_login_form_to_sc_builder', 11 );
		add_filter( 'frm_sc_popup_opts', 'FrmRegShortcodesController::get_login_form_sc_opts', 11, 2 );
		add_action( 'admin_enqueue_scripts', 'FrmRegAppController::enqueue_assets' );
		add_filter( 'frm_before_save_email_action', 'FrmRegShortcodesController::before_save_email_action' );

		if ( FrmAppHelper::doing_ajax() ) {
			add_action( 'wp_ajax_nopriv_frm_login', 'FrmRegLoginController::handle_alternative_login' );
		}
	}

	/**
	 * Load the basic admin hooks to allow updating and display notices
	 *
	 * @since 2.0
	 */
	private static function load_basic_admin_hooks() {
		if ( is_admin() ) {
			add_action( 'admin_init', 'FrmRegAppController::include_updater', 1 );
			add_action( 'admin_notices', 'FrmRegAppController::display_admin_notices' );
			add_action( 'after_plugin_row_formidable-registration/formidable-registration.php', 'FrmRegAppController::min_version_notice' );
		}
	}
}
