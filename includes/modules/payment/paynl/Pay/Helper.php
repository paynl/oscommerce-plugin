<?php

class Pay_Helper {

    /**
     * Bepaal de status aan de hand van het statusid.
     * Over het algemeen worden allen de statussen -90(CANCEL), 20(PENDING) en 100(PAID) gebruikt
     * 
     * @param int $statusId
     * @return string De status
     */
      public static function getStateText($stateId) {
        switch ($stateId) {
            case 80:
            case -51:
                return 'CHECKAMOUNT';
            case 100:
                return 'PAID';
            default:
                if ($stateId < 0) {
                    return 'CANCEL';
                } else {
                    return 'PENDING';
                }
        }
    }

    //remove all empty nodes in an array
    public static function filterArrayRecursive($array) {
        $newArray = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::filterArrayRecursive($value);
            }
            if (!empty($value)) {
                $newArray[$key] = $value;
            }
        }
        return $newArray;
    }
    
    public static function sortPaymentOptions($paymentOptions){
        uasort($paymentOptions, 'sortPaymentOptions');
        return $paymentOptions;
    }   
}
function sortPaymentOptions($a,$b){
    return strcmp($a['name'], $b['name']);
}