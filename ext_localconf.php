<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

require_once(t3lib_extMgm::extPath($_EXTKEY) . 'lib/tx_mocvarnish_tcemain_cachehooks.php');
$config = unserialize($_EXTCONF);

if ($config['enableClearVarnishCache']) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][] = 'tx_mocvarnish_tcemain_cachehooks->clearCacheForListOfUids';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'tx_mocvarnish_tcemain_cachehooks->clearCacheCmd';
}

if ($config['enableESI']) {
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_fe.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'xclass/class.ux_tslib_fe.php';

		// Apply hooks for the relevant TYPO3 version (classes have changed in TYPO3 4.5)
	if (t3lib_div::int_from_ver(TYPO3_version) < 4005000) {
		$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_content.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'xclass/class.ux_tslib_content_4-4.php';
	} else {
		$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_content.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'xclass/class.ux_tslib_content_4-5.php';
		$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/content/class.tslib_content_contentobjectarrayinternal.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'xclass/class.ux_tslib_content_ContentObjectArrayInternal.php';
		$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/content/class.tslib_content_phpscriptinternal.php']          = t3lib_extMgm::extPath($_EXTKEY) . 'xclass/class.ux_tslib_content_PhpScriptInternal.php';
		$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/content/class.tslib_content_userinternal.php']               = t3lib_extMgm::extPath($_EXTKEY) . 'xclass/class.ux_tslib_content_UserInternal.php';
	}
}

if ($config['writeUserLoginCookie']) {
	require_once(t3lib_extMgm::extPath($_EXTKEY) . 'lib/user_writeLoginSessionCookie.php');
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'user_writeLoginSessionCookie';
}

if ($config['disableSetCookieWhenNotNeeded']) {
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_feuserauth.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'xclass/class.ux_tslib_feuserauth.php';
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin/pi1/class.tx_felogin_pi1.php'] = t3lib_extMgm::extPath($_EXTKEY) . 'xclass/class.ux_tx_felogin_pi1.php';
}

if ($config['schedulerPurgeQueue']) {
	if (TYPO3_MODE === 'BE') {
		require_once(t3lib_extMgm::extPath($_EXTKEY) . 'lib/tx_mocvarnish_scheduler_purgequeue.php');
		require_once(t3lib_extMgm::extPath($_EXTKEY) . 'lib/tx_mocvarnish_scheduler_additionalfieldprovider.php');
	}
	$TYPO3_CONF_VARS['SC_OPTIONS']['scheduler']['tasks']['tx_mocvarnish_scheduler_purgequeue'] = array(
		'extension' => $_EXTKEY,
		'title' => 'Varnish Purge Queue',
		'additionalFields' => 'tx_mocvarnish_scheduler_additionalfieldprovider'
	);
}