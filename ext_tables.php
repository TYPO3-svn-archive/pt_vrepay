<?php
/** 
 * ext_tables.php for extension "pt_vrepay"
 *
 * $Id: ext_tables.php,v 1.7 2009/02/16 10:10:20 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-12-01
 */ 

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}


t3lib_extMgm::allowTableOnStandardPages('tx_ptvrepay_transactions');
$TCA['tx_ptvrepay_transactions'] = array (
    'ctrl' => array (
        'title'     => 'LLL:EXT:pt_vrepay/locallang_db.xml:tx_ptvrepay_transactions',      
        'readOnly' => true,
        'label'     => 'reference_no',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate',  
        'delete' => 'deleted',  
        'enablecolumns' => array (      
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_ptvrepay_transactions.gif',
    ),
    'feInterface' => array (
        'fe_admin_fieldList' => 'hidden, reference_no, response_secret, merchant_reference, amount, request_result, status, response_params',
    )
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:pt_vrepay/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'), 'list_type');
#t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/', 'VR-ePay Dialog Transaction (Request)');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2'] = 'layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:pt_vrepay/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'), 'list_type');
#t3lib_extMgm::addStaticFile($_EXTKEY,'pi2/static/', 'VR-ePay Response Processor');


t3lib_extMgm::addStaticFile($_EXTKEY,'static/', 'VR-ePay');



?>