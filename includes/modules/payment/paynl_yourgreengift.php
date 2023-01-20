<?php

require_once('paynl/paynl.php');

class paynl_yourgreengift extends paynl
{
    function paynl_yourgreengift()
    {
        parent::__construct(
            'paynl yourgreengift signature',
            '2.1',
            'paynl_yourgreengift',
            2925,
            'YOURGREENGIFT',
            MODULE_PAYMENT_PAYNL_YOURGREENGIFT_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_YOURGREENGIFT_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_YOURGREENGIFT_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_YOURGREENGIFT_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_YOURGREENGIFT_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_YOURGREENGIFT_STATUS') && (MODULE_PAYMENT_PAYNL_YOURGREENGIFT_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_YOURGREENGIFT_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_YOURGREENGIFT_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_YOURGREENGIFT_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_YOURGREENGIFT_STATUS'
        );
    }

}