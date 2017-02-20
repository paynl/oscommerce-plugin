<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paynl_ideal
 *
 * @author gaetan
 */
require_once('paynl/paynl.php');

class paynl_afterpayem extends paynl
{
  function paynl_afterpayem()
  {
    parent::__construct(
        'paynl afterpayem signature', 
        '2.1',
        'paynl_afterpayem',
        740,
        'AFTERPAYEM' , 
        MODULE_PAYMENT_PAYNL_AFTERPAYEM_TEXT_TITLE,
        MODULE_PAYMENT_PAYNL_AFTERPAYEM_TEXT_PUBLIC_TITLE,
        MODULE_PAYMENT_PAYNL_AFTERPAYEM_TEXT_DESCRIPTION,
        defined('MODULE_PAYMENT_PAYNL_AFTERPAYEM_SORT_ORDER') ? MODULE_PAYMENT_PAYNL_AFTERPAYEM_SORT_ORDER : 0,
        defined('MODULE_PAYMENT_PAYNL_AFTERPAYEM_STATUS') && (MODULE_PAYMENT_PAYNL_AFTERPAYEM_STATUS == 'True') ? true : false, 
        defined('MODULE_PAYMENT_PAYNL_AFTERPAYEM_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYNL_AFTERPAYEM_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYNL_AFTERPAYEM_ORDER_STATUS_ID : 0,
        'MODULE_PAYMENT_PAYNL_AFTERPAYEM_STATUS'
    );



    
  }//end constructor
  
  
}