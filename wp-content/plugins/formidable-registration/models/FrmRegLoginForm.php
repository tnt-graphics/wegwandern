<?php

class FrmRegLoginForm extends FrmRegForm {

	protected $form_id_tracker = 'login_form_ids';
	protected $html_id = 'loginform';
	protected $path = '/views/login_form.php';

	private $messages = array();
	private $style = true;
	private $slide = false;
	private $layout = 'v';
	private $redirect = '';
	private $logout_redirect = '';
	private $labels = array();
	private $html_ids = array();
	private $show_elements = array();
	private $field_values = array();
	private $placeholders = array();
	private $element_classes = array();

	public function __construct( $atts ) {
		$this->init_style( $atts );

		parent::__construct( $atts );

		$this->init_messages();
		$this->init_slide( $atts );
		$this->init_layout( $atts );
		$this->init_redirect( $atts );
		$this->init_logout_redirect( $atts );
		$this->init_labels( $atts );
		$this->init_html_ids( $atts );
		$this->init_show_elements( $atts );
		$this->init_field_values( $atts );
		$this->init_placeholders( $atts );
		$this->add_form_classes();
		$this->init_element_classes( $atts );
	}

	/**
	 * Set the style property
	 * Leave style set to true if class already includes with_frm_style
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_style( $atts ) {
		$this->apply_boolean_attribute( $atts, 'style' );
	}

	/**
	 * Set the form class
	 *
	 * @since 2.0
	 * @param $atts
	 */
	protected function init_class( $atts ) {
		$this->class = isset( $atts['class'] ) ? $atts['class'] : '';

		if ( $this->style ) {
			if ( $this->class === '' ) {
				$this->class = $this->default_style_class();
			}

			if ( strpos( $this->class, 'frm_style_' ) !== false ) {
				$this->class = 'frm_forms with_frm_style ' . $this->class;
				$this->load_formidable_css();
			}
		}
	}

	/**
	 * Set the errors for the form
	 *
	 * @since 2.0
	 */
	protected function init_errors() {
		if ( is_user_logged_in() ) {
			return;
		}

		if ( isset( $_REQUEST['frmreg_error'] ) ) {
			$error_codes = explode( ',', sanitize_text_field( $_REQUEST['frmreg_error'] ) );

			foreach ( $error_codes as $error_code ) {
				$this->errors[] = $this->get_error_message( $error_code );
			}
		}
	}

	/**
	 * Set the messages property
	 *
	 * @since 2.0
	 */
	private function init_messages() {
		if ( is_user_logged_in() ) {
			return;
		}

		if ( isset( $_REQUEST['frm_message'] ) ) {
			$message_codes = explode( ',', sanitize_text_field( $_REQUEST['frm_message'] ) );

			foreach ( $message_codes as $message_code ) {
				$new_message = $this->get_success_message( $message_code );
				if ( $new_message !== '' ) {
					$this->messages[] = $new_message;
				}
			}
		}
	}

	/**
	 * Set the slide property
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_slide( $atts ) {
		$this->apply_boolean_attribute( $atts, 'slide' );
	}

	/**
	 * Set the layout property
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_layout( $atts ) {
		if ( $this->slide ) {
			$this->layout = 'h';
		}

		$this->apply_string_attribute( $atts, 'layout' );
	}

	/**
	 * Set the redirect property
	 * If current URL includes a redirect_to parameter, override redirect parameter in shortcode
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_redirect( $atts ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$this->redirect = FrmAppHelper::get_server_value( 'REQUEST_URI' );

		$current_redirect_to = $this->get_redirect_to_from_url( $this->redirect );
		if ( $current_redirect_to !== '' ) {
			$this->redirect = $current_redirect_to;
		} else {
			$this->apply_string_attribute( $atts, 'redirect' );
			$this->set_wpml_url( $this->redirect );
		}
	}

	/**
	 * Set the logout_redirect property
	 *
	 * @since 2.03
	 *
	 * @param array $atts Login form shortcode attributes.
	 */
	private function init_logout_redirect( $atts ) {
		$logout_redirect = ! empty( $atts['logout_redirect'] ) ? $atts['logout_redirect'] : get_permalink();
		if ( is_numeric( $logout_redirect ) ) {
			$logout_redirect = FrmRegAppHelper::get_page_url( $logout_redirect );
		}
		$this->logout_redirect = $logout_redirect;
	}

