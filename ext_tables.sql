/** 
 * ext_tables.sql for extension "pt_vrepay"
 *
 * $Id: ext_tables.sql,v 1.3 2009/02/13 15:46:58 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>
 * @since   2008-12-12
 */ 

#
# Table structure for table 'tx_ptvrepay_transactions'
#
CREATE TABLE tx_ptvrepay_transactions (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	reference_no varchar(20) DEFAULT '' NOT NULL,
    response_secret varchar(32) DEFAULT '' NOT NULL,
    merchant_reference varchar(17) DEFAULT '' NOT NULL,
	amount int(11) DEFAULT '0' NOT NULL,
    request_result text NOT NULL,
    status varchar(20) DEFAULT '' NOT NULL,
	response_params text NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);