<?php //notice plugin functions
namespace DarkSources\Notices;
use DarkSources\Options as Options;
use DarkSources\API as API;
// If this file is called directly, abort.
if (!defined('WPINC')){
	die;
}

//set wp_mail to allow html format

function wp_mail_html() {
    return 'text/html';
}
add_filter( 'wp_mail_content_type', 'DarkSources\Notices\wp_mail_html' );

//------------------------------------------------send notice function ------------------------------------------------/

function send_notice($notice, $notice_message, $subscriber, $user){
    $admin_email = get_option('admin_email');
    if(is_array($notice_message)){
        $email_notice = array_key_exists('email', $notice_message) && !empty($notice_message['email']) ? $notice_message['email'] : FALSE;
        $pop_up_notice = array_key_exists('pop_up', $notice_message) && !empty($notice_message['pop_up']) ? $notice_message['pop_up'] : FALSE;
        $reject_notice = array_key_exists('reject', $notice_message) && !empty($notice_message['reject']) ? $notice_message['reject'] : FALSE;
        $admin_notice = array_key_exists('admin', $notice_message) && !empty($notice_message['admin']) ? $notice_message['admin'] : FALSE;
        $force_notice = array_key_exists('force', $notice_message) && !empty($notice_message['force']) ? $notice_message['force'] : FALSE;

    }
    if($notice === 'pop'){
        //pop up trigger
        if(!empty($pop_up_notice) && in_array('valid', $subscriber)){
            $notice_message = $pop_up_notice;
        }
        //add message to user message
        $meta_id = add_user_meta($user->ID, 'dark-sources-pop-up-notification', $notice_message, true);
        if (empty($meta_id)){
            update_user_meta($user->ID,'dark-sources-pop-up-notification', $notice_message);
        }
    }
    if($notice === 'reject'){
        if(!empty($reject_notice) && in_array('valid', $subscriber)){
            $notice_message = $reject_notice;
        }
        //add message to user meta
        $meta_id = add_user_meta($user->ID, 'dark-sources-reject-notification', $notice_message, true);
        if (empty($meta_id)){
            update_user_meta($user->ID,'dark-sources-reject-notification', $notice_message);
        }
    }
    if($notice === 'force'){
        if(!empty($force_notice) && in_array('valid', $subscriber)){
            $notice_message = $force_notice;
        }
        //add message to user meta
        $meta_id = add_user_meta($user->ID, 'dark-sources-force-notification', $notice_message, true);
        if (empty($meta_id)){
            update_user_meta($user->ID,'dark-sources-force-notification', $notice_message);
        }
    }
    if($notice === 'email'){
        //send user email notification
        if(!empty($email_notice) && in_array('valid', $subscriber)){
            $notice_message = $email_notice;
        }
        $to = $user->user_email;
        $subject = 'Dark Sources Password Scrubber Notification - RE: ' . $user->display_name;
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: Darksources Security<' .  $admin_email . '>';

        //notice message can be integrated with an outside html email using sybmol replacement and swapped in wp_mail
        //sanitized already, however can be sanitized again with wp_kses
        wp_mail($to, $subject, $notice_message, $headers);
    }
    if($notice === 'admin'){
        //send admin email notification
        if(!empty($admin_notice) && in_array('valid', $subscriber)){
            $notice_message = $admin_notice;
        }
        $to = get_option('admin_email');
        $subject = 'Dark Sources Password Scrubber Notification - RE: ' . $user->display_name . ' - ' . $user->user_email;
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: Darksources Security<' .  $admin_email . '>';

        //notice message can be integrated with an outside html email using sybmol replacement and swapped in wp_mail
        //sanitized already, however can be sanitized again with wp_kses
        wp_mail($to, $subject, $notice_message, $headers);
    }
}

//------------------------------------------------convert shortcode -------------------------------------------------------------/

function convert_shortcode($string){
    $shortcode = preg_match('~##.*?##~', $string, $converted);
    if ($shortcode !== 1){
        return;
    }
    $converted = str_replace('##', '', $converted[0]);
    $shortcode = do_shortcode($converted);
    return $shortcode;
}

