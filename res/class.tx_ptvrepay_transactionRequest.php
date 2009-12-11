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
 * Abstract VR-ePay transaction request class
 *
 * $Id: class.tx_ptvrepay_transactionRequest.php,v 1.23 2009/02/16 15:01:28 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-12-01
 */ 


/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_transactionException.php';
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_transactionAccessor.php';
require_once t3lib_extMgm::extPath('pt_vrepay').'res/class.tx_ptvrepay_log.php';

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php'; // assertion class



/**
 * Transaction request class for FRONTEND usage
 *
 * @package     TYPO3
 * @subpackage  tx_ptvrepay
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-12-01
 */
abstract class tx_ptvrepay_transactionRequest {
    
    /***************************************************************************
     *   CONSTANTS
     **************************************************************************/
    
    /*
     * @var string  the extension key
     */
    const EXT_KEY     = 'pt_vrepay';
    
    /*
     * @var string  path to the locallang file to use within this class
     */
    const LL_FILEPATH = 'res/locallang_res_classes.xml';
    
    
    
    /***************************************************************************
     *   GENERAL PROPERTIES
     **************************************************************************/
    
    /*
     * @var array   extension configuration values
     */
    protected $extensionConfigArr = array();
    
    /*
     * @var integer   PID where to store transaction records
     */
    protected $pid = 0;
    
    /*
     * @var string    merchant/shop reference identificator of the related ordering process, e.g. invoice number, confirmation number or booking id (1-17 chars allowed, has not be unique per transaction)
     */
    protected $merchantReference = '';
    
    /***************************************************************************
     *   PROVIDER REQUEST PROPERTIES: 
     *   named in German using the original VR-ePay request parameter names
     **************************************************************************/
    
    protected $haendlernr = '';     // (string) merchant number (e.g. "80000......") 
    protected $tsatyp = '';         // (string) transaction initialization type ("ECOM" || "MOTO") - "ECOM" = default, see VR-eRay parameter specifications of 'TSATYP' for details
    
    protected $referenznr = '';     // (string) unique transaction reference number (e.g."Order-number 1-1-1")
    protected $betrag = 0;          // (integer) payment amount in smallest unit (e.g. cents for euro: 3,59 EUR -> "359") 
    protected $waehrung = '';       // (string) currency code (currently only "EUR" supported)
    protected $infotext = '';       // (string) optional infotext for the order
    protected $artikelanz = 0;      // (integer) number of different order positions (different articles)
    
    protected $urlerfolg = '';      // (string) payment success URL (e.g. "http://myshop.de/success.html" ) 
    protected $urlfehler = '';      // (string) payment error URL (e.g. "http://myshop.de/error.html")
    protected $urlabbruch = '';     // (string) cancellation URL (e.g. "http://myshop.de/cancel.html") 
    protected $urlantwort = '';     // (string) response URL (e.g. "http://myshop.de/response.html" ) 
    protected $urlagb = '';         // (string) URL of the terms and conditions page (e.g. "http://myshop.de/termscond.html" ) 
    
    protected $antwgeheimnis = '';  // (string) response secret string (for security check)
    protected $benachprof = '';     // (string) notification type ("BSG" || "ZHL" || "KEI" || "ALL") - "ZHL" = default, see VR-eRay parameter specifications of 'BENACHRPROF' for details
    
    protected $zahlart = '';        // (string) transaction type for VR-ePay param ZAHLART (e.g. "KAUFEN" for buying (=default), "RESERVIEREN" for reserving), see VR-eRay 'ZAHLART' parameter specifications of 'ZAHLART' for further options and details
     
    protected $servicename = '';    // (string) transaction service name: "DIALOG" for dialog transactions || "DIREKT" for direct transactions - to be set in inheriting classes
    
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR
     **************************************************************************/
     
