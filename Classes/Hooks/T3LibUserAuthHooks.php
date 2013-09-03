<?php
namespace MOC\MocVarnish\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for writing extra cookie when user is logged in.
 *
 * @package MOC\MocVarnish
 */
class T3LibUserAuthHooks {

	/**
	 * Write new session cookie when user logs in.
	 *
	 * This cookie TYPO3_FE_USER_LOGGED_IN can be used by Varnish to disable or control cache.
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $parent
	 * @return void
	 */
	public function writeLoginSessionCookie($params, \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $parent) {
		$cookieName = 'TYPO3_FE_USER_LOGGED_IN';
		$cookiePath = '/';

		if (GeneralUtility::_GP('logintype') === 'login' && is_array($GLOBALS['TSFE']->fe_user->user)) {
			$settings = $GLOBALS['TYPO3_CONF_VARS']['SYS'];

				// If the cookie lifetime is set, use it:
			$cookieExpire = $parent->isRefreshTimeBasedCookie() ? $GLOBALS['EXEC_TIME'] + $parent->lifetime : 0;

			$cookieDomain = isset($settings['cookieDomain']) ? $settings['cookieDomain'] : '';

				// Use the secure option when the current request is served by a secure connection:
			$cookieSecure = isset($settings['cookieSecure']) ? ((bool) $settings['cookieSecure'] && GeneralUtility::getIndpEnv('TYPO3_SSL')) : FALSE;

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
		} elseif (GeneralUtility::_GP('logintype') === 'logout') {
			setcookie($cookieName, NULL, -1, $cookiePath);
		}
	}

}