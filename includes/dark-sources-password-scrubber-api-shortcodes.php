<?php //shortcode plugin functions
namespace DarkSources\Shortcodes;
// If this file is called directly, abort.
if (!defined('WPINC')){
	die;
}

//------------------------------------------------ email meta data shortcode ------------------------------------/
// use do_shortcode is needed inside a script

function display_email_meta_shortcode($atts, $content = ''){
    $atts = shortcode_atts(array(
        'user' => 'false',
        'email_known' => 'true',
        'name' => 'true',
        'gender' => 'true',
        'car_make' => 'true',
        'car_model' => 'true',
        'ssn' => 'true',
        'num_of_kids' => 'true',
        'dob' => 'true',
        'passwords' => 'true',
        'phone_numbers' => 'true',
        'employers' => 'true',
        'sources' => 'true',
        'logins' => 'true',
        'passwords_hints' => 'true',
        'websites' => 'true',
        'ip_addresses' => 'true',
        'addresses' => 'true',
        'jobs' => 'true',
        'ad_interests' => 'true',
        'known_hacked_accounts' => 'true',
        'known_languages' => 'true',
        'known_cell_phone_records' => 'true',
        'phone_coordinates' => 'true',
        'trump_voter_likeness' => 'true',
        'gmail_gcmid' => 'true',
        'sex_preference' => 'true',
        'dating_limits' => 'true',
        'race' => 'true',
        'height' => 'true',
        'weight' => 'true',
        'eye_color' => 'true',
        'hair_color' => 'true',
        'skye_id' => 'true',
        'msn_id' => 'true',
        'instagram_id' => 'true',
        'linkedin_id' => 'true',
        'google_id' => 'true',
        'facebook_id' => 'true',
        'youtube_id' => 'true',
        'twitter_id' => 'true',
        'table' => 'true',
        'div' => 'true',
        'item' => 'false'
    ), $atts);
    $user_id = '';
    if($atts['user'] === 'false'){
        $user_id = get_current_user_id();
    } else {
        global $WP_User;
        $user = get_user_by('login', $atts['user']);
        if(!empty($user)){
            $user_id = $user->ID;
        }
    }
    $email_meta = !empty(get_user_meta($user_id, 'dark_sources_email_meta', true)) ? unserialize(get_user_meta($user_id, 'dark_sources_email_meta', true)) : FALSE;
    if(!empty($email_meta)){
        if($atts['item'] !== 'false'){
            $single = isset($atts['item']) ? $atts['item'] : FALSE;
            if(!empty($single)){
                return '<span class="email-meta-single">' . return_translation($email_meta[$single], $single) . '</span>';
            } else {
                return 'Email Item not Found';
            }
        } else {
            if($atts['table'] === 'true'){
                $html = '<table class="email_meta_list">';
                foreach ($email_meta as $key => $value){
                    $attsKey = isset($atts[$key]) ? $atts[$key] : FALSE;
                    if($attsKey === 'true'){
                        $html .= '<tr>';
                        $html .= '<td>'. ucwords(str_replace('_',' ',$key)) . '</td>';
                        $html .= '<td>'. ucwords(return_translation($value, $key)) . '</td>';
                        $html .= '</tr>';
                    }
                }
                $html .= '</table>';
                return $html;
            } else {
                $html = '<div class="email_meta_div">';
                foreach ($email_meta as $key => $value){
                    $attsKey = isset($atts[$key]) ? $atts[$key] : FALSE;
                    if($attsKey === 'true'){
                        $html .= '<div>'. ucwords(str_replace('_',' ',$key)) . '</div>';
                        $html .= '<div>'. ucwords(return_translation($value, $key)) . '</div>';
                    }
                }
                $html .= '</div>';
                return $html;
            }
        }
    } else {
        return 'No Email Data Found';
    }
   
}
add_shortcode('display-email-meta', 'DarkSources\Shortcodes\display_email_meta_shortcode', 10, 2);

// ------------------------------------------------ translate 0/1 to custom messages ----------------------------------------------- /

function return_translation($data, $item){
    $boolArray = array('passwords','sources','phone_numbers','employers','sources','logins','password_hints','websites','ip_addresses','jobs','addresses','ad_interests','number_of_kids','known_hacked_accounts','known_languages','cell_phone_hardware','phone_coordinates','trump_voter_likeness');
    if (!in_array($item, $boolArray)){
        if($data === '0'){
            $data = 'Empty';
        } else if ($data === '1'){
            $data = 'Data Exists';
        }
    } 
    return $data;
}