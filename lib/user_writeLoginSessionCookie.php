<?php

function user_writeLoginSessionCookie($content,$parent) {
	if(t3lib_div::_GP('logintype') === 'login' && is_array($GLOBALS['TSFE']->fe_user->user)) {
		$settings = $GLOBALS['TYPO3_CONF_VARS']['SYS'];

		$isRefreshTimeBasedCookie = $parent->isRefreshTimeBasedCookie();
			// If the cookie lifetime is set, use it:
		$cookieExpire = ($isRefreshTimeBasedCookie ? $GLOBALS['EXEC_TIME'] + $parent->lifetime : 0);
			// Use the secure option when the current request is served by a secure connection:
		$cookieSecure = (bool) $settings['cookieSecure'] && t3lib_div::getIndpEnv('TYPO3_SSL');
			// Deliver cookies only via HTTP and prevent possible XSS by JavaScript:
		$cookieHttpOnly = (bool) $settings['cookieHttpOnly'];

		$cookiePath ='/';

		setcookie(
			'TYPO3_FE_USER_LOGGED_IN',
			1,
			$cookieExpire,
			$cookiePath,
			$cookieDomain,
			$cookieSecure,
			$cookieHttpOnly
		);
	}
	if(t3lib_div::_GP('logintype') === 'logout') {
		setcookie('TYPO3_FE_USER_LOGGED_IN',NULL,-1,'/');
	}
}