<?php

class FrmMlcmpUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'Mailchimp';
	public $download_id = 170655;
	public $version = '2.09';

	public function __construct() {
		$this->plugin_file = dirname( __DIR__ ) . '/formidable-mailchimp.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmMlcmpUpdate();
	}
}