    /**
     * Class constructor: retrieves the configuration and sets the object's properties 
     * (Note: this class is intended for FRONTEND usage, requires $GLOBALS['TSFE']->cObj!)
     *
     * @param   string  merchant/shop reference identificator of the related ordering process, e.g. invoice number, confirmation number or booking id (1-17 chars allowed, has not be unique per transaction)
     * @param   double  total sum to pay for the request (use "." as decimal point, 2 digits after decimal point!)
     * @param   integer number of *different* order positions (different articles)
     * @param   string  "salt" string to use for building an md5 for security reasons (to be used e.g. for security check of successful payment returns)
     * @return  void
     * @global  tslib_cObj      $GLOBALS['TSFE']->cObj
     * @throws  exceptionAssertion  if no valid amount passed as param
     * @throws  exceptionAssertion  (multiple) if required properties could not be set
     * @throws  exceptionAssertion  if no valid TSFE cObj found
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-01
     */
    public function __construct($merchantReference, $amount, $articleQuantity) {
        
        // get basic configuration data
        $this->getConfiguration();
        
        // set default initialization (these may be overwritten by appropriate setter methods)
        $this->set_zahlart('KAUFEN');
        $this->set_benachprof('ZHL');
        $this->set_tsatyp('ECOM');
        $this->set_waehrung('EUR'); // currency code, currently only 'EUR' supported!
        $this->set_antwgeheimnis(md5(uniqid(rand(), true))); // create a nearly duplicate safe and extremely difficult to predict response secret for each transaction request, see http://de.php.net/manual/en/function.uniqid.php
        $this->set_infotext('');    // optional individual additional information text (1-1024 chars allowed)
        
        // set service name by calling the inheriting classes method
        $this->setImplementedServicename(); // sets $this->servicename for the concrete transaction service implementation of the inheriting class 
        
        // log new transaction start
        $logMessage = 'Started new "'.$this->get_servicename().'" transaction for merchant reference "'.$merchantReference.'" - created response secret "'.$this->get_antwgeheimnis().'"';
        tx_ptvrepay_log::getInstance()->write('UNKNOWN', $logMessage, tx_ptvrepay_log::MSG_STATUS_INITIAL);
        
        // process constructor params (some constructor params are checked internally within appropriate setters used for property assignment)
        tx_pttools_assert::isNumeric($amount, array('message' => 'No valid amount given.'));
        $this->set_merchantReference($merchantReference);
        $this->set_referenznr($this->getUniqueReferenceNumber($this->get_merchantReference())); // see comment for getUniqueReferenceNumber()
        $this->set_betrag((integer)($amount * 100));  // TODO: check if rounding is ok here
        $this->set_artikelanz($articleQuantity);
        tx_ptvrepay_log::getInstance()->write($this->get_referenznr(), 'Created new unique reference number "'.$this->get_referenznr().'"' , tx_ptvrepay_log::MSG_STATUS_INFO);
        
        // process extension configuration params
        $this->set_haendlernr(!empty($this->extensionConfigArr['merchantId']) ? $this->extensionConfigArr['merchantId'] : $this->extensionConfigArr['accountUserId']);
        $this->set_pid(tx_pttools_div::getPid($this->extensionConfigArr['pidTransactionStorage']));
        
        // build termscond URL and response URL depending on extension configuration param
        tx_pttools_assert::isInstanceOf($GLOBALS['TSFE']->cObj, 'tslib_cObj', array('message' => 'No valid TSFE cObj found.'));
        
        $pidTermsCond = tx_pttools_div::getPid($this->extensionConfigArr['pidTermsCond']);
        tx_pttools_assert::isValidUid($pidTermsCond, true, array('message' => 'No valid termscond pid or alias set in configuration.'));
        $typoLinkUrlTermsCond = $GLOBALS['TSFE']->cObj->getTypoLink_URL($pidTermsCond);
        $this->set_urlagb(t3lib_div::locationHeaderUrl($typoLinkUrlTermsCond));
        
        $pidResponse = tx_pttools_div::getPid($this->extensionConfigArr['pidResponse']);
        tx_pttools_assert::isValidUid($pidResponse, true, array('message' => 'No valid response pid or alias set in configuration.'));
        $typolinkUrlResponse = $GLOBALS['TSFE']->cObj->getTypoLink_URL($pidResponse);
        $this->set_urlantwort(str_replace('http://', 'https://', t3lib_div::locationHeaderUrl($typolinkUrlResponse))); // the response page has to be a SSL URL (https://)!!
        
        // build transaction result URLs
        $selfUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
        $paramSeparator = (stripos($selfUrl, '?') === false ? '?' : '&');
        $this->set_urlerfolg($selfUrl.$paramSeparator.'tx_ptvrepay_pi1_action=success'); // 'tx_ptvrepay_pi1[action]' not possible since the chars '[' and ']' are not allowed by VR-ePay
        $this->set_urlfehler($selfUrl.$paramSeparator.'tx_ptvrepay_pi1_action=error');
        $this->set_urlabbruch($selfUrl.$paramSeparator.'tx_ptvrepay_pi1_action=cancel');
        
        trace($this);
        
    }
    
    
    
