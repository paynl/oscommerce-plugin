<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__ . '/paynl/Pay/Autoload.php');

class paynl {

    var $code, $title, $description, $enabled;
    public $apiVersion = '2.1';

    function paynl($signature, $apiVersion, $code, $payment_method_id, $payment_method_description, $title, $public_title, $description, $sort_order, $enabled, $order_status, $configuration_key) {
        global $order;

        $this->signature = $signature;
        $this->api_version = $apiVersion;

        $this->code = $code;
        $this->title = $title;
        $this->public_title = $public_title;
        $this->description = $description;
        $this->sort_order = $sort_order;
        $this->enabled = $enabled;
        $this->order_status = $order_status;
        $this->configuration_key = $configuration_key;
        $this->payment_method_id = $payment_method_id;
        $this->payment_method_description = $payment_method_description;


        if ($this->enabled === true) {
            if (!tep_not_null(constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_SERVICE_ID')) || !tep_not_null(constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_API_TOKEN'))) {
                $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYNL_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

                $this->enabled = false;
            }
        }

        if ($this->enabled === true) {
            if (isset($order) && is_object($order)) {
                $this->update_status();
            }
        }
    }

    function check() {
        if (!isset($this->_check)) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '" . $this->configuration_key . "'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function update_status() {
        global $order;

        if (($this->enabled == true) && ((int) constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_ZONE') > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_ZONE') . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    function javascript_validation() {
        return false;
    }

    function selection() {
        return array('id' => $this->code,
            'module' => $this->public_title);
    }

    function pre_confirmation_check() {
        return false;
    }

    function confirmation() {
        return false;
    }

    function process_button() {
        return false;
    }

    function save_order() {
        global $order, $customer_id, $order_totals, $languages_id, $currencies;

        $sql_data_array = array('customers_id' => $customer_id,
            'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
            'customers_company' => $order->customer['company'],
            'customers_street_address' => $order->customer['street_address'],
            'customers_suburb' => $order->customer['suburb'],
            'customers_city' => $order->customer['city'],
            'customers_postcode' => $order->customer['postcode'],
            'customers_state' => $order->customer['state'],
            'customers_country' => $order->customer['country']['title'],
            'customers_telephone' => $order->customer['telephone'],
            'customers_email_address' => $order->customer['email_address'],
            'customers_address_format_id' => $order->customer['format_id'],
            'delivery_name' => trim($order->delivery['firstname'] . ' ' . $order->delivery['lastname']),
            'delivery_company' => $order->delivery['company'],
            'delivery_street_address' => $order->delivery['street_address'],
            'delivery_suburb' => $order->delivery['suburb'],
            'delivery_city' => $order->delivery['city'],
            'delivery_postcode' => $order->delivery['postcode'],
            'delivery_state' => $order->delivery['state'],
            'delivery_country' => $order->delivery['country']['title'],
            'delivery_address_format_id' => $order->delivery['format_id'],
            'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
            'billing_company' => $order->billing['company'],
            'billing_street_address' => $order->billing['street_address'],
            'billing_suburb' => $order->billing['suburb'],
            'billing_city' => $order->billing['city'],
            'billing_postcode' => $order->billing['postcode'],
            'billing_state' => $order->billing['state'],
            'billing_country' => $order->billing['country']['title'],
            'billing_address_format_id' => $order->billing['format_id'],
            'payment_method' => $order->info['payment_method'],
            'cc_type' => $order->info['cc_type'],
            'cc_owner' => $order->info['cc_owner'],
            'cc_number' => $order->info['cc_number'],
            'cc_expires' => $order->info['cc_expires'],
            'date_purchased' => 'now()',
            'orders_status' => $order->info['order_status'],
            'currency' => $order->info['currency'],
            'currency_value' => $order->info['currency_value']);
        tep_db_perform(TABLE_ORDERS, $sql_data_array);
        $insert_id = tep_db_insert_id();
        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                'title' => $order_totals[$i]['title'],
                'text' => $order_totals[$i]['text'],
                'value' => $order_totals[$i]['value'],
                'class' => $order_totals[$i]['code'],
                'sort_order' => $order_totals[$i]['sort_order']);
            tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
        }

        $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
        $sql_data_array = array('orders_id' => $insert_id,
            'orders_status_id' => $order->info['order_status'],
            'date_added' => 'now()',
            'customer_notified' => $customer_notification,
            'comments' => $order->info['comments']);
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

// initialized for the email confirmation
        $products_ordered = '';

        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
// Stock Update - Joao Correia
            if (STOCK_LIMITED == 'true') {
                if (DOWNLOAD_ENABLED == 'true') {
                    $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename 
                            FROM " . TABLE_PRODUCTS . " p
                            LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                             ON p.products_id=pa.products_id
                            LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                             ON pa.products_attributes_id=pad.products_attributes_id
                            WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
                    $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';
                    if (is_array($products_attributes)) {
                        $stock_query_raw .= " AND pa.options_id = '" . (int) $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . (int) $products_attributes[0]['value_id'] . "'";
                    }
                    $stock_query = tep_db_query($stock_query_raw);
                } else {
                    $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                }
                if (tep_db_num_rows($stock_query) > 0) {
                    $stock_values = tep_db_fetch_array($stock_query);
// do not decrement quantities if products_attributes_filename exists
                    if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
                        $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
                    } else {
                        $stock_left = $stock_values['products_quantity'];
                    }
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int) $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    if (($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false')) {
                        tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    }
                }
            }

// Update products_ordered (for bestsellers list)
            tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

            $sql_data_array = array('orders_id' => $insert_id,
                'products_id' => tep_get_prid($order->products[$i]['id']),
                'products_model' => $order->products[$i]['model'],
                'products_name' => $order->products[$i]['name'],
                'products_price' => $order->products[$i]['price'],
                'final_price' => $order->products[$i]['final_price'],
                'products_tax' => $order->products[$i]['tax'],
                'products_quantity' => $order->products[$i]['qty']);
            tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
            $order_products_id = tep_db_insert_id();

//------insert customer choosen option to order--------
            $attributes_exist = '0';
            $products_ordered_attributes = '';
            if (isset($order->products[$i]['attributes'])) {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    if (DOWNLOAD_ENABLED == 'true') {
                        $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename 
                               from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa 
                               left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                on pa.products_attributes_id=pad.products_attributes_id
                               where pa.products_id = '" . (int) $order->products[$i]['id'] . "' 
                                and pa.options_id = '" . (int) $order->products[$i]['attributes'][$j]['option_id'] . "' 
                                and pa.options_id = popt.products_options_id 
                                and pa.options_values_id = '" . (int) $order->products[$i]['attributes'][$j]['value_id'] . "' 
                                and pa.options_values_id = poval.products_options_values_id 
                                and popt.language_id = '" . (int) $languages_id . "' 
                                and poval.language_id = '" . (int) $languages_id . "'";
                        $attributes = tep_db_query($attributes_query);
                    } else {
                        $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int) $order->products[$i]['id'] . "' and pa.options_id = '" . (int) $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int) $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int) $languages_id . "' and poval.language_id = '" . (int) $languages_id . "'");
                    }
                    $attributes_values = tep_db_fetch_array($attributes);

                    $sql_data_array = array('orders_id' => $insert_id,
                        'orders_products_id' => $order_products_id,
                        'products_options' => $attributes_values['products_options_name'],
                        'products_options_values' => $attributes_values['products_options_values_name'],
                        'options_values_price' => $attributes_values['options_values_price'],
                        'price_prefix' => $attributes_values['price_prefix']);
                    tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                        $sql_data_array = array('orders_id' => $insert_id,
                            'orders_products_id' => $order_products_id,
                            'orders_products_filename' => $attributes_values['products_attributes_filename'],
                            'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                            'download_count' => $attributes_values['products_attributes_maxcount']);
                        tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                    }
                    $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
                }
            }
