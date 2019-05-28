<?php //activation functions
namespace DarkSources\Activate;
// If this file is called directly, abort.
if (!current_user_can( 'activate_plugins' )){
    return;
}
$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
//double quotes required
check_admin_referer("activate-plugin_{$plugin}");
//ensure htaccess exists
if (!file_exists(get_home_path() . '.htaccess')){
    flush_rewrite_rules();
}

//------------------------------------------------ add new options------------------------------------------------/

function add_settings_options(){
    add_option('dark_sources_api_key', '', '', 'no');
    add_option('dark_sources_password_exists_checkbox', 'checked', '', 'no');
    add_option('dark_sources_password_rank_checkbox', 'checked', '', 'no');
    add_option('dark_sources_password_match_checkbox', 'checked', '', 'no');
    add_option('dark_sources_password_other_match_checkbox', 'checked', '', 'no');
    add_option('dark_sources_reject_message_checkbox', 'checked', '', 'no');
    add_option('dark_sources_email_message_checkbox', 'checked', '', 'no');
    add_option('dark_sources_reject_custom_message', '', '', 'no');
    add_option('dark_sources_email_custom_message', '', '', 'no');
    add_option('dark_sources_admin_message_checkbox', 'unchecked', '', 'no');
    add_option('dark_sources_login_pop_up_custom_message', '', '', 'no');
    add_option('dark_sources_login_force_password_change_checkbox', 'checked', '', 'no');
    add_option('dark_sources_login_pop_up_message_checkbox', 'checked', '', 'no');
    add_option('dark_sources_login_email_message_checkbox', 'checked', '', 'no');
    add_option('dark_sources_login_email_custom_message', '', '', 'no');
    add_option('dark_sources_login_admin_message_checkbox', 'unchecked', '', 'no');
    add_option('dark-sources-password-scrubber', '', '', 'no');
    add_option('dark_sources_password_rank_tolerance', '1000000', '', 'no');
    add_option('dark_sources_password_match_tolerance', '1', '', 'no');
    add_option('dark_sources_password_other_match_tolerance', '2', '', 'no');
    $affiliate_id = get_option('dark_sources_password_affiliate_id', 'default');
    add_option('dark_sources_password_affiliate_id', $affiliate_id, '', 'no');
}
add_settings_options();