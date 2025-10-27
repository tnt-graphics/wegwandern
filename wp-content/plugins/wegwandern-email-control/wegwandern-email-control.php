<?php
/**
 * Plugin Name: Wegwandern Email Control
 * Plugin URI: https://www.pitsolutions.ch/
 * Description: Kontrolliert WordPress Core E-Mail-Benachrichtigungen für Benutzerregistrierung, Kontoänderungen und Kommentare
 * Version: 1.0.0
 * Author: PITS
 * Author URI: https://www.pitsolutions.ch/
 * Text Domain: wegwandern-email-control
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Wegwandern_Email_Control' ) ) :

	class Wegwandern_Email_Control {

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '1.0.0';

		/**
		 * Plugin settings
		 *
		 * @var array
		 */
		private $settings = array();

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->define_constants();
			$this->load_settings();
			$this->init_hooks();
		}

		/**
		 * Define plugin constants
		 */
		private function define_constants() {
			define( 'WEGW_EMAIL_CONTROL_VERSION', $this->version );
			define( 'WEGW_EMAIL_CONTROL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			define( 'WEGW_EMAIL_CONTROL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Load plugin settings
		 */
		private function load_settings() {
			$defaults = array(
				'disable_registration_emails'       => true,
				'disable_profile_change_emails'     => true,
				'disable_comment_notifications'     => true,
				'only_gipfelbuch_users'             => false,
				'disable_password_reset_emails'     => false,
			);

			$this->settings = get_option( 'wegw_email_control_settings', $defaults );
		}

		/**
		 * Initialize hooks
		 */
		private function init_hooks() {
			// Add admin menu
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// Apply email control filters based on settings
			if ( $this->get_setting( 'disable_registration_emails' ) ) {
				$this->disable_registration_emails();
			}

			if ( $this->get_setting( 'disable_profile_change_emails' ) ) {
				$this->disable_profile_change_emails();
			}

			if ( $this->get_setting( 'disable_comment_notifications' ) ) {
				$this->disable_comment_notifications();
			}

			if ( $this->get_setting( 'disable_password_reset_emails' ) ) {
				$this->disable_password_reset_emails();
			}
		}

		/**
		 * Get a specific setting
		 *
		 * @param string $key Setting key
		 * @return mixed Setting value
		 */
		private function get_setting( $key ) {
			return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : false;
		}

		/**
		 * Check if user is a Gipfelbuch user
		 *
		 * @param int $user_id User ID
		 * @return bool
		 */
		private function is_gipfelbuch_user( $user_id ) {
			$user = get_user_by( 'ID', $user_id );
			if ( ! $user ) {
				return false;
			}

			return in_array( 'summit-book-user', (array) $user->roles );
		}

		/**
		 * Check if we should disable emails based on settings
		 *
		 * @param int $user_id User ID
		 * @return bool
		 */
		private function should_disable_email( $user_id ) {
			$only_gipfelbuch = $this->get_setting( 'only_gipfelbuch_users' );

			if ( $only_gipfelbuch ) {
				return $this->is_gipfelbuch_user( $user_id );
			}

			return true; // Disable for all users
		}

		/**
		 * Disable user registration emails
		 */
		private function disable_registration_emails() {
			// Disable admin notification
			add_filter( 'wp_new_user_notification_email_admin', array( $this, 'filter_new_user_admin_email' ), 10, 3 );

			// Disable user notification
			add_filter( 'wp_new_user_notification_email', array( $this, 'filter_new_user_email' ), 10, 3 );
		}

		/**
		 * Filter new user admin notification
		 */
		public function filter_new_user_admin_email( $email, $user, $blogname ) {
			$user_obj = get_user_by( 'login', $user );
			
			if ( $user_obj && $this->should_disable_email( $user_obj->ID ) ) {
				return false;
			}

			return $email;
		}

		/**
		 * Filter new user notification
		 */
		public function filter_new_user_email( $email, $user, $blogname ) {
			$user_obj = get_user_by( 'login', $user );
			
			if ( $user_obj && $this->should_disable_email( $user_obj->ID ) ) {
				return false;
			}

			return $email;
		}

		/**
		 * Disable profile change emails
		 */
		private function disable_profile_change_emails() {
			add_filter( 'send_email_change_email', array( $this, 'filter_email_change' ), 10, 3 );
			add_filter( 'send_password_change_email', array( $this, 'filter_password_change' ), 10, 3 );
		}

		/**
		 * Filter email change notification
		 */
		public function filter_email_change( $send, $user, $userdata ) {
			if ( isset( $user['ID'] ) && $this->should_disable_email( $user['ID'] ) ) {
				return false;
			}

			return $send;
		}

		/**
		 * Filter password change notification
		 */
		public function filter_password_change( $send, $user, $userdata ) {
			if ( isset( $user['ID'] ) && $this->should_disable_email( $user['ID'] ) ) {
				return false;
			}

			return $send;
		}

		/**
		 * Disable password reset emails
		 */
		private function disable_password_reset_emails() {
			add_filter( 'send_password_reset_email', '__return_false' );
		}

		/**
		 * Disable comment notifications
		 */
		private function disable_comment_notifications() {
			add_filter( 'notify_moderator', array( $this, 'filter_comment_moderator' ), 10, 2 );
			add_filter( 'notify_post_author', array( $this, 'filter_comment_post_author' ), 10, 2 );
		}

		/**
		 * Filter comment moderator notification
		 */
		public function filter_comment_moderator( $maybe_notify, $comment_id ) {
			$comment = get_comment( $comment_id );
			$post = get_post( $comment->comment_post_ID );

			$only_gipfelbuch = $this->get_setting( 'only_gipfelbuch_users' );

			if ( $only_gipfelbuch ) {
				// Only disable for Gipfelbuch post types
				$gipfelbuch_post_types = array( 'community_beitrag', 'pinnwand_eintrag', 'bewertung' );
				if ( in_array( $post->post_type, $gipfelbuch_post_types ) ) {
					return false;
				}
			} else {
				// Disable for all post types
				return false;
			}

			return $maybe_notify;
		}

		/**
		 * Filter comment post author notification
		 */
		public function filter_comment_post_author( $maybe_notify, $comment_id ) {
			$comment = get_comment( $comment_id );
			$post = get_post( $comment->comment_post_ID );

			$only_gipfelbuch = $this->get_setting( 'only_gipfelbuch_users' );

			if ( $only_gipfelbuch ) {
				// Only disable for Gipfelbuch post types
				$gipfelbuch_post_types = array( 'community_beitrag', 'pinnwand_eintrag', 'bewertung' );
				if ( in_array( $post->post_type, $gipfelbuch_post_types ) ) {
					return false;
				}
			} else {
				// Disable for all post types
				return false;
			}

			return $maybe_notify;
		}

		/**
		 * Add admin menu
		 */
		public function add_admin_menu() {
			add_options_page(
				__( 'Email Control', 'wegwandern-email-control' ),
				__( 'Email Control', 'wegwandern-email-control' ),
				'manage_options',
				'wegwandern-email-control',
				array( $this, 'render_settings_page' )
			);
		}

		/**
		 * Register settings
		 */
		public function register_settings() {
			register_setting( 'wegw_email_control_settings_group', 'wegw_email_control_settings' );

			add_settings_section(
				'wegw_email_control_main_section',
				__( 'E-Mail Benachrichtigungen Einstellungen', 'wegwandern-email-control' ),
				array( $this, 'render_section_description' ),
				'wegwandern-email-control'
			);

			add_settings_field(
				'disable_registration_emails',
				__( 'Registrierungs-E-Mails deaktivieren', 'wegwandern-email-control' ),
				array( $this, 'render_checkbox_field' ),
				'wegwandern-email-control',
				'wegw_email_control_main_section',
				array( 'field' => 'disable_registration_emails', 'label' => __( 'Keine E-Mails bei Neuregistrierung senden', 'wegwandern-email-control' ) )
			);

			add_settings_field(
				'disable_profile_change_emails',
				__( 'Kontoänderungs-E-Mails deaktivieren', 'wegwandern-email-control' ),
				array( $this, 'render_checkbox_field' ),
				'wegwandern-email-control',
				'wegw_email_control_main_section',
				array( 'field' => 'disable_profile_change_emails', 'label' => __( 'Keine E-Mails bei Änderungen der Kontodaten senden', 'wegwandern-email-control' ) )
			);

			add_settings_field(
				'disable_comment_notifications',
				__( 'Kommentar-Benachrichtigungen deaktivieren', 'wegwandern-email-control' ),
				array( $this, 'render_checkbox_field' ),
				'wegwandern-email-control',
				'wegw_email_control_main_section',
				array( 'field' => 'disable_comment_notifications', 'label' => __( 'Keine E-Mails bei neuen Kommentaren senden', 'wegwandern-email-control' ) )
			);

			add_settings_field(
				'disable_password_reset_emails',
				__( 'Passwort-Reset-Benachrichtigungen deaktivieren', 'wegwandern-email-control' ),
				array( $this, 'render_checkbox_field' ),
				'wegwandern-email-control',
				'wegw_email_control_main_section',
				array( 'field' => 'disable_password_reset_emails', 'label' => __( 'Keine Admin-Benachrichtigung bei Passwort-Reset', 'wegwandern-email-control' ) )
			);

			add_settings_field(
				'only_gipfelbuch_users',
				__( 'Nur für Gipfelbuch-Benutzer', 'wegwandern-email-control' ),
				array( $this, 'render_checkbox_field' ),
				'wegwandern-email-control',
				'wegw_email_control_main_section',
				array( 'field' => 'only_gipfelbuch_users', 'label' => __( 'E-Mails nur für Gipfelbuch-Benutzer deaktivieren (andere Benutzer erhalten weiterhin E-Mails)', 'wegwandern-email-control' ) )
			);
		}

		/**
		 * Render section description
		 */
		public function render_section_description() {
			echo '<p>' . __( 'Steuern Sie, welche WordPress Core E-Mail-Benachrichtigungen gesendet werden sollen.', 'wegwandern-email-control' ) . '</p>';
		}

		/**
		 * Render checkbox field
		 */
		public function render_checkbox_field( $args ) {
			$settings = get_option( 'wegw_email_control_settings' );
			$field = $args['field'];
			$label = $args['label'];
			$checked = isset( $settings[ $field ] ) && $settings[ $field ] ? 'checked' : '';
			?>
			<label>
				<input type="checkbox" name="wegw_email_control_settings[<?php echo esc_attr( $field ); ?>]" value="1" <?php echo $checked; ?>>
				<?php echo esc_html( $label ); ?>
			</label>
			<?php
		}

		/**
		 * Render settings page
		 */
		public function render_settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Show success message if settings saved
			if ( isset( $_GET['settings-updated'] ) ) {
				add_settings_error(
					'wegw_email_control_messages',
					'wegw_email_control_message',
					__( 'Einstellungen gespeichert', 'wegwandern-email-control' ),
					'updated'
				);
			}

			settings_errors( 'wegw_email_control_messages' );
			?>
			<div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wegw_email_control_settings_group' );
					do_settings_sections( 'wegwandern-email-control' );
					submit_button( __( 'Einstellungen speichern', 'wegwandern-email-control' ) );
					?>
				</form>

				<hr>

				<h2><?php _e( 'Übersicht der E-Mail-Einstellungen', 'wegwandern-email-control' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php _e( 'E-Mail Typ', 'wegwandern-email-control' ); ?></th>
							<th><?php _e( 'Status', 'wegwandern-email-control' ); ?></th>
							<th><?php _e( 'Beschreibung', 'wegwandern-email-control' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php _e( 'Benutzerregistrierung', 'wegwandern-email-control' ); ?></td>
							<td>
								<?php echo $this->get_setting( 'disable_registration_emails' ) ? '<span style="color: red;">❌ ' . __( 'Deaktiviert', 'wegwandern-email-control' ) . '</span>' : '<span style="color: green;">✅ ' . __( 'Aktiv', 'wegwandern-email-control' ) . '</span>'; ?>
							</td>
							<td><?php _e( 'E-Mails an Admin und Benutzer bei Neuregistrierung', 'wegwandern-email-control' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Kontoänderungen', 'wegwandern-email-control' ); ?></td>
							<td>
								<?php echo $this->get_setting( 'disable_profile_change_emails' ) ? '<span style="color: red;">❌ ' . __( 'Deaktiviert', 'wegwandern-email-control' ) . '</span>' : '<span style="color: green;">✅ ' . __( 'Aktiv', 'wegwandern-email-control' ) . '</span>'; ?>
							</td>
							<td><?php _e( 'E-Mails bei Änderung von E-Mail-Adresse oder Passwort', 'wegwandern-email-control' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Kommentare', 'wegwandern-email-control' ); ?></td>
							<td>
								<?php echo $this->get_setting( 'disable_comment_notifications' ) ? '<span style="color: red;">❌ ' . __( 'Deaktiviert', 'wegwandern-email-control' ) . '</span>' : '<span style="color: green;">✅ ' . __( 'Aktiv', 'wegwandern-email-control' ) . '</span>'; ?>
							</td>
							<td><?php _e( 'E-Mails an Moderator und Post-Autor bei neuen Kommentaren', 'wegwandern-email-control' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Passwort-Reset', 'wegwandern-email-control' ); ?></td>
							<td>
								<?php echo $this->get_setting( 'disable_password_reset_emails' ) ? '<span style="color: red;">❌ ' . __( 'Deaktiviert', 'wegwandern-email-control' ) . '</span>' : '<span style="color: green;">✅ ' . __( 'Aktiv', 'wegwandern-email-control' ) . '</span>'; ?>
							</td>
							<td><?php _e( 'Admin-Benachrichtigung bei Passwort-Reset', 'wegwandern-email-control' ); ?></td>
						</tr>
					</tbody>
				</table>

				<hr>

				<h2><?php _e( 'Filter-Modus', 'wegwandern-email-control' ); ?></h2>
				<p>
					<strong><?php _e( 'Aktueller Modus:', 'wegwandern-email-control' ); ?></strong>
					<?php
					if ( $this->get_setting( 'only_gipfelbuch_users' ) ) {
						echo '<span style="color: orange;">⚠️ ' . __( 'Nur Gipfelbuch-Benutzer', 'wegwandern-email-control' ) . '</span>';
					} else {
						echo '<span style="color: blue;">🌐 ' . __( 'Alle Benutzer', 'wegwandern-email-control' ) . '</span>';
					}
					?>
				</p>
			</div>
			<?php
		}
	}

	// Initialize the plugin
	function wegwandern_email_control() {
		return new Wegwandern_Email_Control();
	}

	// Start the plugin
	wegwandern_email_control();

endif;