    /***************************************************************************
     *   ABSTRACT METHODS: to be implemented in inheriting class
     **************************************************************************/
    /**
     * Set the transaction service name (property $servicename) for the concrete service implementation of the inheriting class 
     *  
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-02
     */
    abstract protected function setImplementedServicename();
    
    
    
    /***************************************************************************
     *   DOMAIN LOGIC METHODS
     **************************************************************************/
     
    /**
     * Read and check the basic configuration
     *
     * @param   void
     * @return  void
     * @throws  exceptionAssertion  if no typoscript configuration found
     * @throws  exceptionAssertion  (multiple) if required configuration values are not valid
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-01
     */
    protected function getConfiguration() {
        
        $this->extensionConfigArr = tx_pttools_div::typoscriptRegistry('config.tx_ptvrepay.', NULL, 'pt_vrepay');
        trace($this->extensionConfigArr, 0, 'config.tx_ptvrepay.');
        
        tx_pttools_assert::isNotEmptyArray($this->extensionConfigArr, array('message' => 'No typoscript configuration found.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['systemUrl'], array('message' => 'No systemUrl set in configuration.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['accountUserId'], array('message' => 'No accountUserId set in configuration.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['accountPassword'], array('message' => 'No accountPassword set in configuration.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['systemUrl'], array('message' => 'No systemUrl set in configuration.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['pidTransactionStorage'], array('message' => 'No transaction storage pid or alias set in configuration.'));
        tx_pttools_assert::isNotEmptyString($this->extensionConfigArr['pidTermsCond'], array('message' => 'No "terms and conditions" page set in configuration.')); // this is required in VR-ePay request specifications...
        
    }
    
    /**
     * Returns a unique reference number for a given not necessarily unique merchant reference (e.g. invoice number from shop).
     *
     * @param   string  original merchant reference (e.g. invoice number from shop), 1-17 chars allowed
     * @return  string  unique reference number
     * @throws  exceptionAssertion  if no valid reference number given
     * @throws  exceptionAssertion  if the requested reference number is not in range (1-17 chars allowed)
     * @throws  tx_ptvrepay_transactionException  if the cURL request fails
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2009-02-12
     */
    protected static function getUniqueReferenceNumber($merchantReference) {
        
        /*
         * @var int     static counter for appendix number
         */
        static $counter = 1;
        
        if ($counter >= 99) {  // endless loop prevention - this should not happen with "normal" usage
            throw new tx_ptvrepay_transactionException('requestTooManyTriesForSameRefNo', 'Reached 99 requests for merchant reference no. "'.$merchantReference.'"', 'UNKNOWN');
        }
        
        $uniqueReferenceNumber = (string)$merchantReference.'/'.sprintf('%02s', $counter);
        
        // recursive method call with incremented counter if just creater $uniqueReferenceNumber already exists in the database
        if (tx_ptvrepay_transactionAccessor::getInstance()->selectTransactionByReferenceNumber($uniqueReferenceNumber) != false) {
            tx_ptvrepay_log::getInstance()->write('UNKNOWN', 'Reference number "'.$uniqueReferenceNumber.'" already exists, creating new one' , tx_ptvrepay_log::MSG_STATUS_NOTICE);         
            $counter += 1;
            $uniqueReferenceNumber = self::getUniqueReferenceNumber($merchantReference);
        }
        
        return $uniqueReferenceNumber; 
        
    }
    
    /**
     * Prepares and sends the transaction request via cURL
     *
     * @param   void  
     * @return  void
     * @throws  tx_ptvrepay_transactionException  if the cURL request fails
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-02
     */
    public function sendRequest() {
        
        // compose HTTP query  
        $query = http_build_query($this->getPostArray(), '', '&'); // TODO: remove 2.-3. param?
        trace($query, 0, '$query');
 
        // HTTP basic authorisation (MODULE_PAYMENT_VREPAY_INSTITUT:MODULE_PAYMENT_VREPAY_PASSWORT, e.g. "80000.....":"Password") 
        $authString = $this->haendlernr.':'.$this->extensionConfigArr['accountPassword']; 
 
        // cURL initialisation and configuration 
        $curlHandle = curl_init(); 
        curl_setopt($curlHandle, CURLOPT_URL, $this->extensionConfigArr['systemUrl']); 
        curl_setopt($curlHandle, CURLOPT_USERPWD, $authString); 
        curl_setopt($curlHandle, CURLOPT_HTTP_VERSION, 1.1); 
        curl_setopt($curlHandle, CURLOPT_POST, 1); 
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $query); 
        curl_setopt($curlHandle, CURLOPT_SSLVERSION, 3); 
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curlHandle, CURLOPT_HEADER, 1); // show http-header 
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 0); // no automatic redirect 
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);  // sends the result (instead of TRUE) on success as return value of curl_exec()
        
        // store request to database, execute cURL request
        tx_ptvrepay_log::getInstance()->write($this->get_referenznr(), 'Storing transaction request data to database');
        tx_ptvrepay_transactionAccessor::getInstance()->insertTransaction($this);
        tx_ptvrepay_log::getInstance()->write($this->get_referenznr(), 'Sending cURL request: ', tx_ptvrepay_log::MSG_STATUS_INFO, $this->getPostArray());
        $curlResponse = curl_exec($curlHandle); // the result on success, FALSE on failure (since the CURLOPT_RETURNTRANSFER option is set)
        
        // cURL error
        if ($curlResponse == false) { 
            curl_close($curlHandle); // close cURL handler 
            $debugMsg = 'Did not receive any return from the curl_exec() request';
            tx_ptvrepay_transactionAccessor::getInstance()->updateTransactionResult($this->get_referenznr(), '[cURL ERROR] '.$debugMsg);
            throw new tx_ptvrepay_transactionException('requestCurlExecReturnError', 'cURL ERROR: '.$debugMsg, $this->get_referenznr());
          
        // cURL request succeeded:process return
        } else { 
            $curlInfo = curl_getinfo($curlHandle); // get more info from cURL request 
            curl_close($curlHandle); // close cURL handler 
            
            $this->processCurlReturn($curlInfo, $curlResponse);
        }
        
    }
    
