<?php

$config = unserialize($_EXTCONF);

if ($config['enableClearVarnishCache']) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/TceMainCacheHooks.php:&MOC\MocVarnish\Hooks\TceMainCacheHooks->clearCacheCmd';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/TceMainCacheHooks.php:&MOC\MocVarnish\Hooks\TceMainCacheHooks->clearCacheForListOfUids';

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['MOC\\MocVarnish\\Scheduler\\HandlePurgeEventsTask'] = array(
		'extension' => $_EXTKEY,
		'title' => 'Varnish handle purge events',
		'description' => 'Handle Varnish asynchronous purging events',
		'additionalFields' => 'MOC\\MocVarnish\\Scheduler\\HandlePurgeEventsAdditionalFieldProvider'
	);

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
	                                              //\TYPO3\CMS\Felogin\Controller\FrontendLoginController
}



