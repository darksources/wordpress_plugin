<?php
/**
 *
 * @link       www.darksources.com
 * @since      1.0.0
 * @package    Dark_Sources_Password_Scrubber
 */

// If uninstall not called from WordPress, then exit.
if (!defined( 'WP_UNINSTALL_PLUGIN')) {
	exit;
}

//remove all options
delete_option('dark_sources_api_key');
delete_option('dark_sources_password_exists_checkbox');
delete_option('dark_sources_password_rank_checkbox');
delete_option('dark_sources_password_match_checkbox');
delete_option('dark_sources_password_other_match_checkbox');
delete_option('dark_sources_reject_message_checkbox');
delete_option('dark_sources_email_message_checkbox');
delete_option('dark_sources_reject_custom_message');
delete_option('dark_sources_email_custom_message');
delete_option('dark_sources_admin_message_checkbox');
delete_option('dark_sources_login_force_password_change_checkbox');
delete_option('dark_sources_login_pop_up_message_checkbox');
delete_option('dark_sources_login_pop_up_custom_message');
delete_option('dark_sources_login_email_message_checkbox');
delete_option('dark_sources_login_email_custom_message');
delete_option('dark_sources_login_admin_message_checkbox');
delete_option('dark-sources-password-scrubber');
delete_option('dark_sources_password_rank_tolerance');
delete_option('dark_sources_password_match_tolerance');
delete_option('dark_sources_password_other_match_tolerance');
delete_option('dark_sources_nonce');
delete_option('dark_sources_settings_update');
delete_option('dark_sources_default_message');
delete_option('dark_sources_password_affiliate_id');

//remove any leftover user meta options

delete_metadata('user', '', 'dark_sources_email_meta', '', TRUE);
delete_metadata('user', '', 'dark_sources_email_meta_last_updated', '', TRUE);
delete_metadata('user', '', 'dark-sources-reject-notification', '', TRUE);
delete_metadata('user', '', 'dark-sources-pop-up-notification', '', TRUE);
delete_metadata('user', '', 'dark-sources-force-notification', '', TRUE);