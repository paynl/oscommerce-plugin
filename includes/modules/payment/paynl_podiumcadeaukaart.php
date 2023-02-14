<?php

require_once('paynl/paynl.php');

class paynl_podiumcadeaukaart extends paynl
{
    function paynl_podiumcadeaukaart()
    {
        parent::__construct(
            'paynl podiumcadeaukaart signature',
            '2.1',
            'paynl_podiumcadeaukaart',
            816,
            'PODIUMCADEAUKAART',
            MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_STATUS') && (MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_PODIUMCADEAUKAART_STATUS'
        );
    }

}