    /**
     * Processes the return of a cURL request
     *
     * @param   array   associative array retrieved from the cURL request with the PHP function curl_getinfo(), see http://www.php.net/manual/en/function.curl-getinfo.php for array keys
     * @param   mixed   (FALSE, HTTP message string, ....) cURL response / return of PHP function of curl_exec() with the option CURLOPT_RETURNTRANSFER set, see http://www.php.net/manual/en/function.curl-exec.php for details    
     * @return  void
     * @throws  exceptionAssertion  if no valid request info received
     * @throws  exceptionAssertion  if no valid HTTP code received
     * @throws  tx_ptvrepay_transactionException  if an request error occurs
     * @throws  tx_ptvrepay_transactionException  on system error (e.g. if payment service is temporary not available)
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-02
     */
    protected function processCurlReturn($curlInfo, $curlResponse) {
        
        trace($curlInfo, 0, '$curlInfo');
        trace($curlResponse, 0, '$curlResponse');
        tx_pttools_assert::isArray($curlInfo, array('message'=>'No valid request info received'));
        tx_pttools_assert::isNotEmpty($curlInfo['http_code'], array('message'=>'No valid HTTP code received'));
        
        // control HTTP code         
        switch ($curlInfo['http_code']) { 
            
            // redirect = success 
            case '302': 
                
                // parse cURL return
                $httpMsg = $this->httpParseMessage($curlResponse); // divide http-header & body 
                $httpHeader = $this->httpParseHeaders($httpMsg[0]); // get http-header fields 
                $redirectUrl = $httpHeader['LOCATION'];
                trace($redirectUrl, 0, '$redirectUrl - redirected does not work here because of trace() output!');
                
                // redirect to vr-epay destination 
                $message = 'cURL request success, redirecting to received redirect URL: ';
                tx_ptvrepay_log::getInstance()->write($this->get_referenznr(), $message , tx_ptvrepay_log::MSG_STATUS_INFO, array('redirectUrl' => $redirectUrl));
                tx_ptvrepay_transactionAccessor::getInstance()->updateTransactionResult($this->get_referenznr(), '[Request sucess] Received redirect URL: '.$redirectUrl);
                ob_clean();
                header('Location: '.$redirectUrl);
                exit;
                
                break; 
                 
            // POST-content = error occurred 
            case '200': 
                
                $errorParamArray = array();
                
                // parse cURL return
                $httpMsg = $this->httpParseMessage($curlResponse); 
                $errorParamString = $httpMsg[1]; // contains error code and error description in URL querystring format
                
                // handle error
                parse_str($errorParamString, $errorParamArray);
                trace($errorParamArray, 0, '$errorParamArray');
                $debugMsg = 'Return from VR-ePay: '.str_replace('&', ' / ', urldecode($errorParamString));
                tx_ptvrepay_transactionAccessor::getInstance()->updateTransactionResult($this->get_referenznr(), '[Request error] '.$debugMsg);
                throw new tx_ptvrepay_transactionException('requestCurlReturnPostContentError', 'cURL request error: '.$debugMsg, $this->get_referenznr());
                
                break; 
                
            // other http codes = systemerror 
            default:  
                
                $debugMsg = 'System error - Payment service temporary not available (HTTP Code: '.$curlInfo['http_code'].')';
                tx_ptvrepay_transactionAccessor::getInstance()->updateTransactionResult($this->get_referenznr(), '[Request error] '.$debugMsg);
                throw new tx_ptvrepay_transactionException('requestCurlReturnSystemError', 'cURL request error: '.$debugMsg, $this->get_referenznr());
                
                break;
                
        }
        
    }
    
