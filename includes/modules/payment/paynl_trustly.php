<?php

require_once('paynl/paynl.php');

class paynl_trustly extends paynl
{
    function paynl_trustly()
    {
        parent::__construct(
            'paynl trustly signature',
            '2.1',
            'paynl_trustly',
            2718,
            'TRUSTLY',
            MODULE_PAYMENT_PAYNL_TRUSTLY_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_TRUSTLY_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_TRUSTLY_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_TRUSTLY_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_TRUSTLY_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_TRUSTLY_STATUS') && (MODULE_PAYMENT_PAYNL_TRUSTLY_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_TRUSTLY_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_TRUSTLY_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_TRUSTLY_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_TRUSTLY_STATUS'
        );
    }

}