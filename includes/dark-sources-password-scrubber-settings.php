<?php //Settings plugin functions and html display
namespace DarkSources\Settings;
use DarkSources\Helper as Helper;
use DarkSources\API as API;
use DarkSources\Options as Options;
//use DarkSources as DS;
// If this file is called directly, abort.
if (!defined('WPINC')){
	die;

}
//------------------------------------------------ add admin menu page ------------------------------------------------/

function dark_sources_admin_menu(){
    //add icon to settings menu and create settings page
    $menu_icon = plugins_url( '/assets/images/dark-sources-icon.png', dirname( __FILE__ ));
    add_menu_page('Admin Menu','Dark Sources Settings','manage_options','dark-sources-admin-menu','DarkSources\Settings\dark_sources_display_admin_page',$menu_icon,100);
}
add_action('admin_menu', 'DarkSources\Settings\dark_sources_admin_menu');

//------------------------------------------------ display the admin page and functions ------------------------------------------------/

function dark_sources_display_admin_page(){
    //update fields
    if(!empty($_POST)){
        $nonce_name   = isset($_POST['dark_sources_nonce']) ? $_POST['dark_sources_nonce'] : '';
        $nonce_action = 'dark_sources_action';
        if(!isset($nonce_name) || !wp_verify_nonce($nonce_name, $nonce_action)){
            return;
        }
        Options\dark_sources_update_loop();
    //wordpress save/error notice
    ?>
    <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><strong>Settings have been saved!</strong></div>
    <?php } ?>
        <div id="dark-sources-settings" class="wrap">
            <!-- html header -->
            <div class="header section">
                <a href="https://www.darksources.com" target="_blank"><img src="<?php echo plugins_url('/assets/images/dark-sources-logo.png', dirname( __FILE__ ))?>"/></a>
                <h1><?php _e('Password/Security Risk Plugin','darksrouces'); ?></h1>
            </div>
            <form method="post" action="">
                <!-- security nonce -->
                <?php wp_nonce_field('dark_sources_action', 'dark_sources_nonce'); ?>
                <!-- Plugin Settings html -->
                <div class="api-settings section">
                    <h2><?php _e('API Settings / Subscription Information','darksrouces'); ?></h2>
                    <!-- create conditional for displaying registration options if not verified-->
                    <div id="registration-information">
                        <?php
                            //run api check 
                            $api_key =  Options\update_get_option_field('dark_sources_api_key', 'get');
                            $ds = API\load_api_helper($api_key);
                            //set subscription variable
                            if(empty($api_key)){
                                $subscriber = array();
                            } else {
                                $api_check = API\api_call($ds, 'keystats', $api_key);
                                if(empty($api_check)){
                                    $subscriber = array();
                                }
                                if((int)$api_check['status_code'] === 0){
                                    $subscriber[] = 'valid';
                                } else {
                                    $subscriber[] = 'invalid';
                                }
                                if(isset($api_check['data']['plan_overage_queries']) && $api_check['data']['plan_overage_queries'] >= 1){
                                    $subscriber[] = 'paid';
                                }
                            }
                            if(!in_array('valid', $subscriber)){
                                //registration information
                                $r =  wp_remote_get('https://api.darksources.com/data/billing_plans/');
                                $subscription_plans = json_decode(wp_remote_retrieve_body($r));
                                if(!empty($subscription_plans)){
                                ?>
                                <!-- sales content -->
                                <div id="sales-content">
                                    <p>Dark Sources offers an API risk management service allowing you to secure your users from passwords that are idendified as high risk.<br />
                                    To find out more information on the service visit <a href="https://www.darksources.com/passwordscrubber.html" target="_blank"> our product page</a></p>
                                    <p>To view your personal data Dark Sources has collected on you, please request a free profile on our site <a href="https://www.freehackreport.com/">FreeHackReport.com</a></p>
                                    <p>We also highly recommend <a href="https://www.webiron.com/">WebIron</a> for complete cloud based real-time bot and automation website protection. They've were the first and still are the #1 fully AI based on the wire real-time security platform around.</p>
                                </div>
                                <div class="input-wrap">
                                    <label for="plan_range_slider"><?php _e('Slide button to change plan', 'dark-sources-password-scrubber'); ?></label>
                                    <input name="plan_range_slider" id="plan-range-slider" type="range" min="1" max="" value="1">
                                </div>
                                <div id="subscription-wrap">
                                <?php
                                 $allowed_html = array(
                                    'a' => array(
                                        'href' => array(),
                                        'target' => array(),
                                        'title' => array(),
                                    ),
                                    'br' => array(),
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
                                foreach($subscription_plans[0]->plans as $plan){
                                    ?>
                                    <div class="plan">
                                        <h1><?php echo filter_var($plan->name, FILTER_SANITIZE_STRING); ?></h1>
                                        <strong><?php echo filter_var($plan->monthly_cost, FILTER_SANITIZE_STRING); ?></strong>
                                        <p><?php echo html_entity_decode(wp_kses($plan->description, $allowed_html)); ?></p>
                                        <a class="button" href="<?php echo esc_url($plan->url) . '?ref=' . DARK_SOURCES_AFFILIATE_ID; ?>" target="_blank">SIGN UP NOW</a>                                            
                                    </div>
                                <?php
                                    //fix loop bug and clear memory
                                    unset($plan);
                                } ?>
                                </div>
                            <?php }
                            } else {
                                //subscription information - queries left, etc.
                                ?>
                                <div class="flex">
                                    <strong><?php _e('Billing Date','dark-sources-password-scrubber') ?></strong><p><?php echo $api_check['data']['next_rebill']; ?></p>
                                    <strong><?php _e('Allowed Monthly Queries','dark-sources-password-scrubber') ?></strong><p><?php echo number_format($api_check['data']['plan_monthly_queries']); ?></p>
                                    <strong><?php _e('Cost for Overage Queries (US Dollars)','dark-sources-password-scrubber') ?></strong><p>$<?php echo $api_check['data']['plan_overage_price']; ?></p>
                                    <strong><?php _e('Monthly Overage Queries','dark-sources-password-scrubber') ?></strong><p><?php echo number_format($api_check['data']['plan_overage_queries']); ?></p>
                                    <strong><?php _e('Remaining Monthly Queries','dark-sources-password-scrubber') ?></strong><p><?php echo number_format($api_check['data']['queries_left_before_rebill']); ?></p>
                                </div>
                            <?php } ?>
                    </div>
        <!-- global settings -->
                    <div class="input-wrap">
                        <label for="dark_sources_api_key"><?php _e('Please enter a valid API key below. To remove a key clear text and submit.', 'dark-sources-password-scrubber'); ?> <?php echo in_array('valid', $subscriber) || in_array('paid', $subscriber) ? '<span>(<span class="green">API key verified</span>)</span>' : '<span class="red">A Valid API Key is required.</span>'; ?>:</label>
                        <input type="text" name="dark_sources_api_key" id="dark_sources_api_key" class="text" placeholder="Please enter a valid API Key" value="<?php echo in_array('valid', $subscriber) ? 'Valid Key' : ''; ?>">
                    </div>
                </div>
                <div class="tolerance-settings section">
                    <h2><?php _e('Trigger Settings','darksrouces'); ?></h2>
                    <div class="input-wrap">
                        <input type="checkbox" name="dark_sources_password_exists_checkbox" id="dark_sources_password_exists_checkbox" class="checkbox" value="checked" <?php echo (Options\update_get_option_field('dark_sources_password_exists_checkbox', 'get') === 'checked' ? 'checked' : '');?>>
                        <label for="dark_sources_password_exists_checkbox"><?php _e('Password exists for user in Dark Sources\'s hacker database', 'dark-sources-password-scrubber'); ?></label>
                    </div>
                    <div class="input-wrap">
                        <input type="checkbox" name="dark_sources_password_rank_checkbox" id="dark_sources_password_rank_checkbox" class="checkbox" value="checked" <?php echo (Options\update_get_option_field('dark_sources_password_rank_checkbox', 'get') === 'checked' ? 'checked' : '');?>>
                        <label for="dark_sources_password_rank_checkbox"><?php _e('Known password usage is in the top', 'dark-sources-password-scrubber'); ?></label><input type="text" name="dark_sources_password_rank_tolerance" id="dark_sources_password_rank_tolerance" class="number format-number" value="<?php echo Options\update_get_option_field('dark_sources_password_rank_tolerance', 'get'); ?>"><label for="dark_sources_password_rank_tolerance" class="number-label"> <?php _e(' ( default: 1,000,000 top: 900 million)', 'dark-sources-password-scrubber'); ?></label>
                    </div>
                    <div class="input-wrap">
                        <input type="checkbox" name="dark_sources_password_match_checkbox" id="dark_sources_password_match_checkbox" class="checkbox" value="checked" <?php echo (Options\update_get_option_field('dark_sources_password_match_checkbox', 'get') === 'checked' ? 'checked' : '');?>>
                        <label for="dark_sources_password_match_checkbox"><?php _e('User has used the password at least', 'dark-sources-password-scrubber'); ?></label><input type="text" name="dark_sources_password_match_tolerance" id="dark_sources_password_match_tolerance" class="number format-number" value="<?php echo Options\update_get_option_field('dark_sources_password_match_tolerance', 'get'); ?>"><label for="dark_sources_password_match_tolerance" class="number-label"> <?php _e(' time(s) on other clean sites. ( default: 1 )', 'dark-sources-password-scrubber'); ?></label>
                    </div>
                    <div class="input-wrap">
                        <input type="checkbox" name="dark_sources_password_other_match_checkbox" id="dark_sources_password_other_match_checkbox" class="checkbox" value="checked" <?php echo (Options\update_get_option_field('dark_sources_password_other_match_checkbox', 'get') === 'checked' ? 'checked' : '');?>>
                        <label for="dark_sources_password_other_match_checkbox"><?php _e('Password has been used at least', 'dark-sources-password-scrubber'); ?></label><input type="text" name="dark_sources_password_other_match_tolerance" id="dark_sources_password_other_match_tolerance" class="number format-number" value="<?php echo Options\update_get_option_field('dark_sources_password_other_match_tolerance', 'get'); ?>"><label for="dark_sources_password_other_match_tolerance" class="number-label"> <?php _e(' time(s) on other clean sites. ( default: 2 )', 'dark-sources-password-scrubber'); ?></label>
                    </div>
                </div>
                <div class="notification-settings section">
                    <h2><?php _e('Action Settings','darksrouces'); ?></h2>

        <!-- On Password Change -->

                    <p><?php _e('1. On Password Change','darksrouces'); ?></p>
                    <!-- if paid plan - else grey out/disable -->
                    <div class="input-wrap <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'relative' : ''; ?>">
                        <input type="checkbox" name="dark_sources_reject_message_checkbox" id="dark_sources_reject_message_checkbox" class="checkbox <?php echo in_array('valid', $subscriber) && in_array('paid', $subscriber) ? 'checkbox-animate' : ''; ?>" value="checked" <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'readonly' : ''; ?>
                        <?php 
                            if (!in_array('valid', $subscriber)){
                                echo 'checked';
                            } else if(in_array('valid', $subscriber) && !in_array('paid', $subscriber)){
                                echo 'checked';
                            } else if(Options\update_get_option_field('dark_sources_reject_message_checkbox', 'get') === 'checked' && in_array('valid', $subscriber) && in_array('paid', $subscriber)) {
                                echo 'checked';
                            } ?>
                        >
                        <label class="<?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'disabled' : ''; ?>" for="dark_sources_reject_message_checkbox"><?php _e('Reject password', 'dark-sources-password-scrubber'); ?><a href="" class="custom-trigger <?php echo in_array('valid', $subscriber) || in_array('paid', $subscriber) ? 'paid' : '' ?>"><?php _e('create custom message'); ?></a></label>
                    </div>
                    <?php if(in_array('valid', $subscriber) && in_array('paid', $subscriber)){ ?>
                    <div class="input-wrap textarea-wrap">
                        <textarea name="dark_sources_reject_custom_message" id="dark_sources_reject_custom_message" class="textarea" placeholder="Enter custom message here (paid plan only) or leave blank for default" <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'disabled' : ''; ?>><?php echo Options\update_get_option_field('dark_sources_reject_custom_message', 'get') ?></textarea>
                    </div>
                    <?php  }  ?>
                    <!-- if paid plan - else grey out/disable -->
                    <div class="input-wrap <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'relative' : ''; ?>">
                        <input type="checkbox" name="dark_sources_email_message_checkbox" id="dark_sources_email_message_checkbox" class="checkbox <?php echo in_array('valid', $subscriber) && in_array('paid', $subscriber) ? 'checkbox-animate' : ''; ?>" value="checked" <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'readonly' : ''; ?>
                        <?php 
                            if(!in_array('valid', $subscriber)){
                                echo 'checked';
                            } else if(in_array('valid', $subscriber) && !in_array('paid', $subscriber)){
                                echo 'checked';
                            } else if(Options\update_get_option_field('dark_sources_email_message_checkbox', 'get') === 'checked' && in_array('valid', $subscriber) && in_array('paid', $subscriber)) {
                                echo 'checked';
                            } ?>
                        >
                        <label class="<?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'disabled' : ''; ?>" for="dark_sources_email_message_checkbox"><?php _e('Send user notice email', 'dark-sources-password-scrubber'); ?><a href="" class="custom-trigger <?php echo in_array('valid', $subscriber) || in_array('paid', $subscriber) ? 'paid' : '' ?>"><?php _e('create custom email'); ?></a></label>
                    </div>
                    <?php if(in_array('valid', $subscriber) && in_array('paid', $subscriber)){ ?>
                    <div class="input-wrap textarea-wrap">
                        <textarea name="dark_sources_email_custom_message" id="dark_sources_email_custom_message" class="textarea" placeholder="Enter custom message here (paid plan only) or leave blank for default" <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'disabled' : ''; ?>><?php echo Options\update_get_option_field('dark_sources_email_custom_message', 'get') ?></textarea>
                    </div>
                    <?php  }  ?>
                    <!-- if free plan - email to include marketing -->
                    <div class="input-wrap">
                        <input type="checkbox" name="dark_sources_admin_message_checkbox" id="dark_sources_admin_message_checkbox" class="checkbox" value="checked"? <?php echo (Options\update_get_option_field('dark_sources_admin_message_checkbox', 'get') === 'checked' ? 'checked' : '');?>> 
                        <label for="dark_sources_admin_message_checkbox"><?php _e('Send alert to admin', 'dark-sources-password-scrubber'); ?></label>
                    </div>

    <!-- On Successful Login -->

                    <p><?php _e('2. On Successful Login','darksrouces'); ?></p>
                    <div class="input-wrap">
                        <input type="checkbox" name="dark_sources_login_force_password_change_checkbox" id="dark_sources_login_force_password_change_checkbox" class="checkbox" value="checked" <?php echo (Options\update_get_option_field('dark_sources_login_force_password_change_checkbox', 'get') === 'checked' ? 'checked' : '');?>>
                        <label for="dark_sources_login_force_password_change_checkbox"><?php _e('Force password change', 'dark-sources-password-scrubber'); ?></label>
                    </div>
                    <!-- if paid plan - else grey out/disable -->
                    <div class="input-wrap <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'relative' : ''; ?>">
                        <input type="checkbox" name="dark_sources_login_pop_up_message_checkbox" id="dark_sources_login_pop_up_message_checkbox" class="checkbox <?php echo in_array('valid', $subscriber) && in_array('paid', $subscriber) ? 'checkbox-animate' : ''; ?>" value="checked" <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'readonly' : ''; ?>
                        <?php
                            if(!in_array('valid', $subscriber)){
                                echo 'checked';
                            } else if(in_array('valid', $subscriber) && !in_array('paid', $subscriber)){
                                echo 'checked';
                            } else if(Options\update_get_option_field('dark_sources_login_pop_up_message_checkbox', 'get') === 'checked' && in_array('valid', $subscriber) && in_array('paid', $subscriber)) {
                                echo 'checked';
                            } ?>
                        >
                        <label class="<?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'disabled' : ''; ?>" for="dark_sources_login_pop_up_message_checkbox"><?php _e('Popup alert to user', 'dark-sources-password-scrubber'); ?><a href="" class="custom-trigger <?php echo in_array('valid', $subscriber) || in_array('paid', $subscriber) ? 'paid' : '' ?>"><?php _e('create custom message'); ?></a></label>
                    </div>
                    <?php if(in_array('valid', $subscriber) && in_array('paid', $subscriber)){ ?>
                    <div class="input-wrap textarea-wrap">
                        <textarea name="dark_sources_login_pop_up_custom_message" id="dark_sources_login_pop_up_custom_message" class="textarea" placeholder="Enter custom message here (paid plan only) or leave blank for default" <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'disabled' : ''; ?>><?php echo Options\update_get_option_field('dark_sources_login_pop_up_custom_message', 'get') ?></textarea>
                    </div>
                    <?php  }  ?>
                    <!-- if paid plan - else grey out/disable -->
                    <div class="input-wrap <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'relative' : ''; ?>">
                        <input type="checkbox" name="dark_sources_login_email_message_checkbox" id="dark_sources_login_email_message_checkbox" class="checkbox <?php echo in_array('valid', $subscriber) && in_array('paid', $subscriber) ? 'checkbox-animate' : ''; ?>" value="checked" <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'readonly' : ''; ?>
                        <?php
                            if(!in_array('valid', $subscriber)){
                                echo 'checked';
                            } else if(in_array('valid', $subscriber) && !in_array('paid', $subscriber)){
                                echo 'checked';
                            } else if(Options\update_get_option_field('dark_sources_login_email_message_checkbox', 'get') === 'checked' && in_array('valid', $subscriber) && in_array('paid', $subscriber)) {
                                echo 'checked';
                            } ?>
                        >
                        <label class="<?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'disabled' : ''; ?>" for="dark_sources_login_email_message_checkbox"><?php _e('Send user notice email', 'dark-sources-password-scrubber'); ?><a href="" class="custom-trigger <?php echo in_array('valid', $subscriber) || in_array('paid', $subscriber) ? 'paid' : '' ?>"><?php _e('create custom email'); ?></a></label>
                    </div>
                    <?php if(in_array('valid', $subscriber) && in_array('paid', $subscriber)){ ?>
                    <div class="input-wrap textarea-wrap">
                        <textarea name="dark_sources_login_email_custom_message" id="dark_sources_login_email_custom_message" class="textarea" placeholder="Enter custom message here (paid plan only) or leave blank for default" <?php echo !in_array('valid', $subscriber) || !in_array('paid', $subscriber) ? 'disabled' : ''; ?>><?php echo Options\update_get_option_field('dark_sources_login_email_custom_message', 'get') ?></textarea>
                    </div>
                    <?php  }  ?>
                    <div class="input-wrap">
                        <input type="checkbox" name="dark_sources_login_admin_message_checkbox" id="dark_sources_login_admin_message_checkbox" class="checkbox" value="checked"? <?php echo (Options\update_get_option_field('dark_sources_login_admin_message_checkbox', 'get') === 'checked' ? 'checked' : '');?>> 
                        <label for="dark_sources_login_admin_message_checkbox"><?php _e('Send alert to admin', 'dark-sources-password-scrubber'); ?></label>
                    </div>
                </div>
                <!-- form submit -->
                <input type="submit" class="button button-primary" name="dark_sources_settings_update" id="dark_sources_settings_update" value="<?php _e('SAVE ALL SETTINGS', 'dark-sources-password-scrubber'); ?>">
            </form>
        <div>
        <!-- plugin "footer" information -->
        <p>Visit <a href="https://billing.darksources.com" target="_blank">https://billing.darksources.com</a> to change your current plan.</p>
    </div>
<?php
}