    /**
     * Returns the POST array to be used for a general transaction request
     *
     * @param   void 
     * @return  array   the POST array to be used for the request
     * @author  Rainer Kuhn <kuhn@punkt.de>
     * @since   2008-12-01
     */
    protected function getPostArray() {
    
        // collect http-postdata, refer to documentation 
        $postArray = array( 
        
            'HAENDLERNR'    => $this->haendlernr,
            'TSATYP'        => $this->tsatyp, 
            
            'REFERENZNR'    => $this->referenznr,
            'BETRAG'        => $this->betrag,
            'WAEHRUNG'      => $this->waehrung,
            'INFOTEXT'      => $this->infotext,
            'ARTIKELANZ'    => $this->artikelanz,
            
            'URLERFOLG'     => $this->urlerfolg, 
            'URLFEHLER'     => $this->urlfehler,
            'URLABBRUCH'    => $this->urlabbruch,
            'URLANTWORT'    => $this->urlantwort,
            'URLAGB'        => $this->urlagb,
            
            'ANTWGEHEIMNIS' => $this->antwgeheimnis,
            'BENACHRPROF'   => $this->benachprof, 
            
            'ZAHLART'       => $this->zahlart,
            'SERVICENAME'   => $this->servicename,
        
            // TODO: implement German ELV finally - make this ELV-only related data configurable via properties/setter
            'VERWENDANZ'    => '0', 
            #'VERWENDUNG1'   => '',
            #'VERWENDUNG2'   => '', 
            
        );
        
        return $postArray;
        
    }
    