	/**
	 * Set the labels property
	 * Determines the labels for all fields and buttons in login form
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_labels( $atts ) {
		$this->labels = $this->default_labels();

		$conversions = array(
			'username' => 'label_username',
			'password' => 'label_password',
			'remember' => 'label_remember',
			'lost_password' => 'label_lost_password',
			'log_in' => 'label_log_in',
			'log_out' => 'label_log_out',
			'register' => 'label_register',
		);

		foreach ( $conversions as $label_key => $user_key ) {
			if ( isset( $atts[ $user_key ] ) ) {
				$this->labels[ $label_key ] = (string) $atts[ $user_key ];
			}
		}
	}

	/**
	 * Set the html_ids property
	 * Determines the html IDs for all fields and buttons in login form
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_html_ids( $atts ) {
		$defaults = array(
			'id_username'    => 'user_login',
			'id_password'    => 'user_pass',
			'id_remember'    => 'rememberme',
			'id_submit'      => 'wp-submit',
		);

		foreach ( $defaults as $key => $value ) {
			if ( isset( $atts[ $key ] ) ) {
				$this->html_ids[ $key ] = (string) $value;
			} else {
				$key = str_replace( 'id_', '', $key );
				$this->html_ids[ $key ] = $value . $this->form_number;
			}
		}
	}

	/**
	 * Set the show_elements property
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_show_elements( $atts ) {
		$this->show_elements = $this->default_show_elements();

		$conversions = array(
			'remember' => 'remember',
			'show_lost_password' => 'lost_password',
			'show_labels' => 'labels',
			'show_messages' => 'messages',
			'register_link' => 'register_link',
		);

		foreach ( $conversions as $atts_key => $object_key ) {
			if ( isset( $atts[ $atts_key ] ) ) {
				$this->show_elements[ $object_key ] = ( $atts[ $atts_key ] ) ? true : false;
			}

			// For reverse compatibility with 2.0 beta
			if ( $atts_key == 'show_lost_password' && isset( $atts['lost_password'] ) && $atts['lost_password'] ) {
				$this->show_elements[ $object_key ] = true;
			}
		}
	}

	/**
	 * Set the field_values property
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_field_values( $atts ) {
		$this->field_values = array(
			'username' => '',
			'remember' => false,
		);

		if ( isset( $atts['value_username'] ) ) {
			$this->field_values['username'] = (string) $atts['value_username'];
		}

		if ( isset( $atts['value_remember'] ) ) {
			$this->field_values['remember'] = ( $atts['value_remember'] ) ? true : false;
		}
	}

	/**
	 * Set field placeholders
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 */
	private function init_placeholders( $atts ) {
		$this->placeholders = array(
			'username' => '',
			'password' => '',
		);

		foreach ( $this->placeholders as $key => $value ) {
			if ( isset( $atts[ $key . '_placeholder' ] ) ) {
				$this->placeholders[ $key ] = (string) $atts[ $key . '_placeholder' ];
			}
		}
	}

	/**
	 * Set the classes for the various elements in login form
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 * @return void
	 */
	private function init_element_classes( $atts ) {
		$this->element_classes = array(
			'username'      => 'frm_form_field form-field login-username',
			'password'      => 'frm_form_field form-field login-password',
			'remember'      => 'frm_form_field form-field frm_none_container login-remember',
			'lost_password' => 'frm_form_field frm_html_container form-field login_lost_pw',
			'submit'        => 'frm_submit',
			'register'      => 'frm_form_field frm_html_container form-field register',
		);

		if ( $this->show_elements['labels'] === true ) {
			$label_position = ' frm_' . FrmRegAppController::get_style_option( $atts, 'position', 'top' ) . '_container';

			$this->element_classes['username'] .= $label_position;
			$this->element_classes['password'] .= $label_position;
		} else {
			$this->element_classes['username'] .= ' frm_none_container';
			$this->element_classes['password'] .= ' frm_none_container';
		}

		$use_grid_classes = 'h' === $this->layout;

		if ( $use_grid_classes ) {
			$this->element_classes['username'] .= ' frm_first frm_third';
			$this->element_classes['password'] .= ' frm_third';
			$this->element_classes['submit']   .= ' frm_third';

			if ( $this->show_elements['labels'] === true ) {
				$this->element_classes['submit'] .= ' frm_inline_submit';
			}
		}

		if ( $this->show_elements['remember'] && isset( $atts['class_remember'] ) ) {
			$this->element_classes['remember'] .= ' ' . $atts['class_remember'];
		}

		if ( $this->show_elements['lost_password'] && isset( $atts['class_lost_password'] ) ) {
			$this->element_classes['lost_password'] .= ' ' . $atts['class_lost_password'];
		}

		if ( $this->show_elements['register_link'] && ! empty( $atts['class_register'] ) ) {
			$this->element_classes['register'] .= ' ' . $atts['class_register'];
		}

		if ( $this->show_elements['remember'] && $this->show_elements['lost_password'] && ! isset( $atts['class_remember'] ) && ! isset( $atts['class_lost_password'] ) ) {
			if ( $use_grid_classes ) {
				$this->element_classes['remember']      .= ' frm_first frm_third';
				$this->element_classes['lost_password'] .= ' frm_third';
			} else {
				$this->element_classes['remember']      .= ' frm_first frm_half';
				$this->element_classes['lost_password'] .= ' frm_half';
			}
		}
	}

