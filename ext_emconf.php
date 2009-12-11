<?php

########################################################################
# Extension Manager/Repository config file for ext: "pt_vrepay"
#
# Auto generated 11-12-2009 12:56
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'VR-ePay ePayment',
	'description' => 'ePayment extension for payment services of VR-ePay (Germany): allows electronic payment using credit cards and electronic cash (ec).',
	'category' => 'plugin',
	'author' => 'Rainer Kuhn',
	'author_email' => 't3extensions@punkt.de',
	'shy' => '',
	'dependencies' => 'pt_tools',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.2dev',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.0-0.0.0',
			'pt_tools' => '0.4.2-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'cURL enabled PHP (THIS IS JUST A HINT, please ignore if your server is correctly configured), see http://php.net/manual/en/curl.setup.php' => '',
			'SSL enabled webserver (THIS IS JUST A HINT, please ignore if your server is correctly configured), see https://www.vr-epay.info/gad/doc/Anbindungshandbuch%20VR-ePay%20V2.0%20En.pdf' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:98:{s:8:".project";s:4:"ea0b";s:9:"ChangeLog";s:4:"9569";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"4546";s:17:"ext_localconf.php";s:4:"55d1";s:14:"ext_tables.php";s:4:"f864";s:14:"ext_tables.sql";s:4:"c2cc";s:33:"icon_tx_ptvrepay_transactions.gif";s:4:"1e24";s:16:"locallang_db.xml";s:4:"741f";s:7:"tca.php";s:4:"9153";s:16:".svn/all-wcprops";s:4:"4f5e";s:12:".svn/entries";s:4:"31b1";s:11:".svn/format";s:4:"c30f";s:33:".svn/prop-base/ChangeLog.svn-base";s:4:"f2ea";s:34:".svn/prop-base/README.txt.svn-base";s:4:"f2ea";s:38:".svn/prop-base/ext_emconf.php.svn-base";s:4:"a94a";s:36:".svn/prop-base/ext_icon.gif.svn-base";s:4:"1131";s:41:".svn/prop-base/ext_localconf.php.svn-base";s:4:"a94a";s:38:".svn/prop-base/ext_tables.php.svn-base";s:4:"a94a";s:38:".svn/prop-base/ext_tables.sql.svn-base";s:4:"f2ea";s:57:".svn/prop-base/icon_tx_ptvrepay_transactions.gif.svn-base";s:4:"1131";s:40:".svn/prop-base/locallang_db.xml.svn-base";s:4:"f2ea";s:31:".svn/prop-base/tca.php.svn-base";s:4:"a94a";s:33:".svn/text-base/ChangeLog.svn-base";s:4:"9569";s:34:".svn/text-base/README.txt.svn-base";s:4:"ee2d";s:38:".svn/text-base/ext_emconf.php.svn-base";s:4:"2c1a";s:36:".svn/text-base/ext_icon.gif.svn-base";s:4:"4546";s:41:".svn/text-base/ext_localconf.php.svn-base";s:4:"55d1";s:38:".svn/text-base/ext_tables.php.svn-base";s:4:"f864";s:38:".svn/text-base/ext_tables.sql.svn-base";s:4:"c2cc";s:57:".svn/text-base/icon_tx_ptvrepay_transactions.gif.svn-base";s:4:"1e24";s:40:".svn/text-base/locallang_db.xml.svn-base";s:4:"741f";s:31:".svn/text-base/tca.php.svn-base";s:4:"9153";s:44:".settings/com.zend.php.javabridge.core.prefs";s:4:"a560";s:51:".settings/org.eclipse.php.core.projectOptions.prefs";s:4:"196d";s:14:"doc/DevDoc.txt";s:4:"9a67";s:19:"doc/wizard_form.dat";s:4:"76a6";s:20:"doc/wizard_form.html";s:4:"f87d";s:20:"doc/.svn/all-wcprops";s:4:"4cdd";s:16:"doc/.svn/entries";s:4:"d7cd";s:15:"doc/.svn/format";s:4:"c30f";s:38:"doc/.svn/prop-base/DevDoc.txt.svn-base";s:4:"f2ea";s:43:"doc/.svn/prop-base/wizard_form.dat.svn-base";s:4:"f2ea";s:44:"doc/.svn/prop-base/wizard_form.html.svn-base";s:4:"f2ea";s:38:"doc/.svn/text-base/DevDoc.txt.svn-base";s:4:"9a67";s:43:"doc/.svn/text-base/wizard_form.dat.svn-base";s:4:"76a6";s:44:"doc/.svn/text-base/wizard_form.html.svn-base";s:4:"f87d";s:29:"pi1/class.tx_ptvrepay_pi1.php";s:4:"0ce4";s:17:"pi1/locallang.xml";s:4:"29f4";s:20:"pi1/.svn/all-wcprops";s:4:"204b";s:16:"pi1/.svn/entries";s:4:"9aee";s:15:"pi1/.svn/format";s:4:"c30f";s:53:"pi1/.svn/prop-base/class.tx_ptvrepay_pi1.php.svn-base";s:4:"a94a";s:41:"pi1/.svn/prop-base/locallang.xml.svn-base";s:4:"f2ea";s:53:"pi1/.svn/text-base/class.tx_ptvrepay_pi1.php.svn-base";s:4:"0ce4";s:41:"pi1/.svn/text-base/locallang.xml.svn-base";s:4:"29f4";s:29:"pi2/class.tx_ptvrepay_pi2.php";s:4:"94ae";s:17:"pi2/locallang.xml";s:4:"bedc";s:20:"pi2/.svn/all-wcprops";s:4:"6e84";s:16:"pi2/.svn/entries";s:4:"4a5c";s:15:"pi2/.svn/format";s:4:"c30f";s:53:"pi2/.svn/prop-base/class.tx_ptvrepay_pi2.php.svn-base";s:4:"a94a";s:41:"pi2/.svn/prop-base/locallang.xml.svn-base";s:4:"f2ea";s:53:"pi2/.svn/text-base/class.tx_ptvrepay_pi2.php.svn-base";s:4:"94ae";s:41:"pi2/.svn/text-base/locallang.xml.svn-base";s:4:"bedc";s:43:"res/class.tx_ptvrepay_dialogTransaction.php";s:4:"f0c8";s:29:"res/class.tx_ptvrepay_log.php";s:4:"5839";s:45:"res/class.tx_ptvrepay_transactionAccessor.php";s:4:"380a";s:46:"res/class.tx_ptvrepay_transactionException.php";s:4:"ad05";s:44:"res/class.tx_ptvrepay_transactionRequest.php";s:4:"2418";s:45:"res/class.tx_ptvrepay_transactionResponse.php";s:4:"7fbe";s:29:"res/locallang_res_classes.xml";s:4:"13ad";s:20:"res/.svn/all-wcprops";s:4:"4e97";s:16:"res/.svn/entries";s:4:"93f1";s:15:"res/.svn/format";s:4:"c30f";s:67:"res/.svn/prop-base/class.tx_ptvrepay_dialogTransaction.php.svn-base";s:4:"a94a";s:53:"res/.svn/prop-base/class.tx_ptvrepay_log.php.svn-base";s:4:"a94a";s:69:"res/.svn/prop-base/class.tx_ptvrepay_transactionAccessor.php.svn-base";s:4:"a94a";s:70:"res/.svn/prop-base/class.tx_ptvrepay_transactionException.php.svn-base";s:4:"a94a";s:68:"res/.svn/prop-base/class.tx_ptvrepay_transactionRequest.php.svn-base";s:4:"a94a";s:69:"res/.svn/prop-base/class.tx_ptvrepay_transactionResponse.php.svn-base";s:4:"a94a";s:53:"res/.svn/prop-base/locallang_res_classes.xml.svn-base";s:4:"f2ea";s:67:"res/.svn/text-base/class.tx_ptvrepay_dialogTransaction.php.svn-base";s:4:"f0c8";s:53:"res/.svn/text-base/class.tx_ptvrepay_log.php.svn-base";s:4:"5839";s:69:"res/.svn/text-base/class.tx_ptvrepay_transactionAccessor.php.svn-base";s:4:"380a";s:70:"res/.svn/text-base/class.tx_ptvrepay_transactionException.php.svn-base";s:4:"ad05";s:68:"res/.svn/text-base/class.tx_ptvrepay_transactionRequest.php.svn-base";s:4:"2418";s:69:"res/.svn/text-base/class.tx_ptvrepay_transactionResponse.php.svn-base";s:4:"7fbe";s:53:"res/.svn/text-base/locallang_res_classes.xml.svn-base";s:4:"13ad";s:20:"static/constants.txt";s:4:"528b";s:16:"static/setup.txt";s:4:"fe40";s:23:"static/.svn/all-wcprops";s:4:"2d92";s:19:"static/.svn/entries";s:4:"e0b4";s:18:"static/.svn/format";s:4:"c30f";s:44:"static/.svn/prop-base/constants.txt.svn-base";s:4:"f2ea";s:40:"static/.svn/prop-base/setup.txt.svn-base";s:4:"f2ea";s:44:"static/.svn/text-base/constants.txt.svn-base";s:4:"528b";s:40:"static/.svn/text-base/setup.txt.svn-base";s:4:"fe40";}',
	'suggests' => array(
	),
);

?>