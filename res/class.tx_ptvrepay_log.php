<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2009 Rainer Kuhn (kuhn@punkt.de)
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
 * Log class for the 'pt_vrepay' extension
 *
 * $Id: class.tx_ptvrepay_log.php,v 1.2 2009/02/10 13:41:45 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2009-02-09
 */ 



/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php'; // assertion class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_sessionStorageAdapter.php'; // storage adapter for TYPO3 _browser_ sessions



/**
 * Log class for payment transaction logging
 * TODO: this class my be outsourced to a generic logger in pt_tools
 *
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2009-02-09
 * @package     TYPO3
 * @subpackage  tx_ptvrepay
 */
class tx_ptvrepay_log implements tx_pttools_iSingleton {
    
    
    /***************************************************************************
     * Class Constants
     **************************************************************************/
        
    /**
     * @var     string      name of the log file to write
     */
    const LOGFILE_NAME = 'tx_ptvrepay_log';
        
    /**
     * @var     string      name of the session key to store the admin mail sent status
     */
    const SESSION_KEY_NAME_ADMIN_MAIL = 'tx_ptvrepay_log_loggingErrorMailSent';
    
    /**
     * @var     integer     log entry message status: information
     */
    const MSG_STATUS_INFO = 0;
    
    /**
     * @var     integer     log entry message status: notice
     */
    const MSG_STATUS_NOTICE = 1;
    
    /**
     * @var     integer     log entry message status: error
     */
    const MSG_STATUS_ERROR  = 2;
    
    /**
     * @var     integer     log entry message status: initial message of a new transaction
     */
    const MSG_STATUS_INITIAL = -1;
    
    
    
    /***************************************************************************
     *   PROPERTIES
     **************************************************************************/  
    
    /**
     * tx_ptvrepay_log  Singleton unique instance
     */
    private static $uniqueInstance = NULL;
    
    /**
     * string           directory path for generated audit log (absolute server path incl. prefacing and closing slashes "/")
     */
    protected $loggingDirectory;
    
    
    /**
     * string           admin email address (comma-seperated list for multiple recipients) for auto-generated logging error emails
     */
    protected $adminEmailAddress;
    
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR & OBJECT HANDLING METHODS
     **************************************************************************/
    
