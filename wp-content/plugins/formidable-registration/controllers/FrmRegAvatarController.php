<?php
/**
 * @since 2.01
 */
class FrmRegAvatarController {

	/**
	 * Get the avatar
	 *
	 * @since 2.01
	 *
	 * @param string $avatar
	 * @param int|string|object $id_or_email
	 * @param string $size
	 * @param string $default
	 * @param bool $alt
	 * @param array $args
	 *
	 * @return string
	 */
	public static function get_avatar( $avatar, $id_or_email, $size = '96', $default = '', $alt = false, $args = array() ) {
		$field_obj = FrmFieldFactory::get_field_type( 'file' );
		// Don't override the default, and stop here if Pro is not installed.
		if ( ! empty( $args['force_default'] ) || ! is_callable( array( $field_obj, 'get_displayed_file_html' ) ) ) {
			return $avatar;
		}

		$image_args = array(
			'size' => $size,
			'alt'  => $alt,
		);
		$avatar = new FrmRegAvatar( $id_or_email, $avatar, $image_args );

		return $avatar->get_html();
	}
}
