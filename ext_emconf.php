<?php

########################################################################
# Extension Manager/Repository config file for ext "moc_varnish".
#
# Auto generated 05-06-2011 14:22
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'MOC Varnish',
	'description' => 'Extension that provides usefull features when using Varnish with TYPO3, like cache-clearing and automatic ESI.',
	'category' => '',
	'shy' => 0,
	'version' => '1.2.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Jan-Erik Revsbech',
	'author_email' => 'janerik@mocsystems.com',
	'author_company' => 'MOC Systems',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:22:{s:9:"ChangeLog";s:4:"1df2";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"850a";s:8:"ext_icon";s:4:"4660";s:12:"ext_icon.gif";s:4:"4660";s:17:"ext_localconf.php";s:4:"b3b3";s:14:"ext_tables.php";s:4:"fef1";s:14:"ext_tables.sql";s:4:"d41d";s:18:"user_renderInt.php";s:4:"a5aa";s:14:"doc/manual.sxw";s:4:"8fe7";s:19:"doc/wizard_form.dat";s:4:"abce";s:20:"doc/wizard_form.html";s:4:"77a9";s:40:"lib/tx_mocvarnish_tcemain_cachehooks.php";s:4:"c22d";s:18:"lib/URL/Finder.php";s:4:"ade9";s:28:"lib/Varnish/CacheManager.php";s:4:"5975";s:16:"static/setup.txt";s:4:"dc83";s:29:"vcl-examples/esi-forced-5.vcl";s:4:"25da";s:25:"vcl-examples/esi-full.vcl";s:4:"7063";s:38:"vcl-examples/esi-header-controlled.vcl";s:4:"301d";s:25:"vcl-examples/esi-test.vcl";s:4:"1f81";s:33:"xclass/class.ux_tslib_content.php";s:4:"78d0";s:28:"xclass/class.ux_tslib_fe.php";s:4:"3c1e";}',
);

?>