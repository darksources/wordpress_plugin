<?php //api plugin functions
namespace DarkSources\API;
use DarkSources\Helper as Helper;
use DarkSources\Options as Options;
use DarkSources\Notices as Notices;
// If this file is called directly, abort.
if(!defined('WPINC')){
	die;
}
function load_api_helper($api_key){
    $ds = new Helper\DarkSources_API();
    $ds->auth($api_key);
    // Set API debug output to error_log
    $ds->debug(True);
    return $ds;
}

//------------------------------ api call and service up  function ------------------------------

function api_call($ds, $method, $value, $value2 = '', $api_key = ''){
    if(!empty($api_key)){
        $data = $ds->$method($value, $value2, $api_key);
    } else {
        $data = $ds->$method($value);
    }
    if((int)$data['status_code'] !== 3) {
        foreach ($data as $key => $value){
            if(is_array($value)){
                foreach ($value as $nested_key => $nested_value){
                    if(!empty($nested_value)){
                        if($nested_key === 'next_rebill'){
                            $data[$key][$nested_key] = date('m/d/Y', strtotime($nested_value));
			} else if(is_array($nested_value)) {
                           continue;
                        } else if(is_numeric($nested_value)){
                           $data[$key][$nested_key] = filter_var((int)$nested_value, FILTER_SANITIZE_NUMBER_INT);
                        } else {
                            $data[$key][$nested_key] = filter_var($nested_value, FILTER_SANITIZE_STRING);
                        }
                    }
                }
            } else {
                if(!empty($value)){
                    $data[$key] = filter_var($value, FILTER_SANITIZE_STRING);
                    }
                }
        }
        return $data;
    }
    return FALSE;
}

//------------------------------ subscriction validation --------------------------------------------------------

function subscriber_validate($api_key, $ds){
    if(empty($api_key) || substr($api_key, 0, 8) !== 'DSAPIKEY'){
        $subscriber = array();
    } else {
        $api_check = api_call($ds, 'keystats', $api_key);
        if(empty($api_check)){
            $subscriber = array();
        }
        if((int)$api_check['status_code'] === 0){
            $subscriber[] = 'valid';
        }
        if(isset($api_check['data']['plan_overage_queries']) && $api_check['data']['plan_overage_queries'] >= 1){
            $subscriber[] = 'paid';
        } else if(empty($api_check['data']['plan_overage_queries']) && $api_check['data']['plan_monthly_queries'] >= 1) {
            $subscriber[] = 'free';
        }
    }
    return $subscriber;
}

//------------------------------- unknown password field check -------------------------------------------------
//for extending plugin to be compatible with plugins, not currently in use

function unkown_password_field_check($user){
    foreach ($_POST as $key => $value){
        if(wp_check_password($value, $user->user_pass, $user->ID)){
            return $value;
        }
    }
}

//------------------------------ free password check ------------------------------------------------------------
//for api testing

function free_password_check($ds, $hashed_password, $password_rank_tolerance){
    $password_check = api_call($ds, 'password_rank_lookup', $hashed_password);
    if(empty($password_check)){
        return FALSE;
    } else{
        $password_rank = isset($password_check['data']['password_rank']) ? (int)$password_check['data']['password_rank'] : $password_rank_tolerance;
        if($password_rank < $password_rank_tolerance){
            $trigger[] = 'password';
        }
    }
    if(empty($trigger)){
        $trigger = FALSE;
    }
    return $trigger;
}

//------------------------------ simple email check ---------------------------------------------------------------
//for api testing - can be removed

function simple_email_check($ds, $email, $website_password_count_default){
    $email_check = api_call($ds, 'email_lookup', $email);
        if(empty($email_check)){
            return FALSE;
        } else{
            $email_known = isset($email_check['data']['email_known']) ? (int)$email_check['data']['email_known'] : 0;
            $website_password_count = isset($email_check['data']['source_count']) ? (int)$email_check['data']['source_count'] : 0;
            if($email_known === 1 && $website_password_count > $website_password_count_default ){
                $trigger[] = 'email';
            }
        }
    if(empty($trigger)){
        $trigger = FALSE;
    }
    return $trigger;    
}