//------insert customer choosen option eof ----
            $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
        }
        return $insert_id;
    }

    function get_products_ordered($order) {
        global $languages_id, $currencies;
        // initialized for the email confirmation
        $products_ordered = '';

        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
//------insert customer choosen option to order--------
            $attributes_exist = '0';
            $products_ordered_attributes = '';
            if (isset($order->products[$i]['attributes'])) {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    if (DOWNLOAD_ENABLED == 'true') {
                        $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename 
                               from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa 
                               left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                on pa.products_attributes_id=pad.products_attributes_id
                               where pa.products_id = '" . (int) $order->products[$i]['id'] . "' 
                                and pa.options_id = '" . (int) $order->products[$i]['attributes'][$j]['option_id'] . "' 
                                and pa.options_id = popt.products_options_id 
                                and pa.options_values_id = '" . (int) $order->products[$i]['attributes'][$j]['value_id'] . "' 
                                and pa.options_values_id = poval.products_options_values_id 
                                and popt.language_id = '" . (int) $languages_id . "' 
                                and poval.language_id = '" . (int) $languages_id . "'";
                        $attributes = tep_db_query($attributes_query);
                    } else {
                        $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int) $order->products[$i]['id'] . "' and pa.options_id = '" . (int) $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int) $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int) $languages_id . "' and poval.language_id = '" . (int) $languages_id . "'");
                    }
                    $attributes_values = tep_db_fetch_array($attributes);

                    $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
                }
            }
