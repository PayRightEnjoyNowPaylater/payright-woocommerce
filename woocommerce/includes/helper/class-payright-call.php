<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once payright_plugin_path() . '/woocommerce/includes/config.php';

class Payright_Call
{

    public $payright_access_token;

    public $payright_refresh_token;

    public $rates;

    public $conf;

    public $establishment_fees_array;

    public $rdata;

    public $api_end_point;

    public static function authenticate_payright_api_call()
    {

        $api_url       = "oauth/token";
        $theme_options = get_option('woocommerce_payright_gateway_settings');
        $sandbox_mode  = $theme_options['sandbox'];

        if ($sandbox_mode == 'yes') {
            $api_end_point = 'https://api.payright.com.au/';
        } else {
            $api_end_point = 'https://liveapi.payright.com.au/';
        }

        $username      = $theme_options['username'];
        $password      = $theme_options['password'];
        $grant_type    = 'password';
        $client_id     = $theme_options['client_id'];
        $client_secret = $theme_options['client_secret'];
        $reponse_array = array();

        $data = array(
            "username"      => "" . $username . "",
            "password"      => "" . $password . "",
            "grant_type"    => "" . $grant_type . "",
            "client_id"     => "" . $client_id . "",
            "client_secret" => "" . $client_secret . "",
        );
        $url = $api_end_point . $api_url;

        try {

            $pr_json_decode = self::json_decode_data($url, $data, false, null);
            if (isset($pr_json_decode->error) || $pr_json_decode == 'error') {
                return false;
            } else {
                $payright_access_token  = $pr_json_decode->access_token;
                $payright_refresh_token = $pr_json_decode->refresh_token;

                $reponse_array['payrightAccessToken']  = $payright_access_token;
                $reponse_array['payrightRefreshToken'] = $payright_refresh_token;

                if (isset($payright_access_token)) {
                    $reponse_array['status'] = 'Authenticated';
                }

                return $reponse_array;
            }
        } catch (\Exception $e) {
            return "Authentication Error";
        }
    }

    public static function get_rates()
    {
        unset($_SESSION['rates']);

        $api_end_point     = constant("PAYRIGHT_APIENDPOINT");
        $api_url           = "api/v1/configuration";
        $theme_options     = get_option('woocommerce_payright_gateway_settings');
        $merchantusername  = $theme_options['merchantusername'];
        $merchantpasswword = $theme_options['merchantpassword'];

        $data              = array(
            "merchantusername" => "" . $merchantusername . "",
            "merchantpassword" => "" . $merchantpasswword . "",
        );

        $url = $api_end_point . $api_url;

        try {
            $pr_json_decode = self::json_decode_data_get($url, $data);

            if ($pr_json_decode != null || $pr_json_decode == "error") {
                if (isset($pr_json_decode->code) || $pr_json_decode == "error") {
                    return false;
                } else {
                    $rdata = $pr_json_decode->data;
                    return $rdata;
                }
            } else {
                return false;
            }

        } catch (\Exception $e) {
            return "Error";
        }

    }

    public static function json_decode_data($url, $data, $bearer = false, $para_access_token = false)
    {
        $args = array(
            'body'        => $data,
            'timeout'     => '15',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => ($bearer != false && $para_access_token != false) ? array('Authorization' => 'Bearer ' . $para_access_token) : array(),
            'cookies'     => array(),
        );

        $payright_response  = wp_remote_post($url, $args);
        $payright_body      = wp_remote_retrieve_body($payright_response);
        $payright_http_code = wp_remote_retrieve_response_code($payright_response);

        if ($payright_http_code != 200) {
            return "error";
        } else {
            return json_decode($payright_body);
        }
    }

    public static function json_decode_data_get($url, $data)
    {

        $url = $url . "/" . $data['merchantusername'] . "/" . $data['merchantpassword'];

        $payright_response  = wp_remote_get($url);
        $payright_body      = wp_remote_retrieve_body($payright_response);
        $payright_http_code = wp_remote_retrieve_response_code($payright_response);
        if ($payright_http_code != 200) {
            return "error";
        } else {
            return json_decode($payright_body);
        }
    }