// -------------------------------full email check -------------------------------------------------------------
//for api testing - can be removed
function full_email_check($ds, $email, $website_password_count_default){
    $email_check = api_call($ds, 'full_email_lookup', $email);
        if(empty($email_check)){
            return FALSE;
        } else{
            $email_known = isset($email_check['data']['email_known']) ? (int)$email_check['data']['email_known'] : 0;
            $website_password_count = isset($email_check['data']['websites']) ? (int)$email_check['data']['websites'] : 0;
            if($email_known === 1 && $website_password_count > $website_password_count_default ){
                $trigger[] = 'email';
            }
        }
    if(empty($trigger)){
        $trigger = FALSE;
    }
    return $trigger;  
}

//------------------------------ full email and password lookup -------------------------------------------------

function password_email_check($ds, $email, $password, $api_key, $user = ''){
    //get user options
    $password_rank_tolerance = !empty(Options\update_get_option_field('dark_sources_password_rank_tolerance', 'get')) ? Options\update_get_option_field('dark_sources_password_rank_tolerance', 'get') : 10000000;
    $user_password_match = !empty(Options\update_get_option_field('dark_sources_password_match_tolerance', 'get')) ? Options\update_get_option_field('dark_sources_password_match_tolerance', 'get') : 1;
    $websites_password_match = !empty(Options\update_get_option_field('dark_sources_password_other_match_tolerance', 'get')) ? Options\update_get_option_field('dark_sources_password_other_match_tolerance', 'get') : 2;
    $password_exists_checkbox = Options\update_get_option_field('dark_sources_password_exists_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_password_exists_checkbox', 'get') : FALSE;
    $password_rank_checkbox = Options\update_get_option_field('dark_sources_password_rank_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_password_rank_checkbox', 'get') : FALSE;
    $user_match_checkbox = Options\update_get_option_field('dark_sources_password_match_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_password_match_checkbox', 'get') : FALSE;
    $websites_match_checkbox = Options\update_get_option_field('dark_sources_password_other_match_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_password_other_match_checkbox', 'get') : FALSE;
    //api call
    $password_email_check = api_call($ds, 'full_email_password_lookup', $email, $password, $api_key);
    if(empty($password_email_check)){
        return FALSE;
    } else{
        //check password rank
        $password_rank = $password_email_check['data']['password_rank'] ? (int)$password_email_check['data']['password_rank'] : $password_rank_tolerance;
        if($password_rank < $password_rank_tolerance && !empty($password_rank_checkbox)){
            $trigger[] = 'password';
        }
        //check if password is known in hacker database
        $password_in_hacker_database = isset($password_email_check['data']['password_known']) ? (int)$password_email_check['data']['password_known'] : 0;
        if($password_in_hacker_database > 0 && !empty($password_exists_checkbox)){
            $trigger[] = 'hacker'; 
        }
        //check if user has used password on other sites
        $ep_queries = isset($password_email_check['data']['p_queries']) ? count($password_email_check['data']['p_queries']) : 0;



        if($ep_queries >= $user_password_match && !empty($user_match_checkbox)){
            $trigger[] = 'user_match'; 
        }
        //check if password has been used on other sites
        $p_queries = isset($password_email_check['data']['p_queries']) ? count($password_email_check['data']['p_queries']) : 0;
        if($p_queries >= $websites_password_match && !empty($websites_match_checkbox)){
            $trigger[] = 'website_match'; 
        }
        //do not run email call if no user id
        if (!empty($user)){
            //initial email check to begin adding email meta data
            $email_known = isset($password_email_check['data']['email_known']) ? (int)$password_email_check['data']['email_known'] : 0;
            if($email_known === 1){
                //full email check
                $email_check = api_call($ds, 'full_email_lookup', $email);
            if(!empty($email_check)){
                //add email meta data to user meta
                $data = $email_check['data'];
                    if(!empty($data)){
                        
                        $email_meta_id = add_user_meta($user, 'dark_sources_email_meta', serialize($data), TRUE);
                        if(empty($email_meta_id)){
                            update_user_meta($user,'dark_sources_email_meta', serialize($data));
                        }
                        $time_meta_id = add_user_meta($user, 'dark_sources_email_meta_last_updated', time(), TRUE);
                        if(empty($time_meta_id)){
                            update_user_meta($user, 'dark_sources_email_meta_last_updated', time());
                        }
                        
                    }
                }
            }
        }
        if(empty($trigger)){
            $trigger = FALSE;
        }
        return $trigger;
    }
}