	/**
	 * Get the messages property
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function get_messages() {
		return $this->messages;
	}

	/**
	 * Get the style property
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function get_style() {
		return $this->style;
	}

	/**
	 * Get the slide property
	 *
	 * @since 2.0
	 *
	 * @return boolean
	 */
	public function get_slide() {
		return $this->slide;
	}

	/**
	 * Get the layout property
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_layout() {
		return $this->layout;
	}

	/**
	 * Get the redirect property
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_redirect() {
		return $this->redirect;
	}

	/**
	 * Get the logout_redirect property
	 *
	 * @since 2.03
	 *
	 * @return string The logout redirect URL.
	 */
	public function get_logout_redirect() {
		return $this->logout_redirect;
	}

	/**
	 * Get the username label
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_username_label() {
		return $this->labels['username'];
	}

	/**
	 * Get the password label
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_password_label() {
		return $this->labels['password'];
	}

	/**
	 * Get the remember label
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_remember_label() {
		return $this->labels['remember'];
	}

	/**
	 * Get the lost password label
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_lost_password_label() {
		return $this->labels['lost_password'];
	}

	/**
	 * Get the log out label
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_log_out_label() {
		return $this->labels['log_out'];
	}

	/**
	 * Get the submit button label
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_submit_label() {
		return $this->labels['log_in'];
	}

	/**
	 * Get the register user link label.
	 *
	 * @since 2.13
	 *
	 * @return string
	 */
	public function get_register_label() {
		return $this->labels['register'];
	}

	/**
	 * Get the username HTML id
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_username_id() {
		return $this->html_ids['username'];
	}

	/**
	 * Get the password HTML id
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_password_id() {
		return $this->html_ids['password'];
	}

	/**
	 * Get the remember HTML id
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_remember_id() {
		return $this->html_ids['remember'];
	}

	/**
	 * Get the submit HTML id
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_submit_id() {
		return $this->html_ids['submit'];
	}

	/**
	 * Get the show messages status
	 *
	 * @since 2.0
	 *
	 * @return boolean
	 */
	public function get_show_messages() {
		return $this->show_elements['messages'];
	}

	/**
	 * Get the show remember status
	 *
	 * @since 2.0
	 *
	 * @return boolean
	 */
	public function get_show_remember() {
		return $this->show_elements['remember'];
	}

	/**
	 * Get the show lost password status
	 *
	 * @since 2.0
	 *
	 * @return boolean
	 */
	public function get_show_lost_password_link() {
		return $this->show_elements['lost_password'];
	}

	/**
	 * Get the register link status.
	 *
	 * @since 2.13
	 *
	 * @return boolean
	 */
	public function get_show_register_link() {
		return $this->show_elements['register_link'];
	}

	/**
	 * Get the username value
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_username_value() {
		return $this->field_values['username'];
	}

	/**
	 * Get the remember me value
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_remember_value() {
		return $this->field_values['remember'];
	}

	/**
	 * Get the username placeholder
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_username_placeholder() {
		return $this->placeholders['username'];
	}

	/**
	 * Get the password placeholder
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_password_placeholder() {
		return $this->placeholders['password'];
	}

	/**
	 * Get the username class
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_username_class() {
		return $this->element_classes['username'];
	}

	/**
	 * Get the password class
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_password_class() {
		return $this->element_classes['password'];
	}

	/**
	 * Get the remember me class
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_remember_class() {
		return $this->element_classes['remember'];
	}

	/**
	 * Get the lost password link class
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_lost_password_class() {
		return $this->element_classes['lost_password'];
	}

	/**
	 * Get the register link class.
	 *
	 * @since 2.13
	 *
	 * @return string
	 */
	public function get_register_link_class() {
		return $this->element_classes['register'];
	}

