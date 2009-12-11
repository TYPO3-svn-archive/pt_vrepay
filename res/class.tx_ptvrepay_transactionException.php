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
 * Transaction exception class
 *
 * $Id: class.tx_ptvrepay_transactionException.php,v 1.4 2009/02/13 15:46:58 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-12-02
 */ 



/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function

/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_log.php';



/**
 * Transaction exception class derived from PHP's default Exception class
 *
 * @package     TYPO3
 * @subpackage  tx_pttools
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-12-02
 */
class tx_ptvrepay_transactionException extends Exception {
    
    /*
    // Dev Info: Class structure of parent class (PHP5's default Exception):
    
    class Exception
    {
        protected $message = 'Unknown exception';   // exception message
        protected $code = 0;                        // user defined exception code
        protected $file;                            // source filename of exception
        protected $line;                            // source line of exception
    
        function __construct($message = null, $code = 0);
    
        final function getMessage();                // message of exception 
        final function getCode();                   // code of exception
        final function getFile();                   // source filename
        final function getLine();                   // source line
        final function getTrace();                  // an array of the backtrace()
        final function getTraceAsString();          // formated string of trace
    
        // Overrideable
        function __toString();                      // formated string for display
    }
    */
    
    /***************************************************************************
     *  PROPERTIES
     **************************************************************************/
     
    /**
     * @var     string      language label to be used for the exception output to frontend users
     */
    protected $errLanguageLabel;
    
    /**
     * @var     string      additional detailed debug message
     */
    protected $debugMsg;
    
    /**
     * @var     string      ID (reference number) of the related transaction (if available)
     */
    protected $transactionId;
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR
     **************************************************************************/
     
    /**
     * Class constructor: sets properties and calls the parent constructor (Exception::__construct())
     * 
     * @param   string    language label to be used for the exception output to frontend users
     * @param   string    (optional) detailed debug message (not used for frontend display)
     * @param   string    (optional) ID (reference number) of the related transaction (if available)
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-02
     */
    public function __construct($errLanguageLabel, $debugMsg='', $transactionId='') {
        
        $this->errLanguageLabel = $errLanguageLabel;
        $this->debugMsg = $debugMsg;
        $this->transactionId = $transactionId;
        
        // call parent constructor to make sure everything is assigned properly
        parent::__construct($errLanguageLabel, 0);
        
    }
    
    
    
    /***************************************************************************
     *   GENERAL METHODS
     **************************************************************************/
    
    /**
     * Handles an exception: Debug information is written to TYPO3 devlog, TYPO3 syslog and is sent to trace()
     *
     * @param   void       
     * @return  void
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-02
     */
    public function handle() {
        
        $traceString = 
            'Exception Class: '.get_class($this).chr(10).
            'Error Label    : '.$this->getMessage().chr(10).
            (!empty($this->debugMsg) ? 'Debug Message  : '.$this->debugMsg.chr(10) : '')
            ;
        
        // write to transaction log file if a transaction ID is given
        if (!empty($this->transactionId)) {
            $message = 'Transaction exception "'.$this->errLanguageLabel.'": '.$this->debugMsg;
            tx_ptvrepay_log::getInstance()->write($this->transactionId, $message , tx_ptvrepay_log::MSG_STATUS_ERROR);
        }  
            
        // write to TYPO3 devlog
        if (TYPO3_DLOG) {
            t3lib_div::devLog($this->getMessage(), 
                'pt_vrepay', 
                3, // "error"
                array(
                    'exceptionClass' => get_class($this), 
                    'debugMsg' => $this->debugMsg, 
                    'file' => $this->getFile(), 
                    'line' => $this->getLine(), 
                )
            );
        }
        
        // write to TYPO3 syslog
        t3lib_div::sysLog(
            $this->getMessage().'['.get_class($this).': '.$this->debugMsg.']', 
            'pt_vrepay', 
            3 // "error"
        );
            
        trace($traceString, 0, '############ '.get_class($this).' ############');
        
    }
    
    
    
    /***************************************************************************
     *   GETTER
     **************************************************************************/
    
    /**
     * Return the dlanguage label to be used for the exception output to frontend users
     *
     * @param   void
     * @return  string
     * @since   2008-12-02
     */
    public function get_errLanguageLabel() {
        
        return $this->errLanguageLabel;
        
    }
    
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_transactionException.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_transactionException.php']);
}

?>