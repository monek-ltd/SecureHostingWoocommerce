<?php

class WC_Gateway_UPG extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'gateway';
        $this->has_fields = false;
        $this->icon = apply_filters('woocommerce_upg_icon', plugins_url('upg.png', __FILE__));

        // these are used in WordPress administration, in WooCommerce > Settings > Payments.
        $this->method_title = __('SecureHosting', 'woocommerce');
        $this->method_description = __('Pay securely via SecureHosting with your credit/debit card.', 'woocommerce');

        // load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // define user set variables
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->reference = $this->get_option('reference');
        $this->checkcode = $this->get_option('checkcode');
        $this->sharedSecret = $this->get_option('sharedSecret');
        $this->ordstatus = $this->get_option('ordstatus');
        $this->filename = $this->get_option('filename');
        $this->activateas = isset($this->settings['activateas']) && $this->settings['activateas'] == 'yes' ? 'yes' : 'no';
        $this->phrase = $this->get_option('phrase');
        $this->referrer = $this->get_option('referrer');
        $this->testmode = isset($this->settings['testmode']) && $this->settings['testmode'] == 'yes' ? 'yes' : 'no';

        // SecureHosting hosted pages urls
        $this->testurl = 'https://test.secure-server-hosting.com/secutran/secuitems.php';
        $this->liveurl = 'https://www.secure-server-hosting.com/secutran/secuitems.php';

        // save settings
        if (is_admin()) {
            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
            } else {
                add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
            }
        }

        // hooks
        add_action('woocommerce_api_wc_gateway_upg', array(&$this, 'wc_gateway_upg_callbacks'));
    }

    function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enabled', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable SecureHosting Payment Gateway', 'woocommerce'),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'woocommerce'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default' => __('Credit/Debit Card', 'woocommerce'),
                'desc_tip' => true
            ),
            'description' => array(
                'title' => __('Description', 'woocommerce'),
                'type' => 'text',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default' => __('Pay securely via SecureHosting with your credit/debit card.', 'woocommerce'),
                'desc_tip' => true
            ),
            'reference' => array(
                'title' => __('SH Reference', 'woocommerce'),
                'type' => 'text',
                'description' => __('Your SecureHosting account reference.', 'woocommerce'),
                'default' => '',
                'placeholder' => 'SH2XXXXX',
                'desc_tip' => true
            ),
            'checkcode' => array(
                'title' => __('Check Code', 'woocommerce'),
                'type' => 'text',
                'description' => __('The check code for your SecureHosting account.', 'woocommerce'),
                'default' => '',
                'placeholder' => 'XXXXXX',
                'desc_tip' => true
            ),
            'sharedSecret' => array(
                'title' => __('Shared Secret', 'woocommerce'),
                'type' => 'text',
                'description' => __('The shared secret used to verify callbacks from SecureHosting.', 'woocommerce'),
                'default' => '',
                'placeholder' => '',
                'desc_tip' => true
            ),
            'filename' => array(
                'title' => __('File Name', 'woocommerce'),
                'type' => 'text',
                'description' => __('File name for the payment page templates uploaded to your SecureHosting account.', 'woocommerce'),
                'default' => 'woo_template.html',
                'desc_tip' => true
            ),
            'testmode' => array(
                'title' => __('Test Mode', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Only allow test transactions with test cards.', 'woocommerce'),
                'default' => 'yes',
                'description' => __('Test mode can be used to test payments.', 'woocommerce'),
                'desc_tip' => true
            ),
            'activateas' => array(
                'title' => __('Enable Advanced Secuitems', 'woocommerce'),
                'type' => 'checkbox',
                'default' => 'no',
                'description' => __('Advanced Secuitems is a feature to prevent the transaction amount being altered before payment.', 'woocommerce'),
                'desc_tip' => true
            ),
            'phrase' => array(
                'title' => __('Advanced Secuitems Phrase', 'woocommerce'),
                'type' => 'text',
                'description' => __('A phrase to be used to sign your transaction data sent to UPG.', 'woocommerce'),
                'desc_tip' => true
            ),
            'referrer' => array(
                'title' => __('Advanced Secuitems Referrer', 'woocommerce'),
                'type' => 'text',
                'description' => __('The full URL referrer of your shopping cart', 'woocommerce'),
                'desc_tip' => true
            )
        );
    }

    public function admin_options()
    {
        echo '<h3>' . __('SecureHosting Payment Gateway for WooCommerce', 'woocommerce') . '</h3>';
        echo '<p>' . __('The SecureHosting Payment Gateway sends customers to your secure payment page, hosted by Monek. Please visit <a href="http://www.secure-server-hosting.com/" target="_blank"> Secure Hosting</a> for more information.') . '</p>';
        echo '<table class="form-table">';

        // Generate the HTML For the settings form.
        $this->generate_settings_html();
        echo '</table>';
    }

    public function wc_gateway_upg_callbacks()
    {
        // grab the action param
        $action = $_REQUEST['action'];

        // switch on the action
        switch ($action) {
            case "callback":
                // grab the order
                $order_id = $_REQUEST['order_id'];
                $this->handle_payment_callback($order_id);
                wp_die('OK', '', array('response' => 200));
                break;
            case "redirect":
                $order_id = $_REQUEST['order_id'];
                $this->build_redirect_page($order_id);
                break;
            case "thankyou":
                $order_id = $_REQUEST['order_id'];
                $this->handle_redirect_from_upg($order_id);
                break;
            default:
                wp_die('Incorrect method supplied.');
                break;
        }
    }

    function process_payment($order_id)
    {
        // build the redirect url
        $redirectUrl = WooCommerce::api_request_url('wc_gateway_upg');
        $redirectUrl .= strpos($redirectUrl, '?') === false ? '?' : '&';
        $redirectUrl .= 'action=redirect&order_id=' . $order_id;

        return array(
            'result' => 'success',
            'redirect' => $redirectUrl
        );
    }

    function handle_payment_callback($order_id)
    {
        // grab the order
        $order = wc_get_order($_REQUEST['order_id']);

        // grab the transaction number & auth code
        $transactionNumber = $_REQUEST['transactionnumber'];
        $failureReason = $_REQUEST['failurereason'];
        $verify = $_REQUEST['verify'];

        // did we fail the verification? (no secret saved = no verification, otherwise is must match the provided signature)
        if ($this->sharedSecret !== "" && !(isset($_REQUEST['verify']) && $this->verify_callback($verify, $this->sharedSecret, $transactionNumber))) {
            $note = 'Callback received using an invalid shared secret. Please check with Monek if this transaction was successful. (Transaction ID ' . $transactionNumber . ')';
            $order->add_order_note(__($note, 'woocommerce'));
            return;
        }

        // did the payment fail?
        if (!isset($_REQUEST['upgauthcode']) && isset($_REQUEST['failurereason']) && $transactionNumber === '-1') {

            $note = 'Payment declined: ' . $failureReason;
            $order->add_order_note(__($note, 'woocommerce'));
            $order->update_status('failed');
            return;
        }

        // mark as payment complete
        $andVerified = $this->sharedSecret !== '' ? ' and callback verified ' : ' ';
        $note = 'Payment confirmed' . $andVerified . ': (Transaction ID ' . $transactionNumber . ')';
        $order->add_order_note(__($note, 'woocommerce'));
        $order->payment_complete();
        return;
    }

    function build_redirect_page($order_id)
    {
        global $woocommerce;
        $order = wc_get_order($order_id);

        // product data
        $secuitems = $this->build_secuitems($order->get_items());

        $transactionSubTotal = $this->to_standard_format($order->get_subtotal());
        $transactionAmount = $this->to_standard_format($order->get_total());

        $transactionData = array();

        // order Details
        $transactionData['shreference'] = $this->reference;
        $transactionData['checkcode'] = $this->checkcode;
        $transactionData['filename'] = $this->reference . '/' . $this->filename;
        $transactionData['orderid'] = $order->id;
        $transactionData['subtotal'] = $transactionSubTotal;
        $transactionData['transactionamount'] = $transactionAmount;
        $transactionData['transactioncurrency'] = get_woocommerce_currency();
        $transactionData['transactiontax'] = $this->to_standard_format($order->get_total_tax());
        $transactionData['shippingcharge'] = $this->to_standard_format($order->get_total_shipping());
        $transactionData['transactiondiscount'] = $this->to_standard_format($order->get_total_discount());
        $transactionData['secuitems'] = $secuitems;

        // callback
        $callbackUrl = site_url(); // WooCommerce::api_request_url('wc_gateway_upg'); $callbackUrl .= strpos($callbackUrl, '?') === false ? '?' : '&';
        $callbackData = "wc-api|wc_gateway_upg|action|callback|order_id|$order_id";

        $transactionData['callbackurl'] = $callbackUrl;
        $transactionData['callbackdata'] = $callbackData;

        // success redirect
        $successUrl = WooCommerce::api_request_url('wc_gateway_upg');
        $successUrl .= strpos($successUrl, '?') === false ? '?' : '&';
        $successUrl .= 'action=thankyou';
        $successUrl .= '&order_id=' . $order->id;

        $transactionData['success_url'] = $successUrl;

        // back to cart redirect
        $transactionData['return_url'] = $woocommerce->cart->get_cart_url();

        // should we secure the transaction?
        if ($this->activateas == 'yes') {
            if (preg_match('/value=\"([a-zA-Z0-9]{32})\"/', $this->sign_secuitems($secuitems, $transactionAmount), $Matches))
                $transactionData['secuString'] = $Matches[1];
        }

        // cardholder details
        $transactionData['cardholdersname'] = $order->billing_first_name . ' ' . $order->billing_last_name;
        $transactionData['cardholdersemail'] = $order->billing_email;
        $transactionData['cardholdercompany'] = $order->billing_company;
        $transactionData['cardholderaddr1'] = $order->billing_address_1;
        $transactionData['cardholderaddr2'] = $order->billing_address_2;
        $transactionData['cardholdercity'] = $order->billing_city;
        $transactionData['cardholderstate'] = $order->billing_state;
        $transactionData['cardholderpostcode'] = $order->billing_postcode;
        $transactionData['cardholdercountry'] = $order->billing_country;
        $transactionData['cardholdertelephonenumber'] = $order->billing_phone;

        // delivery Details
        $transactionData['deliveryname'] = $order->shipping_first_name . ' ' . $order->shipping_last_name;
        $transactionData['deliverycompany'] = $order->shipping_company;
        $transactionData['deliveryaddr1'] = $order->shipping_address_1;
        $transactionData['deliveryaddr2'] = $order->shipping_address_2;
        $transactionData['deliverycity'] = $order->shipping_city;
        $transactionData['deliverystate'] = $order->shipping_state;
        $transactionData['deliverypostcode'] = $order->shipping_postcode;
        $transactionData['deliverycountry'] = $order->shipping_country;

        // build the form
        $redirectForm = '<form action="' . $this->get_upg_url() . '" method="post">';
        foreach ($transactionData as $field => $value) {
            $redirectForm .= '<input type="hidden" name="' . $field . '" value="' . $value . '">';
        }
        $redirectForm .= '</form>';
        $redirectForm .= '<script>document.forms[0].submit();</script>';
        $redirectForm .= '<div>Please wait while we redirect you to our secure payment form...</div>';

        wp_die($redirectForm, array('response' => 200));
    }

    function handle_redirect_from_upg($order_id)
    {
        global $woocommerce;

        $order = wc_get_order($order_id);

        if (!isset($order)) {
            // show an error
            wp_die("Order ID not recognised.", array('response' => 500));
            return;
        }

        // check the status, if the callback already happened then do not go back to on-hold
        if ($order->needs_payment()) {
            // mark as on-hold (we're awaiting the payment confirmation via callback)
            $order->update_status('on-hold');
            $order->add_order_note(__('Awaiting payment confirmation from SecureHosting.', 'woocommerce'));
        }

        // reduce stock levels
        $order->reduce_order_stock();

        // remove cart
        $woocommerce->cart->empty_cart();

        // redirect to thank you page
        $thankyou = WC_Payment_Gateway::get_return_url($order);
        wp_redirect($thankyou);
    }

    function verify_callback($verify, $sharedSecret, $transactionNumber)
    {
        global $woocommerce;

        return $verify === sha1($sharedSecret . $transactionNumber . $sharedSecret);
    }

    function build_secuitems($products)
    {
        $secuitems = '';
        foreach ($products AS $item) {
            $Options = array();

            foreach ($item As $Attribute => $Value) {
                if (!in_array($Attribute, $this->get_standard_product_fields())) {
                    $Options[$Attribute] = $Value;
                }
            }

            $secuitems .= '[' . $item['product_id'] . '||' . htmlentities($item['name'], ENT_QUOTES);

            if (!empty($Options)) {
                foreach ($Options AS $Key => $Value) {
                    $secuitems .= ', ' . htmlentities($Key, ENT_QUOTES) . ': ' . htmlentities($Value, ENT_QUOTES);
                }
            }
            $secuitems .= '|' . $this->to_standard_format($item['line_total'] / $item['qty'])
                . '|' . $item['qty']
                . '|' . $this->to_standard_format($item['line_total']) . ']';
        }

        return $secuitems;
    }

    function sign_secuitems($secuitems, $transactionAmount)
    {
        // calculate the hash of the products, the amount and the shared phrase
        $secustring = 'value="' . md5($secuitems . $transactionAmount . $this->phrase) . '"';

        return $secustring;
    }

    function get_upg_url()
    {
        return ($this->testmode === 'yes') ? $this->testurl : $this->liveurl;
    }

    function get_standard_product_fields()
    {
        return array(
            'product_id',
            'name',
            'line_total',
            'line_tax_data',
            '_line_tax_data',
            'wc_cog_item_cost',
            'wc_cog_item_total_cost',
            'qty',
            'type',
            'item_meta',
            'item_meta_array',
            'line_subtotal_tax',
            'line_tax',
            'line_subtotal',
            'variation_id',
            'tax_class',
            'pa_booking-class',
            'standard-class-adult',
            'tmcartepo_data',
            'gravity_forms_history',
            '_gravity_form_data',
            'display_description',
            'disable_woocommerce_price',
            'price_before',
            'price_after',
            'disable_calculations',
            'disable_label_subtotal',
            'disable_label_options',
            'disable_label_total',
            'label_subtotal',
            'Subtotal',
            'label_options',
            'Options',
            'label_total',
            'Total'
        );
    }

    function to_standard_format($Number)
    {
        return number_format($Number, 2, '.', '');
    }
}
