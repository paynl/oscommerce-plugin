<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('paynl/paynl.php');

class paynl_spraypay extends paynl
{
    function paynl_spraypay()
    {
        parent::__construct(
            'paynl spraypay signature',
            '2.1',
            'paynl_spraypay',
            1987,
            'SPRAYPAY',
            MODULE_PAYMENT_PAYNL_SPRAYPAY_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_SPRAYPAY_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_SPRAYPAY_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_SPRAYPAY_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_SPRAYPAY_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_SPRAYPAY_STATUS') && (MODULE_PAYMENT_PAYNL_SPRAYPAY_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_SPRAYPAY_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_SPRAYPAY_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_SPRAYPAY_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_SPRAYPAY_STATUS'
        );


    }//end constructor


}