    public static function payright_configuration($access_token)
    {

        $api_end_point     = constant("PAYRIGHT_APIENDPOINT");
        $api_url           = "api/v1/configuration";
        $theme_options     = get_option('woocommerce_payright_gateway_settings');
        $merchantusername  = $theme_options['merchantusername'];
        $merchantpasswword = $theme_options['merchantpassword'];
        $client_id         = $theme_options['client_id'];
        $data              = array(
            "merchantusername" => "" . $merchantusername . "",
            "merchantpassword" => "" . $merchantpasswword . "",
            "client_id"        => "" . $client_id . "",
        );

        $url = $api_end_point . $api_url;

        try {
            $pr_json_decode = self::json_decode_data($url, $data, "Bearer", $access_token);

            if ($pr_json_decode != null || $pr_json_decode == "error") {
                if (isset($pr_json_decode->code) || $pr_json_decode == "error") {

                    return false;
                } else {
                    $rdata = $pr_json_decode->data;
                    return $rdata;
                }
            } else {
                return false;
            }

        } catch (\Exception $e) {

            return "Error";
        }

    }

    public static function payright_transaction_configuration($access_token)
    {
        $api_end_point     = constant("PAYRIGHT_APIENDPOINT");
        $api_url           = "api/v1/initialTransactionConfiguration";
        $theme_options     = get_option('woocommerce_payright_gateway_settings');
        $merchantusername  = $theme_options['merchantusername'];
        $merchantpasswword = $theme_options['merchantpassword'];
        $client_id         = $theme_options['client_id'];
        $sandbox_mode      = $theme_options['sandbox'];

        $data = array(
            "merchantusername" => "" . $merchantusername . "",
            "merchantpassword" => "" . $merchantpasswword . "",
            "client_id"        => "" . $client_id . "",
        );

        if ($sandbox_mode == 'yes') {
            $api_end_point = 'https://api.payright.com.au/';
        } else {
            $api_end_point = 'https://liveapi.payright.com.au/';
        }

        $url = $api_end_point . $api_url;

        try {
            $pr_json_decode = self::json_decode_data($url, $data, "Bearer", $access_token);

            if ($pr_json_decode != null || $pr_json_decode == "error") {
                if (isset($pr_json_decode->code) || $pr_json_decode == "error") {

                    return false;
                } else {
                    $rdata = $pr_json_decode->data;
                    return $rdata;
                }
            } else {
                return false;
            }

        } catch (\Exception $e) {
            return "Error";
        }

    }

    public static function payright_initialize_transaction($jsondata, $cart_total, $wooordernumber, $access_token)
    {

        $theme_options = get_option('woocommerce_payright_gateway_settings');
        $client_id     = $theme_options['client_id'];

        $api_end_point = constant("PAYRIGHT_APIENDPOINT");

        $rand                                = substr(md5(time()), 0, 5);
        $ref                                 = 'WooPr_' . $rand;
        $api_url                             = "api/v1/intialiseTransaction";
        $config_token                        = $jsondata->configToken;
        $auth_token                          = $jsondata->auth->{'auth-token'};
        $transdata['platform_type']          = 'wordpress';
        $transdata['transactionTotal']       = $cart_total;
        $transdata['woocommerceordernumber'] = $wooordernumber;
        $encode_checkout_session_data        = json_encode($transdata);

        $data = array(
            "Token" => $auth_token,
            "ConfigToken"         => $config_token,
            "transactiondata"     => $encode_checkout_session_data,
            "merchantReference"   => $ref,
            "totalAmount"         => $cart_total,
            "clientId"            => $client_id,

        );

        $url = $api_end_point . $api_url;

        try {
            $pr_json_decode      = self::json_decode_data($url, $data, "Bearer", $access_token);
            $ecomm_token         = $pr_json_decode->ecommToken;
            $result_config_token = $pr_json_decode->configToken;

            return array($ecomm_token, $result_config_token);

        } catch (\Exception $e) {
            return "Error";
        }

    }

    public static function payright_activate_plan($plan_number)
    {

        $api_end_point = constant("PAYRIGHT_APIENDPOINT");
        $api_url       = "api/v1/changePlanStatus";
        $auth          = self::authenticate_payright_api_call();
        $rdata         = self::payright_transaction_configuration($auth['payrightAccessToken']);

        $config_token = $rdata->configToken;
        $auth_token   = $rdata->auth->{'auth-token'};
        // $product_price = $productprice;

        $data = array(
            "Token"  => $auth_token,
            "id"     => $plan_number,
            "status" => 'Active',

        );

        $url = $api_end_point . $api_url;

        try {

            $pr_json_decode = self::json_decode_data($url, $data, "Bearer", $auth['payrightAccessToken']);

            return $pr_json_decode;

        } catch (\Exception $e) {
            echo "Error";
        }

    }

