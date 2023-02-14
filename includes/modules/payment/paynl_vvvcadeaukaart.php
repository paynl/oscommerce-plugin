<?php

require_once('paynl/paynl.php');

class paynl_vvvcadeaukaart extends paynl
{
    function paynl_vvvcadeaukaart()
    {
        parent::__construct(
            'paynl vvvcadeaukaart signature',
            '2.1',
            'paynl_vvvcadeaukaart',
            1714,
            'VVVCADEAUKAART',
            MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_STATUS') && (MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_VVVCADEAUKAART_STATUS'
        );
    }

}