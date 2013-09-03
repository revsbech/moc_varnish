#
# Table structure for table 'tx_mocvarnish_purge_queue'
#
CREATE TABLE tx_mocvarnish_purgeevent_queue (
	`uid` int(11) NOT NULL auto_increment,
	`pid` int(11) DEFAULT '0' NOT NULL,
	`tstamp` int(11) DEFAULT '0' NOT NULL,
	`crdate` int(11) DEFAULT '0' NOT NULL,
	`cruser_id` int(11) DEFAULT '0' NOT NULL,
	`data` blob NOT NULL,
	`handled` int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (`uid`),
	KEY handled_crdate (`handled`, `crdate`),
	KEY parent (`pid`)
) ENGINE=InnoDB;