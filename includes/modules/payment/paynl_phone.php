<?php

require_once('paynl/paynl.php');

class paynl_phone extends paynl
{
    function paynl_phone()
    {
        parent::__construct(
            'paynl phone signature',
            '2.1',
            'paynl_phone',
            1600,
            'PHONE',
            MODULE_PAYMENT_PAYNL_PHONE_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_PHONE_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_PHONE_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_PHONE_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_PHONE_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_PHONE_STATUS') && (MODULE_PAYMENT_PAYNL_PHONE_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_PHONE_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_PHONE_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_PHONE_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_PHONE_STATUS'
        );
    }

}