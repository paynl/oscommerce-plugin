<?php

require_once('paynl/paynl.php');

class paynl_biller extends paynl
{
    function paynl_biller()
    {
        parent::__construct(
            'paynl biller signature',
            '2.1',
            'paynl_biller',
            2931,
            'BILLER',
            MODULE_PAYMENT_PAYNL_BILLER_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_BILLER_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_BILLER_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_BILLER_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_BILLER_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_BILLER_STATUS') && (MODULE_PAYMENT_PAYNL_BILLER_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_BILLER_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_BILLER_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_BILLER_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_BILLER_STATUS'
        );
    }

}