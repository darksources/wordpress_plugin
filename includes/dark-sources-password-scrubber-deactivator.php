<?php //deactivation functions
// If this file is called directly, abort.
if (!defined('WPINC')){
	die;
}
if (!current_user_can('activate_plugins')){
    return;
}
$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
//double quotes required
check_admin_referer("deactivate-plugin_{$plugin}");

//------------------------------------------------ unschedule cron ------------------------------------------------/

$timestamp = wp_next_scheduled('remove_email_meta_cron');
if(wp_next_scheduled('remove_email_meta_cron')){
    wp_unschedule_event($timestamp, 'remove_email_meta_cron');
}
unset($timestamp);