    /**
     * Returns a given HTTP message as an array containing header and body (replacement for httpParseMessage() of the pecl_http library)
     *
     * @param   string      HTTP message 
     * @return  array|FALSE header and body exploded into an array ([0]=header, [1]=body) or FALSE if no valid HTTP message passed
     * @author  Rainer Kuhn <kuhn@punkt.de>, based on code example from "VR-ePay Payment Interface" specifications
     * @since   2008-12-02
     */
    protected function httpParseMessage($httpMessage=false){ 
        
        if ($httpMessage === false) { 
            return false; 
        }
        
        // remove carriage returns and explode message parts (header and body) into an array
        $message = str_replace("\r", '', $httpMessage); 
        $msgPartsArr = explode("\n\n", $message, 2); // header and body are divided by TWO new lines
        
        return $msgPartsArr; 
        
    } 
  
    /**
     * Returns a given HTTP message header string as an array with header part names as uppercase array key (replacement for httpParseHeaders() of the pecl_http library)
     *
     * @param   string      HTTP message headers string 
     * @return  array|FALSE header parts array with header part names as uppercase array key or FALSE if no valid HTTP message headers string passed
     * @author  Rainer Kuhn <kuhn@punkt.de>, based on code example from "VR-ePay Payment Interface" specifications
     * @since   2008-12-02
     */
    protected function httpParseHeaders($httpHeaders=false){ 
    
        if ($httpHeaders === false) { 
          return false; 
        } 
        
        // remove carriage returns and explode header parts into an array
        $headers = str_replace("\r", '', $httpHeaders); 
        $headerPartsArr = explode("\n", $headers); // headers are divided by new line 
        
        // make new header parts array with header part name as uppercase array key
        foreach($headerPartsArr as $value) { 
            $headerPart = explode(": ",$value); 
            if ($headerPart[0] && !$headerPart[1]) { 
                $headerdata['STATUS'] = $headerPart[0]; 
            } elseif ($headerPart[0] && $headerPart[1]) { 
                // uppercase for all keys 
                $headerdata[strtoupper($headerPart[0])] = $headerPart[1]; 
            } 
        }
        
        trace($headerdata, 0, '$headerdata');
        return $headerdata; 
        
    } 
    
    
    
    /***************************************************************************
     *   PROPERTY GETTER/SETTER METHODS
     **************************************************************************/
     
    /**
     * Returns the property value
     *
     * @param   void        
     * @return  array       property value
     * @since   2008-12-12
     */
    protected function get_extensionConfigArr() {
    
        return $this->extensionConfigArr;
        
    }

    /**
     * Sets the property value
     *
     * @param   array       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_extensionConfigArr(array $extensionConfigArr) {
    
        $this->extensionConfigArr = $extensionConfigArr;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  integer       property value
     * @since   2008-12-12
     */
    public function get_pid() {
    
        return $this->pid;
        
    }

