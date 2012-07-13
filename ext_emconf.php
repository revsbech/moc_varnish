<?php

########################################################################
# Extension Manager/Repository config file for ext "moc_varnish".
#
# Auto generated 13-07-2012 17:44
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'MOC Varnish',
	'description' => 'Extension that provides useful features when using Varnish with TYPO3, like cache-clearing and automatic ESI.',
	'category' => '',
	'shy' => 0,
	'version' => '1.4.0',
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
	'_md5_values_when_last_written' => 'a:29:{s:9:"ChangeLog";s:4:"1567";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"6fbf";s:12:"ext_icon.gif";s:4:"4660";s:17:"ext_localconf.php";s:4:"e826";s:14:"ext_tables.php";s:4:"fef1";s:14:"ext_tables.sql";s:4:"d41d";s:18:"user_renderInt.php";s:4:"a71b";s:14:"doc/manual.sxw";s:4:"c608";s:19:"doc/wizard_form.dat";s:4:"abce";s:20:"doc/wizard_form.html";s:4:"77a9";s:40:"lib/tx_mocvarnish_tcemain_cachehooks.php";s:4:"097f";s:36:"lib/user_writeLoginSessionCookie.php";s:4:"7c97";s:18:"lib/URL/Finder.php";s:4:"7324";s:28:"lib/Varnish/CacheManager.php";s:4:"4570";s:16:"static/setup.txt";s:4:"24ac";s:29:"vcl-examples/esi-forced-5.vcl";s:4:"da4b";s:25:"vcl-examples/esi-full.vcl";s:4:"2b39";s:38:"vcl-examples/esi-header-controlled.vcl";s:4:"dfe7";s:25:"vcl-examples/esi-test.vcl";s:4:"aa7f";s:27:"vcl-examples/production.vcl";s:4:"57e7";s:37:"xclass/class.ux_tslib_content_4-4.php";s:4:"1328";s:37:"xclass/class.ux_tslib_content_4-5.php";s:4:"5822";s:60:"xclass/class.ux_tslib_content_ContentObjectArrayInternal.php";s:4:"9877";s:51:"xclass/class.ux_tslib_content_PhpScriptInternal.php";s:4:"4d26";s:46:"xclass/class.ux_tslib_content_UserInternal.php";s:4:"4e27";s:28:"xclass/class.ux_tslib_fe.php";s:4:"f16b";s:36:"xclass/class.ux_tslib_feuserauth.php";s:4:"0430";s:34:"xclass/class.ux_tx_felogin_pi1.php";s:4:"e238";}',
	'suggests' => array(
	),
);

?>