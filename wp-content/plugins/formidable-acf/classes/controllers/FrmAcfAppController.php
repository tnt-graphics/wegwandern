<?php
/**
 * App controller
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmAcfAppController
 */
class FrmAcfAppController {

	/**
	 * Shows the incompatible notice.
	 */
	public static function show_incompatible_notice() {
		$messages = FrmAcfAppHelper::get_incompatible_messages();
		if ( ! $messages ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<?php echo '<p>' . FrmAppHelper::kses( implode( '</p><p>', $messages ), array( 'a', 'br', 'span', 'p' ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
	}

	/**
	 * Adds incompatible notice messages to the Frm message list.
	 *
	 * @param array $messages Message list.
	 * @return array
	 */
	public static function add_incompatible_notice_to_message_list( $messages ) {
		return FrmAcfAppHelper::get_incompatible_messages() + $messages;
	}

	/**
	 * Initializes plugin translation.
	 */
	public static function init_translation() {
		load_plugin_textdomain( 'formidable-acf', false, FrmAcfAppHelper::plugin_folder() . '/languages/' );
	}

	/**
	 * Includes addon updater.
	 */
	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			FrmAcfUpdate::load_hooks();
		}
	}
}
