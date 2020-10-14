<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
 */

chdir('../../../../');
require('includes/application_top.php');

require_once(DIR_WS_MODULES . '/payment/paynl/Pay/Autoload.php');

$transactionId = null;
$isExchange = false;
if ($_REQUEST['order_id']) {
    $transactionId = $_REQUEST['order_id'];//exchange
    $method_code = $_REQUEST['method_code'];
    $isExchange = true;
} else {
    $method_code = $payment;
    $transactionId = $_REQUEST['orderId']; //finish
    $isExchange = false;
}


$method = $_REQUEST['method'];

$payApiInfo = new Pay_Api_Info();
$payApiInfo->setApiToken(constant('MODULE_PAYMENT_PAYNL_' . $method . '_API_TOKEN'));
$payApiInfo->setServiceId(constant('MODULE_PAYMENT_PAYNL_' . $method . '_SERVICE_ID'));
$payApiInfo->setTransactionId($transactionId);

$url_success = tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL', false, false);
$url_cancel = tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', false, false);

try {
    $result = $payApiInfo->doRequest();
} catch (Exception $ex) {
    var_dump($ex->getMessage());
    die;
}
$state = Pay_Helper::getStateText($result['paymentDetails']['state']);
$orderId = $result['statsDetails']['extra1'];

if (isAlreadyPAID($transactionId) && $isExchange) die("TRUE|Already PAID");

if (isAlreadyPAID($transactionId) && !$isExchange) {
    global $cart;
    $cart->reset(true);

    paynlSendConfirmEmail($orderId);

    header('Location: ' . $url_success);
    die();
}

//if not already paid

switch ($state) {
    case "PENDING":
        if (!$isExchange) {
            global $cart;
            $cart->reset(true);
            paynlSendConfirmEmail($orderId);
            header('Location: ' . $url_success);
            die();
        }
        echo "TRUE|Ignore pending";
        ob_flush();
        updatePaynlTransaction($transactionId, $state);
        die();
        break;
    case "PAID":
        updatePaynlTransaction($transactionId, $state);
        updateOrderStatus($method, $orderId);
        //clean up cart & session
        global $cart;
        $cart->reset(true);

        if (!$isExchange) {
            paynlSendConfirmEmail($orderId);
            header('Location: ' . $url_success);
        } else {
            echo "TRUE|PAID";
        }
        break;
    case "CANCEL":
        if ($isExchange) {
            echo "TRUE|CANCEL";
            deleteOrder($orderId);
            ob_flush();
        } else {
            deleteOrder($orderId);
            header('Location: ' . $url_cancel);
        }
        break;
}

function deleteOrder($orderId)
{
    updateStock($orderId);

    tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$orderId . '"');

}

/**
 * Update stock after cancellation
 *
 * @param $orderId
 */
function updateStock($orderId)
{
    require_once(DIR_WS_CLASSES . 'order.php');
    $order = new order($orderId);

    $iNumberOfProducts = sizeof($order->products);

    if (STOCK_LIMITED == 'true') {
        for ($i = 0, $n = $iNumberOfProducts; $i < $n; $i++) {
            $productId = tep_get_prid($order->products[$i]['id']);

            if (DOWNLOAD_ENABLED == 'true') {
                $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename ";
                $stock_query_raw .= "FROM " . TABLE_PRODUCTS . " p ";
                $stock_query_raw .= "LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON p.products_id=pa.products_id ";
                $stock_query_raw .= "LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON pa.products_attributes_id=pad.products_attributes_id ";
                $stock_query_raw .= "WHERE p.products_id = '" . $productId . "'";

                // Will work with only one option for downloadable products
                // otherwise, we have to build the query dynamically with a loop
                $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';

                if (is_array($products_attributes)) {
                    $stock_query_raw .= " AND pa.options_id = '" . (int)$products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . (int)$products_attributes[0]['value_id'] . "'";
                }
                $stock_query = tep_db_query($stock_query_raw);

            } else {
                $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . $productId . "'");
            }

            $stock_values = tep_db_fetch_array($stock_query);

            $currentStock = isset($stock_values['products_quantity']) ? (int)$stock_values['products_quantity'] : 0;

            tep_db_query("UPDATE " . TABLE_PRODUCTS . " SET products_quantity = '" . ($currentStock + 1) . "' WHERE products_id = '" . $productId . "'");
        }
    }
}

function isAlreadyPAID($transactionId)
{
    $orderRow = tep_db_query("SELECT order_id FROM paynl_transaction WHERE transaction_id ='" . tep_db_input($transactionId) . "'");

    $orderId = tep_db_fetch_array($orderRow);

    if (!isset($orderId['order_id'])) return false;

    $arrTransactionsResult = tep_db_query('SELECT count(*) as count FROM paynl_transaction WHERE order_id =' . $orderId['order_id'] . ' AND status = "PAID" ');

    $arrTransactionsRow = tep_db_fetch_array($arrTransactionsResult);
    $arrTransactions = $arrTransactionsRow['count'];


    if (intval($arrTransactions) > 0) {
        return true;
    } else return false;
}

function updatePaynlTransaction($transactionId, $status)
{
    tep_db_query("UPDATE paynl_transaction SET status = '" . $status . "' , last_update= now() WHERE transaction_id ='" . $transactionId . "'");
}

function paynlSendConfirmEmail($orderId)
{
    global $method_code, $insert_id;
    $insert_id = $orderId;

    require(DIR_WS_CLASSES . 'payment.php');
    $payment_modules = new payment($method_code);

    $payment_modules->after_process();
}

function updateOrderStatus($method, $orderId)
{
    global $insert_id;

    $insert_id = $orderId;

    $order_status_id = (constant('MODULE_PAYMENT_PAYNL_' . $method . '_TRANSACTION_ORDER_STATUS_ID') > 0 ? (int)constant('MODULE_PAYMENT_PAYNL_' . $method . '_TRANSACTION_ORDER_STATUS_ID') : (int)DEFAULT_ORDERS_STATUS_ID);

    tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status_id . "', last_modified = now() where orders_id = '" . (int)$orderId . "'");

    $sql_data_array = array('orders_id' => $orderId,
        'orders_status_id' => $order_status_id,
        'date_added' => 'now()',
        'customer_notified' => '1',
        'comments' => 'Paynl Transaction [VERIFIED]');

    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
}