//left in just in case we need to switch back to it or use it as a fallback.
//------------------------------ change reset notification on force password change ------------------------------
/*
function filter_reset_password_message($translated_text, $text, $domain) {
    if(!empty($_GET['user'])){
        $user = $_GET['user'];
    }
    if(!empty($user)){
        if($text === 'Enter your new password below.'){
            //get default fallback notice from JSON file. Set to last object message in file.
            $default_notices = json_decode(file_get_contents(plugins_url('includes/default-messages.json', dirname( __FILE__ ))));
            $default_notice = $default_notices[0]->notices;
            $default_notice = end($default_notice);
            $default_notice = $default_notice->message;
            $reject_notice = !empty(get_user_meta($user, 'dark-sources-reject-notification', true)) ? sanitize_text_field(get_user_meta($user, 'dark-sources-reject-notification', true)) : FALSE;
            $force_notice = !empty(get_user_meta($user, 'dark-sources-force-notification', true)) ? sanitize_text_field(get_user_meta($user, 'dark-sources-force-notification', true)) : FALSE;
            $pop_up_notice = !empty(get_user_meta($user, 'dark-sources-pop-up-notification', true)) ? sanitize_text_field(get_user_meta($user, 'dark-sources-pop-up-notification', true)) : FALSE;
            if(!empty($reject_notice)){
                
                    $translated_text = __( $reject_notice, 'dark-sources-password-scrubber') . '<a href="http://www.google.com">Test</a><img src="https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png"/>';
                
            } else if(!empty($force_notice)){
            
                    $translated_text = __( $force_notice, 'dark-sources-password-scrubber' ) . '<a href="http://www.google.com">Test</a><img src="https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png"/>';
            
           } else if(!empty($pop_up_notice)){
            
                    $translated_text = __( $pop_up_notice, 'dark-sources-password-scrubber' ) . '<a href="http://www.google.com">Test</a><img src="https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png"/>';
                
            } else {
                
                    $translated_text = __( $default_notice, 'dark-sources-password-scrubber' ) . '<a href="http://www.google.com">Test</a><img src="https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png"/>';
                
            }
            delete_user_meta($user, 'dark-sources-reject-notification');
            delete_user_meta($user, 'dark-sources-force-notification');
            delete_user_meta($user, 'dark-sources-pop-up-notification');
        }
    }
    
    return $translated_text;
}
add_filter('gettext', 'Darksources\API\filter_reset_password_message', 20, 3);
*/
//------------------------------ log user out and redirect to the generated reset password link ------------------------------

function force_password_change($user){
    $reset_key = get_password_reset_key($user);
    $user_id = $user->ID;
    $user_login = $user->user_login;
    $reset_link = wp_login_url() . '?action=rp&key=' . $reset_key . '&login=' . $user_login . '&user=' . $user_id;
    wp_logout();
    wp_set_current_user(0);
    header('Location: ' . $reset_link);
    die();
}

//--------------------------------------------login hook ------------------------------------------------------------

function on_user_login($login, $user){
    //initial variables and init
    $api_key = Options\update_get_option_field('dark_sources_api_key', 'get');
    $ds = load_api_helper($api_key);
    $user_id = $user->ID;
    $email = $user->user_email;
    $hashed_email = hash('sha1', $email);
    $password = $_POST['pwd'];
    $hashed_password = hash('sha512', $password);
    $hook = 'login_';
    //set trigger to false as default
    $trigger = FALSE;;
    
    $force_password_change = Options\update_get_option_field('dark_sources_login_force_password_change_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_login_force_password_change_checkbox', 'get') : FALSE;
    //validate API Key

    $subscriber = subscriber_validate($api_key, $ds);

    if(!in_array('valid', $subscriber)){

        //if not a valid API KEY stop script
        return;
    }
    if(in_array('paid', $subscriber) || in_array('free', $subscriber)) {

        //-------full call-----------
        //password and email check
        $trigger = password_email_check($ds, $email, $password, $api_key, $user->ID);

    } 
    //check if notifications were triggered   
    if(empty($trigger)){
        //if checks are okay end script - done first to avoid executing undeeded code
        return;

    } else {

        //run user notification options and send

        Notices\notice_init($user, $subscriber, $hook, $trigger);
        //force password change
        if(!empty($force_password_change)){
            force_password_change($user);
        }         
    }
}
add_action('wp_login', 'DarkSources\API\on_user_login', 10, 2);

