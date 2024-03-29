<?php

require_once('paynl/paynl.php');

class paynl_sofortdigitalservices extends paynl
{
    function paynl_sofortdigitalservices()
    {
        parent::__construct(
            'paynl sofortdigitalservices signature',
            '2.1',
            'paynl_sofortdigitalservices',
            577,
            'SOFORTDIGITALSERVICES',
            MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_STATUS') && (MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_SOFORTDIGITALSERVICES_STATUS'
        );
    }

}