    /**
     * Private class constructor: Read and check the basic configuration (set both the directory path for this audit log and the admin email address in TS/Constant Editor). 
     * This constructor must not be called directly, use getInstance() to get the unique instance of the object!
     * 
     *
     * @param   void
     * @return  void
     * @throws  exceptionAssertion  if no typoscript configuration found
     * @throws  exceptionAssertion  (multiple) if required configuration values are not valid
     * @throws  exceptionAssertion  (multiple) if language configuration could not be found
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-09
     */
    private function __construct() {
        
        $this->extensionConfigArr = tx_pttools_div::typoscriptRegistry('config.tx_ptvrepay.', NULL, 'pt_vrepay');
        trace($this->extensionConfigArr, 0, 'config.tx_ptvrepay.');
        
        tx_pttools_assert::isNotEmptyArray($this->extensionConfigArr, array('message' => 'No typoscript configuration found.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['loggingDirectory'], array('message' => 'No loggingDirectory set in configuration.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['adminEmailAddress'], array('message' => 'No adminEmailAddress set in configuration.'));
        
        $this->loggingDirectory = $this->extensionConfigArr['loggingDirectory'];
        $this->adminEmailAddress = $this->extensionConfigArr['adminEmailAddress'];
        
    }
    
    /**
     * Returns a unique instance (Singleton) of the object. Use this method instead of the private class constructor.
     *
     * @param   void
     * @return  tx_ptvrepay_log      unique instance of the object (Singleton) 
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
     *   DOMAIN LOGIC METHODS
     **************************************************************************/
    
    /**
     * Protocolls transaction handling actions (writes an audit log): Appends transaction, date/time and client IP and type of the log entry (notice/error) to every log message.
     * If there's a problem with logging an email is sent to the admin. Set both the directory path for this audit log and the admin email address in TS/Constant Editor. 
     *
     * @param   string      transaction identifier (of the transaction the log entry is related to)
     * @param   string      message to log
     * @param   integer     (optional) status of the message to log: self::MSG_STATUS_INFO (=default) | self::MSG_STATUS_NOTICE | self::MSG_STATUS_ERROR | self::MSG_STATUS_INITIAL
     * @param   array       (optional) additional params to be logged with the message
     * @param   boolean     (optional) flag whether the log entry should be logged to TYPO3 devlog as well (default: true)
     * @return  void        
     * @global  array       $_SERVER
     * @access  public
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-09, based on tx_ptpayment_pi1::auditLog() since 2004-07-26
     */
    public function write($transactionId, $message, $messageStatus=self::MSG_STATUS_INFO, $additionalParams=array(), $logToDevlog=true) {
        
        // process message status for log text and devlog
        switch ($messageStatus) {
            case self::MSG_STATUS_NOTICE:
                $statusText = 'Notice';
                $devlogStatus = 1;
                break;
            case self::MSG_STATUS_ERROR:
                $statusText = 'ERROR';
                $devlogStatus = 3;
                break;
            default:   // this is used for both self::MSG_STATUS_INFO and self::MSG_STATUS_INITIAL
                $statusText = 'Info';
                $devlogStatus = 0;
                break;
        }
        
        // assemble log entry text
        $logEntry =
            ($messageStatus == self::MSG_STATUS_INITIAL ? "\n---------- NEW TRANSACTION ----------\n" : "").
            "[".date("D Y-m-d H:i:s")."] ".
            "[TRID: ".$transactionId."] ".
            "[".$_SERVER['REMOTE_ADDR']."] ".
            "[".$statusText."] ".
            $message.(!empty($additionalParams) ? trim(print_r($additionalParams, 1)) : "")."\n";
            
        // log to devlog, output trace message (in debug context only)
        if (TYPO3_DLOG && $logToDevlog == true) {
            t3lib_div::devLog($message, 'pt_vrepay', $devlogStatus, (empty($additionalParams) ? false : $additionalParams));
        }
        trace($logEntry, 0, '***** LOG ENTRY: '.$statusText.' *****');

        // try logging (dir exists? access rights ok?)
        if (@error_log($logEntry, 3, $this->loggingDirectory.self::LOGFILE_NAME)) {
            
            tx_pttools_sessionStorageAdapter::getInstance()->delete(self::SESSION_KEY_NAME_ADMIN_MAIL); // unset mail-sent-flag in session

        // logging not possible: sent error mail to system email address
        } elseif (tx_pttools_sessionStorageAdapter::getInstance()->read(self::SESSION_KEY_NAME_ADMIN_MAIL) != true) {
            
            // send logging error mail to admin (this is an exception/emergency admin mail that should be sent in any case of the problem, 
            // so here we do _not_ use any heavy weight mail frameworks -e.g. pt_mail or t3lib_htmlmail- for sending this!)
            $mailSubject    = 'Transaction Logging Error ('.get_class($this).') on '.$_SERVER['SERVER_NAME'];
            $mailHeaders    = "From: nobody@".$_SERVER['SERVER_NAME']."\r\n".
                              "Content-Type: text/plain; charset=iso-8859-1\r\n".
                              "Content-Transfer-Encoding: 8bit\r\n".
                              "MIME-Version: 1.0";
            $mailMessage    = "Transaction logging for ".get_class($this)." on server ".$_SERVER['SERVER_NAME']." not possible in\n".
                              $this->loggingDirectory.self::LOGFILE_NAME.".\n\n".
                              "Please check the configured directory path and/or the directory access rights.\n\n".
                              "logEntry = ".$logEntry."\n";
            mail($this->adminEmailAddress, $mailSubject, $mailMessage, $mailHeaders);

            // store mail-sent-flag in session
            tx_pttools_sessionStorageAdapter::getInstance()->store(self::SESSION_KEY_NAME_ADMIN_MAIL, true);
        }
        
    }
    
    
    
} // end class




/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_log.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_log.php']);
}

?>