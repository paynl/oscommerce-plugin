<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('paynl/paynl.php');

class paynl_in3 extends paynl
{
    function paynl_in3()
    {
        parent::__construct(
            'paynl in3 signature',
            '2.1',
            'paynl_in3',
            1813,
            'IN3',
            MODULE_PAYMENT_PAYNL_IN3_TEXT_TITLE,
            MODULE_PAYMENT_PAYNL_IN3_TEXT_PUBLIC_TITLE,
            MODULE_PAYMENT_PAYNL_IN3_TEXT_DESCRIPTION,
            defined('MODULE_PAYMENT_PAYNL_IN3_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_IN3_SORT_ORDER : 0,
            defined('MODULE_PAYMENT_PAYNL_IN3_STATUS') && (MODULE_PAYMENT_PAYNL_IN3_STATUS == 'True') ? true : false,
            defined('MODULE_PAYMENT_PAYNL_IN3_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_IN3_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_IN3_ORDER_STATUS_ID : 0,
            'MODULE_PAYMENT_PAYNL_IN3_STATUS'
        );


    }//end constructor


}