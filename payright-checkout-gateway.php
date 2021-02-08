<?php
/**
 * Plugin Name: Payright
 * Description: A Payment gateway for Payright checkout
 * Author: Payright
 * Author URI: https://www.payright.com.au/
 * Text Domain: wc-gateway-payright
 * Version: 1.0.0
 *
 * Copyright: (c) 2019 Payright
 *
 * Payright is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Payright is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Payright. If not, see <http://www.gnu.org/licenses/>.
 */

// defined('ABSPATH') or exit;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include "woocommerce/includes/helper/class-payright-call.php";

/**
 * check if multisite for activation
 */

if (!class_exists('Payright_WC_Dependencies')) {
    require_once 'woocommerce/includes/class-payright-wc-dependencies.php';
}

/**
 * Woocommerce Detection
 */
if (!function_exists('is_woocommerce_active')) {
    /**
     * Checking the WooCommerce Enable Status
     * @return bool
     */
    function is_woocommerce_active()
    {
        return Payright_WC_Dependencies::woocommerce_active_check();
    }
}

add_action('woocommerce_init', 'payright_start_session', 0);

function payright_start_session()
{
    if (session_id() == '') {
        session_start();
    }
    Payright_Call::payright_get_session_value();
}

//adds instalments to shop, product, home and checkout pages
function payright_shop_installments($price, $product)
{
    $des = '';
    global $woocommerce_loop;

    if (is_object($product)) {
        $product_price = $product->get_price();
    } else {
        $product_price = $product;
    }

    $type = $product->get_type();
    $currency = get_woocommerce_currency();
    $image_url = plugin_dir_url(__FILE__) . 'woocommerce/images/payrightlogo_rgb.png';

    $theme_options = get_option('woocommerce_payright_gateway_settings');
    $enabled = $theme_options['enabled'];
    $minamount = (float) $theme_options['minamount'];
    $product_instalments = $theme_options['installments'];

    if (($type == "simple" || $type == "variable") && !is_admin()) {

        $listinstallments = $theme_options['listinstallments'];
        $front_page_instalments = $theme_options['frontinstallments'];
        $related_product_instalments = $theme_options['relatedinstallments'];

        if (($enabled == 'yes') && ($product_price >= $minamount) && ($currency == "AUD")) {
            $result = Payright_Call::payright_calculate_single_product_installment($product_price);

            if ($result != null || $result != false) {

                if ((is_shop() || is_product_category()) && $listinstallments == 'optionOne') {

                    $des = ("<div id='prshop'><p id='payrightshopinstallment'> Or " . $result[0] . " Fortnightly instalments of $" . $result[1] . " with<img id='payrightLogoimg' src='" . $image_url . "'/></p>
                            </div>");

                } elseif ($woocommerce_loop['name'] == 'related' && $related_product_instalments == 'optionOne' && is_product()) {

                    $des = ("<div id='prshop'><p id='payrightshopinstallment'> Or " . $result[0] . " Fortnightly instalments of $" . $result[1] . " with<img id='payrightLogoimg' src='" . $image_url . "'/></p>
                            </div>");
                } elseif (is_home() && $front_page_instalments == 'optionOne') {

                    $des = ("<div id='prshop'><p id='payrightshopinstallment'> Or " . $result[0] . " Fortnightly instalments of $" . $result[1] . " with<img id='payrightLogoimg' src='" . $image_url . "'/></p>
                            </div>");
                } elseif (is_front_page() && $front_page_instalments == 'optionOne') {

                    $des = ("<div id='prshop'><p id='payrightshopinstallment'> Or " . $result[0] . " Fortnightly instalments of $" . $result[1] . " with<img id='payrightLogoimg' src='" . $image_url . "'/></p>
                            </div>");
                } elseif (is_product() && $product_instalments == 'optionOne' && $woocommerce_loop['name'] != 'related') {

                    $des = ("</br> <div class='payrightProductInstalments'>Or " . $result[0] . " Fortnightly instalments of $" . $result[1] . " with<img id='productPayrightLogoImg' src='" . $image_url . "' /><a style='text-decoration: underline;' class='payright_opener654' id='payright_opener654'>Info</a></div>");
                } else {

                    $des = " ";
                }
            }
        }
    } elseif ($type == "variation") {
        if (($enabled == 'yes') && ($product_price >= $minamount) && ($currency == "AUD")) {
            $result = Payright_Call::payright_calculate_single_product_installment($product_price);
            if (is_product() && $product_instalments == 'optionOne' && $woocommerce_loop['name'] != 'related') {
                $des = ("</br> <div class='payrightProductInstalments'>Or " . $result[0] . " Fortnightly instalments of $" . $result[1] . " with<img id='payrightLogoimg' src='" . $image_url . "' ></img><a style='text-decoration: underline;' class='payright_opener654V' id='payright_opener654V'>Info</a></div>");
            }
        }
    } else {

        $des = " ";
    }

    return $price . $des;
}

add_filter('woocommerce_get_price_html', 'payright_shop_installments', 100, 2);

add_filter('woocommerce_available_payment_gateways', 'payright_filter_gateways', 1);

add_action('wp_footer', 'payright_modal_footer');
function payright_modal_footer()
{

    $primg = plugin_dir_url(__FILE__) . "woocommerce/images/payright-logo.png";
    ob_start();
    include "woocommerce/checkout/modal/popup.php";
    $output = ob_get_contents();
    ob_end_clean();

    echo " <div id='payright_modal654' class='payrightmodal' role='dialog' class='modal-popup payright modal-slide _inner-scroll _show' aria-describedby='modal-content-1' data-role='modal' data-type='popup' tabindex='0' ><!-- Modal content -->" . $output . " </div>";
}

// unsets payright_gateway
function payright_filter_gateways($gateway_list)
{

    $theme_options = get_option('woocommerce_payright_gateway_settings');
    $minamount = (float) $theme_options['minamount'];
    $enabled = $theme_options['enabled'];
    $currency = get_woocommerce_currency();

    if ($enabled != 'yes' && $currency != 'AUD') {
        unset($gateway_list['payright_gateway']);
    }

    if (is_checkout()) {

        $cart_total = WC()->cart->total;

        if ($cart_total === 0) {
            $orderId = get_query_var('order-pay');
            $order = wc_get_order($orderId);
            if($order) {
                $cart_total = $order->get_total();
            }
        }

        $intiliaze_configuration_transaction = Payright_Call::authenticate_payright_api_call();

        if ($intiliaze_configuration_transaction == false) {
            unset($gateway_list['payright_gateway']);
        } else {

            $result = Payright_Call::payright_calculate_single_product_installment($cart_total);
            $transaction_configuration = Payright_Call::payright_transaction_configuration($intiliaze_configuration_transaction['payrightAccessToken']);

            if (($cart_total < $minamount || $currency != 'AUD' || ($result == false))) {
                unset($gateway_list['payright_gateway']);
            } elseif ($transaction_configuration == false) {
                unset($gateway_list['payright_gateway']);
            }

        }

        if (array_key_exists('payright_gateway', $gateway_list)) {
            $gateway_list['payright_gateway']->title = __('Payright - Buy now pay later', 'woocommerce');
        }

    }

    return $gateway_list;
}

/**
 * Add the gateway to WC Available Gateways
 *
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + payright gateway
 */
function wc_payright_add_to_gateways($gateways)
{
    $gateways[] = 'WC_Gateway_Payright';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'wc_payright_add_to_gateways');

/**
 * Adds plugin page links
 *
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_payright_gateway_plugin_links($links)
{
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=payright_gateway') . '">' . __('Configure', 'wc-gateway-payright') . '</a>',

    );

    return array_merge($plugin_links, $links);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_payright_gateway_plugin_links');

function payright_scripts()
{
    // Register the script like this for the plugin:
    wp_enqueue_script('payrightpayment', plugins_url('/woocommerce/assets/js/payrightpayment.js', __FILE__), array(), '1.0.0', 'true');

    $theme_options = get_option('woocommerce_payright_gateway_settings');
    $cssoverride = $theme_options['moduleOverride'];

    $js_class = array(

        'payrightOverrideClass' => $cssoverride,
    );

    wp_localize_script('payrightpayment', 'payrightModuleOverride', $js_class);
}
add_action('wp_enqueue_scripts', 'payright_scripts', 5);

function payright_styles()
{
    // Register the style like this for a plugin:
    wp_enqueue_style('payright_style_modal', plugins_url('woocommerce/assets/css/payright_style_modal.css', __FILE__), array(), '1.0.0', 'all');
    wp_enqueue_style('payright_style_main', plugins_url('woocommerce/assets/css/payright_style_main.css', __FILE__), array(), '1.0.0', 'all');
    wp_enqueue_style('prpopup', plugins_url('woocommerce/assets/css/payright-modal.css', __FILE__), array(), '1.0.0', 'all');

    $theme_options = get_option('woocommerce_payright_gateway_settings');
    $custom_css = '';
    $custom_css = $theme_options['customCss'];

    wp_add_inline_style('payright_style_main', $custom_css, 'after');
}

add_action('wp_enqueue_scripts', 'payright_styles');

function payright_plugin_path()
{

    // gets the absolute path to this plugin directory
    return untrailingslashit(plugin_dir_path(__FILE__));
}

add_filter('woocommerce_locate_template', 'payright_wc_locate_template', 10, 3);

function payright_wc_locate_template($template, $template_name, $template_path)
{
    global $woocommerce;

    $_template = $template;

    if (!$template_path) {
        $template_path = $woocommerce->template_url;
    }

    $plugin_path = payright_plugin_path() . '/woocommerce/';

    // Look within passed path within the theme - this is priority
    $template = locate_template(
        array(
            $template_path . $template_name,
            $template_name,
        )
    );

    // Modification: Get the template from this plugin, if it exists
    if (!$template && file_exists($plugin_path . $template_name)) {
        $template = $plugin_path . $template_name;
    }

    // Use default template
    if (!$template) {
        $template = $_template;
    }

    // Return what we found
    return $template;
}

function payright_redirect($request)
{
    global $woocommerce;
    $url = "";

    if (!empty($_GET['ecommtoken'])) {
        $ecommtoken = ($_GET['ecommtoken']);
        $json = Payright_Call::payright_get_plan_data_by_token($ecommtoken);
        $result = json_decode($json->transactionResult);

        $transdata = json_decode($json->transactionData);
        $woo_order_number = $transdata->woocommerceordernumber;
        $order = wc_get_order($woo_order_number);

        if (!empty($result) && isset($result->prtransactionStatus)) {
            if (isset($result->planData)) {

                $plan_name = $result->planData->name;
                $planid = $result->planData->id;
                $plan_status = $result->planData->status;
                $transaction_status = $result->prtransactionStatus;
            }

            if ((strtolower($transaction_status) == 'approved') && (strtolower($plan_status) == 'approved')) {
                $order->update_status('processing', 'wc-gateway-payright');
                $url = $order->get_checkout_order_received_url();
                add_post_meta($woo_order_number, '_payright_plan_name', $plan_name, true);
                add_post_meta($woo_order_number, '_payright_plan_id', $planid);
                if (isset(WC()->cart)) {
                    WC()->cart->empty_cart();
                }
                wp_redirect($url);
                exit;
            } else {
                $order->update_status('cancelled', 'wc-gateway-payright');
                $plan_id_for_cancel = $result->planId;

                if (isset($plan_id_for_cancel) && $transaction_status != "Declined") {
                    Payright_Call::payright_cancel_plan($plan_id_for_cancel);

                }

                $url = $order->get_cancel_order_url_raw();
                if (!isset(WC()->session)) {
                    WC()->session = new WC_Session_Handler();
                    WC()->session->init();
                }

                wc_add_notice(__("Payright Checkout has been cancelled"), 'error');
                wp_redirect($url);
                exit;
            }

        } else {
            $order->update_status('cancelled', 'wc-gateway-payright');
            $url = $order->get_cancel_order_url_raw();
            if (!isset(WC()->session)) {
                WC()->session = new WC_Session_Handler();
                WC()->session->init();
            }
            wc_add_notice(__("Payright Checkout has been cancelled"), 'error');
            wp_redirect($url);
            exit;
        }
    }
}

add_action('rest_api_init', function () {
    register_rest_route(
        'api/v1',
        '/payrightresponse',
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'payright_redirect',
        )
    );
});

//Add callback if Status changed to Shipping

function pr_order_status_shipped_callback($order_id)
{
    $order = wc_get_order($order_id);

    // add_post_meta($order_id,'_orderdatapr',$order,true);
    $payment_method = get_post_meta($order_id, '_payment_method', true);

    if ($payment_method == "payright_gateway") {
        $planid = get_post_meta($order_id, '_payright_plan_id', true);
        // Check if the custom field has a value.
        if (!empty($planid)) {
            Payright_Call::payright_activate_plan($planid);
        }
    }
}

add_action('woocommerce_order_status_completed', 'pr_order_status_shipped_callback', 10, 1);

//add plan id field in order page
add_action('woocommerce_admin_order_data_after_order_details', 'payright_order_details_plan_id');
function payright_order_details_plan_id($order)
{
    ?>
    <?php
global $post;

    $id = $order->get_id();
    $is_payright = get_post_meta($id, '_payment_method', true);
    $plan_name = get_post_meta($id, '_payright_plan_name', true);

    if (($is_payright == 'payright_gateway') && (!empty($plan_name))):
    ?>
        <br class="clear" />
        <h4>Plan Details</h4>

        <div class="address">

            <p><strong>Payright Plan Name : <strong><?php echo $plan_name ?> </p>

        </div>

            <?php
endif;
}

function payright_admin_notices()
{
    global $post;
    if (isset($post->post_status)) {
        $status = $post->post_status;
    } else {
        $status = '';
    }
    if (isset($post->ID)) {
        $order_id = $post->ID;
    } else {
        $order_id = '';
    }
    $order = wc_get_order($order_id);
    $payment_method = get_post_meta($order_id, '_payment_method', true);
    if ($status == "wc-completed") {
        if ($payment_method == "payright_gateway") {
            $planname = get_post_meta($order_id, '_payright_plan_name', true);

            $json = Payright_Call::payright_query_transaction($planname);
            $result = $json->data;

            // Check if the custom field has a value.
            if (!empty($planname)) {
                $plan_status = $result->planStatus;

                if (isset($plan_status) && $plan_status == 'Active') {
                    echo '<div class="updated notice notice-success is-dismissible" id="planactive"><p><strong>Updated</strong>: Payright Plan has been activated.
                    </p></div>';
                } elseif ((array_key_exists('error', $result)) && $result->error_message != null) {
                    echo '<div class="notice-warning notice notice-success is-dismissible" id="planactive"><p>Payright Plan has not been activated.' . $result->error_message . 'Please contact support@payright.com.au.</p></div>';
                } else {
                    echo '<div class="notice-warning notice notice-success is-dismissible" id="planactive"><p>Payright Plan has not been activated.Please contact support@payright.com.au.</p></div>';
                }
            }
        }
    } elseif ($status == "wc-processing" && $payment_method == "payright_gateway") {
        echo '<div class="notice-warning notice notice-success is-dismissible" id="planactive"><p>Payright Plan will not be activated until order is Complete.</p></div>';
    } else {
    }
}

function payright_current_screen($current_screen)
{

    if ('shop_order' == $current_screen->id) {
        add_action('admin_notices', 'payright_admin_notices');
    }

}
add_action('current_screen', 'payright_current_screen');
add_action('admin_notices', 'payright_check_config');
function payright_check_config()
{
    $theme_options = get_option('woocommerce_payright_gateway_settings');
    $enabled = $theme_options['enabled'];
    $merchantusername = $theme_options['merchantusername'];
    $username = $theme_options['username'];

    if ($enabled == 'yes') {
        try {
            $auth = Payright_Call::authenticate_payright_api_call();

            if ($auth == null && $username != null) {
                echo '<div class="notice-error notice notice-error is-dismissible" ><p>Incorrect Payright API username and password</p></div>';
            }

            $result = Payright_Call::payright_transaction_configuration($auth['payrightAccessToken']);

            if ($result == false) {
                echo '<div class="notice-error notice notice-error is-dismissible" ><p>Incorrect Payright Merchant username and password</p></div>';
            }
        } catch (\Exception $e) {
            echo '<div class="notice-error notice notice-error is-dismissible" ><p>Invalid Credentials</p></div>';
        }
    }
}

/**
 * Payright Payment Gateway
 *
 *
 * @class         WC_Gateway_Payright
 * @extends        WC_Payment_Gateway
 * @version        1.0.0
 * @package        WooCommerce/Classes/Payment
 *
 */
add_action('plugins_loaded', 'wc_payright_gateway_init', 11);

function wc_payright_gateway_init()
{
    class WC_Gateway_Payright extends WC_Payment_Gateway
    {

        /**
         * Constructor for the gateway.
         */
        public function __construct()
        {
            $this->id = 'payright_gateway';
            $this->icon = apply_filters('woocommerce_payright_icon', '');
            $this->has_fields = false;
            $this->title = __('Payright - Interest Free Payments', 'wc-gateway-payright');
            $this->method_title = __('Payright - Interest Free Payments', 'wc-gateway-payright');
            $this->method_description = __('Payright redirects customers to Payright to enter their payment information', 'wc-gateway-payright');

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->enabled = $this->get_option('enabled');
            $this->title = 'Payright - Buy now pay later';
            $this->username = $this->get_option('username');
            $this->password = $this->get_option('password');
            $this->client_id = $this->get_option('client_id');
            $this->client_secret = $this->get_option('client_secret');
            $this->merchantusername = $this->get_option('merchantusername');
            $this->merchantpassword = $this->get_option('merchantpassword');
            $this->grant_type = $this->get_option('grant_type');
            $this->minamount = $this->get_option('minamount');

            $this->sandbox = $this->get_option('sandbox');
            $this->instructions = $this->get_option('instructions');
            $this->installments = $this->get_option('installments');
            $this->listinstallments = $this->get_option('listinstallments');
            $this->frontinstallments = $this->get_option('frontinstallments');
            $this->relatedinstallments = $this->get_option('relatedinstallments');
            $this->moduleOverride = $this->get_option('moduleOverride');
            $this->customCss = $this->get_option('customCss');
            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        }

        /**
         * Initialize Gateway Settings Form Fields
         */
        public function init_form_fields()
        {
            $this->form_fields = apply_filters('wc_payright_form_fields', array(

                'enabled' => array(
                    'title' => __('Enable/Disable', 'wc-gateway-payright'),
                    'type' => 'checkbox',
                    'label' => __('Enable Payright', 'wc-gateway-payright'),
                    'default' => 'No',
                    'description' => __('plugin may be automatically disabled if cart or product price is less than minimum amount.', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),

                'sandbox' => array(
                    'title' => __('Sandbox', 'wc-gateway-payright'),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'desc_tip' => true,
                ),

                'title' => array(
                    'title' => __('Title', 'wc-gateway-payright'),
                    'type' => 'text',
                    'disabled' => true,
                    'value' => 'Payright - Buy now pay later',
                    'default' => __('Payright', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),

                'username' => array(
                    'title' => __('API Username', 'wc-gateway-payright'),
                    'type' => 'text',

                    'default' => __('', 'wc-gateway-payright'),
                    'placeholder' => __('example@domain.com', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),
                'password' => array(
                    'title' => __('API Password', 'wc-gateway-payright'),
                    'type' => 'password',
                    'default' => __('', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),

                'client_id' => array(
                    'title' => __('Client ID', 'wc-gateway-payright'),
                    'type' => 'text',

                    'default' => __('', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),
                'client_secret' => array(
                    'title' => __('API Key', 'wc-gateway-payright'),
                    'type' => 'text',

                    'default' => __('', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),
                'merchant_details' => array(
                    'title' => __('Merchant Details', 'wc-gateway-payright'),
                    'type' => 'title',

                    'description' => __('Enter your details.', 'wc-gateway-payright'),
                ),
                'merchantusername' => array(
                    'title' => __('Merchant Username', 'wc-gateway-payright'),
                    'type' => 'text',

                    'default' => __('', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),
                'merchantpassword' => array(
                    'title' => __('Merchant Password', 'wc-gateway-payright'),
                    'type' => 'password',

                    'default' => __('', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),
                'minamount' => array(
                    'title' => __('Minimum Amount', 'wc-gateway-payright'),
                    'type' => 'text',
                    'description' => __('This amount determines if payright is enabled or not on Checkout and Product page', 'wc-gateway-payright'),
                    'default' => __('5', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),

                'installments' => array(
                    'title' => __('Show Payright instalments information on Product Page', 'wc-gateway-payright'),
                    'type' => 'select',
                    'options' => array(
                        'optionOne' => __('Yes'),
                        'optionTwo' => __('No')),
                    'default' => 'optionOne',
                ),
                'listinstallments' => array(
                    'title' => __('Show Payright instalments information on Shop Page', 'wc-gateway-payright'),
                    'type' => 'select',
                    'options' => array(
                        'optionOne' => __('Yes'),
                        'optionTwo' => __('No')),
                    'default' => 'optionOne',
                ),
                'frontinstallments' => array(
                    'title' => __('Show Payright instalments information on Front Page', 'wc-gateway-payright'),
                    'type' => 'select',
                    'options' => array(
                        'optionOne' => __('Yes'),
                        'optionTwo' => __('No')),
                    'default' => 'optionOne',
                ),
                'relatedinstallments' => array(
                    'title' => __('Show Payright instalments information on Related Products', 'wc-gateway-payright'),
                    'type' => 'select',
                    'options' => array(
                        'optionOne' => __('Yes'),
                        'optionTwo' => __('No')),
                    'default' => 'optionOne',
                ),
                'moduleOverride' => array(
                    'title' => __('Module overide CSS', 'wc-gateway-payright'),
                    'type' => 'textarea',
                    'description' => __('Enter your class or id of the element for your sticky header or element that overrides payright pop up'),
                    'default' => __('', 'wc-gateway-payright'),
                    'placeholder' => __('Enter your class or id of the element for your sticky header or element that overrides payright pop up for example: .classOne #elementId', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),
                'customCss' => array(
                    'title' => __('Custom CSS', 'wc-gateway-payright'),
                    'type' => 'textarea',
                    'description' => __('Enter your custom css'),
                    'default' => __('', 'wc-gateway-payright'),
                    'placeholder' => __('Enter your CSS here', 'wc-gateway-payright'),
                    'desc_tip' => true,
                ),

            ));
        }

        /**
         * Get the Payright request URL for an order.
         *
         * @param  WC_Order $order   Order object.
         * @param  bool     $sandbox Whether to use sandbox mode or not.
         * @return string
         */
        public function get_request_url($order)
        {
            $this->endpoint = constant("PAYRIGHT_ENDPOINT");
            if (WC()->cart->total === 0) {
                $cart_total = $order->get_total();
            } else {
                $cart_total = WC()->cart->total;
            }

            $payright_api_call = new Payright_Call();
            $auth = $payright_api_call->authenticate_payright_api_call();
            $json = $payright_api_call->payright_transaction_configuration($auth['payrightAccessToken']);
            list($resultecomm, $resultconfig) = $payright_api_call->payright_initialize_transaction($json, $cart_total, $order->get_order_number(), $auth['payrightAccessToken']);
            if (!isset($resultecomm)) {
                return array(
                    'result' => 'failure',
                    'messages' => 'Payright error',
                );

            } else {
                return $this->endpoint . $resultecomm;
            }

        }

        /**
         * Process the payment and return the result.
         *
         * @param  int $order_id Order ID.
         * @return array
         */
        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);

            // Reduce stock levels
            $order->reduce_order_stock();

            return array(
                'result' => 'success',
                'redirect' => $this->get_request_url($order),

            );
        }

        public function get_icon()
        {
            $icon_html = " ";
            $image_url = plugin_dir_url(__FILE__) . 'woocommerce/images/payrightlogo_rgb.png';

            $icon_html .= '<img src="' . $image_url . '"" id="pricon" />';
            return apply_filters('woocommerce_gateway_icon', $icon_html, $this->id);
            // return apply_filters( 'woocommerce_payright_icon', $icon );
        }
        public function get_description()
        {
            if (WC()->cart->total === 0) {
                $orderId = get_query_var('order-pay');
                $order = wc_get_order($orderId);
                $cart_total = $order->get_total();
            } else {
                $cart_total = WC()->cart->total;
            }

            $result = Payright_Call::payright_calculate_single_product_installment($cart_total);

            if (count($result) > 0 && $result[0] != '') {
                $description = '<div class="bodybox">
                <div class="payRight_container">
                <article>
                    <div class="payRight_columns">

                        <div class="insideColumns payRight_is-5" id="payrightis5">
                            <h2 class="payRightH2 paymentstitle" id="payrightmargin">' . $result[0] . ' Fortnightly instalments of $' . $result[1] . '</h2>
                            <p class="payRightPayment" id="payrightdeposit" >Excluding deposit</p>
                        </div>

                    </div>
                </article>

                <article>
                    <div class="payRight_columns">

                        <div class="insideColumns payRight_is-8" id="payrightis8">
                            <p class="payRightPayment" id="payrightdeposit">You will be directed to the Payright website to complete the application process.<br/>Once approved you will return to our page<br/>See Payright<a href="https://www.payright.com.au/terms-of-use/"  target=" "> Terms & Conditions</a> for further information.</p>
                        </div>
                    </div>
                </article>

                </div>

            </div> ';
            } else {
                $description = "";
            }

            return apply_filters('woocommerce_gateway_description', $description, $this->id);
        }
    } // end \WC_Gateway_Payright class
}