    public static function payright_cancel_plan($plan_number)
    {

        $api_end_point = constant("PAYRIGHT_APIENDPOINT");
        $api_url       = "api/v1/changePlanStatus";
        $auth          = self::authenticate_payright_api_call();
        $rdata         = self::payright_transaction_configuration($auth['payrightAccessToken']);

        $config_token = $rdata->configToken;
        $auth_token   = $rdata->auth->{'auth-token'};
        // $product_price = $productprice;

        $data = array(
            "Token"  => $auth_token,
            "id"     => $plan_number,
            "status" => 'Cancelled',

        );

        $url = $api_end_point . $api_url;

        try {

            $pr_json_decode = self::json_decode_data($url, $data, "Bearer", $auth['payrightAccessToken']);

            return $pr_json_decode;

        } catch (\Exception $e) {
            echo "Error";
        }

    }

// used to check plan status for plan activation
    public static function payright_query_transaction($plan_number)
    {
        // payright_query_transaction
        $api_end_point = constant("PAYRIGHT_APIENDPOINT");
        $api           = "api/v2/getQueryTransaction";

        $auth         = self::authenticate_payright_api_call();
        $rdata        = self::payright_transaction_configuration($auth['payrightAccessToken']);
        $config_token = $rdata->configToken;
        $auth_token   = $rdata->auth->{'auth-token'};

        $url  = $api_end_point . $api;
        $data = array(
            'prToken'     => $auth_token,
            'configToken' => $config_token,
            'planNumber'  => $plan_number,
        );

        try {
            $pr_json_decode = self::json_decode_data($url, $data, "Bearer", $auth['payrightAccessToken']);

            return $pr_json_decode;

        } catch (\Exception $e) {
            echo "Error";
        }

    }

//used to get plan status
    public static function payright_get_plan_data_by_token($ecommerceToken)
    {
        $api_end_point          = constant("PAYRIGHT_APIENDPOINT");
        $api                    = "api/v1/getEcomTokenData";
        $auth_token             = self::authenticate_payright_api_call();
        $get_pay_right_access_token = $auth_token['payrightAccessToken'];

        $url  = $api_end_point . $api;
        $data = array(
            'ecomToken' => $ecommerceToken,
        );

        try {
            $pr_json_decode = self::json_decode_data($url, $data, "Bearer", $get_pay_right_access_token);

            return $pr_json_decode;

        } catch (\Exception $e) {
            return "Error";
        }

    }

// gets rates
    public static function payright_get_session_value()
    {
        $theme_options    = get_option('woocommerce_payright_gateway_settings');
        $enabled          = $theme_options['enabled'];
        $merchantusername = $theme_options['merchantusername'];
        $username         = $theme_options['username'];
        if ($enabled == "yes" && !isset($_SESSION['rates'])) {
            try {

                $get_api_configuration    = self::get_rates();
                $rates                    = $get_api_configuration->rates;
                $conf                     = $get_api_configuration->conf;
                $establishment_fees_array = $get_api_configuration->establishment_fee;

                $_SESSION['monthlyAccountFees']     = $conf->{'Monthly Account Keeping Fee'};
                $_SESSION['rates']                  = $rates;
                $_SESSION['establishmentFeesArray'] = $establishment_fees_array;
                $_SESSION['paymentProcessingFee']   = $conf->{'Payment Processing Fee'};
                $_SESSION['conf']                   = $conf;
            } catch (\Exception $e) {
                return "Error";
            }
        } else { }
    }

