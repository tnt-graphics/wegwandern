<?php
/**
 * Addon update class
 *
 * @package FrmAcf
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmAcfUpdate
 */
class FrmAcfUpdate extends FrmAddon {

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	public $plugin_name = 'ACF Forms';

	/**
	 * Download ID.
	 *
	 * @var int
	 */
	public $download_id = 28158728;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->plugin_file = FrmAcfAppHelper::plugin_file();
		$this->version     = FrmAcfAppHelper::$plug_version;
		parent::__construct();
	}
}
