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

    tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$orderId . '"');
    tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$orderId . '"');


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