    //calculates instalments
    public static function payright_calculate_single_product_installment($sale_amount)
    {

        $get_rates                = $_SESSION['rates'];
        $conf                     = $_SESSION['conf'];
        $establishment_fees_array = $_SESSION['establishmentFeesArray'];

        if (isset($get_rates) && $sale_amount > 0) {
            $payright_installment_approval = self::payright_get_maximum_sale_amount($get_rates, $sale_amount);

            if ($payright_installment_approval == 0) {
                $account_keeping_fees = $conf->{'Monthly Account Keeping Fee'};
                $payment_processing_fee = $conf->{'Payment Processing Fee'};

                $loan_term       = self::payright_fetch_loan_term_for_sale($get_rates, $sale_amount);
                $get_min_deposit = self::get_calculate_min_deposit($get_rates, $sale_amount, $loan_term);
                $get_frequancy   = self::payright_get_payment_frequancy($account_keeping_fees, $loan_term);

                $calculated_no_of_repayments     = $get_frequancy['numberofRepayments'];
                $calculated_account_keeping_fees = $get_frequancy['accountKeepingFees'];

                $loan_amount          = $sale_amount - $get_min_deposit;
                $formated_loan_amount = number_format((float) $loan_amount, 2, '.', '');

                $res_establishment_fees = self::payright_get_establishment_fees($loan_term, $establishment_fees_array);

                $establishment_fee_per_payment = $res_establishment_fees / $calculated_no_of_repayments;
                $loan_amount_per_payment          = $formated_loan_amount / $calculated_no_of_repayments;

                $CalculateRepayments = self::payright_calculate_repayment(
                    $calculated_no_of_repayments,
                    $calculated_account_keeping_fees,
                    $res_establishment_fees,
                    $formated_loan_amount,
                    $payment_processing_fee
                );

                $payrightResult = array($calculated_no_of_repayments, $CalculateRepayments);
                return $payrightResult;

            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public static function payright_get_maximum_sale_amount($get_rates, $sale_amount)
    {
        $chk_loan_limit = 0;

        $keys = array_keys($get_rates);

        for ($i = 0; $i < count($get_rates); $i++) {
            foreach ($get_rates[$keys[$i]] as $key => $value) {
                if ($key == 4) {
                    $get_val[] = $value;
                }
            }
        }

        //var_dump($get_val);

        if (max($get_val) < $sale_amount) {
            $chk_loan_limit = 1;
        }

        return $chk_loan_limit;
    }

    public static function payright_fetch_loan_term_for_sale($rates, $sale_amount)
    {
        $rates_array = array();

        foreach ($rates as $key => $rate) {
            $rates_array[$key]['Term'] = $rate['2'];
            $rates_array[$key]['Min']  = $rate['3'];
            $rates_array[$key]['Max']  = $rate['4'];

            if (($sale_amount >= $rates_array[$key]['Min'] && $sale_amount <= $rates_array[$key]['Max'])) {
                $generateloan_term[] = $rates_array[$key]['Term'];
            }
        }

        if (isset($generateloan_term)) {
            return min($generateloan_term);
        } else {
            return 0;
        }
    }

    public static function get_calculate_min_deposit($get_rates, $sale_amount, $loan_term)
    {
        for ($i = 0; $i < count($get_rates); $i++) {
            for ($l = 0; $l < count($get_rates[$i]); $l++) {
                if ($get_rates[$i][2] == $loan_term) {
                    $per[] = $get_rates[$i][1];
                }
            }
        }

        if (isset($per)) {
            $percentage = min($per);
            $value      = $percentage / 100 * $sale_amount;

            // If above PHP 7.4 check, source: https://www.php.net/manual/en/function.money-format.php
            if (function_exists('money_format')) {
                return money_format('%.2n', $value);
            } else {
                return sprintf('%01.2f', $value);
            }
        } else {
            return 0;
        }

    }

    public static function payright_get_payment_frequancy($account_keeping_fees, $loan_term)
    {
        $repayment_frequecy = 'Fortnightly';

        if ($repayment_frequecy == 'Weekly') {
            $j = floor($loan_term * (52 / 12));
            $o = $account_keeping_fees * 12 / 52;
        }

        if ($repayment_frequecy == 'Fortnightly') {
            $j = floor($loan_term * (26 / 12));
            if ($loan_term == 3) {
                $j = 7;
            }
            $o = $account_keeping_fees * 12 / 26;
        }

        if ($repayment_frequecy == 'Monthly') {
            $j = parseInt(k);
            $o = $account_keeping_fees;
        }

        $number_of_repayments = $j;
        $account_keeping_fees = $o;

        $return_array['numberofRepayments'] = $number_of_repayments;
        $return_array['accountKeepingFees'] = round($account_keeping_fees, 2);

        return $return_array;
    }

    public static function payright_get_establishment_fees($loan_term, $establishment_fees_array)
    {
        $establishment_fees  = $establishment_fees_array;
        $fee_band_array      = array();
        $fee_band_calculator = 0;

        foreach ($establishment_fees as $key => $row) {
            $fee_band_array[$key]['term']            = $row->term;
            $fee_band_array[$key]['initial_est_fee'] = $row->initial_est_fee;
            $fee_band_array[$key]['repeat_est_fee']  = $row->repeat_est_fee;

            if ($fee_band_array[$key]['term'] == $loan_term) {
                $h = $row->initial_est_fee;
            }

            $fee_band_calculator++;
        }
        return $h;
    }

    public static function payright_calculate_repayment($number_of_repayments, $account_keeping_fees, $establishment_fees, $loan_amount, $payment_processing_fee)
    {

        $repayment_amount_init = ((floatval($establishment_fees) + floatval($loan_amount)) / $number_of_repayments);

        $repayment_amount = floatval($repayment_amount_init) + floatval($account_keeping_fees) + floatval($payment_processing_fee);
        return bcdiv($repayment_amount, 1, 2);
    }

}
