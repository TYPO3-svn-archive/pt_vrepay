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
 * Frontend Plugin 'VR-ePay Response Processor' for the 'pt_vrepay' extension.
 *
 * $Id: class.tx_ptvrepay_pi2.php,v 1.11 2009/02/13 15:46:58 ry37 Exp $
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

/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_transactionResponse.php';
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_log.php';


/**
 * VR-ePay Response Processor Plugin Controller: This plugin acts as server for VR-ePay response requests, no HTML display intended currently!
 *
 * @package     TYPO3
 * @subpackage  tx_ptvrepay
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-12-12
 */
class tx_ptvrepay_pi2 extends tslib_pibase {
    
    /***************************************************************************
     *   CONSTANTS
     **************************************************************************/
    
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
    public $prefixId      = 'tx_ptvrepay_pi2';        // Same as class name
    public $scriptRelPath = 'pi2/class.tx_ptvrepay_pi2.php';    // Path to this script relative to the extension dir.
    public $extKey        = 'pt_vrepay';    // The extension key.
    
    /**
     * @var array   multilingual language labels (locallang) for this class
     */
    protected $llArray = array();
    
    
    
    /***************************************************************************
     *   MAIN
     **************************************************************************/
    
    /**
     * Main method: Prepares properties and acts as a controller - NO DISPLAY INTENTED CURRENTLY 
     *
     * @param   string      HTML content of the plugin to be displayed within the TYPO3 page - NO DISPLAY INTENTED CURRENTLY 
     * @param   array       Global configuration for this plugin (mostly done in Constant Editor/TS setup)
     * @return  string      HTML plugin content for output on the page (if not redirected before) - NO DISPLAY INTENTED CURRENTLY 
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-12
     */
    function main($content, $conf)    {
        
        // ********** DEFAULT PLUGIN INITIALIZATION **********
        
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj = 1;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
            
        try {
            $responsePostParamsArray = t3lib_div::_POST();
            
            // get locallang data
            $llFilePath = t3lib_extMgm::extPath($this->extKey).self::LL_FILEPATH;
            tx_pttools_assert::isFilePath($llFilePath, array('message' => 'Language file not found.'));
            $this->llArray = tx_pttools_div::readLLfile($llFilePath); // get locallang data
            tx_pttools_assert::isArray($this->llArray, array('message' => 'No language labels found.'));
            
            // security check for possible abuse
            $allowedResponseIp = tx_pttools_div::typoscriptRegistry('config.tx_ptvrepay.responseIp', NULL, 'pt_vrepay');
            tx_pttools_assert::isNotEmptyString($allowedResponseIp, array('message'=>'No response IP set.'));
            
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                $message = 'Response was sent with request method '.$_SERVER['REQUEST_METHOD'].' (POST expected)';
                throw new tx_ptvrepay_transactionException('responseErrorRequestMethod', $message, $responsePostParamsArray['REFERENZNR']);
            } elseif ($_SERVER['REMOTE_ADDR'] != $allowedResponseIp) {
                $message = 'Response was sent from IP "'.$_SERVER['REMOTE_ADDR'].'" ("'.$allowedResponseIp.'" expected)';
                throw new tx_ptvrepay_transactionException('responseErrorRemoteIP', $message, $responsePostParamsArray['REFERENZNR']);
            }
            
            
        // ********** CONTROLLER: execute approriate method for any action command (retrieved form buttons/GET vars) **********
            
            $content .= $this->defaultAction($responsePostParamsArray);
            
            
        // ********** EXCEPTION HANDLING **********
        
        // if a tx_ptvrepay_transactionException has been catched, handle it and add approriate message box to plugin content
        } catch (tx_ptvrepay_transactionException $excObj) {
            
            // handle exception (writes log automatically), output message
            $excObj->handle();
            $errorMessage = tx_pttools_div::getLLL(get_class($excObj).'.'.$excObj->get_errLanguageLabel(), $this->llArray); 
            $content .= new tx_pttools_msgBox('error', $errorMessage);
            
         // if a tx_pttools_exception has been catched, handle and log it and overwrite plugin content with plain error message
        } catch (tx_pttools_exception $excObj) {
            
            $excObj->handle();
            tx_ptvrepay_log::getInstance()->write($responsePostParamsArray['REFERENZNR'], get_class($excObj).': '.$excObj->getDebugMsg() , tx_ptvrepay_log::MSG_STATUS_ERROR, array(), false); // do not log to devlog here since this is done by the exception already
            $content = '<i>'.$excObj->__toString().'</i>';
        
        // catch default exception, try to handle it and log it
        } catch (Exception $excObj) {
            
            if (method_exists($excObj, 'handle')) {
                $excObj->handle();
            }
            tx_ptvrepay_log::getInstance()->write($responsePostParamsArray['REFERENZNR'], get_class($excObj).': '.$excObj->getMessage() , tx_ptvrepay_log::MSG_STATUS_ERROR, array(), false); // do not log to devlog here since this is done by the exception already
            
        }  
        
        // ********** RETURN PLUGIN CONTENT **********
    
        return $this->pi_wrapInBaseClass($content);
        
    }
    
    
    
    /***************************************************************************
     *   CONTROLLER ACTIONS
     **************************************************************************/
    
    /**
     * Default action: validates and stores the response data received from VR-ePay as POST params
     *
     * @param   array   response data received from VR-ePay as POST params
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-12
     */
    public function defaultAction($responsePostParamsArray) {
        
        // log response
        $message = 'Received VR-ePay response from '.$_SERVER['REMOTE_ADDR'].': ';
        tx_ptvrepay_log::getInstance()->write($responsePostParamsArray['REFERENZNR'], $message , tx_ptvrepay_log::MSG_STATUS_INFO, $responsePostParamsArray);
        /*
        $traceTmp = $GLOBALS['trace'];
        $GLOBALS['trace'] = 2; trace($responsePostParamsArray, 0, '$responsePostParamsArray'); $GLOBALS['trace'] = $traceTmp;
        */
       
       // validate and store the response data received from VR-ePay
       foreach ($responsePostParamsArray as $key=>$value) {
            $responseDataArray[strtolower($key)] = $value;
       }
       $responseObj = new tx_ptvrepay_transactionResponse(); // creates empty response object
       $responseObj->setPropertiesFromArray($responseDataArray); // fill response object with received POST params
       $responseObj->saveProviderResponseParamsToDatabase();
            
    }
    
    
    
} // end class



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/pi2/class.tx_ptvrepay_pi2.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/pi2/class.tx_ptvrepay_pi2.php']);
}

?>