//------insert customer choosen option eof ----
            $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
        }
        return $products_ordered;
    }

    function send_email($insert_id) {
        global $language;
        require(DIR_WS_CLASSES . 'order.php');
        $order = new order($insert_id);
        
         include(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS);
        $products_ordered = $this->get_products_ordered($order);
        $customer_id = $order->customer['id'];

        // lets start with the email confirmation
        $email_order = STORE_NAME . "\n" .
                EMAIL_SEPARATOR . "\n" .
                EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
                EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . "\n" .
                EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
        if ($order->info['comments']) {
            $email_order .= tep_db_output($order->info['comments']) . "\n\n";
        }
        $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                EMAIL_SEPARATOR . "\n" .
                $products_ordered .
                EMAIL_SEPARATOR . "\n";

        for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
            $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
        }

        if ($order->content_type != 'virtual') {
            $arrAddressD= $order->delivery;
            $arrAddressD['address_format_id'] = $order->delivery['format_id'];
            $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                    EMAIL_SEPARATOR . "\n" .                    
                    tep_address_label($customer_id, $arrAddressD, 0, '', "\n") . "\n";
        }

        $arrAddressB= $order->billing;
        $arrAddressB['address_format_id'] = $order->billing['format_id'];
        $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                EMAIL_SEPARATOR . "\n" .
                tep_address_label($customer_id,  $arrAddressB, 0, '', "\n") . "\n\n";
      
            $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                    EMAIL_SEPARATOR . "\n";
            $payment_class = $this;
            $email_order .= $order->info['payment_method'] . "\n\n";
            if (isset($payment_class->email_footer)) {
                $email_order .= $payment_class->email_footer . "\n\n";
            }
        
        tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

