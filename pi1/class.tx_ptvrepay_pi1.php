<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2009 Rainer Kuhn <kuhn@punkt.de>
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
 * Frontend Plugin 'VR-ePay Dialog Transaction' for the 'pt_vrepay' extension.
 *
 * $Id: class.tx_ptvrepay_pi1.php,v 1.24 2009/02/13 15:46:58 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-12-01
 */ 



/**
 * Inclusion of TYPO3 libraries
 */
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_div.php');

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php'; // assertion class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_msgBox.php'; // message box class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_paymentRequestInformation.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_paymentReturnInformation.php';

/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_dialogTransaction.php';
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_transactionResponse.php';
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_transactionException.php';
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_log.php';



/**
 * VR-ePay Dialog Transaction Plugin Controller
 *
 * @package     TYPO3
 * @subpackage  tx_ptvrepay
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-12-01
 */
class tx_ptvrepay_pi1 extends tslib_pibase {
    
    /***************************************************************************
     *   CONSTANTS
     **************************************************************************/
    
    /**
     * @var     string      name of the session key used to store the transaction identifier (unique reference number) into
     */
    const SESSION_KEY_TRANSACTION_ID = 'tx_ptvrepay_transactionIdentifier';
    
    /*
     * @var string path to the not-plugin-related locallang file to use within this class
     */
    const LL_FILEPATH = 'res/locallang_res_classes.xml';
    
    
    /***************************************************************************
     *   PROPERTIES
     **************************************************************************/
    
    /**
     * tslib_pibase (parent class) properties
     */
    public $prefixId      = 'tx_ptvrepay_pi1';        // Same as class name
    public $scriptRelPath = 'pi1/class.tx_ptvrepay_pi1.php';    // Path to this script relative to the extension dir.
    public $extKey        = 'pt_vrepay';    // The extension key.
    
    /**
     * @var array   multilingual language labels (locallang) for this class
     */
    protected $llArray = array();
    
    /**
     * @var string   transaction identifier (unique reference number) from session
     */
    protected $sessionTransactionId;
    
    
    
    /***************************************************************************
     *   MAIN
     **************************************************************************/
    
