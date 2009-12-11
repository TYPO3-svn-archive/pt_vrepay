<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008-2009 Rainer Kuhn (kuhn@punkt.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Database accessor class for transaction records
 *
 * $Id: class.tx_ptvrepay_transactionAccessor.php,v 1.7 2009/02/13 15:46:58 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-12-12
 */ 



/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_transactionRequest.php';

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general helper library class
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_iSingleton.php'; // interface for Singleton design pattern



/**
 *  Database accessor class for transaction records
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-12-12
 * @package     TYPO3
 * @subpackage  tx_ptvrepay
 */
class tx_ptvrepay_transactionAccessor implements tx_pttools_iSingleton {
    
    /***************************************************************************
     *   PROPERTIES
     **************************************************************************/
    
    /**
     * tx_ptvrepay_transactionAccessor  Singleton unique instance
     */
    private static $uniqueInstance = NULL;
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR & OBJECT HANDLING METHODS
     **************************************************************************/
    
    /**
     * Private class constructor: must not be called directly in order to use getInstance() to get the unique instance of the object.
     *
     * @param   void
     * @return  void
     * @global  object      $GLOBALS['TYPO3_DB']: t3lib_db Object (TYPO3 DB API)
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-02-06
     */
    private function __construct() {
        
        // for TYPO3 3.8.0+: enable storage of last built SQL query in $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery for all query building functions of class t3lib_DB
        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
        
    }
    
    /**
     * Returns a unique instance (Singleton) of the object. Use this method instead of the private/protected class constructor.
     *
     * @param   void
     * @return  tx_ptvrepay_transactionAccessor      unique instance of the object (Singleton) 
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-12
     */
    public static function getInstance() {
        
        if (self::$uniqueInstance === NULL) {
            $className = __CLASS__;
            self::$uniqueInstance = new $className;
        }
        return self::$uniqueInstance;
        
    }
    
    /**
     * Final method to prevent object cloning (using 'clone'), in order to use only the unique instance of the Singleton object.
     * @param   void
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2005-09-15
     */
    public final function __clone() {
        
        trigger_error('Clone is not allowed for '.get_class($this).' (Singleton)', E_USER_ERROR);
        
    }
    
    
    
    /***************************************************************************
     *   ACCESSOR METHODS
     **************************************************************************/
     
    /**
     * Returns the data of a transaction record (specified by reference number and optionally validated by response secret)
     *
     * @param   string      unique transaction reference number
     * @param   string      (optional) response secret to verify reference number
     * @global  object      $GLOBALS['TYPO3_DB']: t3lib_db Object (TYPO3 DB API)
     * @return  array|FALSE array of the specified record on success, FALSE otherwise
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-12
     */
    public function selectTransactionByReferenceNumber($referenceNumber, $responseSecret='') {
        
        // query preparation
        $select  = 'uid, '.
                   'pid, '.
                   'tstamp, '.
                   'reference_no, '.
                   'response_secret, '.
                   'merchant_reference, '.
                   'amount, '.
                   'request_result, '.
                   'status, '.
                   'response_params ';
        $from    = 'tx_ptvrepay_transactions';
        $where   = 'reference_no = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($referenceNumber, $from).' '.
                   (!empty($responseSecret) ? 'AND response_secret = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($responseSecret, $from).' ' : '').
                   tx_pttools_div::enableFields($from); 
        $groupBy = '';
        $orderBy = '';
        $limit   = '';
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
        trace(str_replace(chr(9), '', $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery));
        if ($res == false) {
            $return = false;
        } else {
            $return = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);  // returns result array
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
        
        trace($return); 
        return $return;
        
    }
    
    /**
     * Inserts a new transaction (request) record into the TYPO3 database
     *
     * @param   tx_ptvrepay_transactionRequest  transaction request object containing the data to insert
     * @return  integer     ID of the inserted record
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-12 
     */
    public function insertTransaction(tx_ptvrepay_transactionRequest $requestObj) {
        
        $insertFieldsArr = array();
        
        // query preparation
        $table = 'tx_ptvrepay_transactions';
        $insertFieldsArr['pid']             = $requestObj->get_pid();
        $insertFieldsArr['tstamp']          = time();
        $insertFieldsArr['crdate']          = time();
        $insertFieldsArr['reference_no']    = $requestObj->get_referenznr();
        $insertFieldsArr['response_secret'] = $requestObj->get_antwgeheimnis();
        $insertFieldsArr['merchant_reference'] = $requestObj->get_merchantReference();
        $insertFieldsArr['amount']          = $requestObj->get_betrag();

        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFieldsArr);
        trace(str_replace(chr(9), '', $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery));
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, $GLOBALS['TYPO3_DB']->sql_error());
        }
        $lastInsertedId = $GLOBALS['TYPO3_DB']->sql_insert_id();
        
        trace($lastInsertedId);
        return $lastInsertedId;
        
    }
     
    /**
     * Updates an existing transaction record with request error or response result data
     *
     * @param   string      reference number (transaction request identifier)
     * @param   string      (optional) request result: success message with URL in case of request success, error message in case of request failure (default = '': do not store request result)
     * @param   array       (optional) response POST params in case of request success (default = array(): do not store response)
     * @param   integer     (optional) transaction status (default = -1: do not store status)
     * @return  boolean     TRUE on success
     * @throws  tx_pttools_exception   if the query fails/returns false
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-12 
     */
    public function updateTransactionResult($referenceNo, $requestResult='', $responseParamsArray=array(), $transactionStatus='') {
        
        tx_pttools_assert::isString($referenceNo);
        tx_pttools_assert::isArray($responseParamsArray);
        tx_pttools_assert::isString($transactionStatus);
        tx_pttools_assert::isString($requestResult);
        
        $updateFieldsArr = array();
        
        // query preparation
        $table = 'tx_ptvrepay_transactions';
        
        $updateFieldsArr['tstamp'] = time();
        if (!empty($requestResult)) {
            $updateFieldsArr['request_result'] = $requestResult;
        }
        if (!empty($responseParamsArray)) {
            $updateFieldsArr['response_params'] = serialize($responseParamsArray);
        }
        if ($transactionStatus != -1) {
            $updateFieldsArr['status'] = $transactionStatus;
        }

        $where = 'reference_no = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($referenceNo, $table);
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFieldsArr);
        trace(str_replace(chr(9), '', $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery));
        if ($res == false) {
            throw new tx_pttools_exception('Query failed', 1, 'Transaction DB update query failed: '.$GLOBALS['TYPO3_DB']->sql_error());
        }
        
        trace((boolean)$res); 
        return (boolean)$res;
        
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_transactionAccessor.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_transactionAccessor.php']);
}

?>