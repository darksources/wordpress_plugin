<?php //cron functions
namespace DarkSources\Remove;
// If this file is called directly, abort.
if (!current_user_can('activate_plugins')){
    return;
}

//------------------------------------------------ create cron hook function to loop through users and delete email user meta ------------------------------------------------/

function remove_email_meta(){
    delete_metadata('user', '', 'dark_sources_email_meta', '', TRUE);
    delete_metadata('user', '', 'dark_sources_email_meta_last_updated', '', TRUE);
}
add_action('remove_email_meta_cron', 'remove_email_meta');

//scedule cron
if(!wp_next_scheduled('remove_email_meta_cron')){
    wp_schedule_event(time(), 'daily', 'remove_email_meta_cron');
}