// send emails to other people
        if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
            tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
    }

    function before_process() {
        global $customer_id, $order, $sendto, $currency, $HTTP_POST_VARS, $insert_id;

        $insert_id = $this->save_order();

        $paynlService = new Pay_Api_Start();
        $paynlService->setAmount(intval($this->format_raw($order->info['total']) * 100));
        $paynlService->setApiToken(constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_API_TOKEN'));
        $paynlService->setServiceId(constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_SERVICE_ID'));
        $paynlService->setCurrency(substr($currency, 0, 3));
        $paynlService->setPaymentOptionId($this->payment_method_id);
        $paynlService->setExchangeUrl(tep_href_link('ext/modules/payment/paynl/paynl_exchange.php?method=' . $this->payment_method_description, '', 'SSL', false, false));
        $paynlService->setFinishUrl(tep_href_link('ext/modules/payment/paynl/paynl_exchange.php?method=' . $this->payment_method_description, '', 'SSL', false, false));
        // $paynlService->setDescription(substr(STORE_NAME, 0, 255));
        $paynlService->setDescription($insert_id);
        $paynlService->setExtra1($insert_id);
        $paynlService->setExtra2($customer_id);


        $b_address = $this->splitAddress(trim($order->billing['street_address']));
        $d_address = $this->splitAddress(trim($order->delivery['street_address']));

        $paynlService->setEnduser(
                array('language' => $order_info['lang_code'],
                    'initials' => substr($order->delivery['firstname'], 0, 1),
                    'lastName' => substr($order->delivery['lastname'], 0, 50),
                    'phoneNumber' => $order->customer['telephone'],
                    'emailAddress' => $order->customer['email_address'],
                    'address' => array('streetName' => $d_address[0],
                        'streetNumber' => substr($d_address[1], 0, 4),
                        'zipCode' => $order->delivery['postcode'],
                        'city' => $order->delivery['city'],
                        'countryCode' => $order->delivery['country']['iso_code_2']),
                    'invoiceAddress' => array('initials' => substr($order->billing['firstname'], 0, 1),
                        'lastname' => substr($order->billing['lastname'], 0, 50),
                        'streetName' => $d_address[0],
                        'streetNumber' => substr($d_address[1], 0, 4),
                        'zipCode' => $order->billing['postcode'],
                        'city' => $order->billing['city'],
                        'countryCode' => $order->billing['country']['iso_code_2']))
        );

        //add products
        foreach ($order->products as $product) {
            $paynlService->addProduct($product['id'], $product['name'], round($product['final_price'] * 100), $product['qty']);
        }

        //add ship cost
        $paynlService->addProduct('shipcost', $order->info['shipping_method'], round($order->info['shipping_cost'] * 100), 1);

        try {
            $result = $paynlService->doRequest();
            $url = $result['transaction']['paymentURL'];
            $this->insertPaynlTransaction($result['transaction']['transactionId'], $this->payment_method_id, intval($this->format_raw($order->info['total'])) * 100, $insert_id);

            tep_redirect($url);
        } catch (Exception $error) {

            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=paynl&paynlErrorMessage=' . urlencode($error->getMessage()), 'SSL'));
        }
    }

    function after_process() {
        global $insert_id;
        $this->send_email($insert_id);
    }

    function get_error() {
        global $HTTP_GET_VARS;

        $error_message = constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_ERROR_GENERAL');

        switch ($HTTP_GET_VARS['error']) {
            case 'verification':
                $error_message = constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_ERROR_VERIFICATION');
                break;

            case 'declined':
                $error_message = constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_ERROR_DECLINED');
                break;

            case 'paynl':
                $error_message = urldecode($_REQUEST['paynlErrorMessage']);
//        tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, 'PAYNL_'.$this->payment_method_description, $error_message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                break;

            default:
                $error_message = constant('MODULE_PAYMENT_' . $this->payment_method_description . '_ERROR_GENERAL');
                break;
        }



        $error = array('title' => constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_ERROR_TITLE'),
            'error' => $error_message);

        return $error;
    }

    function install($parameter = null) {
        $sql = "CREATE TABLE IF NOT EXISTS paynl_transaction (" .
                "`id` int(11) NOT NULL AUTO_INCREMENT," .
                "`transaction_id` varchar(50) NOT NULL," .
                "`option_id` int(11) NOT NULL," .
                "`amount` int(11) NOT NULL," .
                "`order_id` int(11) NOT NULL," .
                "`status` varchar(10) NOT NULL DEFAULT 'PENDING'," .
                "`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," .
                "`last_update` timestamp ," .
                "`start_data` timestamp," .
                "PRIMARY KEY (`id`)" .
                ") ENGINE=myisam AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";

        tep_db_query($sql);



        $params = $this->getParams();

        if (isset($parameter)) {
            if (isset($params[$parameter])) {
                $params = array($parameter => $params[$parameter]);
            } else {
                $params = array();
            }
        }

        foreach ($params as $key => $data) {
            $sql_data_array = array('configuration_title' => $data['title'],
                'configuration_key' => $key,
                'configuration_value' => (isset($data['value']) ? $data['value'] : ''),
                'configuration_description' => $data['desc'],
                'configuration_group_id' => '6',
                'sort_order' => '0',
                'date_added' => 'now()');

            if (isset($data['set_func'])) {
                $sql_data_array['set_function'] = $data['set_func'];
            }

            if (isset($data['use_func'])) {
                $sql_data_array['use_function'] = $data['use_func'];
            }

            tep_db_perform(TABLE_CONFIGURATION, $sql_data_array);
        }
    }

    function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
        $keys = array_keys($this->getParams());

        if ($this->check()) {
            foreach ($keys as $key) {
                if (!defined($key)) {
                    $this->install($key);
                }
            }
        }

        return $keys;
    }

    function getParams() {
        if (!defined('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_TRANSACTION_ORDER_STATUS_ID')) {
            $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Pay.nl [PAID]' limit 1");

            if (tep_db_num_rows($check_query) < 1) {
                $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
                $status = tep_db_fetch_array($status_query);

                $status_id = $status['status_id'] + 1;

                $languages = tep_get_languages();

                foreach ($languages as $lang) {
                    tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Pay.nl [PAID]')");
                }

                $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
                if (tep_db_num_rows($flags_query) == 1) {
                    tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
                }
            } else {
                $check = tep_db_fetch_array($check_query);

                $status_id = $check['orders_status_id'];
            }
        } else {
            $status_id = constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_TRANSACTION_ORDER_STATUS_ID');
        }

        $params = array('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_STATUS' => array('title' => 'Enable Pay.nl Server Integration Method',
                'desc' => 'Do you want to accept Pay.nl Server Integration Method payments?',
                'value' => 'True',
                'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
            'MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_SERVICE_ID' => array('title' => 'Service ID',
                'desc' => 'The Service ID used for the pay.nl service'),
            'MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_API_TOKEN' => array('title' => 'API Token ',
                'desc' => 'The API Token used for the pay.nl service'),
            'MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_ORDER_STATUS_ID' => array('title' => 'Set Pending Status',
                'desc' => 'Set the status of pending orders made with this payment module to this value',
                'value' => '0',
                'use_func' => 'tep_get_order_status_name',
                'set_func' => 'tep_cfg_pull_down_order_statuses('),
//      'MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_REVIEW_ORDER_STATUS_ID' => array('title' => 'Review Order Status',
//        'desc' => 'Set the status of orders flagged as being under review to this value',
//        'value' => '0',
//        'use_func' => 'tep_get_order_status_name',
//        'set_func' => 'tep_cfg_pull_down_order_statuses('),
            'MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_TRANSACTION_ORDER_STATUS_ID' => array('title' => 'Paid Order Status',
                'desc' => 'Include paid transaction information in this order status level',
                'value' => $status_id,
                'use_func' => 'tep_get_order_status_name',
                'set_func' => 'tep_cfg_pull_down_order_statuses('),
            'MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_ZONE' => array('title' => 'Payment Zone',
                'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                'value' => '0',
                'set_func' => 'tep_cfg_pull_down_zone_classes(',
                'use_func' => 'tep_get_zone_class_title'),
            'MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                'desc' => 'All parameters of an invalid transaction will be sent to this email address.'),
            'MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_SORT_ORDER' => array('title' => 'Sort order of display.',
                'desc' => 'Sort order of display. Lowest is displayed first.',
                'value' => '0'));

        return $params;
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
        global $currencies, $currency;

        if (empty($currency_code) || !$this->is_set($currency_code)) {
            $currency_code = $currency;
        }

        if (empty($currency_value) || !is_numeric($currency_value)) {
            $currency_value = $currencies->currencies[$currency_code]['value'];
        }

        return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function splitAddress($strAddress) {
        $strAddress = trim($strAddress);
        $a = preg_split('/([0-9]+)/', $strAddress, 2, PREG_SPLIT_DELIM_CAPTURE);
        $strStreetName = trim(array_shift($a));
        $strStreetNumber = trim(implode('', $a));

        if (empty($strStreetName)) { // American address notation
            $a = preg_split('/([a-zA-Z]{2,})/', $strAddress, 2, PREG_SPLIT_DELIM_CAPTURE);

            $strStreetNumber = trim(implode('', $a));
            $strStreetName = trim(array_shift($a));
        }

        return array($strStreetName, $strStreetNumber);
    }

    function sendDebugEmail($response = array()) {
        global $HTTP_POST_VARS, $HTTP_GET_VARS;

        if (tep_not_null(constant('MODULE_PAYMENT_' . $this->payment_method_description . '_DEBUG_EMAIL'))) {
            $email_body = '';

            if (!empty($response)) {
                $email_body .= 'RESPONSE:' . "\n\n" . print_r($response, true) . "\n\n";
            }

            if (!empty($HTTP_POST_VARS)) {
                $email_body .= '$HTTP_POST_VARS:' . "\n\n" . print_r($HTTP_POST_VARS, true) . "\n\n";
            }

            if (!empty($HTTP_GET_VARS)) {
                $email_body .= '$HTTP_GET_VARS:' . "\n\n" . print_r($HTTP_GET_VARS, true) . "\n\n";
            }

            if (!empty($email_body)) {
                tep_mail('', constant('MODULE_PAYMENT_PAYNL_' . $this->payment_method_description . '_DEBUG_EMAIL'), 'Pay.nl ' . $this->payment_method_description . ' Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }
        }
    }

    function insertPaynlTransaction($transactionId, $option_id, $amout, $orderId) {
        tep_db_query("insert into paynl_transaction (transaction_id, option_id, amount, order_id, start_data) values ('" . $transactionId . "','" . $option_id . "','" . $amount . "','" . $orderId . "','" . date('Y-m-d H:i:s') . "')");
    }

}

//payment method id
?>
