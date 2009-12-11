<?php
/** 
 * tca.php for extension "pt_vrepay"
 *
 * $Id: tca.php,v 1.3 2009/02/13 15:46:58 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-12-12
 */ 

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$TCA['tx_ptvrepay_transactions'] = array(
    'ctrl' => $TCA['tx_ptvrepay_transactions']['ctrl'],
    'interface' => array(
        'showRecordFieldList' => 'hidden,reference_no,response_secret,merchant_reference,amount,request_result,status,response_params'
    ),
    'feInterface' => $TCA['tx_ptvrepay_transactions']['feInterface'],
    'columns' => array(
        'hidden' => array(        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array(
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'reference_no' => array(        
            'exclude' => 1,        
            'label' => 'LLL:EXT:pt_vrepay/locallang_db.xml:tx_ptvrepay_transactions.reference_no',        
            'config' => array(
                'type' => 'input',    
                'size' => '30',    
                'max' => '20',    
                'eval' => 'required,trim',
            )
        ),
        'response_secret' => array(        
            'exclude' => 1,        
            'label' => 'LLL:EXT:pt_vrepay/locallang_db.xml:tx_ptvrepay_transactions.response_secret',        
            'config' => array(
                'type' => 'input',    
                'size' => '35',    
                'max' => '32',    
                'eval' => 'required,trim',
            )
        ),
        'merchant_reference' => array(        
            'exclude' => 1,        
            'label' => 'LLL:EXT:pt_vrepay/locallang_db.xml:tx_ptvrepay_transactions.merchant_reference',        
            'config' => array(
                'type' => 'input',    
                'size' => '20',    
                'max' => '17',    
                'eval' => 'required,trim',
            )
        ),
        'amount' => array(        
            'exclude' => 1,        
            'label' => 'LLL:EXT:pt_vrepay/locallang_db.xml:tx_ptvrepay_transactions.amount',        
            'config' => array(
                'type'     => 'input',
                'size'     => '10',
                'max'      => '10',
                'eval'     => 'int',
                'checkbox' => '0',
                'range'    => array(
                    'lower' => '0'
                ),
                'default' => 0
            )
        ),
        'request_result' => array(        
            'exclude' => 1,        
            'label' => 'LLL:EXT:pt_vrepay/locallang_db.xml:tx_ptvrepay_transactions.request_result',        
            'config' => array(
                'type' => 'text',
                'cols' => '48',    
                'rows' => '6',
            )
        ),
        'status' => array(        
            'exclude' => 1,        
            'label' => 'LLL:EXT:pt_vrepay/locallang_db.xml:tx_ptvrepay_transactions.status',        
            'config' => array(
                'type' => 'input',    
                'size' => '20',    
                'max' => '20',    
                'eval' => 'trim',
            )
        ),
        'response_params' => array(        
            'exclude' => 1,        
            'label' => 'LLL:EXT:pt_vrepay/locallang_db.xml:tx_ptvrepay_transactions.response_params',        
            'config' => array(
                'type' => 'text',
                'cols' => '48',    
                'rows' => '6',
            )
        ),
    ),
    'types' => array(
        '0' => array('showitem' => 'hidden;;1;;1-1-1, reference_no, response_secret, merchant_reference, amount, request_result, status, response_params')
    ),
    'palettes' => array(
        '1' => array('showitem' => '')
    )
);

?>