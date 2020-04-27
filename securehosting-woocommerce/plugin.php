<?php
/*
    Plugin Name: SecureHosting Payment Gateway for WooCommerce
    Description: Plugin allowing integration between WooCommerce and the SecureHosting Payment Gateway, for secure and robust processing of online card payments.
    Version: 1.0
    Author: Monek Ltd
    Author URI: http://www.monek.com

    NOTE: This header comment is required for WordPress, see https://developer.wordpress.org/plugins/plugin-basics/header-requirements/#header-fields for details.
*/

function initialise()
{
    if (!class_exists('WC_Payment_Gateway')) return;

    require_once('gateway.php');

    // add gateway to WooCommerce
    function add_gateway($methods)
    {
        $methods[] = 'SecureHostingGateway';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_gateway');

    // add 'Settings' link, beneath plugin name.
    function add_settings_link($links)
    {
        $settings_url = admin_url('admin.php?page=wc-settings&tab=checkout&section=securehosting');

        $plugin_links = [
            '<a href="' . $settings_url . '">' . __('Settings', 'securehosting') . '</a>'
        ];

        $links = array_merge($plugin_links, $links);

        return $links;
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_settings_link');

    // add 'View on GitHub' link, beneath description, following version and author.
    function add_github_link($links, $file)
    {
        if (strpos($file, 'plugin.php') !== false)
        {
            $github_url = 'https://github.com/monek-ltd/SecureHostingWoocommerce';

            $new_links = [
                '<a href="' . $github_url . '" target="_blank">View on GitHub</a>'
            ];

            $links = array_merge($links, $new_links);
        }

        return $links;
    }

    add_filter('plugin_row_meta', 'add_github_link', 10, 2);
}

add_action('plugins_loaded', 'initialise', 0);
