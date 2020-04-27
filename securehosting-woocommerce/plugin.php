<?php
/*
    Plugin Name: SecureHosting Payment Gateway for WooCommerce
    Description: Plugin allowing integration between WooCommerce and the SecureHosting Payment Gateway, for secure and robust processing of online card payments.
    Version: 1.0
    Author: Monek Ltd
    Author URI: http://www.monek.com

    NOTE: This header comment is required for WordPress, see https://developer.wordpress.org/plugins/plugin-basics/header-requirements/#header-fields for details.
*/

function woocommerce_gateway_upg_init()
{
    if (!class_exists('WC_Payment_Gateway')) return;

    require_once('gateway.php');

    // add gateway to WooCommerce
    function woocommerce_add_upg_gateway($methods)
    {
        $methods[] = 'WC_Gateway_UPG';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_upg_gateway');

    // add 'Settings' link to action bar
    function wc_gateway_upg_action_links($links)
    {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_gateway_upg') . '">' . __('Settings', 'wc_gateway_upg') . '</a>'
        );

        // merge our link with the default ones
        return array_merge($plugin_links, $links);
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_gateway_upg_action_links');

    function wc_gateway_upg_plugin_metalinks($links, $file)
    {
        if (strpos($file, 'plugin.php') !== false) {

            $new_links = array(
                '<a href="https://github.com/monek-ltd/SecureHostingWoocommerce" target="_blank">Repo</a>'
            );

            $links = array_merge($links, $new_links);
        }

        return $links;
    }

    add_filter('plugin_row_meta', 'wc_gateway_upg_plugin_metalinks', 10, 2);
}

add_action('plugins_loaded', 'woocommerce_gateway_upg_init', 0);