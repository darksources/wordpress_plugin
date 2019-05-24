<?php
/**
 * @link              www.darksources.com
 * @since             1.0.0
 * @package           Dark_Sources_Password_Scrubber
 *
 * @wordpress-plugin
 * Plugin Name:       Dark Sources Password Scrubber
 * Plugin URI:        https://github.com/darksources/wordpress_plugin
 * Description:       Detect and security risks and force them to be corrected upon user login through our dark web database, including common hacked passwords, leaked email addresses and personal data.
 * Version:           1.0.5
 * Author:            Dark Sources Security
 * Author URI:        www.darksources.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dark-sources-password-scrubber
 * Domain Path:       /languages
 */
namespace DarkSources;
// If this file is called directly, abort.
if (!defined('WPINC')){
	die;
}

//Current plugin version.
define('DARK_SOURCES_PASSWORD_SCRUBBER_VERSION', '1.0.5');
if(file_exists(plugin_dir_path(__FILE__) . 'AFFILIATEID.txt')){
	$affiliate_id = filter_var(file_get_contents(plugin_dir_path(__FILE__) . 'AFFILIATEID.txt'), FILTER_SANITIZE_NUMBER_INT);
	define('DARK_SOURCES_AFFILIATE_ID', $affiliate_id);
}

//Admin Scripts
function dark_sources_admin_enqueue_scripts($page) {
	if($page === 'toplevel_page_dark-sources-admin-menu'){
		wp_enqueue_style('dark-sources-admin-css', plugins_url('/assets/css/dark-sources-password-scrubber-admin.css', __FILE__));
		wp_enqueue_style('dark-sources-fonts-css', 'https://fonts.googleapis.com/css?family=Quicksand:300,500');
        wp_enqueue_script('dark-sources-admin-js', plugins_url('/assets/js/dark-sources-password-scrubber-admin.js', __FILE__ ), array('jquery'));
    }
}
add_action('admin_enqueue_scripts', 'DarkSources\dark_sources_admin_enqueue_scripts');

//The code that runs during plugin activation.
 function activate_dark_sources_password_scrubber() {
    require_once plugin_dir_path(__FILE__) . 'includes/dark-sources-password-scrubber-activator.php';
}

//The code that runs during plugin deactivation.
function deactivate_dark_sources_password_scrubber() {
	require_once plugin_dir_path(__FILE__) . 'includes/dark-sources-password-scrubber-deactivator.php';
}

register_activation_hook(__FILE__, 'DarkSources\activate_dark_sources_password_scrubber');
register_deactivation_hook(__FILE__, 'DarkSources\deactivate_dark_sources_password_scrubber');

function dark_sources_plugin_init(){
	//functions files
	require_once plugin_dir_path(__FILE__) . 'includes/darksources-api.php';
	require_once plugin_dir_path(__FILE__) . 'includes/dark-sources-password-scrubber-settings-options.php';

	require plugin_dir_path(__FILE__) . 'includes/dark-sources-password-scrubber-settings.php';

	//API Functions file
    require plugin_dir_path(__FILE__) . 'includes/dark-sources-password-scrubber-api-functions.php';
    require plugin_dir_path(__FILE__) . 'includes/dark-sources-password-scrubber-api-notices.php';

    //shortcodes
    require plugin_dir_path(__FILE__) . 'includes/dark-sources-password-scrubber-api-shortcodes.php';

    //cron function
    require plugin_dir_path(__FILE__) . 'includes/dark-sources-password-scrubber-remove-email-meta.php';
}
add_action('plugins_loaded', 'DarkSources\dark_sources_plugin_init', 10);