    /**
     * Sets the property value
     *
     * @param   integer       transaction storage pid     
     * @return  void
     * @since   2008-12-12
     */
    protected function set_pid($pid) {
    
        tx_pttools_assert::isInteger($this->pid, array('message' => 'No valid transaction storage pid given.'));
        $this->pid = (integer) $pid;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2009-02-13
     */
    public function get_merchantReference() {
    
        return $this->merchantReference;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       merchant eference number (1-17 chars allowed)
     * @see     getUniqueReferenceNumber()
     * @return  void
     * @since   2009-02-13
     */
    protected function set_merchantReference($merchantReference) {
        
        tx_pttools_assert::isNotEmptyString($merchantReference, array('message' => 'No valid merchant refernce given.'));
        tx_pttools_assert::isInRange(strlen($merchantReference), 1, 17,  array('message' => 'Given merchant reference length is not in range (1-17 chars allowed).'));
        
        $this->merchantReference = (string) $merchantReference;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_haendlernr() {
    
        return $this->haendlernr;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_haendlernr($haendlernr) {
    
        tx_pttools_assert::isNotEmptyString($haendlernr, array('message' => 'No merchantId (haendlernr) given.'));
        $this->haendlernr = (string) $haendlernr;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_tsatyp() {
    
        return $this->tsatyp;
        
    }

    /**
     * Sets the property value
     *
     * @param   string      transaction initialization type ("ECOM" || "MOTO"), see VR-eRay parameter specifications of 'TSATYP' for details 
     * @return  void
     * @throws  exceptionAssertion  if no valid transaction initialization type given
     * @since   2008-12-03
     */
    public function set_tsatyp($tsatyp) {
        
        tx_pttools_assert::isInList($tsatyp, 'ECOM,MOTO', array('message' => 'No valid transaction initialization type given.'));
        $this->tsatyp = $tsatyp;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_referenznr() {
    
        return $this->referenznr;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       Reference number (4-20 chars allowed); NEW (undocumented) information from VR-ePay: this must be unique!
     * @see     getUniqueReferenceNumber()
     * @return  void
     * @since   2008-12-12
     */
    protected function set_referenznr($referenznr) {
    
        tx_pttools_assert::isNotEmptyString($referenznr, array('message' => 'No reference number (referenznr) given.'));
        tx_pttools_assert::isInRange(strlen($referenznr), 4, 20,  array('message' => 'Reference number (referenznr) is not in range (4-20 chars allowed).'));
        $this->referenznr = (string) $referenznr;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  integer       property value
     * @since   2008-12-12
     */
    public function get_betrag() {
    
        return $this->betrag;
        
    }

    /**
     * Sets the property value
     *
     * @param   integer       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_betrag($betrag) {
    
        tx_pttools_assert::isPositiveInteger($betrag, false, array('message' => 'No amount (betrag) given.'));
        $this->betrag = (integer) $betrag;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_waehrung() {
    
        return $this->waehrung;
        
    }

    /**
     * Sets the property value: currency code, currently only 'EUR' supported!
     *
     * @param   string       property value: currency code, currently only 'EUR' supported!
     * @return  void
     * @since   2008-12-12
     */
    public function set_waehrung($waehrung) {
        
        tx_pttools_assert::isInList($waehrung, 'EUR', array('message' => 'No valid currency code (waehrung) given: currently only "EUR" supported.'));
        $this->waehrung = (string) $waehrung;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_infotext() {
    
        return $this->infotext;
        
    }

    /**
     * Sets the property value: optional individual additional information text 
     *
     * @param   string        optional individual additional information text (length: 0-1024 chars allowed) 
     * @return  void
     * @since   2008-12-12
     */
    public function set_infotext($infotext) {
    
        tx_pttools_assert::isString($infotext, array('message' => 'No valid infotext given.'));
        tx_pttools_assert::isInRange(strlen($infotext), 0, 1024,  array('message' => 'Infotext length is not in range (0-1024 chars allowed).'));
        $this->infotext = (string) $infotext;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_artikelanz() {
    
        return $this->artikelanz;
        
    }

    /**
     * Sets the property value
     *
     * @param   integer       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_artikelanz($artikelanz) {
    
        tx_pttools_assert::isPositiveInteger($artikelanz, false, array('message' => 'No valid article quantity (artikelanz) given.'));
        $this->artikelanz = (integer) $artikelanz;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_urlerfolg() {
    
        return $this->urlerfolg;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_urlerfolg($urlerfolg) {
    
        tx_pttools_assert::isNotEmptyString($urlerfolg, array('message' => 'No valid success url (urlerfolg) given.'));
        $this->urlerfolg = (string) $urlerfolg;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_urlfehler() {
        return $this->urlfehler;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_urlfehler($urlfehler) {
    
        tx_pttools_assert::isNotEmptyString($urlfehler, array('message' => 'No valid error url (urlfehler) given.'));
        $this->urlfehler = (string) $urlfehler;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_urlabbruch() {
    
        return $this->urlabbruch;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_urlabbruch($urlabbruch) {
    
        tx_pttools_assert::isNotEmptyString($urlabbruch, array('message' => 'No valid cancel url (urlabbruch) given.'));
        $this->urlabbruch = (string) $urlabbruch;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_urlantwort() {
    
        return $this->urlantwort;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_urlantwort($urlantwort) {
    
        tx_pttools_assert::isNotEmptyString($urlantwort, array('message' => 'No valid response url (urlantwort) given.'));
        $this->urlantwort = (string) $urlantwort;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_urlagb() {
    
        return $this->urlagb;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_urlagb($urlagb) {
    
        tx_pttools_assert::isNotEmptyString($urlagb, array('message' => 'No valid termscond url (urlagb) given.'));
        $this->urlagb = (string) $urlagb;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_antwgeheimnis() {
    
        return $this->antwgeheimnis;
        
    }

    /**
     * Sets the property value
     *
     * @param   string       property value       
     * @return  void
     * @since   2008-12-12
     */
    protected function set_antwgeheimnis($antwgeheimnis) {
    
        tx_pttools_assert::isNotEmptyString($antwgeheimnis, array('message' => 'No valid response secret (antwgeheimnis) given.'));
        tx_pttools_assert::isInRange(strlen($antwgeheimnis), 1, 32,  array('message' => 'Response secret (antwgeheimnis) length is not in range (1-32 chars allowed).'));
        $this->antwgeheimnis = (string) $antwgeheimnis;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_benachprof() {
    
        return $this->benachprof;
        
    }

    /**
     * Sets the property value
     *
     * @param   string      notification type ("BSG" || "ZHL" || "KEI" || "ALL"), see VR-eRay parameter specifications of 'BENACHRPROF' for details   
     * @return  void
     * @throws  exceptionAssertion  if no valid notification type given
     * @since   2008-12-02
     */
    public function set_benachprof($benachprof) {
        
        tx_pttools_assert::isInList($benachprof, 'BSG,ZHL,KEI,ALL', array('message' => 'No valid notification type given.'));
        $this->benachprof = $benachprof;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_zahlart() {
    
        return $this->zahlart;
        
    }

    /**
     * Sets the property value
     *
     * @param   string      transaction type: (e.g. "KAUFEN" for buying, "RESERVIEREN" for reserving) see VR-eRay parameter 'ZAHLART' specifications for further options and details
     * @return  void
     * @throws  exceptionAssertion  if no valid notification type given
     * @since   2008-12-02
     */
    public function set_zahlart($zahlart) {
        
        tx_pttools_assert::isInList($zahlart, 'RESERVIEREN,STRESERVIEREN,BUCHEN,STBUCHEN,KAUFEN,STKAUFEN,GUTSCHREIBEN,STGUTSCHREIBEN', 
                                    array('message' => 'No valid transaction type given.'));
        $this->zahlart = $zahlart;
        
    }

    /**
     * Returns the property value
     *
     * @param   void        
     * @return  string       property value
     * @since   2008-12-12
     */
    public function get_servicename() {
    
        return $this->servicename;
        
    }

    /**
     * Sets the property value
     *
     * @param   string      transaction service name: "DIALOG" for dialog transactions || "DIREKT" for direct transactions    
     * @return  void
     * @throws  exceptionAssertion  if no valid servicename given
     * @since   2008-12-02
     */
    public function set_servicename($servicename) {
        
        tx_pttools_assert::isInList($servicename, 'DIALOG,DIREKT', array('message' => 'No valid servicename given.'));
        $this->servicename = $servicename;
        
    }

    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_transactionRequest.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_vrepay/res/class.tx_ptvrepay_transactionRequest.php']);
}

?>
