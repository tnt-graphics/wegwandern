<?php
/*
 Plugin Name: WP Media folder
 Plugin URI: http://www.joomunited.com
 Description: WP media Folder is a WordPress plugin that enhance the WordPress media manager by adding a folder manager inside.
 Author: Joomunited
 Version: 6.2.4
 Update URI: https://www.joomunited.com/juupdater_files/wp-media-folder.json
 Author URI: http://www.joomunited.com
 Text Domain: wpmf
 Domain Path: /languages
 Licence : GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 Copyright : Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 */
// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');

// Define plugin constants
if (!defined('WP_MEDIA_FOLDER_PLUGIN_DIR')) {
    define('WP_MEDIA_FOLDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('WPMF_PLUGIN_URL')) {
    define('WPMF_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('WPMF_VERSION')) {
    define('WPMF_VERSION', '6.2.4');
}

if (!defined('WPMF_TAXO')) {
    define('WPMF_TAXO', 'wpmf-category');
}

if (!defined('WPMF_FILE')) {
    define('WPMF_FILE', __FILE__);
}

if (!defined('_WPMF_GALLERY_PREFIX')) {
    define('_WPMF_GALLERY_PREFIX', '_wpmf_gallery_');
}

if (!defined('WPMF_DOMAIN')) {
    define('WPMF_DOMAIN', 'wpmf');
}

if (!defined('WPMF_HIDE_USER_MEDIA_FOLDER_ROOT')) {
    define('WPMF_HIDE_USER_MEDIA_FOLDER_ROOT', true);
}

// disable warning function _load_textdomain_just_in_time was called incorrectly
add_filter('doing_it_wrong_trigger_error', '__return_false');

// Load Internationalization
require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-i18n.php';
\Joomunited\WPMediaFolder\I18n::init();

// Include Helper class
require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-helper.php';

// Check plugin requirements
if (!\Joomunited\WPMediaFolder\WpmfHelper::checkPhpVersion()) {
    return;
}
\Joomunited\WPMediaFolder\WpmfHelper::init();
\Joomunited\WPMediaFolder\WpmfHelper::initQueueIntegration();
\Joomunited\WPMediaFolder\WpmfHelper::initRequirementsCheck();

register_uninstall_hook(WPMF_FILE, array('\Joomunited\WPMediaFolder\WpmfHelper', 'wpmfUnInstall'));
register_activation_hook(WPMF_FILE, array('\Joomunited\WPMediaFolder\WpmfHelper', 'wpmfInstall'));

// Initialize admin-related hooks
\Joomunited\WPMediaFolder\WpmfHelper::initAdminHooks();

// Load core components
require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-loader.php';
\Joomunited\WPMediaFolder\WpmfLoader::initModules();

require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-updater.php';
\Joomunited\WPMediaFolder\WpmfUpdater::init();

// Load backward compatibility functions.
require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-backward-functions.php';
