<?php
$theme_options = get_option('woocommerce_payright_gateway_settings');
$sandbox       = $theme_options['sandbox'];

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");

if (strtolower($sandbox) == "yes") {

    define("PAYRIGHT_ENDPOINT", "https://betaonline.payright.com.au/loan/new/");
    define("PAYRIGHT_APIENDPOINT", "https://api.payright.com.au/");

} else {

    define("PAYRIGHT_ENDPOINT", "https://online.payright.com.au/loan/new/");
    define("PAYRIGHT_APIENDPOINT", "https://liveapi.payright.com.au/");
}

