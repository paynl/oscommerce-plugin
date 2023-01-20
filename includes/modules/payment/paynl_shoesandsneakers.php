<?php

require_once('paynl/paynl.php');

class paynl_shoesandsneakers extends paynl
{
    function paynl_shoesandsneakers()
    {
        parent::__construct(
            'paynl shoesandsneakers signature',
            '2.1',
            'paynl_shoesandsneakers',
            2937,
            'SHOESANDSNEAKERS',
            MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_STATUS') && (MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_SHOESANDSNEAKERS_STATUS'
        );
    }

}