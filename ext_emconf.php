<?php

########################################################################
# Extension Manager/Repository config file for ext: "pt_vrepay"
#
# Auto generated 13-02-2009 16:52
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
	'version' => '0.0.1',
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
	'_md5_values_when_last_written' => 'a:26:{s:9:"ChangeLog";s:4:"9569";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"4546";s:17:"ext_localconf.php";s:4:"55d1";s:14:"ext_tables.php";s:4:"3ab0";s:14:"ext_tables.sql";s:4:"c2cc";s:33:"icon_tx_ptvrepay_transactions.gif";s:4:"1e24";s:16:"locallang_db.xml";s:4:"741f";s:7:"tca.php";s:4:"9153";s:21:"doc/.#DevDoc.txt.1.16";s:4:"e380";s:14:"doc/DevDoc.txt";s:4:"65db";s:19:"doc/wizard_form.dat";s:4:"76a6";s:20:"doc/wizard_form.html";s:4:"f87d";s:29:"pi1/class.tx_ptvrepay_pi1.php";s:4:"0ce4";s:17:"pi1/locallang.xml";s:4:"29f4";s:20:"static/constants.txt";s:4:"0d34";s:16:"static/setup.txt";s:4:"fe40";s:43:"res/class.tx_ptvrepay_dialogTransaction.php";s:4:"f0c8";s:29:"res/class.tx_ptvrepay_log.php";s:4:"5839";s:45:"res/class.tx_ptvrepay_transactionAccessor.php";s:4:"380a";s:46:"res/class.tx_ptvrepay_transactionException.php";s:4:"ad05";s:44:"res/class.tx_ptvrepay_transactionRequest.php";s:4:"1589";s:45:"res/class.tx_ptvrepay_transactionResponse.php";s:4:"7fbe";s:29:"res/locallang_res_classes.xml";s:4:"13ad";s:29:"pi2/class.tx_ptvrepay_pi2.php";s:4:"94ae";s:17:"pi2/locallang.xml";s:4:"bedc";}',
	'suggests' => array(
	),
);

?>