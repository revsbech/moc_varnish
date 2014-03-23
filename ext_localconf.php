<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$config = unserialize($_EXTCONF);

if ($config['enableClearVarnishCache']) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/TceMainCacheHooks.php:&MOC\MocVarnish\Hooks\TceMainCacheHooks->clearCacheCmd';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/TceMainCacheHooks.php:&MOC\MocVarnish\Hooks\TceMainCacheHooks->clearCacheForListOfUids';

	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/TceMainCacheHooks.php:&MOC\MocVarnish\Hooks\FrontendHooks->sendCacheHeaders';
	//'tx_varnish_hooks_tslib_fe->sendHeader';

	/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

	if ($config['enable_async_purge'] && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('moc_message_queue')) {
		$signalSlotDispatcher->connect(
			'MOC\MocMessageQueue\Command\QueueWorkerCommandController',
			'messageReceived',
			'MOC\MocVarnish\Slots\MessageQueue',
			'handleAsynchronousMessage'
		);
	} else {
		$signalSlotDispatcher->connect(
			'MOC\MocVarnish\CacheManager',
			'clearCacheForUrl',
			'MOC\MocVarnish\Slots\CacheManager',
			'clearCacheForUrl'
		);
		$signalSlotDispatcher->connect(
			'MOC\MocVarnish\CacheManager',
			'clearCacheForPageUid',
			'MOC\MocVarnish\Slots\CacheManager',
			'clearCacheForPageUid'
		);
		$signalSlotDispatcher->connect(
			'MOC\MocVarnish\CacheManager',
			'clearAllCache',
			'MOC\MocVarnish\Slots\CacheManager',
			'clearAllCache'
		);
	}
}

if ($config['writeUserLoginCookie']) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY] = 'MOC\MocVarnish\Hooks\T3LibUserAuthHooks->writeLoginSessionCookie';
}

if ($config['disableSetCookieWhenNotNeeded']) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication']['className'] = 'MOC\MocVarnish\Frontend\Authentication\FrontendUserAuthentication';
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Felogin\Controller\FrontendLoginController']['className'] = 'MOC\MocVarnish\Frontend\FrontendLoginController';
}