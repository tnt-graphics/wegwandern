<?php
/**
 * Wegwandern Summit Book
 *
 * @package           wegwandern-summit-book
 * @author            PITS
 * @copyright         2023 Pit Solutions Pvt Ltd
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Wegwandern Summit Book
 * Plugin URI:        https://www.pitsolutions.ch/
 * Description:       Summit Book Module for Wegwandern
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            PITS
 * Author URI:        https://www.pitsolutions.ch/
 * Text Domain:       wegwandern-summit-book
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://www.pitsolutions.ch/
 */

define( 'PROFILE_PAGE_URL', home_url( 'gipfelbuch-profil-bearbeiten' ) );
/* Dashboard Post */
define( 'DASHBOARD_PAGE_URL', home_url( 'gipfelbuch-dashboard' ) );
/* Add hiking post - `Meine Wanderungen` */
define( 'NEUE_TOUR_POSTEN_PAGE_URL', home_url( 'neue-tour-posten' ) );
/* Add Pinwall Post */
define( 'INSERAT_ERSTELLEN_URL', home_url( 'inserat-erstellen' ) );

/* Terms of use page for user account */
define( 'TERMS_OF_USE_URL', home_url( 'nutzungsbedingungen-user-account' ) );

define( 'SUMMIT_BOOK_USER_ROLE', 'summit-book-user' );
define( 'SUMMIT_BOOK_USER_MENU_NAME', 'Summit Book User Menu' );

define( 'SUMMIT_BOOK_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'SUMMIT_BOOK_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

/* B2B user Role*/
define( 'B2B_USER_ROLE', 'b2b-user' );


require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/utility-functions.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/summit-book-register-scripts.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/summit-book-post-types.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/registration-login.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/profile.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/summit-book-shortcodes.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/user-navigation.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/formidable-form-customization.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/templates/profile-content.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/templates/neue-tour-posten-content.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/templates/user-dashboard-content.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/templates/inserat-erstellen-content.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/templates/pinnwand-content.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/community-beitrag.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/admin/community-beitrag.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/pinnwand-eintrag.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/admin/pinnwand-eintrag.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/watchlist.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/kommentar.php';

require_once SUMMIT_BOOK_PLUGIN_DIR_PATH . 'inc/admin/kommentar.php';

function load_text_domain() {
    load_plugin_textdomain('wegwandern-summit-book', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('init', 'load_text_domain');