	/**
	 * Get the submit button class
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_submit_class() {
		return $this->element_classes['submit'];
	}

	/**
	 * @since 2.02.02
	 */
	public function reset_password_link() {
		$url = FrmRegResetPasswordController::reset_password_page_url( 'none' );

		if ( empty( $url ) ) {
			$url = wp_lostpassword_url();
		}

		return $url;
	}

	/**
	 * Returns registration page link.
	 *
	 * @since 2.13
	 * @return string
	 */
	public function register_link() {
		return FrmRegAppHelper::registration_page_url();
	}

	/**
	 * Load the login form JS
	 *
	 * @since 2.0
	 */
	public function load_login_form_js() {
		if ( ! $this->slide ) {
			return;
		}

		$handle  = 'frmreg_login_form_js';
		$source  = FrmRegAppHelper::plugin_url() . '/js/login_form.js';
		$version = FrmRegAppHelper::plugin_version();
		wp_enqueue_script( $handle, $source, array( 'jquery' ), $version );
	}

	/**
	 * Add form classes
	 *
	 * @since 2.0
	 */
	private function add_form_classes() {
		$this->class .= ' frm_login_form';

		if ( $this->layout == 'h' ) {
			$this->class .= ' frm_inline_login';
		}

		if ( $this->show_elements['labels'] === false ) {
			$this->class .= ' frm_no_labels';
		}

		if ( $this->slide ) {
			$this->class .= ' frm_slide';
		}
	}

	/**
	 * Apply a boolean attribute
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 * @param string $key
	 */
	private function apply_boolean_attribute( $atts, $key ) {
		if ( isset( $atts[ $key ] ) ) {
			$this->{$key} = ( $atts[ $key ] ) ? true : false;
		}
	}

	/**
	 * Set specific property to user-defined attribute, if it is set
	 *
	 * @since 2.0
	 *
	 * @param array $atts
	 * @param string $key
	 */
	private function apply_string_attribute( $atts, $key ) {
		if ( isset( $atts[ $key ] ) ) {
			$this->{$key} = (string) $atts[ $key ];
		}
	}

	/**
	 * Get the error message from the code
	 *
	 * @since 2.0
	 *
	 * @param string $error_code
	 *
	 * @return string
	 */
	protected function get_error_message( $error_code ) {

		if ( $error_code === 'empty_username' ) {
			$message = __( 'The username field is empty.', 'frmreg' );

		} elseif ( $error_code === 'empty_password' ) {
			$message = __( 'The password field is empty.', 'frmreg' );

		} elseif ( $error_code === 'invalid_username' || $error_code === 'incorrect_password' ) {
			$message = sprintf( __( 'Invalid username or password. %1$sLost your password%2$s?', 'frmreg' ), '<a href="' . wp_lostpassword_url() . '">', '</a>' );

		} elseif ( $error_code === 'invalid_key' ) {
			$message = FrmRegMessagesHelper::activation_invalid_key_message();

		} elseif ( $this->is_resend_activation_error( $error_code ) ) {
			$user_id = (int) str_replace( 'resend_activation_', '', $error_code );
			$message = FrmRegMessagesHelper::resend_activation_message( $user_id );

		} elseif ( isset( $_GET['frm_message_text'] ) && $_GET['frm_message_text'] !== '' ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$message = urldecode( $_GET['frm_message_text'] );
			$message = FrmAppHelper::kses( $message, 'all' );
		} elseif ( FrmRegSessionErrorController::get_error_from_session( $error_code ) ) {
			$message = FrmRegSessionErrorController::get_error_from_session( $error_code );
		} else {
			$message = __( 'An error occurred. Please try again.', 'frmreg' );
		}

		return apply_filters( 'frmreg_login_error', $message );
	}

	/**
	 * Check if the current error is a resend activation error
	 * This error occurs if pending email confirmation user tries to log in
	 *
	 * @param string $error_code
	 *
	 * @return bool
	 */
	private function is_resend_activation_error( $error_code ) {
		$is_resend_activation_error = false;

		if ( FrmRegMessagesHelper::is_resend_activation_link_code( $error_code ) ) {
			$user_id = str_replace( 'resend_activation_', '', $error_code );
			if ( is_numeric( $user_id ) ) {
				$is_resend_activation_error = true;
			}
		}

		return $is_resend_activation_error;
	}

