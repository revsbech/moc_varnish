#
# Table structure for table 'tx_mocvarnish_purge_queue'
#
CREATE TABLE tx_mocvarnish_purge_queue (
	`uid` int(11) NOT NULL auto_increment,
	`pid` int(11) DEFAULT '0' NOT NULL,
	`tstamp` int(11) DEFAULT '0' NOT NULL,
	`crdate` int(11) DEFAULT '0' NOT NULL,
	`cruser_id` int(11) DEFAULT '0' NOT NULL,
	`url` varchar(255) DEFAULT '' NOT NULL,
	`domain` varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (`uid`),
	UNIQUE KEY `identifier` (`url`, `domain`),
	KEY parent (`pid`)
) ENGINE=InnoDB;