//------------------------------------------- password reset hook ----------------------------------------------

function on_user_password_reset($user, $password){
    //initial variables and init
    $api_key = Options\update_get_option_field('dark_sources_api_key', 'get');
    $ds = load_api_helper($api_key);
    $user_id = $user->ID;
    $email = $user->user_email;
    $hashed_email = hash('sha1', $email);
    $hashed_password = hash('sha512', $password);
    //intentially left blank
    $hook = '';
    //set trigger to false as default
    $trigger = FALSE;;
    
    $force_password_change = Options\update_get_option_field('dark_sources_reject_message_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_reject_message_checkbox', 'get') : FALSE;
    
    $subscriber = subscriber_validate($api_key, $ds);

    if(!in_array('valid', $subscriber)){

        //if not a valid API KEY stop script
        return;
    }
    if(in_array('paid', $subscriber) || in_array('free', $subscriber)) {

        //-------full call-----------
        //password and email check
        $trigger = password_email_check($ds, $email, $password, $api_key, $user->ID);
    } 
    //check if notifications were triggered
    if(empty($trigger)){
        //if checks are okay end script - done first to avoid executing undeeded code
        return;

    } else {

        //run user notification options and send
        Notices\notice_init($user, $subscriber, $hook, $trigger);
        //force password change
        if(!empty($force_password_change)){
            force_password_change($user);
        }         
    }
}
add_action('password_reset', 'DarkSources\API\on_user_password_reset', 10, 2);


//----------------------------------------- reject password on change in user profile -------------------------------------------

function on_user_password_update($errors, $update, $user){
     //initial variables and init
    $api_key = Options\update_get_option_field('dark_sources_api_key', 'get');
    $ds = load_api_helper($api_key);
    $password = $_POST['pass1'];
    $email = $_POST['email'];
    $hashed_email = hash('sha1', $email);
    $hashed_password = hash('sha512', $password);
    //intentially left blank
    $hook = '';
    //set trigger to false as default
    $trigger = FALSE;

    $reject_password_change = Options\update_get_option_field('dark_sources_reject_message_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_reject_message_checkbox', 'get') : FALSE;
    $subscriber = subscriber_validate($api_key, $ds);

    if(!in_array('valid', $subscriber)){

        //if not a valid API KEY stop script
        return;
    }
    if(in_array('paid', $subscriber) || in_array('free', $subscriber)) {

        //-------full call-----------
        //password and email check
        if($update){
            //user update run full version
            $trigger = password_email_check($ds, $email, $password, $api_key, $user->ID);
        } else {
            //new user run no id version
            $trigger = password_email_check($ds, $email, $password, $api_key);
        }
    }
    //check if notifications were triggered
    if(empty($trigger)){
        //if checks are okay end script - done first to avoid executing undeeded code
        return;

    } else {
        //run user notification options and send if user is being updated
        if($update){
            $notices = Notices\notice_init($user, $subscriber, $hook, $trigger);
        }
        //throw wordpress error to halt save if force or reject is checked
        if (in_array('reject', $notices)) {
            $errors->add('reject_password_error',__('Password rejected due to potential security risk.'));
        }
    }
}
add_action('user_profile_update_errors', 'DarkSources\API\on_user_password_update', 10, 3);

//----------------------------------------- failed login bot check -------------------------------------------

function failed_login_bot_check($username){
    $api_key = Options\update_get_option_field('dark_sources_api_key', 'get');
    if(empty($api_key)){
        //end function if no API Key is found
        return;
    }
    $ds = load_api_helper($api_key);
    $subscriber = subscriber_validate($api_key, $ds);
    if(in_array('valid', $subscriber) && in_array('paid', $subscriber)){
        $bot = $ds->bot_check_submit($api_key);
    } else {
        //end function if not subscriper
        return;
    }
    //send bot notifications if bot detected
}
add_action('wp_login_failed', 'DarkSources\API\failed_login_bot_check', 10);
