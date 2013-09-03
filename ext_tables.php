<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'static/', 'MOC Varnish');

$TCA['tx_mocvarnish_purgeevent_queue'] = array(
	'ctrl' => array(
		'title' => 'Varnish purge eventqueue',
		'label' => 'Purge Event',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'hideTable' => 1,
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/PurgeeventQueue.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/PurgeeventQueue.png'
	)
);