	/**
	 * Get a success message from a message code
	 *
	 * @since 2.0
	 *
	 * @param string $message_code
	 *
	 * @return string
	 */
	private function get_success_message( $message_code ) {
		switch ( $message_code ) {
			case 'activation_sent':
				$message = FrmRegMessagesHelper::activation_sent_message();
				break;

			case 'check_email':
				$message = __( 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox.', 'frmreg' );
				break;

			case 'pw_changed':
				$message = __( 'Your password has been reset.', 'frmreg' );
				break;

			default:
				$message = '';
		}

		return $message;
	}

	/**
	 * Get redirect_to parameter from URL string
	 *
	 * @since 2.0
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	private function get_redirect_to_from_url( $url ) {
		// split the URL at the first ? to separate the query args
		$parts = explode( '?', $url, 2 );

		// If no query is set, stop here
		if ( ! isset( $parts[1] ) ) {
			return '';
		}

		parse_str( $parts[1], $query );
		if ( ! empty( $query['redirect_to'] ) ) {
			$redirect_to = $query['redirect_to'];
		} else {
			$redirect_to = '';
		}

		return $redirect_to;
	}

	/**
	 * Add the language parameter to the redirect url after login.
	 * Note: As of Feb 2019, WPML does not switch relative urls.
	 *
	 * @since 2.03
	 */
	private function set_wpml_url( &$url ) {
		$lang = apply_filters( 'wpml_current_language', null );

		if ( ! empty( $lang ) ) {
			$url = apply_filters( 'wpml_permalink', $url, $lang, true );
		}
	}

	/**
	 * Get the default label values
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	private function default_labels() {
		$defaults = array(
			'username' => __( 'Username', 'frmreg' ),
			'password' => __( 'Password', 'frmreg' ),
			'remember' => __( 'Remember Me', 'frmreg' ),
			'lost_password' => __( 'Forgot your password?', 'frmreg' ),
			'log_in' => __( 'Login', 'frmreg' ),
			'log_out' => __( 'Logout', 'frmreg' ),
			'register' => __( 'New user? Click here to register', 'frmreg' ),
		);

		if ( $this->slide ) {
			$defaults['username'] = $defaults['password'] = '';
		}

		return $defaults;
	}

	/**
	 * Get the default show_elements
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	private function default_show_elements() {
		$defaults = array(
			'remember' => true,
			'lost_password' => false,
			'labels' => true,
			'messages' => true,
			'register_link' => false,
		);

		if ( $this->slide ) {
			$defaults['remember']      = false;
			$defaults['register_link'] = false;
		}

		if ( $this->labels['username'] == '' && $this->labels['password'] == '' ) {
			$defaults['labels'] = false;
		}

		return $defaults;
	}

	/**
	 * Outputs the login button html.
	 *
	 * @since 2.13
	 * @return void
	 */
	public function get_login_button_html() {
		if ( $this->get_layout() === 'v' ) {
			do_action( 'login_form' );
		}
		?>
		<div class="<?php echo esc_attr( $this->get_submit_class() ); ?>">
			<input type="submit" name="wp-submit" id="<?php echo esc_attr( $this->get_submit_id() ); ?>" value="<?php echo esc_attr( $this->get_submit_label() ); ?>" />
		</div>
		<div style="clear:both;"></div>

		<?php
		if ( $this->get_layout() === 'h' ) {
			do_action( 'login_form' );
		}
	}

	/**
	 * Initializes the fields label position class.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	protected function init_label_class() {
		// Do not bother calling FrmRegAppController::get_style_option since we will be calling it in init_element_classes().
	}

	/**
	 * @since 3.0.1
	 *
	 * @return string
	 */
	public function get_form_action_url() {
		if ( FrmRegAppHelper::supports_alternative_login_url() ) {
			return add_query_arg( 'action', 'frm_login', FrmAppHelper::get_ajax_url() );
		}
		return site_url( 'wp-login.php', 'login_post' );
	}

	/**
	 * Load the login form CSS.
	 *
	 * @since 2.0
	 * @deprecated 2.10
	 *
	 * @return void
	 */
	public function load_login_form_css() {
		_deprecated_function( __FUNCTION__, '2.10' );
	}
}
