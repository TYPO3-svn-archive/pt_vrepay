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
 * Dialog transaction request class
 *
 * $Id: class.tx_ptvrepay_dialogTransaction.php,v 1.10 2009/02/13 15:46:58 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-12-01
 */ 



/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_transactionRequest.php';

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class



/**
 * Dialog transaction request class
 *
 * @package     TYPO3
 * @subpackage  tx_ptvrepay
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-12-01
 */
class tx_ptvrepay_dialogTransaction extends tx_ptvrepay_transactionRequest {
    
    /***************************************************************************
     *   CONSTANTS
     **************************************************************************/
    
    /*
     * @var string  language to be used for dialog transaction GUI (currently only "DE" for German and "EN" for English supported)
     */
    const TRANSACTION_SERVICE_NAME = 'DIALOG'; 
    
    
    
    /***************************************************************************
     *   REQUEST PROPERTIES: 
     *   named in German using the original VR-ePay request parameter names
     **************************************************************************/
    
    /*
     * @var string  language to be used for dialog transaction GUI (currently only "DE" for German and "EN" for English supported)
     */
    protected $sprache = ''; 
    
    /*
     * @var string  flag whther a payment method choice should be offered to the user: 'J' for yes, 'N' for no
     */
    protected $auswahl = ''; 
    
    /*
     * @var string  *Semicolon* separated list of allowed payment methods to be used by the "dialog" transaction interface. Currently the extension supports credit card payment types (VISA = Visa, ECMC = Mastercard, DINERS = Diners, AMEX = Amex, JCB = JCB) and electronic cash for Germany (ELV = German "elektonisches Lastschriftverfahren).
     */
    protected $brand = ''; 
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR
     **************************************************************************/
     
    /**
     * Class constructor: calls the parent constructor and adds individual action
     *
     * @param   string  see parent constructor tx_ptvrepay_transactionRequest::__construct
     * @param   double  see parent constructor tx_ptvrepay_transactionRequest::__construct
     * @param   string  (optional) see parent constructor tx_ptvrepay_transactionRequest::__construct
     * @param   string  (optional) see parent constructor tx_ptvrepay_transactionRequest::__construct
     * @return  void
     * @throws  exceptionAssertion  if no reference number given (see parent constructor tx_ptvrepay_transactionRequest::__construct)
     * @throws  exceptionAssertion  if valid amount given (see parent constructor tx_ptvrepay_transactionRequest::__construct)
     * @throws  exceptionAssertion  if no GSA Shop typoscript config found and 2nd param is set to true (see parent constructor tx_ptvrepay_transactionRequest::__construct)
     * @throws  exceptionAssertion  if required properties could not be set from extension configuration (see parent constructor tx_ptvrepay_transactionRequest::__construct)
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-01
     */
    public function __construct($merchantReference, $amount, $transactionType='KAUFEN', $infotext='') {
        
        parent::__construct($merchantReference, $amount, $transactionType, $infotext);
        
        $this->set_sprache($this->extensionConfigArr['dialogLanguage']);
        $this->setPaymentMethods($this->extensionConfigArr['allowedPaymentMethods']);
         
        trace($this);
        
    }
    
    
    
    /***************************************************************************
     *   INHERITED METHODS
     **************************************************************************/
     
    /**
     * Read and check the basic configuration
     *
     * @param   void
     * @return  void
     * @throws  exceptionAssertion  if no typoscript configuration found
     * @throws  exceptionAssertion  (multiple) if required configuration values are not valid
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-09
     */
    protected function getConfiguration() {
        
        parent::getConfiguration();
        
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['dialogLanguage'], array('message' => 'No dialogLanguage set in configuration.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['allowedPaymentMethods'], array('message' => 'No allowedPaymentMethods set in configuration.'));
        
    }
    
    /**
     * Sets the the $servicename property for the the dialog transaction service implementation
     *  
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-02
     */
    protected function setImplementedServicename() {
        
        $this->set_servicename(self::TRANSACTION_SERVICE_NAME);
        
    }
    
    /**
     * Returns the POST array to be used for the dialog transaction request
     *
     * @param   void  
     * @return  array   the POST array to be used for the dialog transaction request
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-01
     */
    public function getPostArray() {
        
        $parentPostArray = parent::getPostArray();
        
        $additionalDialogPostArray = array( 
        
            'SPRACHE'   => $this->sprache,
            'AUSWAHL'   => $this->auswahl,
            'BRAND'     => $this->brand
            
        );
        
        $postArray = array_merge($parentPostArray, $additionalDialogPostArray);
        trace($postArray, 0, '$postArray for dialog transaction');
        
        return $postArray;
        
    }
    
    
    
    /***************************************************************************
     *   GENERAL METHODS
     **************************************************************************/
     
    /**
     * Sets the payment method related properties from a given comma separated payment methods list
     *
     * @param   string      comma separated allowed payment methods list
     * @return  void
     * @throws  exceptionAssertion  if no valid allowedPaymentMethods list given
     * @throws  exceptionAssertion  if allowedPaymentMethods contains an invalid brand
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-09
     */
    protected function setPaymentMethods($paymentMethodsCsl) {
        
        // check validity of given param
        $brandArray = tx_pttools_div::returnArrayFromCsl($paymentMethodsCsl);
        tx_pttools_assert::isNotEmptyArray($brandArray, array('message' => 'No valid allowedPaymentMethods list given.'));
        foreach ($brandArray as $brand) {
            tx_pttools_assert::isInList($brand, 'VISA,ECMC,DINERS,AMEX,JCB,ELV', array('message' => 'Invalid brand "'.$brand.'" given.'));
        }
        
        // set related properties
        if (count($brandArray) == 1) {
            $this->set_auswahl('N');
        } else {
            $this->set_auswahl('J');
        }
        
        $this->set_brand(implode(';', $brandArray));
        
    }
    
    
    
    /***************************************************************************
     *   PROPERTY GETTER/SETTER METHODS
     **************************************************************************/
     
    /**
     * Sets the property value
     *
     * @param   string      language to be used for dialog transaction GUI (currently only "DE" for German and "EN" for English supported)
     * @return  void
     * @throws  exceptionAssertion  if no valid dialog language given
     * @since   2008-12-02
     */
    public function set_sprache($sprache) {
        
        tx_pttools_assert::isInList($sprache, 'DE,EN', array('message' => 'No valid dialog language given.'));
        $this->sprache = $sprache;
        
    }
     
    /**
     * Sets the property value
     *
     * @param   string      *Semicolon* separated list of allowed payment methods to be used by the "dialog" transaction interface. Currently the extension supports credit card payment types (VISA = Visa, ECMC = Mastercard, DINERS = Diners, AMEX = Amex, JCB = JCB) and electronic cash for Germany (ELV = German "elektonisches Lastschriftverfahren)
     * @return  void
     * @throws  exceptionAssertion  if no valid payment choice flag
     * @since   2009-02-09
     */
    protected function set_brand($brand) {
        
        tx_pttools_assert::isNotEmptyString($brand, array('message' => 'No valid brand given.'));
        $this->brand = $brand;
        
    }
     
    /**
     * Sets the property value
     *
     * @param   string      flag whether a payment method choice should be offered to the user: 'J' for yes, 'N' for no
     * @return  void
     * @throws  exceptionAssertion  if no valid payment choice flag
     * @since   2009-02-09
     */
    protected function set_auswahl($auswahl) {
        
        tx_pttools_assert::isInList($auswahl, 'J,N', array('message' => 'No valid payment choice flag given.'));
        $this->auswahl = $auswahl;
        
    }

    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_dialogTransaction.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_dialogTransaction.php']);
}

?>
