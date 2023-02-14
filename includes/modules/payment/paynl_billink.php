<?php

require_once('paynl/paynl.php');

class paynl_billink extends paynl
{
    function paynl_billink()
    {
        parent::__construct(
            'paynl billink signature',
            '2.1',
            'paynl_billink',
            1672,
            'BILLINK',
            MODULE_PAYMENT_PAYNL_BILLINK_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_BILLINK_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_BILLINK_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_BILLINK_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_BILLINK_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_BILLINK_STATUS') && (MODULE_PAYMENT_PAYNL_BILLINK_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_BILLINK_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_BILLINK_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_BILLINK_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_BILLINK_STATUS'
        );
    }

}