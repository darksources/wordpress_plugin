<?php //admin options plugin functions
namespace DarkSources\Options;
// If this file is called directly, abort.
if (!defined('WPINC')){
	die;
}

//------------------------------------------------ function to set or get plugin options ------------------------------------------------ /

function update_get_option_field($field, $type, $value = ''){
        if($type === 'get'){
            $value = get_option($field, '');
            if (is_numeric($value)){
                $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            } else {
                $value = sanitize_text_field($value);
            }
            return $value;
        }
        if($type === 'update') {
            if($value === 'Valid Key'){
                return;
            }
            if($field === 'dark_sources_password_match_tolerance' || $field === 'dark_sources_password_other_match_tolerance' || $field === 'dark_sources_password_rank_tolerance'){
                $value = str_replace(',', '', $value);
            }
            if(strpos($field, 'checkbox') !== FALSE){
                if($value === 'checked' || $value === 'unchecked' ){
                    //proceed
                } else {
                    return;
                }
            }
            if($field === 'dark_sources_api_key'){
                $value = str_replace(' ', '', $value);
            }
            if (is_numeric($value)){
                $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            } else {
                $value = sanitize_text_field($value);
            }
            return update_option($field, $value);
        }
}

//------------------------------------------------ make checkbox updateable ------------------------------------------------ /

function dark_sources_update_loop(){
    $checkboxArray = array('dark_sources_force_password_change_checkbox','dark_sources_reject_message_checkbox','dark_sources_email_message_checkbox','dark_sources_admin_message_checkbox','dark_sources_login_force_password_change_checkbox','dark_sources_login_pop_up_message_checkbox','dark_sources_login_email_message_checkbox','dark_sources_login_admin_message_checkbox','dark_sources_password_exists_checkbox','dark_sources_password_rank_checkbox', 'dark_sources_password_match_checkbox', 'dark_sources_password_other_match_checkbox');
    foreach($checkboxArray as $field){
        if(!array_key_exists($field, $_POST)){
            update_get_option_field($field, 'update', 'unchecked');
        }
    }
    foreach($_POST as $field => $value){
        if(array_key_exists($field, $_POST)){
            update_get_option_field($field, 'update', $value);
        }
    }
}