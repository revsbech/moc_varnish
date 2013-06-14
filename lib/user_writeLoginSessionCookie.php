<?php
function user_writeLoginSessionCookie($content, $parent) {
	$cookieName = 'TYPO3_FE_USER_LOGGED_IN';
	$cookiePath = '/';

	if (t3lib_div::_GP('logintype') === 'login' && is_array($GLOBALS['TSFE']->fe_user->user)) {
		$settings = $GLOBALS['TYPO3_CONF_VARS']['SYS'];

			// If the cookie lifetime is set, use it:
		$cookieExpire = $parent->isRefreshTimeBasedCookie() ? $GLOBALS['EXEC_TIME'] + $parent->lifetime : 0;

		$cookieDomain = isset($settings['cookieDomain']) ? $settings['cookieDomain'] : '';

			// Use the secure option when the current request is served by a secure connection:
		$cookieSecure = isset($settings['cookieSecure']) ? ((bool) $settings['cookieSecure'] && t3lib_div::getIndpEnv('TYPO3_SSL')) : FALSE;

			// Deliver cookies only via HTTP and prevent possible XSS by JavaScript:
		$cookieHttpOnly = isset($settings['cookieHttpOnly']) ? (bool) $settings['cookieHttpOnly'] : FALSE;

		setcookie(
			$cookieName,
			1,
			$cookieExpire,
			$cookiePath,
			$cookieDomain,
			$cookieSecure,
			$cookieHttpOnly
		);
	} elseif (t3lib_div::_GP('logintype') === 'logout') {
		setcookie($cookieName, NULL, -1, $cookiePath);
	}
}