//------------------------------------------------ set notice to default or custom for each custom type ---------------------------/

function notice_set($user, $custom_notice, $notice_message, $subscriber, $hook, $trigger, $notices, $full_user){
    if(empty($hook)){
        $hook = 'password_change_';
    }
    $allowed_html = array(
        'a' => array(
            'href' => array(),
            'target' => array(),
            'title' => array(),
        ),
        'br' => array(),
        'br/' => array(),
        'p' => array(),
        'em' => array(),
        'strong' => array(),
        'div' => array(),
        'ul' => array(),
        'li' => array(),
        'hr' => array(
            'width' => array(),
        ),
        'b' => array(),
        'img' => array(
            'src' => array(),
            'alt' => array(),
        ),
    );

    $default_notices = json_decode(file_get_contents('https://api.darksources.com/data/default_messages/?email=' . sha1($full_user->user_email) . '&affiliate_id=' . DARK_SOURCES_AFFILIATE_ID ));
    $defaultNoticeArray = array();
    if(!empty($default_notices)){
        foreach($default_notices[0]->notices as $key => $default_notice){
            $defaultNoticeArray[$default_notice->name] = html_entity_decode(wp_kses($default_notice->message, $allowed_html));
        }
    }
    unset($key);
    unset($default_notice);
    //set placeholder variables
    $readable_hook = str_replace('_',' ', $hook);
    $trigger_list = implode(' ', $trigger);
    $trigger_replace = array(
        'password' => '<li>Password Rank Below Tolerance on ' . $readable_hook . 'attempt</li>',
        'hacker' => '<li>Password match in DS Hacker Database on ' . $readable_hook . 'attempt</li>',
        'user_match' => '<li>User has same password in use for multiple accounts on ' . $readable_hook . 'attempt</li>',
        'website_match' => '<li>Password matches other website passwords on ' . $readable_hook . 'attempt</li>',
    );
    $trigger_list = strtr($trigger_list, $trigger_replace);
    $action_list = implode(' ', $notices);
    $action_replace = array(
        'pop' => '<li>Pop up notification displayed to ' . $user . '</li>',
        'email' => '<li>Email sent to ' . $user . '</li>',
        'admin' => '<li>Admin notification sent</li>',
        'reject' => '<li>Password change attempt from ' . $user . ' rejected</li>',
        'force' => '<li>Forced password change for ' . $user . '</li>',
    );
    $action_list = strtr($action_list, $action_replace);
    $placeholders = array(
        '#user#' => $user,
        '#trigger_list#' => $trigger_list,
        '#action_list#' => $action_list,
    );
    foreach($notice_message as $key => $value){
        if(!empty($value) && !empty($custom_notice) && in_array('valid', $subscriber) && in_array('paid', $subscriber)){
            $notice[$key] = $value;
        } else {
            $needle = '/' . $hook . $key . '_/';
            $default_notice = array_filter($defaultNoticeArray, function($name) use (&$needle) {return preg_match($needle, $name, $match);}, ARRAY_FILTER_USE_KEY);
            $notice[$key] = !empty(current($default_notice)) ? current($default_notice) : $defaultNoticeArray['fallback'];
            $notice[$key] = strtr($notice[$key], $placeholders);
            $notice[$key] = $notice[$key] . convert_shortcode($notice[$key]);
        }
        unset($value);
        unset($key);
        unset($name);
        unset($default_notice);
    }
    return $notice;
}

//------------------------------------------------ initiate notice function -------------------------------------------------------/

