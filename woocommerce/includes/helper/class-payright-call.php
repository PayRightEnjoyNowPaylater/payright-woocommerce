<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once payright_plugin_path() . '/woocommerce/includes/config.php';

class Payright_Call
{
    public $rates;

    public $conf;

    public $establishment_fees_array;

    public $rdata;

    public $api_end_point;

    public static function get_rates()
    {
        unset($_SESSION['rates']);

        $api_end_point     = constant("PAYRIGHT_APIENDPOINT");
        $api_url           = "api/v1/merchant/configuration";
        $data              = array();

        $url = $api_end_point . $api_url;

        try {
            $pr_json_decode = self::payrightApiGet($url, $data);

            if ($pr_json_decode != null || $pr_json_decode == "error") {
                if (isset($pr_json_decode->code) || $pr_json_decode == "error") {
                    return false;
                } else {
                    return $pr_json_decode;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return "Error";
        }
    }
    public static function callPayrightApi($url, $data)
    {
        $theme_options = get_option('woocommerce_payright_gateway_settings');
        $accesstoken  = $theme_options['accesstoken'];

        $args = array(
            'body'        => $data,
            'timeout'     => '15',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     =>  array('Content-Type' =>  'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer ' . $accesstoken),
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

    public static function payrightApiGet($url, $data)
    {

        $theme_options = get_option('woocommerce_payright_gateway_settings');
        $accesstoken  = $theme_options['accesstoken'];

        $args = array(
            'body'        => $data,
            'timeout'     => '15',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     =>  array('Content-Type' =>  'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer ' . $accesstoken),
            'cookies'     => array(),
        );

        $payright_response  = wp_remote_get($url, $args);
        $payright_body      = wp_remote_retrieve_body($payright_response);
        $payright_http_code = wp_remote_retrieve_response_code($payright_response);

        if ($payright_http_code != 200) {
            return "error";
        } else {
            return json_decode($payright_body);
        }
    }

    public static function payright_initialize_transaction($cart_total, $orderId)
    {
        $rand                                = substr(md5(time()), 0, 5);
        $ref                                 = 'WooPr_' . $rand;
        $api_end_point                       = constant("PAYRIGHT_APIENDPOINT");
        $api_url                             = "api/v1/checkouts/";
        $data = array(
            "merchantReference" => $ref,
            "saleAmount" => $cart_total,
            "type"     => "standard",
            "redirectUrl"   => get_rest_url() . 'api/v1/payrightresponse/?id=' . $orderId,

        );
        $url = $api_end_point . $api_url;

        try {
            $pr_json_decode      = self::callPayrightApi($url, json_encode($data));
            $redirectUrl = $pr_json_decode->data->redirectEndpoint;

            return $redirectUrl;
        } catch (\Exception $e) {
            return "Error";
        }
    }

    public static function payright_activate_plan($checkoutId)
    {
        $api_end_point = constant("PAYRIGHT_APIENDPOINT");
        $data = array();
        $api_url = "api/v1/checkouts/";
        $url = $api_end_point . $api_url . $checkoutId . "/activate";
        $theme_options = get_option('woocommerce_payright_gateway_settings');
        $accesstoken  = $theme_options['accesstoken'];

        try {
            $args = array(
                'body'        => $data,
                'timeout'     => '15',
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     =>  array('Content-Type' =>  'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer ' . $accesstoken),
                'cookies'     => array(),
                'method'       => 'PUT',
            );

            $payright_response  = wp_remote_request($url, $args);
            $payright_body      = wp_remote_retrieve_body($payright_response);
            $payright_http_code = wp_remote_retrieve_response_code($payright_response);

            if ($payright_http_code != 200) {
                return "error";
            } else {
                return json_decode($payright_body);
            }
        } catch (\Exception $e) {
            echo "Error";
        }
    }

    //used to get plan status
    public static function payright_get_plan_data_by_token($token)
    {
        $api_end_point          = constant("PAYRIGHT_APIENDPOINT");
        $api                    = "api/v1/checkouts/" . $token;
        $url = $api_end_point . $api;
        $data = array();

        try {
            $pr_json_decode = self::payrightApiGet($url, $data);

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

        if ($enabled == "yes" && !isset($_SESSION['rates'])) {

            try {
                $get_api_configuration    = self::get_rates();
                if (isset($get_api_configuration->data)) {
                    $rates                   = $get_api_configuration->data->rates;
                    $conf                     = $get_api_configuration->data->otherFees;
                    $establishment_fees_array = $get_api_configuration->data->establishmentFees;

                    $_SESSION['rates']                  = $rates;
                    $_SESSION['establishmentFeesArray'] = $establishment_fees_array;
                    $_SESSION['other']                   = $conf;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                return "Error";
            }
        } 
    }

    public static function payright_calculate_single_product_installment($sale_amount)
    {

        if (isset($_SESSION['rates']) && $sale_amount > 0) {

            $get_rates                = $_SESSION['rates'];
            $conf                     = $_SESSION['other'];
            $establishment_fees_array = $_SESSION['establishmentFeesArray'];


            // Get your 'minimum deposit amount', from 'rates' data received and sale amount.
            $minimumDepositAndTerm = self::get_minimum_deposit_and_term($get_rates, $sale_amount);

            if (empty($minimumDepositAndTerm)) {
                return false;
            }

            $account_keeping_fees = $conf->monthlyAccountKeepingFee;
            $payment_processing_fee = $conf->paymentProcessingFee;

            $get_min_deposit = $minimumDepositAndTerm['minimumDepositAmount'];
            $loan_term = $minimumDepositAndTerm['minimumDepositTerm'];
            $loan_amount = $sale_amount - $get_min_deposit;
            $get_frequancy   = self::payright_get_payment_frequancy($account_keeping_fees, $loan_term);

            $calculated_no_of_repayments     = $get_frequancy['numberofRepayments'];
            $calculated_account_keeping_fees = $get_frequancy['accountKeepingFees'];
            $formated_loan_amount = number_format((float) $loan_amount, 2, '.', '');

            $res_establishment_fees = self::payright_get_establishment_fees($loan_term, $establishment_fees_array);

            $CalculateRepayments = self::payright_calculate_repayment(
                $calculated_no_of_repayments,
                $calculated_account_keeping_fees,
                $res_establishment_fees,
                $formated_loan_amount,
                $payment_processing_fee
            );

            $payrightResult = array($calculated_no_of_repayments, $CalculateRepayments, $get_min_deposit);
            return $payrightResult;

        } else {
            return false;
        }
    }

    public static function payright_fetch_loan_term_for_sale($rates, $sale_amount)
    {
        $rates_array = array();

        foreach ($rates as $key => $rate) {
            $rates_array[$key]['Term'] = $rate->term;
            $rates_array[$key]['Min']  = $rate->minimumPurchase;
            $rates_array[$key]['Max']  = $rate->maximumPurchase;

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

    public static function get_minimum_deposit_and_term($rates, $saleAmount)
    {
        // Iterate through each term, apply the minimum deposit to the sale amount and see if it fits in the rate card. If not found, move to a higher term
        foreach ($rates as $rate) {
            $minimumDepositPercentage = $rate->minimumDepositPercentage;
            $depositAmount = $saleAmount * ($minimumDepositPercentage / 100);
            $loanAmount = $saleAmount - $depositAmount;

            // Check if loan amount is within range
            if ($loanAmount >= $rate->minimumPurchase && $loanAmount <= $rate->maximumPurchase) {
                return [
                    'minimumDepositPercentage' => $minimumDepositPercentage,
                    // If above PHP 7.4 check, source: https://www.php.net/manual/en/function.money-format.php
                    'minimumDepositAmount' => function_exists('money_format') ? money_format('%.2n', $depositAmount) : sprintf('%01.2f', $depositAmount),
                    'minimumDepositTerm' => $rate->term,
                ];
            }
        }
        // No valid term and deposit found
        return [];
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
        $h = 0;
        foreach ($establishment_fees as $key => $row) {
            $fee_band_array[$key]['term']            = $row->term;
            $fee_band_array[$key]['initial_est_fee'] = $row->initialEstFee;
            $fee_band_array[$key]['repeat_est_fee']  = $row->repeatEstFee;

            if ($fee_band_array[$key]['term'] == $loan_term) {
                $h = $row->initialEstFee;
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
