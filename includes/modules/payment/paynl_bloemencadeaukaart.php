<?php

require_once('paynl/paynl.php');

class paynl_bloemencadeaukaart extends paynl
{
    function paynl_bloemencadeaukaart()
    {
        parent::__construct(
            'paynl bloemencadeaukaart signature',
            '2.1',
            'paynl_bloemencadeaukaart',
            2607,
            'BLOEMENCADEAUKAART',
            MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_STATUS') && (MODULE_PAYMENT_PAYNLBLOEMENCADEAUKAART__STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_BLOEMENCADEAUKAART_STATUS'
        );
    }

}