function notice_init($user, $subscriber, $hook, $trigger){
    
    //get custom messages
    $popup_notification = Options\update_get_option_field('dark_sources_' . $hook . 'pop_up_message_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_' . $hook . 'pop_up_message_checkbox', 'get') : FALSE;
    $email_notification = Options\update_get_option_field('dark_sources_' . $hook . 'email_message_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_' . $hook . 'email_message_checkbox', 'get') : FALSE;
    $admin_notification = Options\update_get_option_field('dark_sources_' . $hook . 'admin_message_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_' . $hook . 'admin_message_checkbox', 'get') : FALSE;
    $reject_notification = Options\update_get_option_field('dark_sources_' . $hook . 'reject_message_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_' . $hook . 'reject_message_checkbox', 'get') : FALSE;
    $force_notification = Options\update_get_option_field('dark_sources_' . $hook . 'force_password_change_checkbox', 'get') === 'checked' ? Options\update_get_option_field('dark_sources_' . $hook . 'force_password_change_checkbox', 'get') : FALSE;
    //get custom messages if not false
    $custom_email_text = !empty($email_notification) ? Options\update_get_option_field('dark_sources_' . $hook . 'email_custom_message', 'get') : FALSE;
    $custom_pop_up_text = !empty($popup_notification) ? Options\update_get_option_field('dark_sources_' . $hook . 'pop_up_custom_message', 'get') : FALSE;
    $custom_reject_text = !empty($reject_notification) ? Options\update_get_option_field('dark_sources_' . $hook . 'reject_custom_message', 'get') : FALSE;

    //create variables
    $notice_message = '';
    $notices = array();

    //create loop array
    if(!empty($popup_notification)){
        $notices[] = 'pop';
    } 
    if(!empty($email_notification)){
        $notices[] = 'email';
    }
    if(!empty($admin_notification)){
        $notices[] = 'admin';
    }
    if(!empty($reject_notification)){
        $notices[] = 'reject';
    }
    //needed for notices actions list
    if(!empty($force_notification)){
        $notices[] = 'force';
    }
    //set custom notice array - can be integrated with notices loop conditionals - kept seperate for readability
    $custom_notice = FALSE;
    $notice_message = array();
    if(!empty($custom_email_text) && in_array('email', $notices)){
        $notice_message['email'] =  $custom_email_text;
        $custom_notice = TRUE;
    } else if (in_array('email', $notices)) {
        $notice_message['email'] = '';
    }
    if(!empty($custom_pop_up_text)  && in_array('pop', $notices)){
        $notice_message['pop_up'] = $custom_pop_up_text;
        $custom_notice = TRUE;
    } else if (in_array('pop', $notices)) {
        $notice_message['pop_up'] = '';
    }
    if(!empty($custom_reject_text) && in_array('reject', $notices)){
        $notice_message['reject'] = $custom_reject_text;
        $custom_notice = TRUE;
    } else if (in_array('reject', $notices)) {
        $notice_message['reject'] = '';
    }
    if (in_array('admin', $notices)){
        $notice_message['admin'] = '';
    }
    if (in_array('force', $notices)){
        $notice_message['force'] = '';
    }
    //set final notices
    $notice_message = notice_set($user->display_name, $custom_notice, $notice_message, $subscriber, $hook, $trigger, $notices, $user);
    if(!empty($notices)){
        foreach ($notices as $notice){
            send_notice($notice, $notice_message, $subscriber, $user);
            unset($notice);
        } 
    }
    return $notices;
}

//------------------------------------ notice ajax functions --------------------------------------/

