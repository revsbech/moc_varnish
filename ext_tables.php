<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// initialize static extension templates
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'MOC Varnish');

$TCA['tx_mocvarnish_purge_queue'] = array(
	'ctrl' => array(
		'title' => 'Varnish Purge Queue',
		'label' => 'url',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'tx_mocvarnish_purge_queue.png'
	)
);