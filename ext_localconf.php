<?php

$config = unserialize($_EXTCONF);

if ($config['enableClearVarnishCache']) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/TceMainCacheHooks.php:&MOC\MocVarnish\Hooks\TceMainCacheHooks->clearCacheCmd';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/TceMainCacheHooks.php:&MOC\MocVarnish\Hooks\TceMainCacheHooks->clearCacheForListOfUids';

	if (intval($config['event.']['enable_beanstalk']) == 0) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['MOC\\MocVarnish\\Scheduler\\HandlePurgeEventsTask'] = array(
			'extension' => $_EXTKEY,
			'title' => 'Varnish handle purge events',
			'description' => 'Handle Varnish asynchronous purging events',
			'additionalFields' => 'MOC\\MocVarnish\\Scheduler\\HandlePurgeEventsAdditionalFieldProvider'
		);
	} else {
		\MOC\MocVarnish\Event\EventBroker::$useBeanstalk = TRUE;
		\MOC\MocVarnish\Event\EventBroker::$beanstalkServer = $config['event.']['beanstalk_server'];
		\MOC\MocVarnish\Event\EventBroker::$beanstalkTube = $config['event.']['beanstalk_tube'];

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Moc\MocVarnish\Command\PheanstalkWorkerCommandController';
		$pheanstalkClassRoot = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . '/Classes';
		require_once($pheanstalkClassRoot . '/Pheanstalk/ClassLoader.php');
		Pheanstalk_ClassLoader::register($pheanstalkClassRoot);

	}

	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY] = array();
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['synchronousEventHandlers'] = array();
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['aSynchronousEventHandlers'] = array();

	if ($config['event.']['enable_async_pageuid_event']) {
		array_push($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['aSynchronousEventHandlers'], 'MOC\MocVarnish\Event\Handler\PurgePageUidEventHandler');
	} else {
		array_push($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['synchronousEventHandlers'], 'MOC\MocVarnish\Event\Handler\PurgePageUidEventHandler');
	}

	if ($config['event.']['enable_async_url_event']) {
		array_push($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['aSynchronousEventHandlers'], 'MOC\MocVarnish\Event\Handler\PurgeUrlEventHandler');
	} else {
		array_push($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['synchronousEventHandlers'], 'MOC\MocVarnish\Event\Handler\PurgeUrlEventHandler');
	}
}

if ($config['writeUserLoginCookie']) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY] = 'MOC\MocVarnish\Hooks\T3LibUserAuthHooks->writeLoginSessionCookie';
}

if ($config['disableSetCookieWhenNotNeeded']) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication']['className'] = 'MOC\MocVarnish\Frontend\Authentication\FrontendUserAuthentication';
}