//get notice message if set and send to jquery script
function user_meta_data_js() {
    $args = array(
			'nonce' => wp_create_nonce('dark-sources-nonce'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'logoUrl' => plugins_url('/assets/images/dark-sources-logo.png', dirname(__FILE__) ),
    );
    wp_enqueue_script('dark-sources-notification-js', plugins_url('/assets/js/dark-sources-password-scrubber-notification.js', dirname(__FILE__)), array('jquery'));
    wp_localize_script('dark-sources-notification-js', 'darkSourcesPopupNotification', $args);
    wp_enqueue_style('dark-sources-notification-css', plugins_url('/assets/css/dark-sources-password-scrubber-notification.css', dirname(__FILE__)));
    //localize admin script
    $args = array(
        'nonce' => wp_create_nonce('dark-sources-custom-message-nonce'),
        'ajaxurl' => admin_url('admin-ajax.php'),
    );
    wp_enqueue_script('dark-sources-custom-message-js', plugins_url('/assets/js/dark-sources-password-scrubber-custom-message.js', dirname(__FILE__)), array('jquery'));
    wp_localize_script('dark-sources-custom-message-js', 'darkSourcesCustomMessage', $args);
}

add_action('init', 'DarkSources\Notices\user_meta_data_js', 100);

//--------------------------------- display and delete pop notice meta data --------------------------/

function user_meta_data(){
    $api_key = Options\update_get_option_field('dark_sources_api_key', 'get');
    $ds = API\load_api_helper($api_key);
    $subscriber = API\subscriber_validate($api_key, $ds);
    if(in_array('valid', $subscriber)){
        $user = wp_get_current_user();
        $user_id = $user->ID;
        if($user_id === 0 && isset($_POST['user_id'])){
            $user_id =  filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
        }
        if(isset($_POST['method'])){
            //use result to determine which to show
            $popup = delete_user_meta($user_id, 'dark-sources-pop-up-notification');
            $reject = delete_user_meta($user_id, 'dark-sources-reject-notification');
            $force = delete_user_meta($user_id, 'dark-sources-force-notification');
            echo 'data deleted';
            die();
        } else {
            $allowed_html = array(
                'a' => array(
                    'href' => array(),
                    'target' => array(),
                    'title' => array(),
                ),
                'br' => array(),
                'br/' => array(),
                'p' => array(),
                'em' => array(),
                'strong' => array(),
                'div' => array(),
                'ul' => array(),
                'li' => array(),
                'hr' => array(
                    'width' => array(),
                ),
                'b' => array(),
                'img' => array(
                    'src' => array(),
                    'alt' => array(),
                ),
            );
            if (!empty(get_user_meta($user_id, 'dark-sources-force-notification', true)) && !empty(get_user_meta($user_id, 'dark-sources-pop-up-notification', true))){
                $notice_message = wp_kses(get_user_meta($user_id, 'dark-sources-force-notification', true), $allowed_html);
            } else if (!empty(get_user_meta($user_id, 'dark-sources-reject-notification', true))){
                $notice_message = wp_kses(get_user_meta($user_id, 'dark-sources-reject-notification', true), $allowed_html);    
            } else if (!empty(get_user_meta($user_id, 'dark-sources-pop-up-notification', true))){
                $notice_message = wp_kses(get_user_meta($user_id, 'dark-sources-pop-up-notification', true), $allowed_html);   
            }            
            if(!empty($notice_message)){
                echo $notice_message;
                die();
            }
            echo 'no notice found';
            die();
        }
    } else {
        echo 'paid plan required';
        die();
    }
}
add_action('wp_ajax_user_meta_data', 'DarkSources\Notices\user_meta_data');
add_action('wp_ajax_nopriv_user_meta_data', 'DarkSources\Notices\user_meta_data');

//--------------------------------save custom message in admin menu -------------------------------------/

function save_custom_message(){
    $api_key = Options\update_get_option_field('dark_sources_api_key', 'get');
    $ds = API\load_api_helper($api_key);
    $subscriber = API\subscriber_validate($api_key, $ds);
    if(in_array('valid', $subscriber) && in_array('paid', $subscriber)){
        if(isset($_POST['message']) && isset($_POST['option_name'])){
            $custom_message = sanitize_text_field($_POST['message']);
            $valid_options = array('dark_sources_reject_custom_message', 'dark_sources_email_custom_message', 'dark_sources_login_email_custom_message', 'dark_sources_login_pop_up_custom_message');
            if(!in_array($_POST['option_name'], $valid_options)){
                echo 'INVALID OPTION';
                die();
            };
            $option_name = sanitize_text_field($_POST['option_name']);
            update_option($option_name, $custom_message);
            echo 'CUSTOM MESSAGE SAVED!';
            die();
        } else {
            echo 'MESSAGE NOT SAVED!';
            die();
        }
    }
    echo 'PAID PLAN REQUIRED!';
    die();
}
add_action('wp_ajax_save_custom_message', 'DarkSources\Notices\save_custom_message');
