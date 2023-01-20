<?php

require_once('paynl/paynl.php');

class paynl_soforthighrisk extends paynl
{
    function paynl_soforthighrisk()
    {
        parent::__construct(
            'paynl soforthighrisk signature',
            '2.1',
            'paynl_soforthighrisk',
            595,
            'SOFORTHIGHRISK',
            MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_STATUS') && (MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_SOFORTHIGHRISK_STATUS'
        );
    }

}