    /**
     * Main method: Prepares properties and acts as a controller
     *
     * @param   string      HTML content of the plugin to be displayed within the TYPO3 page
     * @param   array       global configuration for this plugin (mostly done in Constant Editor/TS setup)
     * @return  string      HTML plugin content for output on the page (if not redirected before) 
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-01
     */
    function main($content, $conf)    {
        
        // ********** DEFAULT PLUGIN INITIALIZATION **********
        
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj = 1;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
        
        
        try {
            
            // dev info
            trace($_SERVER['REQUEST_METHOD'], 0, '$_SERVER[REQUEST_METHOD]');
            trace(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'), 0, 't3lib_div::getIndpEnv(TYPO3_REQUEST_URL)');
            
            // get locallang data
            $llFilePath = t3lib_extMgm::extPath($this->extKey).self::LL_FILEPATH;
            tx_pttools_assert::isFilePath($llFilePath, array('message' => 'Language file not found.'));
            $this->llArray = tx_pttools_div::readLLfile($llFilePath); // get locallang data
            tx_pttools_assert::isArray($this->llArray, array('message' => 'No language labels found.'));
            
            // check response IP (we don't need to send a request if the response is not allowed...)
            $allowedResponseIp = tx_pttools_div::typoscriptRegistry('config.tx_ptvrepay.responseIp', NULL, 'pt_vrepay');
            tx_pttools_assert::isNotEmptyString($allowedResponseIp, array('message'=>'No response IP set.'));
            
            // get possibly available transaction ID from session
            $this->sessionTransactionId = tx_pttools_sessionStorageAdapter::getInstance()->read(self::SESSION_KEY_TRANSACTION_ID);
            
        
        // ********** CONTROLLER: execute approriate method for any action command (retrieved form buttons/GET vars) **********
            
            $action = t3lib_div::_GET('tx_ptvrepay_pi1_action'); // 'tx_ptvrepay_pi1[action]' not possible since the chars '[' and ']' are not allowed by VR-ePay
            if ($action == 'success') {
                $content .= $this->successAction();
            } elseif ($action == 'error') {
                $content .= $this->errorAction();
            } elseif ($action == 'cancel') {
                $content .= $this->cancelAction();
            } else {
                $content .= $this->defaultAction();
            }
            
            
        // ********** EXCEPTION HANDLING **********
        
        // if a tx_ptvrepay_transactionException has been catched, handle it and add approriate message box to plugin content
        } catch (tx_ptvrepay_transactionException $excObj) {
            
            // handle exception (writes log automatically), output message
            $excObj->handle();
            $errorMessage = tx_pttools_div::getLLL(get_class($excObj).'.'.$excObj->get_errLanguageLabel(), $this->llArray);
            if (empty($errorMessage)) {
                $errorMessage = '[Error message text not found]';
            }
            $content .= new tx_pttools_msgBox('error', $errorMessage);

         // if a tx_pttools_exception has been catched, handle and log it and overwrite plugin content with plain error message
        } catch (tx_pttools_exception $excObj) {
            
            $excObj->handle();
            $transactionId = (empty($this->sessionTransactionId) ? 'UNKNOWN' : $this->sessionTransactionId);
            tx_ptvrepay_log::getInstance()->write($transactionId, get_class($excObj).': '.$excObj->getDebugMsg() , tx_ptvrepay_log::MSG_STATUS_ERROR, array(), false); // do not log to devlog here since this is done by the exception already 
            $content = '<i>'.$excObj->__toString().'</i>';
        
        // catch default exception, try to handle it and log it
        } catch (Exception $excObj) {
            
            if (method_exists($excObj, 'handle')) {
                $excObj->handle();
            }
            $transactionId = (empty($this->sessionTransactionId) ? 'UNKNOWN' : $this->sessionTransactionId);
            tx_ptvrepay_log::getInstance()->write($transactionId, get_class($excObj).': '.$excObj->getMessage() , tx_ptvrepay_log::MSG_STATUS_ERROR, array(), false); // do not log to devlog here since this is done by the exception already
            
        }
        
        // ********** RETURN PLUGIN CONTENT **********
        
        return $this->pi_wrapInBaseClass($content);
        
    }
    
    
    
    /***************************************************************************
     *   CONTROLLER ACTION METHODS
     **************************************************************************/
    
    /**
     * Default action: send dialog transaction request
     *
     * @param   void
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-01
     */
    public function defaultAction() {
        
//        /**********************************************************************/
//        // TODO: Shop simulation (for development only)
//        $epaymentRequestDataArray = array(
//            'merchantReference' => 'InvNumber '.rand(10000, 99999), #$this->orderWrapperObj->get_relatedDocNo(),
//            'amount' => 4.99, #$this->orderWrapperObj->get_orderObj()->getPaymentSumTotal(),
//            'currencyCode' => 'EUR', #$this->gsaShopConfig['currencyCode'],
//            'articleQuantity' => 1, #$this->orderWrapperObj->get_orderObj()->countArticlesTotal(),
//            'infotext' => 'Infotext bla' #$epaymentDescription,
//            // 'billingAddress' => $this->orderWrapperObj->get_feCustomerObj()->getDefaultBillingAddress() // not needed for VR-ePay
//        );
//        $epaymentRequestDataObj = new tx_pttools_paymentRequestInformation($epaymentRequestDataArray);
//        $epaymentRequestDataObj->storeToSession();
//        /**********************************************************************/
    
        // create transaction request with session order data from the shop
        $sessionRequestObj = new tx_pttools_paymentRequestInformation(array(), true); // reads payment data from session automatically
        $dialogTransactionObj = new tx_ptvrepay_dialogTransaction($sessionRequestObj->get_merchantReference(), 
                                                                  $sessionRequestObj->get_amount(),
                                                                  $sessionRequestObj->get_articleQuantity()
                                                                 ); 
        $dialogTransactionObj->set_waehrung($sessionRequestObj->get_currencyCode());
        $dialogTransactionObj->set_infotext($sessionRequestObj->get_infotext());
        
        // store unique reference number for current transaction to session (required for transaction response security check) and send request
        $this->sessionTransactionId = $dialogTransactionObj->get_referenznr();
        tx_pttools_sessionStorageAdapter::getInstance()->store(self::SESSION_KEY_TRANSACTION_ID, $this->sessionTransactionId);
        $dialogTransactionObj->sendRequest();
        
    }
    
    /**
     * Success action: Checks the response data for success status, stores the standardized response info for the shop system into the browser session and redirects to success page of the shop
     *
     * @param   void
     * @return  void    (redirects to shop success page by default or throws an exception)
     * @throws  exceptionAssertion  if expected transaction status does not match status retrieved from stored data
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-04
     */
    public function successAction() {
        
        tx_ptvrepay_log::getInstance()->write((empty($this->sessionTransactionId) ? 'UNKNOWN' : $this->sessionTransactionId), 'Redirect from VR-ePay to SUCCESS page');
        $this->storeAndRedirect(tx_pttools_paymentReturnInformation::STATUS_SUCCESS, $this->conf['pidShopReturnOnSuccess']);
       
    }
    
    /**
     * Error action: Checks the response data for error status, stores the standardized response info for the shop system into the browser session and redirects to error page of the shop
     *
     * @param   void
     * @return  void    (redirects to shop error page by default or throws an exception)
     * @throws  exceptionAssertion  if expected transaction status does not match status retrieved from stored data
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-04
     */
    public function errorAction() {
        
        tx_ptvrepay_log::getInstance()->write((empty($this->sessionTransactionId) ? 'UNKNOWN' : $this->sessionTransactionId), 'Redirect from VR-ePay to ERROR page');
        $this->storeAndRedirect(tx_pttools_paymentReturnInformation::STATUS_ERROR, $this->conf['pidShopReturnOnError']);
        
    }
    
    /**
     * Cancel action: Checks the response data for abort status, stores the standardized response info for the shop system into the browser session and redirects to abort page of the shop
     *
     * @param   void
     * @return  void    (redirects to shop cancel page by default or throws an exception)
     * @throws  exceptionAssertion  if expected transaction status does not match status retrieved from stored data
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-06
     */
    public function cancelAction() {
        
        tx_ptvrepay_log::getInstance()->write((empty($this->sessionTransactionId) ? 'UNKNOWN' : $this->sessionTransactionId), 'Redirect from VR-ePay to CANCEL page');
        $this->storeAndRedirect(tx_pttools_paymentReturnInformation::STATUS_ABORT, $this->conf['pidShopReturnOnAbort']);
        
    }
    
    
    
    /***************************************************************************
     *   HELPER METHODS
     **************************************************************************/
    
    /**
     * Checks the response data for the given status, stores the standardized response info for the shop system into the browser session and redirects to given shop page
     *
     * @param   integer         tx_pttools_paymentReturnInformation::STATUS_SUCCESS | tx_pttools_paymentReturnInformation::STATUS_ERROR | | tx_pttools_paymentReturnInformation::STATUS_ABORT
     * @param   integer|string  redirect PID or alias (GET params may be attached to pid or alias, e.g. '55?param=value' or 'alias?param=value')
     * @see     tx_pttools_paymentReturnInformation
     * @return  void    (redirects to shop cancel page by default or throws an exception)
     * @global  tslib_cobj  $GLOBALS['TSFE']->cObj
     * @throws  exceptionAssertion  if expected transaction status does not match status retrieved from stored data
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-06
     */
    protected function storeAndRedirect($status, $redirectPidOrAlias) {
           
        // check response DB data for transaction id and store standardized response info for shop system in browser session
        if (!empty($this->sessionTransactionId)) {
            
            $responseObj = new tx_ptvrepay_transactionResponse($this->sessionTransactionId, $status);
            $responseObj->checkStatus();  // throws an exception if expected status does not match
            $responseObj->storeToSession();  // stores standardized information to session (key name defined in tx_pttools_paymentReturnInformation::SESSION_KEY_NAME_PAYMENT_RETURN)
            tx_pttools_sessionStorageAdapter::getInstance()->delete(self::SESSION_KEY_TRANSACTION_ID); // delete request transaction id from session
            
            // redirect to appropriate shop page
            $redirectParamsArr = array();
            $redirectParts = explode('?', $redirectPidOrAlias);
            if (isset($redirectParts[1])) { // if GET params are attached
                $redirectParamsParts = explode('&', $redirectParts[1]);
                foreach ($redirectParamsParts as $paramKeyValuePair) {
                    $paramKeyValuePairParts = explode('=', $paramKeyValuePair);
                    $redirectParamsArr[$paramKeyValuePairParts[0]] = $paramKeyValuePairParts[1];
                }
            }
            $redirectTarget = $GLOBALS['TSFE']->cObj->getTypoLink_URL(tx_pttools_div::getPid($redirectParts[0]), $redirectParamsArr); 
            tx_pttools_div::localRedirect($redirectTarget); 
            
        } else {
            $message = 'No transaction ID found in session key "'.self::SESSION_KEY_TRANSACTION_ID.'" to assign response to.';
            throw new tx_ptvrepay_transactionException('noRequestIdFoundForResponse', $message, 'UNKNOWN');
        }
        
    }
    
    
    
} // end class



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/pi1/class.tx_ptvrepay_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/pi1/class.tx_ptvrepay_pi1.php']);
}

?>