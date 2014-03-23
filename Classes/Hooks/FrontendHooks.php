<?php
namespace MOC\MocVarnish\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for clearing cache whenever TYPO3 TCE clears the internal cache
 *
 * @package MOC\MocVarnish\Hooks
 */
class FrontendHooks {

	/**
	 * Send extra header to tell varnish whichpage id this is. Used for banning specific pages.
	 *
	 * @param array $parameters
	 * @param tslib_fe $parent
	 */
	public function sendCacheHeaders(array $parameters, \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $parent) {
		header('TYPO3-Pid: ' . $parent->id);
	}

}