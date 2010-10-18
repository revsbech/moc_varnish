<?php 
require_once(t3lib_extMgm::extPath($_EXTKEY).'lib/tx_mocvarnish_tcemain_cachehooks.php');
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][] = 'tx_mocvarnish_tcemain_cachehooks->clearCacheForListOfUids';

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);


if($confArr['enableESI']) {
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_content.php'] = t3lib_extMgm::extPath($_EXTKEY).'xclass/class.ux_tslib_content.php';
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_fe.php'] = t3lib_extMgm::extPath($_EXTKEY).'xclass/class.ux_tslib_fe.php';
}
